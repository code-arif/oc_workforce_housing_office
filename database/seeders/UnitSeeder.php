<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Units for Downtown Residence (Property ID: 1)
        Unit::create([
            'property_id' => 1,
            'name' => '101',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 1,
            'name' => '102',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Units for Suburban Villa (Property ID: 2)
        Unit::create([
            'property_id' => 2,
            'name' => '201',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 2,
            'name' => '202',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Units for Business Plaza (Property ID: 3)
        Unit::create([
            'property_id' => 3,
            'name' => '301',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 3,
            'name' => '302',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Units for City Hostel (Property ID: 4)
        Unit::create([
            'property_id' => 4,
            'name' => '401',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 4,
            'name' => '402',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 4,
            'name' => '403',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Units for Student Housing Complex (Property ID: 5)
        Unit::create([
            'property_id' => 5,
            'name' => '501',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 5,
            'name' => '502',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 5,
            'name' => '503',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Units for Industrial Warehouse (Property ID: 6)
        Unit::create([
            'property_id' => 6,
            'name' => '601',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Unit::create([
            'property_id' => 6,
            'name' => '602',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
