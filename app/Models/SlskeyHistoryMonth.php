<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SlskeyHistoryMonth extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'month',
        'year',
        'activated_count',
        'extended_count',
        'reactivated_count',
        'deactivated_count',
        'blocked_active_count',

        'monthly_change',
        'total_users'
    ];

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
            $currentMonth = SlskeyHistoryMonth::getHistoryCountsForMonthAndYear($slskeyCodes, $dateCombination[0], $dateCombination[1]);
            $monthlyChange = $currentMonth->activated_count + $currentMonth->reactivated_count - $currentMonth->deactivated_count - $currentMonth->blocked_active_count;
            $totalUsers = $totalUsers += $monthlyChange;

            $currentMonth->total_users = $totalUsers;
            $currentMonth->monthly_change = $monthlyChange;

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
        $query = $query->where('success', true)->whereIn('action', ['ACTIVATED', 'DEACTIVATED', 'EXTENDED', 'REACTIVATED', 'BLOCKED_ACTIVE', 'BLOCKED_INACTIVE']);
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
                    CAST(SUM(CASE WHEN action = "BLOCKED_ACTIVE" THEN 1 ELSE 0 END) AS SIGNED) as blocked_active_count'
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
                'monthly_change' => 0,
                'total_users' => 0
            ]);
        } else {
            return new self([
                'month' => $result->month,
                'year' => $result->year,
                'activated_count' => $result->activated_count,
                'extended_count' => $result->extended_count,
                'reactivated_count' => $result->reactivated_count,
                'deactivated_count' => $result->deactivated_count,
                'blocked_active_count' => $result->blocked_active_count,
                'monthly_change' => 0,
                'total_users' => 0
            ]);
        }
    }
}
