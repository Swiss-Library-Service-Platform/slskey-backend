<?php

namespace App\Exports;

use App\Models\SlskeyHistoryMonth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportingExport implements FromArray, WithHeadings
{
    use Exportable;

    protected $slskeyGroupIds;
    protected $firstDate;

    public function __construct($slskeyGroupIds, $firstDate)
    {
        $this->slskeyGroupIds = $slskeyGroupIds;
        $this->firstDate = $firstDate;
    }

    public function array(): array
    {
        $slskeyHistories =  SlskeyHistoryMonth::getGroupedByMonthWithActionCounts($this->slskeyGroupIds, $this->firstDate);

        return $slskeyHistories;
    }

    public function headings(): array
    {
        return [
            'Month',
            'Year',
            'New Users',
            'Extensions',
            'Reactivations',
            'Deactivations',
            'Blocks',
            'Monthly Change',
            'Active Users'
        ];
    }
}
