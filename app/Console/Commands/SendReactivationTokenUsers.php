<?php

namespace App\Console\Commands;

use App\Enums\ActivationActionEnums;
use App\Enums\TriggerEnums;
use App\Enums\WorkflowEnums;
use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Services\MailService;
use App\Services\TokenService;
use App\Interfaces\AlmaAPIInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LogJob;

class SendReactivationTokenUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:send-reactivation-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends emails with reactivation-tokens to users with expiring activations. Should run every day.';

    protected $tokenService;

    protected $mailService;

    protected $almaApiService;

    protected $textFileLogger;

    public function __construct(TokenService $tokenService, MailService $mailService, AlmaAPIInterface $almaApiService)
    {
        $this->tokenService = $tokenService;
        $this->mailService = $mailService;
        $this->almaApiService = $almaApiService;
        $this->textFileLogger = Log::channel('send-reactivation-token');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->textFileLogger->info("START Sendind tokens to expiring users");

        // Get all SLSKey Groups with expiring activations
        $slskeyGroups = SlskeyGroup::query()
            ->where('workflow', WorkflowEnums::WEBHOOK)
            ->where('webhook_token_reactivation', 1)
            ->whereNotNull('webhook_token_reactivation_days_send_before_expiry')
            ->get();

        $tokensPerGroup = [];
        $hasFail = false;

        foreach ($slskeyGroups as $slskeyGroup) {
            $countTotal = 0;
            $countSuccess = 0;

            $this->textFileLogger->info("Checking SLSKey Group $slskeyGroup->slskey_code for expiring activations.");
            // select expiring activations for this group that is exatcly $daysUntilExpiration days in the future
            $expiringActivations = SlskeyActivation::query()
                ->where('slskey_group_id', $slskeyGroup->id)
                ->whereNotNull('expiration_date')
                ->where('expiration_date', '>=', now()->addDays($slskeyGroup->webhook_token_reactivation_days_send_before_expiry)->startOfDay())
                ->where('expiration_date', '<', now()->addDays($slskeyGroup->webhook_token_reactivation_days_send_before_expiry)->endOfDay())
                ->withSlskeyUserAndSlskeyGroup()
                ->get();

            if (! count($expiringActivations)) {
                $this->textFileLogger->info("No expiring activations found for group $slskeyGroup->slskey_code.");

                continue;
            }

            $countTotal = count($expiringActivations);

            // Send reminder email to all users with expiring activations
            foreach ($expiringActivations as $activation) {
                $recipientMail = $this->getRecipientMail($slskeyGroup, $activation);

                if (! $recipientMail) {
                    $this->textFileLogger->info("Ignored: No email address found for user $activation->slskey_user_id.");

                    continue;
                }
                $primaryId = $activation->slskeyUser->primary_id;

                $response = $this->tokenService->createTokenIfNotExisting($activation->slskeyUser->id, $slskeyGroup, $recipientMail, $slskeyGroup->webhook_mail_activation);

                if (! $response->success) {
                    $this->textFileLogger->info("Error: Failed to create token for user $primaryId: $response->message");

                    continue;
                }

                // Send e-mail
                $sent = $this->mailService->sendReactivationTokenUserMail($slskeyGroup, $recipientMail, $response->reactivationLink);

                if (! $sent) {
                    $this->textFileLogger->info("Failed to send token to user $primaryId.");

                    continue;
                }

                $this->textFileLogger->info("Success: Sent token to user $primaryId");

                // Create History
                $slskeyHistory = SlskeyHistory::create([
                    'slskey_user_id' => $activation->slskeyUser->id,
                    'slskey_group_id' => $slskeyGroup->id,
                    'action' => ActivationActionEnums::TOKEN_SENT,
                    'author' => null,
                    'trigger' => TriggerEnums::SYSTEM_TOKEN_EXPIRATION,
                ]);

                $countSuccess++;
            }

            $this->textFileLogger->info("Finished sending tokens to expiring users for group $slskeyGroup->slskey_code.");

            $tokensPerGroup[] = [
                'slskey_group' => $slskeyGroup->slskey_code,
                'total' => $countTotal,
                'success' => $countSuccess,
                'failed' => $countTotal - $countSuccess,
            ];
            $hasFail = $hasFail || $countTotal - $countSuccess > 0;
        }

        $this->logJobResultToDatabase($tokensPerGroup, $hasFail);

        // 0 = Success
        // 2 = Invalid (No tokens to send)
        return count($tokensPerGroup) > 0 ? 0 : 2;
    }

    protected function logJobResultToDatabase(array $databaseInfo, $hasFail = false)
    {
        $this->textFileLogger->info("Logging job result to database.");
        LogJob::create([
            'job' => class_basename(__CLASS__),
            'info' => $databaseInfo, // json_encode($databaseInfo),
            'has_fail' => $hasFail,
        ]);
    }

    protected function getRecipientMail($slskeyGroup, $activation)
    {
        // When slskeygroup activations are based on user email
        if ($slskeyGroup->webhook_mail_activation) {
            return $activation->webhook_activation_mail;
        }

        // Otherwise, get preferred mail from Alma
        $almaServiceResponse = $this->almaApiService->getUserFromSingleIz($activation->slskeyUser->primary_id, $slskeyGroup->alma_iz);
        if (! $almaServiceResponse->success) {
            $this->textFileLogger->info("Failed to get Alma user details for user $primaryId: $almaServiceResponse->errorText");

            return null;
        }
        $almaUser = $almaServiceResponse->almaUser;

        return $almaUser->preferred_email;
    }
}
