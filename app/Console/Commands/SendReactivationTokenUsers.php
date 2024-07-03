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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    protected $logger;

    public function __construct(TokenService $tokenService, MailService $mailService)
    {
        $this->tokenService = $tokenService;
        $this->mailService = $mailService;
        $this->logger = Log::channel('send-reactivation-token');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->logger->info('---------- START ----------');
        $this->logger->info("Sendind tokens to expiring users");

        // Get all SLSKey Groups with expiring activations
        $slskeyGroups = SlskeyGroup::query()
            ->where('workflow', WorkflowEnums::WEBHOOK)
            ->where('webhook_mail_activation', 1)
            ->whereNotNull('webhook_mail_activation_days_send_before_expiry')
            ->get();

        foreach ($slskeyGroups as $slskeyGroup) {
            $this->logger->info("Checking SLSKey Group $slskeyGroup->slskey_code for expiring activations.");
            // select expiring activations for this group that is exatcly $daysUntilExpiration days in the future
            $expiringActivations = SlskeyActivation::query()
                ->where('slskey_group_id', $slskeyGroup->id)
                ->whereNotNull('expiration_date')
                ->where('expiration_date', '>=', now()->addDays($slskeyGroup->webhook_mail_activation_days_send_before_expiry)->startOfDay())
                ->where('expiration_date', '<', now()->addDays($slskeyGroup->webhook_mail_activation_days_send_before_expiry)->endOfDay())
                ->withSlskeyUserAndSlskeyGroup()
                ->get();

            if (! count($expiringActivations)) {
                $this->logger->info("No expiring activations found for group $slskeyGroup->slskey_code.");

                continue;
            }

            // Send reminder email to all users with expiring activations
            foreach ($expiringActivations as $activation) {
                if (! $activation->webhook_activation_mail) {
                    $this->logger->info("Ignored: No email address found for user $activation->slskey_user_id.");

                    continue;
                }
                $primaryId = $activation->slskeyUser->primary_id;

                /* Add SlskeyHistory entry */
                // Create History
                $slskeyHistory = SlskeyHistory::create([
                    'slskey_user_id' => $activation->slskeyUser->id,
                    'slskey_group_id' => $slskeyGroup->id,
                    'primary_id' => $primaryId,
                    'action' => ActivationActionEnums::TOKEN_SENT,
                    'author' => null,
                    'trigger' => TriggerEnums::SYSTEM_TOKEN_EXPIRATION,
                    'success' => false, // set it true after success
                ]);

                $response = $this->tokenService->createTokenIfNotExisting($activation->slskeyUser->id, $slskeyGroup);

                if (! $response->success) {
                    $this->logger->info("Error: Failed to create token for user $primaryId: $response->message");
                    $slskeyHistory->setErrorMessage("Failed: $response->message");

                    continue;
                }

                // Send e-mail
                $sent = $this->mailService->sendReactivationTokenUserMail($slskeyGroup, $activation->webhook_activation_mail, $response->reactivationLink);

                if ($sent) {
                    $this->logger->info("Success: Sent token to user $primaryId");
                    $slskeyHistory->setSuccess(true);
                } else {
                    $slskeyHistory->setErrorMessage('Email failed');
                    $this->logger->info("Error: Failed to send token to user $primaryId.");
                }
            }
        }

        return 1;
    }
}
