<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Residential Properties
        Property::create([
            'name' => 'Phillips House',
            'slug' => 'phillips-house',
            'description' => 'Home of the Original Phillips Crab House To Modern Workforce Housing.',
            'address' => '2004 Philadelphia Ave Ocean City, MD 21842, USA',
            'property_type_id' => 1,
            'image_path' => '/images/property1.jpg',
            'latitude' => 38.350001,
            'longitude' => -75.078288,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Property::create([
            'name' => 'Phillips Plaza',
            'slug' => 'phillips-plaza',
            'description' => 'The Phillips Plaza To Classic Dorm-Style Living',
            'address' => '2004 Philadelphia Ave Ocean City, MD 21842, USA',
            'property_type_id' => 1,
            'image_path' => '/images/property2.jpg',
            'latitude' => 38.350001,
            'longitude' => -75.078288,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Commercial Properties
        Property::create([
            'name' => 'Phillips Plaza Suites',
            'slug' => 'phillips-plaza-suites',
            'description' => 'The Phillips Plaza Suites To Modern Workforce Housing.',
            'address' => '2004 Philadelphia Ave Ocean City, MD 21842, USA',
            'property_type_id' => 2,
            'image_path' => '/images/property3.jpg',
            'latitude' => 38.350001,
            'longitude' => -75.078288,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }
}
