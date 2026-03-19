<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTenantDatabases extends Command
{
    protected $signature = 'tenants:update-databases';

    protected $description = 'Update the database path for all tenants';

    public function handle()
    {
        $tenants = DB::connection('landlord')->table('tenants')->get();

        foreach ($tenants as $tenant) {
            DB::connection('landlord')->table('tenants')->where('id', $tenant->id)->update([
                'database' => database_path('tenant_' . $tenant->id . '.sqlite'),
            ]);
            $this->info("Updated database path for tenant {$tenant->name}");
        }

        return 0;
    }
}
