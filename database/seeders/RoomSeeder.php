<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // Rooms for Building A (Unit ID: 1)
        Room::create([
            'unit_id' => 1,
            'room_number' => 'A',
            'name' => 'Room 101',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Room::create([
            'unit_id' => 1,
            'room_number' => 'B',
            'name' => 'Room 102',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for Building B (Unit ID: 2)
        Room::create([
            'unit_id' => 2,
            'room_number' => 'A',
            'name' => 'Room 201',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for East Wing (Unit ID: 3)
        Room::create([
            'unit_id' => 3,
            'room_number' => 'A',
            'name' => 'Room 301',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Room::create([
            'unit_id' => 3,
            'room_number' => 'B',
            'name' => 'Room 302',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for West Wing (Unit ID: 4)
        Room::create([
            'unit_id' => 4,
            'room_number' => 'A',
            'name' => 'Room 401',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for Male Dormitory (Unit ID: 7)
        Room::create([
            'unit_id' => 7,
            'room_number' => 'A',
            'name' => 'Male Dorm Room 1',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for Female Dormitory (Unit ID: 8)
        Room::create([
            'unit_id' => 8,
            'room_number' => 'A',
            'name' => 'Female Dorm Room 1',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for Private Rooms (Unit ID: 9)
        Room::create([
            'unit_id' => 9,
            'room_number' => 'A',
            'name' => 'Private Room 1',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for Block A (Unit ID: 10)
        Room::create([
            'unit_id' => 10,
            'room_number' => 'A',
            'name' => 'Block A Room 1',
            'gender_designation' => 'male',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Rooms for Block B (Unit ID: 11)
        Room::create([
            'unit_id' => 11,
            'room_number' => 'A',
            'name' => 'Block B Room 1',
            'gender_designation' => 'female',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
