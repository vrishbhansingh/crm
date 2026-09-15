<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * The downloadable "Import Report" — every skipped/failed row from a bulk
 * Lead import plus its reason, so a user can fix those specific rows and
 * re-upload rather than re-checking the whole original file by hand.
 */
class LeadImportReportExport implements FromArray, WithHeadings
{
    /** @param array<int, array{row:int,name:?string,email:?string,phone:?string,status:string,reason:?string}> $rows */
    public function __construct(private readonly array $rows) {}

    public function array(): array
    {
        return array_map(fn ($r) => [
            $r['row'],
            $r['name'],
            $r['email'],
            $r['phone'],
            $r['status'],
            $r['reason'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Row', 'Name', 'Email', 'Phone', 'Status', 'Reason'];
    }
}
