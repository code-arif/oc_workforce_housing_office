<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_source' => 'self',
            'status' => 'active',

            'move_in_date' => $this->faker->date(),
            'arrival_date' => $this->faker->date(),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),

            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),

            'otp' => null,
            'otp_expires_at' => null,
            'reset_password_token' => null,
            'reset_password_token_expire_at' => null,

            'approval_token' => Str::uuid(),
            'approval_token_expires_at' => now()->addDays(7),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
