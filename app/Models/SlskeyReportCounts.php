<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class SlskeyReportCounts extends Model
{
    use HasFactory;

    // Define the table if it doesn't follow Laravel's naming convention
    protected $table = 'slskey_report_counts';

    protected $fillable = [
        'slskey_group_id',
        'month',
        'year',
        'activated_count',
        'extended_count',
        'reactivated_count',
        'deactivated_count',
        'blocked_active_count',
        'blocked_inactive_count',
        'monthly_change_count',
        'total_active_users',
        'total_active_educational_users'
    ];

    // Optionally, define default values for the properties
    protected $attributes = [
       'activated_count' => 0,
       'extended_count' => 0,
       'reactivated_count' => 0,
       'deactivated_count' => 0,
       'blocked_active_count' => 0,
       'blocked_inactive_count' => 0,
       'monthly_change_count' => 0,
       'total_active_users' => 0,
       'total_active_educational_users' => 0,
    ];

    /**
     * Get Slskey Group
     *
     * @return BelongsTo
     */
    public function slskeyGroup(): BelongsTo
    {
        return $this->belongsTo(SlskeyGroup::class);
    }

    /**
     * Get the history counts for a specific month and year
     * They are not stored in the database yet, but calculated on the fly
     *
     * @param array $slskeyGroupIds
     * @param string $month
     * @param string $year
     * @return SlskeyReportCounts
     */
    public static function getCurrentMonthCounts(array $slskeyGroupIds): array
    {
        $currentMonth = now()->format('m');
        $currentYear = now()->format('Y');
        $currentMonth =  self::getHistoryCountsForMonthAndYear($slskeyGroupIds, $currentMonth, $currentYear);
        $totalCurrentCount = SlskeyActivation::whereIn('slskey_group_id', $slskeyGroupIds)->where('activated', 1)->count();
        $totalCurrentMemberEducationalInstitutionCount = SlskeyActivation::whereIn('slskey_group_id', $slskeyGroupIds)->where('activated', 1)->where('member_educational_institution', 1)->count();

        return [
            'month' => $currentMonth->month,
            'year' => $currentMonth->year,
            'activated_count' => $currentMonth->activated_count,
            'extended_count' => $currentMonth->extended_count,
            'reactivated_count' => $currentMonth->reactivated_count,
            'deactivated_count' => $currentMonth->deactivated_count,
            'blocked_active_count' => $currentMonth->blocked_active_count,
            'blocked_inactive_count' => $currentMonth->blocked_inactive_count,
            'monthly_change_count' => $currentMonth->monthly_change_count,
            'total_active_users' => $totalCurrentCount,
            'total_active_educational_users' => $totalCurrentMemberEducationalInstitutionCount
        ];
    }

    /**
     * Get the SlskeyGroup that the SlskeyHistory belongs to.
     *
     * @param Builder $query
     * @param array $slskeyCodes
     * @param string $firstDate
     * @return array
     */
    public static function getGroupedByMonthWithActionCounts(array $slskeyCodes, string $firstDate): array
    {
        // Generate a sequence of months and years
        $startDate = new \DateTime($firstDate);
        // enddate is last minute of current month
        $endDate = new \DateTime(date('Y-m-d 24:00:00', strtotime('last day of this month')));

        $dateCombinations = [];
        while ($startDate <= $endDate) {
            // Add tuple of month and year to the array
            $dateCombinations[] = [
                $startDate->format('m'),
                $startDate->format('Y'),
            ];
            // Move to the next month
            $startDate->modify('+1 month');
        }

        $result = [];
        $totalUsers = 0;

        foreach ($dateCombinations as $key => $dateCombination) {
            $currentMonth = self::getHistoryCountsForMonthAndYear($slskeyCodes, $dateCombination[0], $dateCombination[1]);
            $totalUsers += $currentMonth->monthly_change_count;
            $currentMonth->total_users = $totalUsers;
            $result[] = $currentMonth;
        }

        //invert the array
        $result = array_reverse($result);

        return $result;
    }

    /**
     * Get History Count for given Month and Year
     *
     * @param Builder $query
     * @param array $slskeyGroupIds
     * @param integer $month
     * @param integer $year
     * @return self
     */
    public static function getHistoryCountsForMonthAndYear(array $slskeyGroupIds, int $month, int $year): self
    {
        $query = SlskeyHistory::query();
        $query = $query->whereIn('action', ['ACTIVATED', 'DEACTIVATED', 'EXTENDED', 'REACTIVATED', 'BLOCKED_ACTIVE', 'BLOCKED_INACTIVE']);
        $query = $query->whereIn('slskey_group_id', $slskeyGroupIds);

        $driverName = DB::connection()->getDriverName();

        $result = $query
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw(
                ($driverName === 'sqlite' ?
                    "strftime('%Y', created_at) as year, strftime('%m', created_at) as month, " :
                    'YEAR(created_at) as year, MONTH(created_at) as month,')
                    .
                    'CAST(SUM(CASE WHEN action = "ACTIVATED" THEN 1 ELSE 0 END) AS SIGNED) as activated_count,
                    CAST(SUM(CASE WHEN action = "EXTENDED" THEN 1 ELSE 0 END) AS SIGNED) as extended_count,
                    CAST(SUM(CASE WHEN action = "REACTIVATED" THEN 1 ELSE 0 END) AS SIGNED) as reactivated_count,
                    CAST(SUM(CASE WHEN action = "DEACTIVATED" THEN 1 ELSE 0 END) AS SIGNED) as deactivated_count,
                    CAST(SUM(CASE WHEN action = "BLOCKED_ACTIVE" THEN 1 ELSE 0 END) AS SIGNED) as blocked_active_count,
                    CAST(SUM(CASE WHEN action = "BLOCKED_INACTIVE" THEN 1 ELSE 0 END) AS SIGNED) as blocked_inactive_count'
            )
            ->groupBy('year', 'month')
            ->first();

        if (!$result) {
            return new self([
                // format 5 to 05
                'month' => sprintf('%02d', $month),
                'year' => str($year),
                'activated_count' => 0,
                'extended_count' => 0,
                'reactivated_count' => 0,
                'deactivated_count' => 0,
                'blocked_active_count' => 0,
                'blocked_inactive_count' => 0,
                'monthly_change_count' => 0,
            ]);
        } else {
            $monthlyChange = $result->activated_count + $result->reactivated_count - $result->deactivated_count - $result->blocked_active_count;

            return new self([
                'month' => $result->month,
                'year' => $result->year,
                'activated_count' => $result->activated_count,
                'extended_count' => $result->extended_count,
                'reactivated_count' => $result->reactivated_count,
                'deactivated_count' => $result->deactivated_count,
                'blocked_active_count' => $result->blocked_active_count,
                'blocked_inactive_count' => $result->blocked_inactive_count,
                'monthly_change_count' => $monthlyChange,
            ]);
        }
    }
}
