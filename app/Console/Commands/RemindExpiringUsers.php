<?php

namespace App\Console\Commands;

use App\Enums\ActivationActionEnums;
use App\Enums\TriggerEnums;
use App\Enums\WorkflowEnums;
use App\Interfaces\AlmaAPIInterface;
use App\Interfaces\Repositories\SlskeyActivationRepositoryInterface;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistory;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LogJob;

class RemindExpiringUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:send-remind-expiring-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminds users that have expiring activations in the near future. Should run every day.';

    protected $almaApiService;

    protected $mailService;

    protected $activationRepository;

    protected $textFileLogger;

    public function __construct(AlmaAPIInterface $almaApiService, MailService $mailService, SlskeyActivationRepositoryInterface $activationRepository)
    {
        $this->almaApiService = $almaApiService;
        $this->mailService = $mailService;
        $this->activationRepository = $activationRepository;
        $this->textFileLogger = Log::channel('send-remind-expiring-users');
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
        $this->textFileLogger->info('START Reminding expiring users.');

        // Get all SLSKey Groups with expiring activations
        $slskeyGroups = SlskeyGroup::query()
            ->where('workflow', WorkflowEnums::MANUAL)
            ->whereNotNull('days_expiration_reminder')
            ->get();

        $countTotal = 0;
        $countSuccess = 0;

        foreach ($slskeyGroups as $slskeyGroup) {
            $this->textFileLogger->info("Checking SLSKey Group $slskeyGroup->slskey_code for expiring activations.");

            $expiringActivations = $this->activationRepository->getActivationsToBeReminded($slskeyGroup);

            if (! count($expiringActivations)) {
                $this->textFileLogger->info("No expiring activations found for group $slskeyGroup->slskey_code.");

                continue;
            }

            // Send reminder email to all users with expiring activations
            foreach ($expiringActivations as $activation) {
                $countTotal++;

                $primaryId = $activation->slskeyUser->primary_id;
                // Get Alma User Details of user
                $almaServiceResponse = $this->almaApiService->getUserFromSingleIz($primaryId, $slskeyGroup->alma_iz);
                if (! $almaServiceResponse->success) {
                    $this->textFileLogger->info("Failed to get Alma user details for user $primaryId: $almaServiceResponse->errorText");

                    continue;
                }
                $almaUser = $almaServiceResponse->almaUser;

                $sent = $this->mailService->sendRemindExpiringUserMail($slskeyGroup, $almaUser);

                if (! $sent) {
                    $this->textFileLogger->info("Failed to send email to user $primaryId.");

                    continue;
                }

                $activation->setReminded(true);

                // Create History
                SlskeyHistory::create([
                    'slskey_user_id' => $activation->slskeyUser->id,
                    'slskey_group_id' => $slskeyGroup->id,
                    'action' => ActivationActionEnums::REMINDED,
                    'author' => null,
                    'trigger' => TriggerEnums::SYSTEM_REMIND_EXPIRATION,
                ]);

                $this->textFileLogger->info("Sent reminder to user $primaryId to $almaUser->preferred_email.");

                $countSuccess++;
            }
        }

        $this->logJobResultToDatabase($countTotal, $countSuccess, $countTotal - $countSuccess);

        return 1;
    }

    protected function logJobResultToDatabase($totalCount, $countSuccess, $countFailed)
    {
        $this->textFileLogger->info("Logging job result to database.");
        $databaseInfo = [
            'users_to_remind' => $totalCount,
            'success' => $countSuccess,
            'failed' => $countFailed,
        ];
        LogJob::create([
            'job' => class_basename(__CLASS__),
            'info' => json_encode($databaseInfo),
            'has_fail' => $countFailed > 0,
        ]);
    }
}
