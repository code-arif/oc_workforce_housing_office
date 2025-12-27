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
            'name' => 'Downtown Residence',
            'slug' => 'downtown-residence',
            'description' => 'A premium residential property in downtown area',
            'address' => '123 Main Street, City Center',
            'property_type_id' => 1,
            'image_path' => '/images/property1.jpg',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Property::create([
            'name' => 'Suburban Villa',
            'slug' => 'suburban-villa',
            'description' => 'Spacious villa in peaceful suburban area',
            'address' => '456 Oak Avenue, Suburbs',
            'property_type_id' => 1,
            'image_path' => '/images/property2.jpg',
            'latitude' => 40.7580,
            'longitude' => -73.9855,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Commercial Properties
        Property::create([
            'name' => 'Business Plaza',
            'slug' => 'business-plaza',
            'description' => 'Modern commercial space for offices',
            'address' => '789 Commerce Street, Business District',
            'property_type_id' => 2,
            'image_path' => '/images/property3.jpg',
            'latitude' => 40.7489,
            'longitude' => -73.9680,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Hostel Properties
        Property::create([
            'name' => 'City Hostel',
            'slug' => 'city-hostel',
            'description' => 'Budget-friendly hostel with dormitory and private rooms',
            'address' => '321 Travel Road, Tourist Area',
            'property_type_id' => 3,
            'image_path' => '/images/property4.jpg',
            'latitude' => 40.7614,
            'longitude' => -73.9776,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Property::create([
            'name' => 'Student Housing Complex',
            'slug' => 'student-housing-complex',
            'description' => 'Modern housing complex designed for students',
            'address' => '654 University Lane, Education Zone',
            'property_type_id' => 3,
            'image_path' => '/images/property5.jpg',
            'latitude' => 40.8075,
            'longitude' => -73.9626,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Industrial Properties
        Property::create([
            'name' => 'Industrial Warehouse',
            'slug' => 'industrial-warehouse',
            'description' => 'Large warehouse facility for storage and operations',
            'address' => '987 Factory Lane, Industrial Zone',
            'property_type_id' => 4,
            'image_path' => '/images/property6.jpg',
            'latitude' => 40.6892,
            'longitude' => -73.9760,
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
