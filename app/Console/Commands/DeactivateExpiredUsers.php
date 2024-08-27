<?php

namespace App\Console\Commands;

use App\Enums\TriggerEnums;
use App\Models\SlskeyActivation;
use App\Services\ActivationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:deactivate-expired-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate all SLSKey Activations that are expired. Should run every day.';

    protected $activationService;

    protected $logger;

    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
        $this->logger = Log::channel('deactivate-expired-users');
        parent::__construct();
    }

    /**
     * Get the signature of the console command.
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->logger->info('---------- START ----------');
        $this->logger->info('Deactivating expired user.');

        $expiredActivations = SlskeyActivation::whereNotNull('expiration_date')
            ->where('expiration_date', '<', now())
            ->withSlskeyUserAndSlskeyGroup()
            ->get();

        if (!count($expiredActivations)) {
            $this->logger->info("No expired users found.");

            return 0;
        }

        // Deactivate all expired activations
        foreach ($expiredActivations as $activation) {
            $response = $this->activationService->deactivateSlskeyUser(
                $activation->slskeyUser->primary_id,
                $activation->slskeyGroup->slskey_code,
                $activation->remark,
                null,
                TriggerEnums::SYSTEM_EXPIRATION
            );

            if ($response->success) {
                $this->logger->info("Success: Deactivated user {$activation->slskeyUser->primary_id}");
            } else {
                $this->logger->info("Error: User failed {$activation->slskeyUser->primary_id}: {$response->message}");
            }
        }

        return 1;
    }
}
