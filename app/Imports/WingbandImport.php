<?php

namespace App\Imports;

use App\Enums\Season;
use App\Models\Breeder;
use App\Models\Chapter;
use App\Models\Farm;
use App\Models\Season as ModelsSeason;
use App\Models\Stag;
use App\Models\Wingband;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WingbandImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $arrayData = [];
        $duplicateWingband = [];
        $dateError = [];

        $rows = $rows->filter(function ($row) {
            $fieldsToCheck = [
                'date', 'stag_registry_no', 'name_of_breeders', 'farm_name',
                'farm_address', 'chapter', 'province', 'contact_no',
                'wingband_no', 'feather_color', 'leg_color',
                'comb_shape', 'nose_markings', 'feet_markings',
            ];

            foreach ($fieldsToCheck as $field) {
                if (! empty($row[$field])) {
                    return true;
                }
            }

            return false;
        });

        foreach ($rows as $row) {

            $fieldsToCheck = [
                'date', 'stag_registry_no', 'name_of_breeders', 'farm_name',
                'farm_address', 'chapter', 'province', 'contact_no',
                'wingband_no', 'feather_color', 'leg_color',
                'comb_shape', 'nose_markings', 'feet_markings',
            ];

            if (array_filter($fieldsToCheck, fn ($field) => $row[$field] === null)) {
                $arrayData[] = $row['row_no'];
            }

            $convertedDate = Carbon::create(1899, 12, 30)->addDays($row['date']);

            if ($convertedDate->equalTo(Carbon::create(1899, 12, 30))) {
                Log::info('Skipping invalid date conversion for row: '.$row['row_no']);

                continue;
            }

            $seasonRanges = [
                ['start' => '01-02', 'end' => '01-30', 'season' => Season::EARLY_BIRD],
                ['start' => '03-01', 'end' => '03-30', 'season' => Season::LOCAL],
                ['start' => '04-01', 'end' => '04-30', 'season' => Season::NATIONAL],
                ['start' => '06-01', 'end' => '06-30', 'season' => Season::LATE_BORN],
            ];

            $seasons = null;
            foreach ($seasonRanges as $range) {
                if ($convertedDate->format('m-d') >= $range['start'] && $convertedDate->format('m-d') <= $range['end']) {
                    $seasons = $range['season'];
                    break;
                }
            }

            if (! $seasons) {
                Log::info('failed: '.$convertedDate);
                $dateError[] = $row['row_no'];
                $seasons = new \stdClass;
                $seasons->value = 1;
            }

            $checkWingband = Wingband::where('wingband_number', $row['wingband_no'])
                ->where('season', $seasons->value)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $checkWingband) {
                Wingband::create([
                    'stag_registry' => $row['stag_registry_no'],
                    'breeder_name' => ucwords($row['name_of_breeders']),
                    'farm_name' => ucwords($row['farm_name']),
                    'farm_address' => ucwords($row['farm_address']),
                    'province' => $row['province'],
                    'wingband_number' => $row['wingband_no'],
                    'feather_color' => $row['feather_color'],
                    'leg_color' => $row['leg_color'],
                    'comb_shape' => $row['comb_shape'],
                    'nose_markings' => $row['nose_markings'],
                    'feet_markings' => $row['feet_markings'],
                    'season' => $seasons->value,
                    'wingband_date' => $convertedDate,
                    'created_by' => auth()->user()->id,
                ]);
            } else {
                $wingbandDate = Carbon::parse($checkWingband->wingband_date);
                if ($wingbandDate->year == $convertedDate->year) {
                    $duplicateWingband[] = $row['row_no'];
                }
            }

            $checkStag = Stag::where('stag_registry', $row['stag_registry_no'])->first();

            if (! $checkStag) {
                $stag = new Stag;
                $stag->stag_registry = $row['stag_registry_no'];
                $stag->farm_name = ucwords($row['farm_name']);
                $stag->farm_address = ucwords($row['farm_address']);
                $stag->breeder_name = ucwords($row['name_of_breeders']);
                $stag->chapter = ucfirst($row['chapter']);
                $stag->banded_cockerels = 1;
                $stag->save();
            } else {
                $checkStag->banded_cockerels += 1;
                $checkStag->save();
            }

            $checkBreeder = Breeder::where('name', ucwords($row['name_of_breeders']))->first();

            if (! $checkBreeder) {
                $breeder = new Breeder;
                $breeder->name = ucwords($row['name_of_breeders']);
                $breeder->farm_name = ucwords($row['farm_name']);
                $breeder->farm_address = ucwords($row['farm_address']);
                $breeder->chapter = ucfirst($row['chapter']);
                $breeder->banded_cockerels = 1;
                $breeder->save();
            } else {
                $checkBreeder->banded_cockerels += 1;
                $checkBreeder->save();
            }

            $checkFarm = Farm::where('name', ucwords($row['farm_name']))->first();

            if (! $checkFarm) {
                $farm = new Farm;
                $farm->name = ucwords($row['farm_name']);
                $farm->address = ucwords($row['farm_address']);
                $farm->breeder_name = ucwords($row['name_of_breeders']);
                $farm->banded_cockerels = 1;
                $farm->save();
            } else {
                $checkFarm->banded_cockerels += 1;
                $checkFarm->save();
            }

            $checkChapter = Chapter::where('chapter', ucfirst($row['chapter']))->first();

            if (! $checkChapter) {
                $chapter = new Chapter;
                $chapter->chapter = ucfirst($row['chapter']);
                $chapter->banded_cockerels = 1;
                $chapter->save();
            } else {
                $checkChapter->banded_cockerels += 1;
                $checkChapter->save();
            }

            $season = ModelsSeason::where('season', $seasons->value)->where('year', now()->year)->first();

            if (! $season) {
                $season = new ModelsSeason;
                $season->season = $seasons->value;
                $season->entry += 1;
                $season->year = now()->year;
                $season->save();
            } else {
                $season->entry += 1;
                $season->save();
            }
        }

        if (count($arrayData) > 0) {
            $arrayData['unsaved_data'] = true;

            throw new \Exception(json_encode($arrayData));
        }

        if (count($duplicateWingband) > 0) {
            $duplicateWingband['duplicate_data'] = true;

            throw new \Exception(json_encode($duplicateWingband));
        }

        if (count($dateError) > 0) {
            $dateError['date_error'] = true;

            throw new \Exception(json_encode($dateError));
        }
    }
}
