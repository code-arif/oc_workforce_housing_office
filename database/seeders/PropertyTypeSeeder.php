<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyType::create([
            'name' => 'Residential',
            'slug' => 'residential',
            'description' => 'Residential properties including apartments, villas, and houses',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        PropertyType::create([
            'name' => 'Commercial',
            'slug' => 'commercial',
            'description' => 'Commercial properties including offices and retail spaces',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        PropertyType::create([
            'name' => 'Hostel',
            'slug' => 'hostel',
            'description' => 'Hostel and accommodation properties',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        PropertyType::create([
            'name' => 'Industrial',
            'slug' => 'industrial',
            'description' => 'Industrial and warehouse properties',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
