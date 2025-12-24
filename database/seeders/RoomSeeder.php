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
                'name' => 'Deluxe Room',
                'room_number' => 'R-104',
                'description' => 'Spacious deluxe room with attached bathroom',
                'gender_designation' => 'Mixed',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Male Shared Room',
                'room_number' => 'R-105',
                'description' => 'Shared room for male residents',
                'gender_designation' => 'Male',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Female Shared Room',
                'room_number' => 'R-106',
                'description' => 'Shared room for female residents',
                'gender_designation' => 'Female',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Inactive Room',
                'room_number' => 'R-107',
                'description' => 'Currently unavailable',
                'gender_designation' => null,
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
