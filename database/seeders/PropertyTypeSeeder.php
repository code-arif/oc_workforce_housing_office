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
            'name' => 'Dorm Style Housing',
            'slug' => 'dorm-style-housing',
            'description' => '4 Person & 6 Person Rooms, Shared Bathrooms, and Laundry Facilities',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
