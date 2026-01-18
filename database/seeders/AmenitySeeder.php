<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('amenities')->insert([
            ['name' => 'Swimming Pool'],
            ['name' => 'Fitness Center'],
            ['name' => 'Parking Garage'],
            ['name' => 'Community Room'],
            ['name' => 'Playground'],
            ['name' => 'Laundry Service'],
            ['name' => 'Pet Park'],
            ['name' => 'Rooftop Terrace'],
            ['name' => 'Business Center'],
            ['name' => 'Bicycle Storage'],
            ['name' => 'Garden'],
            ['name' => 'Security System'],

        ]);
    }
}
