<?php

namespace App\Jobs;

use App\Enums\TriggerEnums;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Events\DataImportProgressEvent;
use App\Helpers\WebhookMailActivation\WebhookMailActivationHelper;
use App\Interfaces\AlmaAPIInterface;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Models\SlskeyActivation;
use App\Models\SlskeyHistory;
use App\Enums\ActivationActionEnums;
use App\Services\API\AlmaAPIService;
use App\Services\ActivationService;
use Carbon\Carbon;

/*
    ATTENTION: if any changes are made to this file, please restart supervisor service on the productive/stating environemnt
    sudo systemctl restart supervisor
*/
class ImportCsvJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $importRows;
    protected $testRun;
    protected $withoutExternalApis;
    protected $checkIsActive;
    protected $setHistoryActivationDate;

    protected $activationService;
    protected $almaApiService;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 86400; // 24 hours

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($importRows, $testRun, $withoutExternalApis, $checkIsActive, $setHistoryActivationDate)
    {
        $this->importRows = $importRows;
        $this->testRun = $testRun;
        $this->withoutExternalApis = $withoutExternalApis;
        $this->checkIsActive = $checkIsActive;
        $this->setHistoryActivationDate = $setHistoryActivationDate;
    }

    /**
     * Handle the job
     * Inject dependencies
     *
     * @param ActivationService $activationService
     * @param AlmaApiService $almaApiService
     */
    public function handle(ActivationService $activationService, AlmaAPIInterface $almaApiService)
    {
        $this->activationService = $activationService;
        $this->almaApiService = $almaApiService;

        $currentRow = 0;

        foreach ($this->importRows as $row) {
            if (Cache::get('is_import_cancelled')) {
                break;
            }

            $result = $this->processImportRow($row, $this->testRun, $this->withoutExternalApis, $this->checkIsActive, $this->setHistoryActivationDate);

            event(new DataImportProgressEvent(
                $currentRow,
                $row['primary_id'],
                $row['slskey_code'],
                $result['success'],
                $result['message'],
                $result['isActive'] ?? null,
                $result['isVerified'] ?? null
            ));

            $currentRow++;
        }
    }

    /**
     * Process the import row (same logic as your original processImportRow method)
     */
    private function processImportRow(array $row, bool $testRun, bool $withoutExternalApis, bool $checkIsActive, bool $setHistoryActivationDate): array
    {
        // Get slskey group
        $slskeyGroup = SlskeyGroup::where('slskey_code', $row['slskey_code'])->first();

        if (!$slskeyGroup) {
            return ['success' => false, 'message' => 'SlskeyGroup not found', 'isActive' => false];
        }

         // Check if we want to set Activation or Expiration Date
        try {
            $activationDate = $row['activation_date'] && $row['activation_date'] != 'NULL' ? Carbon::parse($row['activation_date']) : null;
            $expirationDate = $row['expiration_date'] && $row['expiration_date'] != 'NULL' ? Carbon::parse($row['expiration_date']) : null;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Invalid date format: {$e->getMessage()}", 'isActive' => false];
        }

        // Activate without external APIs
        if ($withoutExternalApis) {
            return $this->activateWithoutExternalApis(
                $row['primary_id'],
                $row['firstname'],
                $row['lastname'],
                $activationDate,
                $expirationDate,
                $slskeyGroup->slskey_code,
                $testRun
            );
        }

        $isActive = null;
        if ($checkIsActive) {
            try {
                $response = $this->activationService->verifySwitchStatusSlskeyUser($row['primary_id'], $slskeyGroup->slskey_code);
                $isActive = $response->success;
                if (! $isActive) {
                    return ['success' => false, 'message' => $response->message, 'isActive' => false];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage(), 'isActive' => false];
            }
        }

        // Check Alma user
        $almaServiceResponse = $this->almaApiService->getUserFromSingleIz($row['primary_id'], $slskeyGroup->alma_iz);
        if (! $almaServiceResponse->success) {
            return [
                'success' => false,
                'message' => $almaServiceResponse->errorText,
                'isActive' => $isActive
            ];
        }
        $almaUser = $almaServiceResponse->almaUser;

        // Check for custom verification
        $userIsVerified = $slskeyGroup->checkCustomVerificationForUser($almaUser);
        if (!$userIsVerified) {
            return [
                'success' => false,
                'message' => 'User is not verified for this SlskeyGroup',
                'isActive' => $isActive,
                'isVerified' => $userIsVerified,
            ];
        }

        // Separate Z01 into Z01 and MBA
        $almaUserWebhookActivationMail = null;
        if (strtoupper($slskeyGroup->slskey_code) === 'Z01') {
            $mbaSlskeyGroup = SlskeyGroup::where('slskey_code', 'z01mba')->first();
            $webhookMailActivationHelper = new WebhookMailActivationHelper($mbaSlskeyGroup->webhook_mail_activation_domains);
            $almaUserWebhookActivationMail = $webhookMailActivationHelper->getWebhookActivationMail($almaUser);
            if ($almaUserWebhookActivationMail) {
                $slskeyGroup = $mbaSlskeyGroup;
            }
        }

        // Check if test run, if so, return
        if ($testRun) {
            return [
                'success' => true,
                'message' => "User is ready for: {$slskeyGroup->slskey_code}",
                'isActive' => $isActive,
                'isVerified' => $userIsVerified,
            ];
        }

        // Activate user via SWITCH API
        $response = $this->activationService->activateSlskeyUser(
            $row['primary_id'],
            $slskeyGroup->slskey_code,
            TriggerEnums::SYSTEM_MASS_IMPORT,
            null, // author
            $almaUser,
            $almaUserWebhookActivationMail,
            $setHistoryActivationDate ? $activationDate : null,
        );

        // Error handling
        if (! $response->success) {
            return [
                'success' => false,
                'message' => $response->message,
                'isActive' => $isActive,
                'isVerified' => $userIsVerified,
            ];
        }

        // Set remark
        if ($row['remark'] && $row['remark'] != 'NULL') {
            $this->activationService->setActivationRemark($row['primary_id'], $slskeyGroup->slskey_code, $row['remark']);
        }

        // Set Historic: Activation Date and Expiration Date
        if ($activationDate) {
            // Always do this, this is not slskeyhistory, therefore not used for reporting.
            $this->activationService->updateActivationDate($row['primary_id'], $slskeyGroup->slskey_code, $activationDate);
        }
        if ($expirationDate) {
            $this->activationService->updateExpirationDate($row['primary_id'], $slskeyGroup->slskey_code, $expirationDate);
        }

        // Set Member of educational institution
        $isMemberEducationalInstitution = !empty($row['is_member_education_institution'])
            && ($row['is_member_education_institution'] == 1 || $row['is_member_education_institution'] === '1');

        if ($isMemberEducationalInstitution) {
            $this->activationService->setActivationMemberEducationalInstitution($row['primary_id'], $slskeyGroup->slskey_code, true);
        }

        return [
            'success' => true,
            'message' => $response->message,
            'isActive' => $isActive,
            'isVerified' => $userIsVerified,
        ];
    }

    private function activateWithoutExternalApis($primaryId, $firstname, $lastname, $activationDate, $expirationDate, $slskeyCode, $testRun)
    {
        $slskeyUser = SlskeyUser::where('primary_id', '=', $primaryId)->first();
        // Get SLSKey Group
        $slskeyGroup = SlskeyGroup::where('slskey_code', '=', $slskeyCode)->first();
        // Check if primaryId is edu-ID.
        if (!SlskeyUser::isPrimaryIdEduId($primaryId)) {
            return [
                'success' => false,
                'message' => 'Primary ID is not an edu-ID',
                'isActive' => false
            ];
        }
        // Check if SLSKey activation exists
        $activation = null;
        if ($slskeyUser) {
            $activation = SlskeyActivation::where('slskey_user_id', '=', $slskeyUser->id)
                ->where('slskey_group_id', '=', $slskeyGroup->id)->first();
        }
        // Check if test run
        if ($testRun) {
            return [
                'success' => true,
                'message' => 'User is ready to be activated',
                'isActive' => $activation !== null,
                'isVerified' => true,
            ];
        }
        // Create User
        if (!$slskeyUser) {
            $slskeyUser = SlskeyUser::create([
                'primary_id' => $primaryId,
                'first_name' => $firstname,
                'last_name' => $lastname,
            ]);
        }

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
                'webhook_activation_mail' => null,
            ]);
        } else {
            // Error if already activated
            return [
                'success' => false,
                'message' => 'User already has activation',
                'isActive' => true,
                'isVerified' => true,
            ];
        }
        // Create History for Logging
        $slskeyHistory = SlskeyHistory::create([
            'slskey_user_id' => $slskeyUser->id,
            'slskey_group_id' => $slskeyGroup->id,
            'action' => ActivationActionEnums::ACTIVATED,
            'author' => null,
            'trigger' => TriggerEnums::SYSTEM_MASS_IMPORT,
            'created_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Activated without external APIs',
            'isActive' => true,
            'isVerified' => true,
        ];
    }
}
