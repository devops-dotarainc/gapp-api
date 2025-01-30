<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'FU',
            'name' => 'Facebook URL',
            'value' => 'www.facebook.com/gapp-api'
        ]);

        Setting::create([
            'key' => 'YU',
            'name' => 'Youtube URL',
            'value' => 'www.youtube.com/gapp-api'
        ]);

        Setting::create([
            'key' => 'LU',
            'name' => 'LinkedIn URL',
            'value' => 'www.linkedin.com/in/gapp-api'
        ]);

        Setting::create([
            'key' => 'TEL',
            'name' => 'Telephone Number',
            'value' => '(02)833-45-67'
        ]);
    }
}
