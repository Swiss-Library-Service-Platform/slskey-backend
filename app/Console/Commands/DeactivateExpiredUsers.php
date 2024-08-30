<?php

namespace App\Console\Commands;

use App\Enums\TriggerEnums;
use App\Models\SlskeyActivation;
use App\Services\ActivationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LogJob;

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

    protected $textFileLogger;

    public function __construct(ActivationService $activationService)
    {
        $this->activationService = $activationService;
        $this->textFileLogger = Log::channel('deactivate-expired-users');
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
        $this->textFileLogger->info('START Deactivating expired user.');

        $expiredActivations = SlskeyActivation::whereNotNull('expiration_date')
            ->where('expiration_date', '<', now())
            ->withSlskeyUserAndSlskeyGroup()
            ->get();

        if (!count($expiredActivations)) {
            $this->textFileLogger->info("No expired users found.");
        }

        $countSuccess = 0;

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
                $countSuccess++;
                $this->textFileLogger->info("Success: Deactivated user {$activation->slskeyUser->primary_id} for group {$activation->slskeyGroup->slskey_code}");
            } else {
                $this->textFileLogger->info("Error: User failed {$activation->slskeyUser->primary_id} for group {$activation->slskeyGroup->slskey_code} with message: {$response->message}");
            }
        }

        $this->logJobResultToDatabase(count($expiredActivations), $countSuccess, count($expiredActivations) - $countSuccess);

        return 1;
    }

    protected function logJobResultToDatabase($totalCount, $countSuccess, $countFailed)
    {
        $this->textFileLogger->info("Logging job result to database.");
        $databaseInfo = [
            'expired_activations' => $totalCount,
            'success' => $countSuccess,
            'failed' => $countFailed,
        ];
        LogJob::create([
            'job' => class_basename(__CLASS__),
            'info' => json_encode($databaseInfo),
        ]);
    }
}
