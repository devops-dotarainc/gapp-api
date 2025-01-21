<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FloodUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userCount = User::all()->count();

        $userLimit = (int) env('SEEDER_LIMIT', 1) + $userCount;

        $start = 1 + $userCount;

        $password = Hash::make('password');

        for($i = $start; $i < $userLimit; $i++) {
            User::create([
                'username' => 'user-' . $i,
                'password' => $password,
                'role' => rand(1, 3)
            ]);
        }
    }
}
