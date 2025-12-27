<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rooms')->insert([
            [
                'unit_id' => 1,
                'name' => 'Deluxe Room',
                'room_number' => 'D',
                'description' => 'Spacious deluxe room with attached bathroom',
                'gender_designation' => 'Mixed',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'unit_id' => 2,
                'name' => 'Male Shared Room',
                'room_number' => 'E',
                'description' => 'Shared room for male residents',
                'gender_designation' => 'Male',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'unit_id' => 3,
                'name' => 'Female Shared Room',
                'room_number' => 'F',
                'description' => 'Shared room for female residents',
                'gender_designation' => 'Female',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'unit_id' => 2,
                'name' => 'Inactive Room',
                'room_number' => 'G',
                'description' => 'Currently unavailable',
                'gender_designation' => null,
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
