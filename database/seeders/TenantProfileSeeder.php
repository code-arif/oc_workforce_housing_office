<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantProfile;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantProfileSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::where('status', 'active')->take(10)->get();

        foreach ($tenants as $tenant) {
            TenantProfile::create([
                'tenant_id' => $tenant->id,
                'first_name' => fake()->firstName(),
                'middle_name' => fake()->optional()->firstName(),
                'last_name' => fake()->lastName(),
                'phone' => fake()->phoneNumber(),
                'country_code' => '+880',
                'avatar' => null,
            ]);
        }
    }
}
