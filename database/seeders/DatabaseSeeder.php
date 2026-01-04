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
            UserSeeder::class,
            SettingSeeder::class,
            PropertyTypeSeeder::class,
            PropertySeeder::class,
            UnitSeeder::class,
            RoomSeeder::class,
            BedSeeder::class,
            RolePermissionSeeder::class
        ]);
    }
}
