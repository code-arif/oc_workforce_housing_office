<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WorkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Maintenance',
            'Installation',
            'Inspection',
            'Repair',
            'Cleaning',
            'Delivery',
            'Logistics',
            'Construction',
            'Consultation',
            'Training',
            'Survey',
            'Design',
            'Testing',
            'Audit',
            'Support',
            'Upgrade',
            'Monitoring',
            'Fabrication',
            'Transportation',
            'Planning'
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category
            ]);
        }
    }
}
