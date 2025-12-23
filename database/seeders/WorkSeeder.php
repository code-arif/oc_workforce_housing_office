<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Work;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all team IDs
        $teams = Team::pluck('id')->toArray();
        // Fetch all category IDs
        $categories = Category::pluck('id')->toArray();

        for ($i = 1; $i <= 20; $i++) {
            $workDate = now()->addDays($i);

            // Random all day
            $isAllDay = rand(0, 1) == 1;

            if ($isAllDay) {
                // All day event
                $startDatetime = $workDate->copy()->startOfDay();
                $endDatetime = $workDate->copy()->endOfDay();
            } else {
                // Specific time event
                $startHour = rand(8, 16); // 8 AM to 4 PM
                $startMinute = rand(0, 3) * 15; // 0, 15, 30, 45
                $duration = rand(1, 4); // 1 to 4 hours

                $startDatetime = $workDate->copy()->setTime($startHour, $startMinute, 0);
                $endDatetime = $startDatetime->copy()->addHours($duration);
            }

            Work::create([
                'title' => 'Work Task ' . $i,
                'description' => 'Description for Work Task ' . $i,
                'location' => 'Location ' . $i,
                'latitude' => 23.7000 + ($i * 0.001), // dummy latitude
                'longitude' => 90.4000 + ($i * 0.001), // dummy longitude
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'is_all_day' => $isAllDay,
                'is_completed' => rand(0, 1) == 1, // random completed status
                'is_rescheduled' => false,
                'note' => 'Note for Work Task ' . $i,
                'team_id' => $teams[array_rand($teams)], // random team
                'category_id' => $categories[array_rand($categories)], // random category
                'google_event_id' => null, // initially no Google sync
                'google_synced_at' => null,
            ]);
        }
    }
}
