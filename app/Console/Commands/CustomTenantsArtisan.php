<?php

namespace App\Console\Commands;

use Illuminate\Support\Arr;
use Spatie\Multitenancy\Commands\TenantsArtisanCommand;
use Illuminate\Support\Facades\DB;

class CustomTenantsArtisan extends TenantsArtisanCommand
{
    protected $name = 'custom:tenants:artisan';

    public function getTenants()
    {
        $tenantModel = config('multitenancy.tenant_model');

        $tenantQuery = DB::connection('landlord')->table(app($tenantModel)->getTable());

        if ($this->option('tenant')) {
            $tenantQuery->whereIn('id', Arr::wrap($this->option('tenant')));
        }

        return $tenantQuery->get();
    }
}
