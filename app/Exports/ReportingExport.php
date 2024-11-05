<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportingExport implements FromArray, WithHeadings, WithMapping
{
    use Exportable;

    protected $reportCounts;
    protected $isAnyEducationalUsers;

    public function __construct($reportCounts, $isAnyEducationalUsers)
    {
        $this->reportCounts = $reportCounts;
        $this->isAnyEducationalUsers = $isAnyEducationalUsers;
    }

    /**
     * Headings
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Month',
            'Year',
            'New Users',
            'Reactivations',
            'Extensions',
            'Deactivations',
            'Blocks',
            'Monthly Change',
            'Active Users Total',
            $this->isAnyEducationalUsers ? 'Active Educational Users Total' : '',
        ];
    }

    /**
     * Map the rows
     *
     * @param [type] $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['month'],
            $row['year'],
            $row['activated_count'],
            $row['reactivated_count'],
            $row['extended_count'],
            $row['deactivated_count'],
            $row['blocked_active_count'] + $row['blocked_inactive_count'],
            $row['monthly_change_count'],
            $row['total_active_users'],
            $this->isAnyEducationalUsers ? $row['total_active_educational_users'] : '',
        ];
    }

    public function array(): array
    {
        return $this->reportCounts;
    }
}
