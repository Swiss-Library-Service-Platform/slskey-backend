<?php

namespace App\Console\Commands;

use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyHistoryMonth;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LogJob;

class SendMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:send-monthly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send statistics about activations in the last month. Should run on the first day of a new month.';

    protected $mailService;

    protected $textFileLogger;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
        $this->textFileLogger = Log::channel('send-monthly-report');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->textFileLogger->info("START Sending Monthly Reports");

        // Get all SLSKey Groups with expiring activations
        $slskeyGroups = SlskeyGroup::all();
        $sentReports = [];

        foreach ($slskeyGroups as $slskeyGroup) {
            $countRecipients = 0;
            $isSuccess = false;

            // Get all Report Emails for this SLSKey Group
            $reportEmailAddresses = $slskeyGroup->reportEmailAddresses->pluck('email_address')->toArray();

            if (count($reportEmailAddresses) == 0) {
                $this->textFileLogger->info("$slskeyGroup->slskey_code: Error: No report email addresses found");

                continue;
            } else {
                $countRecipients = count($reportEmailAddresses);

                // Get last month
                $currentMonth = date('m', strtotime('-1 month'));
                $currentYear = date('Y', strtotime('-1 month'));

                // Get Month History
                $slskeyHistoryMonth = SlskeyHistoryMonth::getHistoryCountsForMonthAndYear([$slskeyGroup->id], $currentMonth, $currentYear);

                // Total count
                $totalCurrentCount = SlskeyActivation::where('slskey_group_id', $slskeyGroup->id)->where('activated', 1)->count();
                $totalCurrentMemberEducationalInstitutionCount = SlskeyActivation::where('slskey_group_id', $slskeyGroup->id)->where('activated', 1)->where('member_educational_institution', 1)->count();
                $sent = $this->mailService->sendMonthlyReportMail(
                    $slskeyGroup,
                    $slskeyHistoryMonth,
                    $totalCurrentCount,
                    $totalCurrentMemberEducationalInstitutionCount,
                    $reportEmailAddresses
                );

                if ($sent) {
                    $isSuccess = true;
                    $this->textFileLogger->info("$slskeyGroup->slskey_code: Success: Sent report to " . implode(', ', $reportEmailAddresses));
                } else {
                    $this->textFileLogger->info("$slskeyGroup->slskey_code: Error: Failed to send report to " . implode(', ', $reportEmailAddresses));
                }
            }

            $sentReports[] = [
                'slskey_group' => $slskeyGroup->slskey_code,
                'success' => $isSuccess,
                'recipients' => $countRecipients,
            ];
        }

        $this->logJobResultToDatabase($sentReports);

        // 0 = Success
        // 2 = Invalid (No reports to send)
        return count($sentReports) > 0 ? 0 : 2;
    }

    protected function logJobResultToDatabase(array $databaseInfo)
    {
        $this->textFileLogger->info("Logging job result to database.");
        LogJob::create([
            'job' => class_basename(__CLASS__),
            'info' => $databaseInfo, //json_encode($databaseInfo),
            'has_fail' => count(array_filter(array_column($databaseInfo, 'success'), fn ($success) => !$success)),
        ]);
    }
}
