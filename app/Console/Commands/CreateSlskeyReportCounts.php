<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SlskeyGroup;
use App\Models\SlskeyReportCounts;
use App\Models\SlskeyHistory;


class CreateSlskeyReportCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-report-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Slskey Report Counts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Creating Slskey Report Counts...\n";
        $slskeyGroups = SlskeyGroup::all();

        foreach ($slskeyGroups as $slskeyGroup) {

            // Get first Slskeyhistory activation month and year
            $firstHistory = SlskeyHistory::query()
                ->where('slskey_group_id', $slskeyGroup->id)
                ->where('action', 'ACTIVATED')
                ->orderBy('created_at', 'asc')
                ->first();

            $firstDate = $firstHistory ? $firstHistory->created_at : date('Y-m-d');

            // Get SlskeyActivations
            $slskeyreportCounts = SlskeyReportCounts::getGroupedByMonthWithActionCounts([$slskeyGroup->id], $firstDate);

            $reportCounts = [];
            foreach ($slskeyreportCounts as $slskeyreportCount) {
                // dont create report counts for the current month
                if ($slskeyreportCount->month == now()->format('m') && $slskeyreportCount->year == now()->format('Y')) {
                    continue;
                }

                $reportCount = [
                    'month' => $slskeyreportCount->month,
                    'year' => $slskeyreportCount->year,
                    'activated_count' => $slskeyreportCount->activated_count,
                    'extended_count' => $slskeyreportCount->extended_count,
                    'reactivated_count' => $slskeyreportCount->reactivated_count,
                    'deactivated_count' => $slskeyreportCount->deactivated_count,
                    'blocked_active_count' => $slskeyreportCount->blocked_active_count,
                    'blocked_inactive_count' => $slskeyreportCount->blocked_inactive_count,
                    'monthly_change_count' => $slskeyreportCount->monthly_change_count,
                    'total_active_users' => $slskeyreportCount->total_users,
                    'total_active_educational_users' => 0,
                ];

                print_r($reportCount);

                $reportCounts[] = $reportCount;
            }

            $reportCounts = collect($reportCounts);

            $slskeyGroup->reportCounts()->createMany($reportCounts->toArray());

            echo "Created report counts for Slskey Group: $slskeyGroup->slskey_code\n";
        }
    }
}
