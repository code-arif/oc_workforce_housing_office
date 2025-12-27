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
                'room_number' => 'D',
                'gender_designation' => 'male',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'unit_id' => 2,
                'room_number' => 'E',
                'gender_designation' => 'male',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'unit_id' => 3,
                'room_number' => 'F',
                'gender_designation' => 'female',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'unit_id' => 2,
                'room_number' => 'G',
                'gender_designation' => null,
                'is_active' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
