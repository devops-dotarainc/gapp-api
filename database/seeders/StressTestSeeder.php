<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\Stag;
use App\Models\User;
use App\Models\Breeder;
use App\Models\Chapter;
use App\Models\Wingband;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Season as ModelsSeason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StressTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wingbands = Wingband::all()->count();

        $wingbandLimit = (int) env('SEEDER_LIMIT', 1) + $wingbands;        

        $start = 1 + $wingbands;

        $chapters = ['Chapter 1', 'Chapter 2', 'Chapter 3', 'Chapter 4', 'Chapter 5'];
        $breeders = ['Breeder 1', 'Breeder 2', 'Breeder 3', 'Breeder 4', 'Breeder 5'];
        $farms = ['Farm 1', 'Farm 2', 'Farm 3', 'Farm 4', 'Farm 5'];
        $addresses = ['Address 1', 'Address 2', 'Address 3', 'Address 4', 'Address 5'];
        $provinces = ['Province 1', 'Province 2', 'Province 3', 'Province 4', 'Province 5'];
        $colors = ['G', 'B', 'White', 'Red'];

        for($i = $start; $i < $wingbandLimit; $i++) {
            $breederName = $breeders[array_rand($breeders)];
            $farm = $farms[array_rand($farms)];
            $address = $addresses[array_rand($addresses)];
            $province = $provinces[array_rand($provinces)];
            $color = $colors[array_rand($colors)];
            $chapter = $chapters[array_rand($chapters)];

            $wingband = Wingband::create([
                'stag_registry' => '1000' . rand(1, 999),
                'breeder_name' => $breederName,
                'farm_name' => $farm,
                'farm_address' => $address,
                'province' => $province,
                'wingband_number' => $i,
                'feather_color' => $color,
                'leg_color' => $color,
                'comb_shape' => 'C',
                'nose_markings' => 'N',
                'feet_markings' => 'M',
                'season' => rand(1, 4),
                'status' => 1,
                'wingband_date' => Carbon::now()->format('Y-m-d'),
                'created_by' => rand(1, 100000) 
            ]);            

            $checkStag = Stag::where('stag_registry', $wingband->stag_registry)->first();

            if (! $checkStag) {
                $stag = new Stag;
                $stag->stag_registry = $wingband->stag_registry;
                $stag->farm_name = ucwords($farm);
                $stag->farm_address = ucwords($address);
                $stag->breeder_name = ucwords($breederName);
                $stag->chapter = ucfirst($chapter);
                $stag->banded_cockerels = 1;
                $stag->save();
            } else {
                $checkStag->banded_cockerels += 1;
                $checkStag->save();
            }

            $checkBreeder = Breeder::where('name', ucwords($breederName))->first();

            if (! $checkBreeder) {
                $breeder = new Breeder;
                $breeder->name = ucwords($breederName);
                $breeder->farm_name = ucwords($farm);
                $breeder->farm_address = ucwords($address);
                $breeder->chapter = ucfirst($chapter);
                $breeder->banded_cockerels = 1;
                $breeder->save();
            } else {
                $checkBreeder->banded_cockerels += 1;
                $checkBreeder->save();
            }

            $checkFarm = Farm::where('name', ucwords($farm))->first();

            if (! $checkFarm) {
                $farm = new Farm;
                $farm->name = ucwords($wingband->farm_name);
                $farm->address = ucwords($address);
                $farm->breeder_name = ucwords($breederName);
                $farm->banded_cockerels = 1;
                $farm->save();
            } else {
                $checkFarm->banded_cockerels += 1;
                $checkFarm->save();
            }

            $checkChapter = Chapter::where('chapter', ucfirst($chapter))->first();

            $chapterName = $chapter;

            if (! $checkChapter) {
                $chapter = new Chapter;
                $chapter->chapter = ucfirst($chapterName);
                $chapter->banded_cockerels = 1;
                $chapter->save();
            } else {
                $checkChapter->banded_cockerels += 1;
                $checkChapter->save();
            }

            $season = ModelsSeason::where('season', $wingband->season)->where('year', now()->year)->first();

            if (! $season) {
                $season = new ModelsSeason;
                $season->season = $wingband->season;
                $season->entry += 1;
                $season->year = now()->year;
                $season->save();
            } else {
                $season->entry += 1;
                $season->save();
            }
        }
    }
}
