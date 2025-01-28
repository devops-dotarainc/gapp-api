<?php

namespace App\Exports;

use App\Models\Stag;
use App\Classes\ActivityLogClass;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
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
            'stag_count',
            'farm_address',
        ];
    }

    public function collection()
    {

        $stag = Stag::select(
            'stag_registry',
            'breeder_name',
            'banded_cockerels',
            'farm_address',
        );

        if (! is_null($this->chapter)) {
            $stag = $stag->where('chapter', $this->chapter);
        }

        ActivityLogClass::create('Stag Summary Export');

        return $stag->get();
    }
}
