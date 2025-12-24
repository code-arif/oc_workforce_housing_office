<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Bed;

class RoomWithBedsSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all();
        $rooms->each(function ($room) {
            Bed::where('room_id', $room->id)->delete();
            $room->delete();
        });
        $rooms = [
            [
                'name' => 'Deluxe Room',
                'room_number' => 'R-101',
                'gender_designation' => 'Mixed',
                'beds' => [
                    ['bed_label' => 'A', 'bed_number' => 'A'],
                    ['bed_label' => 'B', 'bed_number' => 'B'],
                ],
            ],
            [
                'name' => 'Male Shared Room',
                'room_number' => 'R-102',
                'gender_designation' => 'Male',
                'beds' => [
                    ['bed_label' => 'A', 'bed_number' => 'A'],
                    ['bed_label' => 'B', 'bed_number' => 'B'],
                    ['bed_label' => 'C', 'bed_number' => 'C'],
                ],
            ],
            [
                'name' => 'Female Shared Room',
                'room_number' => 'R-103',
                'gender_designation' => 'Female',
                'beds' => [
                    ['bed_label' => 'A', 'bed_number' => 'A'],
                    ['bed_label' => 'B', 'bed_number' => 'B'],
                ],
            ],
        ];

        foreach ($rooms as $roomData) {
            $beds = $roomData['beds'];
            unset($roomData['beds']);

            $room = Room::create([
                'name' => $roomData['name'],
                'room_number' => $roomData['room_number'],
                'description' => 'Auto generated room',
                'gender_designation' => $roomData['gender_designation'],
                'is_active' => true,
            ]);

            foreach ($beds as $bed) {
                $room->beds()->create([
                    'bed_label' => $bed['bed_label'],
                    'bed_number' => $bed['bed_number'],
                    'description' => 'Auto generated bed',
                    'is_active' => true,
                ]);
            }
        }
    }
}
