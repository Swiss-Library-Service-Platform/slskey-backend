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
use App\Services\API\AlmaAPIService;
use App\Services\SlskeyUserService;
use Carbon\Carbon;

class ImportCsvJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $importRows;
    protected $checkIsActive;
    protected $testRun;

    protected $slskeyUserService;
    protected $almaApiService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($importRows, $checkIsActive, $testRun)
    {
        $this->importRows = $importRows;
        $this->checkIsActive = $checkIsActive;
        $this->testRun = $testRun;
    }

    /**
     * Handle the job
     * Inject dependencies
     *
     * @param SlskeyUserService $slskeyUserService
     * @param AlmaApiService $almaApiService
     */
    public function handle(SlskeyUserService $slskeyUserService, AlmaAPIInterface $almaApiService)
    {
        $this->slskeyUserService = $slskeyUserService;
        $this->almaApiService = $almaApiService;

        $currentRow = 0;

        foreach ($this->importRows as $row) {
            if (Cache::get('is_import_cancelled')) {
                break;
            }

            $result = $this->processImportRow($row, $this->testRun, $this->checkIsActive);

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

        Cache::put('is_import_cancelled', false, 60);
    }

    /**
     * Process the import row (same logic as your original processImportRow method)
     */
    private function processImportRow(array $row, bool $testRun, bool $checkIsActive): array
    {
        // Get slskey group
        $slskeyGroup = SlskeyGroup::where('slskey_code', $row['slskey_code'])->first();

        if (!$slskeyGroup) {
            return ['success' => false, 'message' => 'SlskeyGroup not found', 'isActive' => false];
        }

        $isActive = null;
        if ($checkIsActive) {
            try {
                $response = $this->slskeyUserService->verifySwitchStatusSlskeyUser($row['primary_id'], $slskeyGroup->slskey_code);
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
            return ['success' => false, 'message' => $almaServiceResponse->errorText, 'isActive' => $isActive];
        }
        $almaUser = $almaServiceResponse->almaUser;

        // Check if we want to set Activation or Expiration Date
        try {
            $activationDate = $row['activation_date'] && $row['activation_date'] != 'NULL' ? Carbon::parse($row['activation_date']) : null;
            $expirationDate = $row['expiration_date'] && $row['expiration_date'] != 'NULL' ? Carbon::parse($row['expiration_date']) : null;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Invalid date format: {$e->getMessage()}", 'isActive' => $isActive];
        }

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
                // FIXME: remove for prod import
                /*
                return [
                    'success' => false,
                    'message' => "User is MBA member",
                    'isActive' => false,
                    'isVerified' => false,
                ];
                */
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
        $response = $this->slskeyUserService->activateSlskeyUser(
            $row['primary_id'],
            $slskeyGroup->slskey_code,
            null, // author
            TriggerEnums::SYSTEM_MASS_IMPORT,
            $almaUser,
            $almaUserWebhookActivationMail
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
            $this->slskeyUserService->setActivationRemark($row['primary_id'], $slskeyGroup->slskey_code, $row['remark']);
        }

        // Set Historic: Activation Date and Expiration Date
        if ($activationDate) {
            $this->slskeyUserService->updateActivationDate($row['primary_id'], $slskeyGroup->slskey_code, $activationDate);
        }
        if ($expirationDate) {
            $this->slskeyUserService->updateExpirationDate($row['primary_id'], $slskeyGroup->slskey_code, $expirationDate);
        }

        return [
            'success' => true,
            'message' => $response->message,
            'isActive' => $isActive,
            'isVerified' => $userIsVerified,
        ];
    }
}
