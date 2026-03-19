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
        $landlordConnection = config('multitenancy.landlord_database_connection_name', 'landlord');

        if (is_null(config("database.connections.{$landlordConnection}"))) {
            $this->error("La conexión de base de datos '{$landlordConnection}' no está configurada. Revisa config/database.php y limpia la caché de configuración.");
            return 1;
        }

        $tenants = DB::connection($landlordConnection)->table('tenants')->get();

        foreach ($tenants as $tenant) {
            DB::connection($landlordConnection)->table('tenants')->where('id', $tenant->id)->update([
                'database' => database_path('tenant_' . $tenant->id . '.sqlite'),
            ]);
            $this->info("Updated database path for tenant {$tenant->name}");
        }

        return 0;
    }
}
