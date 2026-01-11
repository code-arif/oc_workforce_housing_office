<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lease;
use App\Models\Bed;
use App\Models\LeaseAssignment;

class LeaseAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $leases = Lease::take(10)->get();
        $beds   = Bed::all();

        foreach ($leases as $lease) {
            LeaseAssignment::create([
                'lease_id' => $lease->id,
                'bed_id' => $beds->random()->id,

                'assigned_at' => now(),
                'actual_move_in' => $lease->start_date,
                'actual_move_out' => null,

                'is_current' => true,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
