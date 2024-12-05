<?php

namespace App\Exports;

// Set System
ini_set('max_execution_time', 180); // 3 minutes
ini_set('memory_limit', '512M'); // 512 megabytes

use App\Models\SlskeyUser;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromArray, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Headings
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Primary ID',
            'SLSKey Group',
            'Activation Date',
            'Expiration Date',
            'Blocked Date',
            'Remark',
            'Status',
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
        return $row;
    }

    /**
     * Build the row
     *
     * @param [type] $slskeyUser
     * @param [type] $activation
     * @return array
     */
    public function buildRow($slskeyUser, $activation)
    {
        return [
            $slskeyUser->primary_id,
            $activation->slskeyGroup->name,
            $activation->activation_date,
            $activation->expiration_date,
            $activation->blocked_date,
            $activation->remark,
            $activation->blocked ? 'Blocked' : ($activation->activated ? 'Active' : 'Inactive'),
        ];
    }

    /**
     * Array of rows
     *
     * @return array
     */
    public function array(): array
    {
        $slskeyActivationRows = [];

        SlskeyUser::filterWithPermittedActivations()
            ->filter(Request::all())
            // we need to do this on costs of performance, but otherwise memory will be exhausted
            ->chunk(3000, function ($slskeyUsers) use (&$slskeyActivationRows) {
                foreach ($slskeyUsers as $slskeyUser) {
                    foreach ($slskeyUser->slskeyActivations as $activation) {
                        $slskeyActivationRows[] = $this->buildRow($slskeyUser, $activation);
                    }
                }
            });

        return $slskeyActivationRows;
    }
}
