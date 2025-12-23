<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Team::create([
                'name' => 'Team ' . $i,
                'description' => 'This is description for Team ' . $i,
            ]);
        }
    }
}
