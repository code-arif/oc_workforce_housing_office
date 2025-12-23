<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'phone' => '1234546788',
                'role' => 'admin',
                'password' => Hash::make('12345678'), // admin hashed password
            ],
        ];

        // Generate 19 employees
        for ($i = 1; $i <= 19; $i++) {
            $users[] = [
                'name' => 'employee' . $i,
                'email' => 'employee' . $i . '@gmail.com',
                'phone' => '9876543' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'role' => 'employee',
                'password' => '12345678',
            ];
        }

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
