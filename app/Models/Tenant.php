<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    // Permitir asignación masiva de la columna `database`
    protected $fillable = ['name', 'domain', 'database'];

    public function admins()
    {
        return $this->hasMany(\App\Models\User::class, 'tenant_id');
    }
}
