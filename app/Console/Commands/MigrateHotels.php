<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class MigrateHotels extends Command
{
    protected $signature = 'migrate:hotels {--hotel=* : ID(s) of hotel(s) to migrate}';

    protected $description = 'Run migrations for all hotel schemas (tenant-based)';

    public function handle()
    {
        $hotelModel = app(\App\Models\hotel::class);

        $query = $hotelModel->newQuery();
        if ($this->option('hotel')) {
            $ids = $this->option('hotel');
            $query->whereIn('id', $ids);
        }

        $hotels = $query->get();

        if ($hotels->isEmpty()) {
            $this->info('No hotels found to migrate.');
            return 0;
        }

        foreach ($hotels as $hotel) {
            $this->line("Migrating hotel {$hotel->id} - {$hotel->name}");

            if (empty($hotel->database)) {
                $this->warn("Hotel {$hotel->id} has no database/schema configured; skipping");
                continue;
            }

            $tenantConn = Config::get('database.connections.tenant') ?? Config::get('database.connections.' . env('TENANT_DB_CONNECTION', 'tenant'));
            if (!$tenantConn) {
                $this->error('Tenant DB connection not configured in config/database.php');
                return 1;
            }

            $hotelConn = $tenantConn;
            // set schema/search_path for postgres
            $hotelConn['schema'] = $hotel->database;
            Config::set('database.connections.hotel', $hotelConn);
            DB::purge('hotel');

            $this->line("Running migrations on schema {$hotel->database}");
            Artisan::call('migrate', ['--database' => 'hotel', '--force' => true]);
            $this->info(Artisan::output());
        }

        $this->info('Done.');
        return 0;
    }
}
