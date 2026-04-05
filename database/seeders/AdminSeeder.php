<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@admin.com';

        if (User::where('email', $email)->exists()) {
            $this->command->info("Superadmin already exists: {$email}");
            return;
        }

        User::create([
            'name' => 'Super Admin',
            'last_name' => 'Administrator',
            'cell_phone' => '',
            'DNI' => '',
            'ubication' => '',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_superadmin' => true,
            'type' => 'superadmin',
        ]);

        $this->command->info("Created superadmin: {$email} (password: password)");
    }
}
