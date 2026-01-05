<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            PropertyTypeSeeder::class,
            PropertySeeder::class,
            UnitSeeder::class,
            RoomSeeder::class,
            BedSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
