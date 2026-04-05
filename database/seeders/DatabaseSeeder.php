<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Create default superadmin and any other seeders
        $this->call(\Database\Seeders\RoleSeeder::class);
        $this->call(\Database\Seeders\AdminSeeder::class);
        $this->call(\Database\Seeders\PlanSeeder::class);
        $this->call(\Database\Seeders\HotelAdminSeeder::class);
        $this->call(\Database\Seeders\ServiceSeeder::class);
    }
}
