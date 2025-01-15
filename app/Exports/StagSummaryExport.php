<?php

namespace App\Exports;

use App\Models\Stag;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StagSummaryExport implements FromCollection, WithHeadings, WithStyles
{
    protected $chapter;

    public function __construct($chapter)
    {
        $this->chapter = $chapter;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        return [
            'stag_registry',
            'name_of_breeder',
            'farm_address',
            'stag_count',
        ];
    }

    public function collection()
    {
        return Stag::select(
            'stag_registry',
            'breeder_name',
            'farm_address',
            'banded_cockerels',
        )
            ->where('chapter', $this->chapter)
            ->get();
    }
}
