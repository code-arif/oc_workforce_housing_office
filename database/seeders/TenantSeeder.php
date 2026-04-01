<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::factory()->count(10)->create([
            'status' => 'approved',
        ]);
    }
}
