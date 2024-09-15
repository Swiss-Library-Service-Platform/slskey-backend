<?php

namespace App\Services;

use App\DTO\ActivationServiceResponse;
use App\Enums\ActivationActionEnums;
use App\Enums\WorkflowEnums;
use App\Interfaces\SwitchAPIInterface;
use App\Models\AlmaUser;
use App\Models\LogActivationFails;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Models\SlskeyUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Enums\TriggerEnums;


class ActivationService
{
    protected $switchApiService;

    protected $mailService;

    /**
     * ActivationService constructor.
     *
     * @param SwitchAPIInterface $switchApiService
     * @param MailService $mailService
     */
    public function __construct(SwitchAPIInterface $switchApiService, MailService $mailService)
    {
        $this->switchApiService = $switchApiService;
        $this->mailService = $mailService;
    }

    /**
     * Activate SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $author
     * @param string $trigger
     * @param AlmaUser|null $almaUser -> for setting user details and sending activation mail
     * @param string|null $webhookActivationMail
     * @return ActivationServiceResponse
     */
    public function activateSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        string $trigger,
        ?string $author = null,
        ?AlmaUser $almaUser = null,
        ?string $webhookActivationMail = null,
        ?Carbon $historicActivationDate = null,
    ): ActivationServiceResponse {
        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();
        if (!$slskeyGroup) {
            return new ActivationServiceResponse(false, 'No SLSKey Group found');
        }

        // Get SLSKey Activation
        $activation = null;

        if ($slskeyUser) {
            $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)
                ->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        }

        // Get Action for History
        $action = $activation ?
            ($activation->activated ? ActivationActionEnums::EXTENDED : ActivationActionEnums::REACTIVATED)
            : ActivationActionEnums::ACTIVATED;

        // Check if user is blocked
        if ($slskeyUser && $slskeyUser->isBlocked($slskeyGroup->id)) {
            return $this->logAndReturnError($primaryId, $action, 'user_blocked');
        }

        // Check if primaryId is edu-ID.
        if (!SlskeyUser::isPrimaryIdEduId($primaryId)) {
            return $this->logAndReturnError($primaryId, $action, 'no_edu_id');
        }

        // Get SWITCH groups 
        if ($slskeyGroup->switchGroups->count() === 0) {
            return $this->logAndReturnError($primaryId, $action, 'no_switch_group');
        }

        // Check if Activation Mail is defined when configured
        if (!$slskeyGroup->checkActivationMailDefinedIfSendActivationMailIsTrue()) {
            return $this->logAndReturnError($primaryId, $action, 'no_notify_mail_content');
        }

        // Activate User via SWITCH API
        $activatedGroups = [];
        try {
            $successMessage = $this->activateSwitchGroups($primaryId, $slskeyGroup, $activatedGroups);
        } catch (\Exception $e) {
            $this->rollbackActivatedGroups($primaryId, $activatedGroups);

            return $this->logAndReturnError($primaryId, $action, 'switch_api_error', $e->getMessage());
        }

        // Create User
        if (!$slskeyUser) {
            $slskeyUser = SlskeyUser::create([
                'primary_id' => $primaryId,
            ]);
        }

        // Update SLSKey User
        if ($almaUser) {
            $slskeyUser->updateUserDetails($almaUser);
        }

        // Get Activation
        $activationDate = now();
        $expirationDate = (
            $slskeyGroup->workflow === WorkflowEnums::WEBHOOK && !$slskeyGroup->webhook_mail_activation ?
            null : // No Expiration when normal Webhook apprach
            now()->addDays($slskeyGroup->days_activation_duration) // Expiration Date when Email Domain Activation
        );

        if (!$activation) {
            // Create SLSKey Activation
            $activation = SlskeyActivation::create([
                'slskey_user_id' => $slskeyUser->id,
                'slskey_group_id' => $slskeyGroup->id,
                'activated' => true,
                'activation_date' => $activationDate,
                'expiration_date' => $expirationDate,
                'deactivation_date' => null,
                'blocked' => false,
                'blocked_date' => null,
                'remark' => null,
                'webhook_activation_mail' => $webhookActivationMail,
            ]);
        } else {
            // Update SLSKey Activation
            $activation->setActivated($activationDate, $expirationDate);
            if ($webhookActivationMail) {
                $activation->setWebhookActivationMail($webhookActivationMail);
            }
        }

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => $action,
            'author' => $author,
            'trigger' => $trigger,
            'created_at' => $historicActivationDate ?? now(),
        ]);

        // Send notify email to user, if group has enabled email feature
        /*
        //  FIXME: dont notify users when importing MBA
        if (
            $slskeyGroup->send_activation_mail &&
            $almaUser
        ) {
            $sent = $this->mailService->sendNotifyUserActivationMail($slskeyGroup, $almaUser, $activation);
            SlskeyHistory::create([
                'slskey_user_id' => $activation->slskey_user_id,
                'slskey_group_id' => $slskeyGroup->id,
                'action' => ActivationActionEnums::NOTIFIED,
                'author' => null,
                'trigger' => TriggerEnums::SYSTEM,
            ]);
        }
        */
        
        
        $messageCode = $action === ActivationActionEnums::ACTIVATED ? 'user_activated' : ($action === ActivationActionEnums::EXTENDED ? 'user_extended' : 'user_reactivated');

        // $message = __("flashMessages.$messageCode") . ': ' . $successMessage;
        $message = __("flashMessages.$messageCode");

        return new ActivationServiceResponse(true, $message);
    }

    /**
     * Deactivate SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @param string|null $author
     * @param string $trigger
     * @return ActivationServiceResponse
     */
    public function deactivateSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $remark,
        ?string $author,
        string $trigger,
    ): ActivationServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::DEACTIVATED, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            // There is no activation
            return $this->logAndReturnError($primaryId, ActivationActionEnums::DEACTIVATED, 'no_activation');
        }

        // Deactivate User via SWITCH API
        if ($slskeyGroup->switchGroups->count() === 0) {
            return $this->logAndReturnError($primaryId, $action, 'no_switch_group');
        }

        try {
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                // If user has different activation for same SWITCH Group, dont remove user from the SWITCH group
                if ($slskeyUser->hasActiveActivationForSwitchGroupViaDifferentGroup($switchGroup->switch_group_id, $slskeyGroup->id)) {
                    continue;
                }
                $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $switchGroup->switch_group_id);
            }
        } catch (\Exception $e) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::DEACTIVATED, 'switch_api_error', $e->getMessage());
        }

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => ActivationActionEnums::DEACTIVATED,
            'author' => $author,
            'trigger' => $trigger,
        ]);

        // Update SLSKey Activation
        $activation->setDeactivated($remark);

        return new ActivationServiceResponse(true, __("flashMessages.user_deactivated"));
    }

    /**
     * Block SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @param string|null $author
     * @param string $trigger
     * @return ActivationServiceResponse
     */
    public function blockSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $remark,
        ?string $author,
        string $trigger,
    ): ActivationServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Get SLSKey Activation
        $activation = $slskeyUser ? SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)
            ->where('slskey_group_id', '=', $slskeyGroup->id)->first() : null;

        // Get Action for History
        $action = $activation && $activation->activated ? ActivationActionEnums::BLOCKED_ACTIVE : ActivationActionEnums::BLOCKED_INACTIVE;

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, $action, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, $action, 'no_activation');
        }

        // Deactivate User via SWITCH API
        if ($slskeyGroup->switchGroups->count() === 0) {
            return $this->logAndReturnError($primaryId, $action, 'no_switch_group');
        }

        try {
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $switchGroup->switch_group_id);
            }
        } catch (\Exception $e) {
            return $this->logAndReturnError($primaryId, $action, 'switch_api_error', $e->getMessage());
        }

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => $action,
            'author' => $author,
            'trigger' => $trigger,
        ]);

        // Update SLSKey Activation
        $activation->setBlocked($remark);

        return new ActivationServiceResponse(true, __("flashMessages.user_blocked"));
    }

    /**
     * Unblock SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @param string|null $author
     * @param string $trigger
     * @return ActivationServiceResponse
     */
    public function unblockSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $remark,
        ?string $author,
        string $trigger,
    ): ActivationServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UNBLOCKED, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UNBLOCKED, 'no_activation');
        }

        // Update SLSKey Activation
        $activation->setUnblocked($remark);

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => ActivationActionEnums::UNBLOCKED,
            'author' => $author,
            'trigger' => $trigger,
        ]);

        return new ActivationServiceResponse(true, __("flashMessages.user_unblocked"));
    }

    /**
     * Disable Expiration Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $author
     * @param string $trigger
     * @return ActivationServiceResponse
     */
    public function disableExpirationSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $author,
        string $trigger,
    ): ActivationServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UNBLOCKED, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UNBLOCKED, 'no_activation');
        }

        // Update SLSKey Activation
        $activation->setExpirationDisabled();

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => ActivationActionEnums::EXPIRATION_DISABLED,
            'author' => $author,
            'trigger' => $trigger,
        ]);

        return new ActivationServiceResponse(true, __("flashMessages.user_expiration_disabled"));
    }

    /**
     * Enable Expiration Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $author
     * @param string $trigger
     * @return ActivationServiceResponse
     */
    public function enableExpirationSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $author,
        string $trigger,
    ): ActivationServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UNBLOCKED, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UNBLOCKED, 'no_activation');
        }

        // Update SLSKey Activation
        $newExpirationDate = ($slskeyGroup->workflow === WorkflowEnums::WEBHOOK && !$slskeyGroup->webhook_mail_activation ?
            null :
            now()->addDays($slskeyGroup->days_activation_duration));
        $activation->setExpirationEnabled($newExpirationDate);

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => ActivationActionEnums::EXPIRATION_ENABLED,
            'author' => $author,
            'trigger' => $trigger,
        ]);

        return new ActivationServiceResponse(true, __("flashMessages.user_expiration_enabled"));
    }

    /**
     * Set Activation Remark for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @return ActivationServiceResponse
     */
    public function setActivationRemark(string $primaryId, string $slskeyCode, ?string $remark): ActivationServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::REMARK_UPDATED, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::REMARK_UPDATED, 'no_activation');
        }

        // Update SLSKey Activation
        if ($remark) {
            $activation->setRemark($remark);
        } else {
            $activation->removeRemark();
        }

        return new ActivationServiceResponse(true, __("flashMessages.remark_set"));
    }

    /**
     * Set activation member educational institution for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param bool $memberEducationalInstitution
     * @return ActivationServiceResponse
     */
    public function setActivationMemberEducationalInstitution(string $primaryId, string $slskeyCode, bool $memberEducationalInstitution): ActivationServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::SET_MEMBER_EDUCATION, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::SET_MEMBER_EDUCATION, 'no_activation');
        }

        // Update SLSKey Activation
        $activation->setMemberEducationalInstitution($memberEducationalInstitution);

        return new ActivationServiceResponse(true, __("flashMessages.user_member_educational_institution_changed"));
    }

    /**
     * Update Activation Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param Carbon $activationDate
     * @return ActivationServiceResponse
     */
    public function updateActivationDate(string $primaryId, string $slskeyCode, Carbon $activationDate): ActivationServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UPDATE_ACTIVATION_DATE, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UPDATE_ACTIVATION_DATE, 'no_activation');
        }

        // Update SLSKey Activation
        if ($activationDate) {
            $activation->setActivationDate($activationDate);
        }

        return new ActivationServiceResponse(true, __("flashMessages.activation_date_set"));
    }

    /**
     * Update Expiration Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param Carbon $expirationDate
     * @return ActivationServiceResponse
     */
    public function updateExpirationDate(string $primaryId, string $slskeyCode, Carbon $expirationDate): ActivationServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UPDATE_EXPIRATION_DATE, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::UPDATE_EXPIRATION_DATE, 'no_activation');
        }

        // Update SLSKey Activation
        if ($expirationDate) {
            $activation->setExpirationDate($expirationDate);
        }

        return new ActivationServiceResponse(true, __("flashMessages.expiration_date_set"));
    }

    /**
     * Verify if SLSKey User is activated in all SWITCH groups.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @return ActivationServiceResponse
     */
    public function verifySwitchStatusSlskeyUser(string $primaryId, string $slskeyCode): ActivationServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();
        if (!$slskeyGroup) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::VERIFY_SWITCH_STATUS, 'no_slskey_group');
        }

        // Check if slskeygroup has switchgroups
        $groupIds = $slskeyGroup->getSwitchGroupIds();
        if (!$groupIds) {
            return $this->logAndReturnError($primaryId, ActivationActionEnums::VERIFY_SWITCH_STATUS, 'no_switch_group');
        }

        try {
            $isActive = $this->switchApiService->userIsOnAllGroups($primaryId, $groupIds);
        } catch (\Exception $e) {
            return new ActivationServiceResponse(false, $e->getMessage());
        }

        if (!$isActive) {
            return new ActivationServiceResponse(false, 'User is not activated in all SWITCH groups.');
        }

        return new ActivationServiceResponse(true, 'User is activated in all SWITCH groups.');
    }

    /**
     * Activate SWITCH groups for user.
     *
     * @param string $primaryId
     * @param SlskeyGroup $slskeyGroup
     * @param array $activatedGroups
     * @return string
     */
    protected function activateSwitchGroups($primaryId, $slskeyGroup, &$activatedGroups)
    {
        $successMessage = '';
        foreach ($slskeyGroup->switchGroups as $switchGroup) {
            $this->switchApiService->activatePublisherForUser($primaryId, $switchGroup->switch_group_id);
            $successMessage .= $switchGroup->name . ($switchGroup !== $slskeyGroup->switchGroups->last() ? ', ' : '');
            $activatedGroups[] = $switchGroup->switch_group_id;
        }

        return $successMessage;
    }

    /**
     * Rollback activated SWITCH groups for user.
     *
     * @param string $primaryId
     * @param array $activatedGroups
     */
    protected function rollbackActivatedGroups($primaryId, $activatedGroups)
    {
        foreach ($activatedGroups as $activatedGroup) {
            try {
                $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $activatedGroup);
            } catch (\Exception $e) {
                // Log the rollback error if necessary
            }
        }
    }

    /**
     * Log error and return response.
     *
     * @param string $primaryId
     * @param string $action
     * @param string $errorMessage
     * @param string|null $errorAdditionalMessage
     * @return ActivationServiceResponse
     */
    private function logAndReturnError(string $primaryId, string $action, string $errorMessage, string $errorAdditionalMessage = null): ActivationServiceResponse
    {
        $flashMessage = __('flashMessages.errors.activations.' . $errorMessage);
        $logMessage = $errorMessage;
        if ($errorAdditionalMessage) {
            $flashMessage .= ': ' . $errorAdditionalMessage;
            $logMessage .= ': ' . $errorAdditionalMessage;
        }
        LogActivationFails::create([
            'primary_id' => $primaryId,
            'action' => $action,
            'message' => $logMessage,
            'author' => Auth::user()?->user_identifier,
        ]);

        // return new ActivationServiceResponse(false, $errorMessage);
        return new ActivationServiceResponse(false, $flashMessage);
    }
}
