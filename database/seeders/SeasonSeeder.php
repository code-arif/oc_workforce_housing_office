<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        Season::insert([
            [
                'name' => 'Summer',
                'year' => now()->year,
                'blanket_start_date' => now()->year . '-04-01',
                'blanket_end_date' => now()->year . '-09-30',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Winter',
                'year' => now()->year,
                'blanket_start_date' => now()->year . '-10-01',
                'blanket_end_date' => now()->year . '-03-31',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
