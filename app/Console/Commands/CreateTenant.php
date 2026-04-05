<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    protected $signature = 'tenants:create-tenant {name} {domain}';

    protected $description = 'Create a new tenant';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');

        $id = DB::connection('landlord')->table('tenants')->insertGetId([
            'name' => $name,
            'domain' => $domain,
            'database' => 'placeholder', // Add a placeholder value
        ]);

        $databasePath = database_path('tenant_' . $id . '.sqlite');

        DB::connection('landlord')->table('tenants')->where('id', $id)->update([
            'database' => $databasePath,
        ]);

        $this->info("Tenant {$name} created successfully!");

        return 0;
    }
}
