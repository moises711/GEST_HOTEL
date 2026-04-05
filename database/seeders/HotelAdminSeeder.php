<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class HotelAdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@hotel.com';

        if (User::where('email', $email)->exists()) {
            $this->command->info("Hotel admin already exists: {$email}");
            return;
        }

        // Ensure there is at least one hotel
        $hotel = \App\Models\hotel::first();
        if (!$hotel) {
            $hotel = \App\Models\hotel::create([
                'name' => 'Demo Hotel',
                'location' => 'Demo Location',
                'description' => 'Demo hotel created by seeder',
                'id_cliente' => 0,
                'id_user' => 0,
                'database' => 'hotel_demo'
            ]);

            // Attempt to create a sqlite file and run migrations for the demo hotel
            try {
                $dir = database_path('hotels');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $dbFile = $dir . DIRECTORY_SEPARATOR . $hotel->database . '.sqlite';
                if (!file_exists($dbFile)) {
                    touch($dbFile);
                }
                $sqliteConfig = array_merge(config('database.connections.sqlite'), ['database' => $dbFile]);
                config(['database.connections.hotel' => $sqliteConfig]);
                DB::purge('hotel');
                try {
                    Artisan::call('migrate', ['--database' => 'hotel', '--force' => true]);
                } catch (\Exception $e) {
                    report($e);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        // Ensure the demo hotel has a plan (as requested: 'Pro')
        try {
            $plan = Plan::where('name', 'Básico')->orWhere('name', 'Basico')->first();
            if ($plan) {
                // attach if not already attached
                if ($hotel && !$hotel->plans()->where('plan_id', $plan->id)->exists()) {
                    // attach but mark as active=false and pending=false by default for Basico
                    $hotel->plans()->attach($plan->id, [
                        'starts_at' => now(),
                        'ends_at' => now()->addMonths($plan->duration_months ?? 1),
                        'active' => true,
                        'pending' => false,
                    ]);
                }

                // for Basico we propagate only its modules (baseline)
                try {
                    $hotel->modules = $plan->modules;
                    $hotel->save();
                } catch (\Exception $e) {
                    report($e);
                }
            }
        } catch (\Exception $e) {
            report($e);
        }

        // Create the admin user
        $user = User::create([
            'name' => 'Hotel Admin',
            'last_name' => 'Admin',
            'cell_phone' => '000000000',
            'DNI' => 'N/A',
            'ubication' => $hotel->location ?? 'N/A',
            'email' => $email,
            'password' => Hash::make('password'),
            'type' => 'admin',
            'is_superadmin' => false,
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }

        // update hotel owner id_user to this user
        try {
            if ($hotel && $hotel->id) {
                $hotel->id_user = $user->id;
                $hotel->save();
            }
        } catch (\Exception $e) {
            report($e);
        }

        // Attach to hotel
        try {
            DB::table('hotel_user')->insert([
                'hotel_id' => $hotel->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            report($e);
        }

        $this->command->info("Created hotel admin: {$email} (password: password)");
    }
}
