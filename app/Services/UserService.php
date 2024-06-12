<?php

namespace App\Services;

use App\DTO\UserServiceResponse;
use App\Enums\ActivationActionEnums;
use App\Enums\WorkflowEnums;
use App\Interfaces\SwitchAPIInterface;
use App\Models\AlmaUser;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Models\SlskeyUser;
use Carbon\Carbon;

class UserService
{
    protected $switchApiService;

    protected $mailService;

    /**
     * UserService constructor.
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
     * @param AlmaUser|null $almaUser
     * @param string|null $webhookActivationMail
     * @return UserServiceResponse
     */
    public function activateSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $author = null,
        string $trigger,
        ?AlmaUser $almaUser = null,
        ?string $webhookActivationMail = null,
    ): UserServiceResponse {
        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();
        if (!$slskeyGroup) {
            return new UserServiceResponse(false, 'No SLSKey Group found');
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

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'primary_id' => $primaryId,
            'action' => $action,
            'author' => $author,
            'trigger' => $trigger,
            'success' => false, // set it true after success,
            'created_at' => now(),
        ]);

        // Check if user is blocked
        if ($slskeyUser && $slskeyUser->isBlocked($slskeyGroup->id)) {
            return $this->logAndReturnError('user_blocked', $slskeyHistory);
        }

        // Check if primaryId is edu-ID.
        if (!SlskeyUser::isPrimaryIdEduId($primaryId)) {
            return $this->logAndReturnError('no_edu_id', $slskeyHistory);
        }

        // Get SWITCH groups
        /* FIXME: remove comment
        if ($slskeyGroup->switchGroups->count() === 0) {
            return $this->logAndReturnError('no_switch_group', $slskeyHistory);
        }
        */

        // Check if Activation Mail is defined when configured
        if (!$slskeyGroup->checkActivationMailDefinedIfSendActivationMailIsTrue()) {
            return $this->logAndReturnError('no_notify_mail_content', $slskeyHistory);
        }

        // Activate User via SWITCH API
        $activatedGroups = [];
        try {
            $successMessage = '';
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                $this->switchApiService->activatePublisherForUser($primaryId, $switchGroup->switch_group_id);
                // Add name and comma if its not the last group
                $successMessage .= $switchGroup->name . ($switchGroup !== $slskeyGroup->switchGroups->last() ? ', ' : '');
                $activatedGroups[] = $switchGroup->switch_group_id;
            }
        } catch (\Exception $e) {
            $slskeyHistory->setErrorMessage($e->getMessage());
            // Fix Danger of Inconsitency, e.g. when first group is activated and the second failed
            foreach ($activatedGroups as $activatedGroup) {
                try {
                    $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $activatedGroup);
                } catch (\Exception $e) {
                }
            }

            return new UserServiceResponse(false, $e->getMessage());
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

        // Set SLSKey History to Successful
        $slskeyHistory->setSuccess(true);
        $slskeyHistory->setSlskeyUserId($slskeyUser->id);

        // Send notify email to user, if group has enabled email feature
        if (
            $slskeyGroup->send_activation_mail &&
            $almaUser
        ) {
            // Create History
            $slskeyHistory = SlskeyHistory::create([
                'slskey_user_id' => $slskeyUser?->id,
                'slskey_group_id' => $slskeyGroup->id,
                'primary_id' => $primaryId,
                'action' => ActivationActionEnums::NOTIFIED,
                'author' => null,
                'trigger' => $trigger,
                'success' => false, // set it true after success
            ]);
            $sent = $this->mailService->sendNotifyUserActivationMail($slskeyGroup, $almaUser, $webhookActivationMail);
            if (!$sent) {
                $slskeyHistory->setErrorMessage('Email failed');
            } else {
                $slskeyHistory->setSuccess(true);
            }
        }

        $messageCode = $action === ActivationActionEnums::ACTIVATED ? 'user_activated' : ($action === ActivationActionEnums::EXTENDED ? 'user_extended' : 'user_reactivated');

        // $message = __("flashMessages.$messageCode") . ': ' . $successMessage;
        $message = __("flashMessages.$messageCode");

        return new UserServiceResponse(true, $message);
    }

    /**
     * Deactivate SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @param string|null $author
     * @param string $trigger
     * @return UserServiceResponse
     */
    public function deactivateSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $remark,
        ?string $author,
        string $trigger,
    ): UserServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'primary_id' => $primaryId,
            'action' => ActivationActionEnums::DEACTIVATED,
            'author' => $author,
            'trigger' => $trigger,
            'success' => false, // set it true after success
        ]);

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError('no_user', $slskeyHistory);
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            // There is no activation
            return $this->logAndReturnError('no_activation', $slskeyHistory);
        }

        // Deactivate User via SWITCH API
        /* FIXME: remove comment
        if ($slskeyGroup->switchGroups->count() === 0) {
            return $this->logAndReturnError('no_switch_group', $slskeyHistory);
        }
        */

        try {
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                // If user has different activation for same SWITCH Group, dont remove user from the SWITCH group
                if ($slskeyUser->hasActiveActivationForSwitchGroupViaDifferentGroup($switchGroup->switch_group_id, $slskeyGroup->id)) {
                    continue;
                }
                $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $switchGroup->switch_group_id);
            }
        } catch (\Exception $e) {
            $slskeyHistory->setErrorMessage($e->getMessage());

            return new UserServiceResponse(false, $e->getMessage());
        }

        // Update SLSKey Activation
        $activation->setDeactivated($remark);

        // Set SLSKey History to Successful
        $slskeyHistory->setSuccess(true);

        return new UserServiceResponse(true, __("flashMessages.user_deactivated"));
    }

    /**
     * Block SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @param string|null $author
     * @param string $trigger
     * @return UserServiceResponse
     */
    public function blockSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $remark,
        ?string $author,
        string $trigger,
    ): UserServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Get SLSKey Activation
        $activation = $slskeyUser ? SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)
            ->where('slskey_group_id', '=', $slskeyGroup->id)->first() : null;

        // Get Action for History
        $action = $activation && $activation->activated ? ActivationActionEnums::BLOCKED_ACTIVE : ActivationActionEnums::BLOCKED_INACTIVE;

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'primary_id' => $primaryId,
            'action' => $action,
            'author' => $author,
            'trigger' => $trigger,
            'success' => false, // set it true after success
        ]);

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError('no_user', $slskeyHistory);
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError('no_activation', $slskeyHistory);
        }

        // Deactivate User via SWITCH API
        if ($slskeyGroup->switchGroups->count() === 0) {
            return $this->logAndReturnError('no_switch_group', $slskeyHistory);
        }

        try {
            foreach ($slskeyGroup->switchGroups as $switchGroup) {
                $this->switchApiService->removeUserFromGroupAndVerify($primaryId, $switchGroup->switch_group_id);
            }
        } catch (\Exception $e) {
            $slskeyHistory->setErrorMessage($e->getMessage());

            return new UserServiceResponse(false, $e->getMessage());
        }

        // Update SLSKey Activation
        $activation->setBlocked($remark);

        // Set History succesfull
        $slskeyHistory->setSuccess(true);

        return new UserServiceResponse(true, __("flashMessages.user_blocked"));
    }

    /**
     * Unblock SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @param string|null $author
     * @param string $trigger
     * @return UserServiceResponse
     */
    public function unblockSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $remark,
        ?string $author,
        string $trigger,
    ): UserServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'primary_id' => $primaryId,
            'action' => ActivationActionEnums::UNBLOCKED,
            'author' => $author,
            'trigger' => $trigger,
            'success' => false, // set it true after success
        ]);

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError('no_user', $slskeyHistory);
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError('no_activation', $slskeyHistory);
        }

        // Update SLSKey Activation
        $activation->setUnblocked($remark);

        // Set History succesfull
        $slskeyHistory->setSuccess(true);

        return new UserServiceResponse(true, __("flashMessages.user_unblocked"));
    }

    /**
     * Disable Expiration Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $author
     * @param string $trigger
     * @return UserServiceResponse
     */
    public function disableExpirationSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $author,
        string $trigger,
    ): UserServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'primary_id' => $primaryId,
            'action' => ActivationActionEnums::EXPIRATION_DISABLED,
            'author' => $author,
            'trigger' => $trigger,
            'success' => false, // set it true after success
        ]);

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError('no_user', $slskeyHistory);
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError('no_activation', $slskeyHistory);
        }

        // Update SLSKey Activation
        $activation->setExpirationDisabled();

        // Set History succesfull
        $slskeyHistory->setSuccess(true);

        return new UserServiceResponse(true, __("flashMessages.user_expiration_disabled"));
    }

    /**
     * Enable Expiration Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $author
     * @param string $trigger
     * @return UserServiceResponse
     */
    public function enableExpirationSlskeyUser(
        string $primaryId,
        string $slskeyCode,
        ?string $author,
        string $trigger,
    ): UserServiceResponse {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser?->id,
            'slskey_group_id' => $slskeyGroup->id,
            'primary_id' => $primaryId,
            'action' => ActivationActionEnums::EXPIRATION_ENABLED,
            'author' => $author,
            'trigger' => $trigger,
            'success' => false, // set it true after success
        ]);

        // Check if user exists
        if (!$slskeyUser) {
            return $this->logAndReturnError('no_user', $slskeyHistory);
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return $this->logAndReturnError('no_activation', $slskeyHistory);
        }

        // Update SLSKey Activation
        $newExpirationDate = ($slskeyGroup->workflow === WorkflowEnums::WEBHOOK && !$slskeyGroup->webhook_mail_activation ?
            null :
            now()->addDays($slskeyGroup->days_activation_duration));
        $activation->setExpirationEnabled($newExpirationDate);

        // Set History succesfull
        $slskeyHistory->setSuccess(true);

        return new UserServiceResponse(true, __("flashMessages.user_expiration_enabled"));
    }

    /**
     * Set Activation Remark for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param string|null $remark
     * @return UserServiceResponse
     */
    public function setActivationRemark(string $primaryId, string $slskeyCode, ?string $remark): UserServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return new UserServiceResponse(false, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return new UserServiceResponse(false, 'no_activation');
        }

        // Update SLSKey Activation
        if ($remark) {
            $activation->setRemark($remark);
        } else {
            $activation->removeRemark();
        }

        return new UserServiceResponse(true, __("flashMessages.remark_set"));
    }

    /**
     * Update Activation Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param Carbon $activationDate
     * @return UserServiceResponse
     */
    public function updateActivationDate(string $primaryId, string $slskeyCode, Carbon $activationDate): UserServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return new UserServiceResponse(false, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return new UserServiceResponse(false, 'no_activation');
        }

        // Update SLSKey Activation
        if ($activationDate) {
            $activation->setActivationDate($activationDate);
        }

        return new UserServiceResponse(true, __("flashMessages.activation_date_set"));
    }

    /**
     * Update Expiration Date for SLSKey User.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @param Carbon $expirationDate
     * @return UserServiceResponse
     */
    public function updateExpirationDate(string $primaryId, string $slskeyCode, Carbon $expirationDate): UserServiceResponse
    {
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();

        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();

        // Check if user exists
        if (!$slskeyUser) {
            return new UserServiceResponse(false, 'no_user');
        }

        // Get Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return new UserServiceResponse(false, 'no_activation');
        }

        // Update SLSKey Activation
        if ($expirationDate) {
            $activation->setExpirationDate($expirationDate);
        }

        return new UserServiceResponse(true, __("flashMessages.expiration_date_set"));
    }

    /**
     * Verify if SLSKey User is activated in all SWITCH groups.
     *
     * @param string $primaryId
     * @param string $slskeyCode
     * @return UserServiceResponse
     */
    public function verifySwitchStatusSlskeyUser(string $primaryId, string $slskeyCode): UserServiceResponse
    {
        // Get SLSKey User
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();
        if (!$slskeyUser) {
            return new UserServiceResponse(false, 'No SLSKey User found');
        }

        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();
        if (!$slskeyGroup) {
            return new UserServiceResponse(false, 'No SLSKey Group found');
        }

        // Get SLSKey Activation
        $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)
            ->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        if (!$activation) {
            return new UserServiceResponse(false, 'No SLSKey Activation found');
        }

        // Check if slskeygroup has switchgroups
        $groupIds = $slskeyGroup->getSwitchGroupIds();
        if (!$groupIds) {
            return new UserServiceResponse(false, 'No Switch Groups found for this SLSKey group');
        }

        try {
            $isActive = $this->switchApiService->userIsOnAllGroups($primaryId, $groupIds);
        } catch (\Exception $e) {
            return new UserServiceResponse(false, $e->getMessage());
        }

        if (!$isActive) {
            return new UserServiceResponse(false, 'User is not activated in all SWITCH groups.');
        }

        return new UserServiceResponse(true, 'User is activated in all SWITCH groups.');
    }

    /**
     * Log error and return response.
     *
     * @param string $errorMessage
     * @param SlskeyHistory $slskeyHistory
     * @return UserServiceResponse
     */
    private function logAndReturnError(string $errorMessage, SlskeyHistory $slskeyHistory): UserServiceResponse
    {
        $logMessage = __('flashMessages.errors.' . $errorMessage);
        $slskeyHistory->setErrorMessage($logMessage);

        // return new UserServiceResponse(false, $errorMessage);
        return new UserServiceResponse(false, $logMessage);
    }
}
