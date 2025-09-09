<?php

namespace App\Console\Commands;

use App\Models\SlskeyActivation;
use App\Models\SlskeyGroup;
use App\Models\SlskeyReportCounts;
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

            // Get last month
            $currentMonth = date('m', strtotime('-1 month'));
            $currentYear = date('Y', strtotime('-1 month'));

            // Get Month History
            $slskeyReportCount = SlskeyReportCounts::getHistoryCountsForMonthAndYear([$slskeyGroup->id], $currentMonth, $currentYear);

            // Total count
            $totalCurrentCount = SlskeyActivation::where('slskey_group_id', $slskeyGroup->id)->where('activated', 1)->count();
            $totalCurrentMemberEducationalInstitutionCount = SlskeyActivation::where('slskey_group_id', $slskeyGroup->id)->where('activated', 1)->where('member_educational_institution', 1)->count();

            // Save report counts for every group
            $this->saveReportCounts($slskeyGroup, $slskeyReportCount, $totalCurrentCount, $totalCurrentMemberEducationalInstitutionCount);

            if (count($reportEmailAddresses) == 0) {
                $this->textFileLogger->info("$slskeyGroup->slskey_code: Error: No report email addresses found");
                continue;
            } else {
                $countRecipients = count($reportEmailAddresses);
                // Send report
                $sent = $this->mailService->sendMonthlyReportMail(
                    $slskeyGroup,
                    $slskeyReportCount,
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
                $sentReports[] = [
                    'slskey_group' => $slskeyGroup->slskey_code,
                    'success' => $isSuccess,
                    'recipients' => $countRecipients,
                ];
            }
        }

        $this->logJobResultToDatabase($sentReports);

        // 0 = Success
        // 2 = Invalid (No reports to send)
        return count($sentReports) > 0 ? 0 : 2;
    }

    protected function saveReportCounts($slskeyGroup, $slskeyReportCount, $totalCurrentCount, $totalCurrentMemberEducationalInstitutionCount)
    {
        $reportCount = [
            'month' => $slskeyReportCount->month,
            'year' => $slskeyReportCount->year,
            'activated_count' => $slskeyReportCount->activated_count,
            'extended_count' => $slskeyReportCount->extended_count,
            'reactivated_count' => $slskeyReportCount->reactivated_count,
            'deactivated_count' => $slskeyReportCount->deactivated_count,
            'blocked_active_count' => $slskeyReportCount->blocked_active_count,
            'blocked_inactive_count' => $slskeyReportCount->blocked_inactive_count,
            'monthly_change_count' => $slskeyReportCount->monthly_change_count,
            'total_active_users' => $totalCurrentCount,
            'total_active_educational_users' => $totalCurrentMemberEducationalInstitutionCount,
        ];

        $slskeyGroup->reportCounts()->create($reportCount);
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
