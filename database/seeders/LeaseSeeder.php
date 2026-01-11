<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\Season;
use App\Models\User;

class LeaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenants   = Tenant::take(10)->get();
        $properties = Property::all();
        $seasons   = Season::all();
        $admin     = User::first(); // created_by

        foreach ($tenants as $tenant) {
            $season = $seasons->random();

            Lease::create([
                'tenant_id' => $tenant->id,
                'property_id' => $properties->random()->id,
                'season_id' => $season->id,

                'status' => 'ACTIVE',

                'start_date' => $season->blanket_start_date,
                'end_date' => $season->blanket_end_date,

                'rent_amount' => fake()->numberBetween(800, 2000),
                'deposit_amount' => fake()->numberBetween(300, 800),

                'payment_frequency' => fake()->randomElement([
                    'MONTHLY',
                    'WEEKLY',
                    'BIWEEKLY'
                ]),

                'notes' => fake()->sentence(),
                'created_by' => $admin?->id,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
