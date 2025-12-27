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
                'room_number' => 'A',
                'gender_designation' => 'Mixed',
                'beds' => [
                    ['bed_label' => 'A-1', 'bed_number' => '1'],
                    ['bed_label' => 'A-2', 'bed_number' => '2'],
                ],
            ],
            [
                'name' => 'Male Shared Room',
                'room_number' => 'B',
                'gender_designation' => 'Male',
                'beds' => [
                    ['bed_label' => 'B-1', 'bed_number' => '1'],
                    ['bed_label' => 'B-2', 'bed_number' => '2'],
                    ['bed_label' => 'B-3', 'bed_number' => '3'],
                ],
            ],
            [
                'name' => 'Female Shared Room',
                'room_number' => 'C',
                'gender_designation' => 'Female',
                'beds' => [
                    ['bed_label' => 'C-1', 'bed_number' => '1'],
                    ['bed_label' => 'C-2', 'bed_number' => '2'],
                ],
            ],
        ];

        foreach ($rooms as $roomData) {
            $beds = $roomData['beds'];
            unset($roomData['beds']);

            $room = Room::create([
                'unit_id' => 1,
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
