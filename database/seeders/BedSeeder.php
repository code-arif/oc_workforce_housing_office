<?php

namespace Database\Seeders;

use App\Models\Bed;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Beds for Room 1 (Building A)
        Bed::create([
            'room_id' => 1,
            'bed_number' => 1,
            'bed_label' => 'A1',
            'base_rent' => 5000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 1,
            'bed_number' => 2,
            'bed_label' => 'A2',
            'base_rent' => 5000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 2 (Building A)
        Bed::create([
            'room_id' => 2,
            'bed_number' => 1,
            'bed_label' => 'B1',
            'base_rent' => 6000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 2,
            'bed_number' => 2,
            'bed_label' => 'B2',
            'base_rent' => 6000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 2,
            'bed_number' => 3,
            'bed_label' => 'B3',
            'base_rent' => 6000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 3 (Building B)
        Bed::create([
            'room_id' => 3,
            'bed_number' => 1,
            'bed_label' => 'C1',
            'base_rent' => 5500,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 3,
            'bed_number' => 2,
            'bed_label' => 'C2',
            'base_rent' => 5500,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 4 (East Wing)
        Bed::create([
            'room_id' => 4,
            'bed_number' => 1,
            'bed_label' => 'D1',
            'base_rent' => 7000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 4,
            'bed_number' => 2,
            'bed_label' => 'D2',
            'base_rent' => 7000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 5 (East Wing)
        Bed::create([
            'room_id' => 5,
            'bed_number' => 1,
            'bed_label' => 'E1',
            'base_rent' => 6500,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 6 (West Wing)
        Bed::create([
            'room_id' => 6,
            'bed_number' => 1,
            'bed_label' => 'F1',
            'base_rent' => 7500,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 6,
            'bed_number' => 2,
            'bed_label' => 'F2',
            'base_rent' => 7500,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 7 (Male Dormitory - City Hostel)
        Bed::create([
            'room_id' => 7,
            'bed_number' => 1,
            'bed_label' => 'M1',
            'base_rent' => 3000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 7,
            'bed_number' => 2,
            'bed_label' => 'M2',
            'base_rent' => 3000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 7,
            'bed_number' => 3,
            'bed_label' => 'M3',
            'base_rent' => 3000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 8 (Female Dormitory - City Hostel)
        Bed::create([
            'room_id' => 8,
            'bed_number' => 1,
            'bed_label' => 'F1',
            'base_rent' => 3000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 8,
            'bed_number' => 2,
            'bed_label' => 'F2',
            'base_rent' => 3000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 9 (Private Rooms - City Hostel)
        Bed::create([
            'room_id' => 9,
            'bed_number' => 1,
            'bed_label' => 'P1',
            'base_rent' => 5000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 10 (Block A - Student Housing)
        Bed::create([
            'room_id' => 10,
            'bed_number' => 1,
            'bed_label' => 'SA1',
            'base_rent' => 4000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Bed::create([
            'room_id' => 10,
            'bed_number' => 2,
            'bed_label' => 'SA2',
            'base_rent' => 4000,
            'is_occupied' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Beds for Room 11 (Block B - Student Housing)
        Bed::create([
            'room_id' => 11,
            'bed_number' => 1,
            'bed_label' => 'SB1',
            'base_rent' => 4000,
            'is_occupied' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
