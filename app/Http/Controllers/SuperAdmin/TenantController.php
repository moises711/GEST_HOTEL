<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants for SuperAdmin.
     */
    public function index()
    {
        $tenants = Tenant::all()->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'domain' => $t->domain,
                'database' => $t->database ?? null,
                'created_at' => $t->created_at?->toDateTimeString(),
            ];
        });

        return Inertia::render('SuperAdmin/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /** Show form to create tenant + admin credentials */
    public function create()
    {
        return Inertia::render('SuperAdmin/Tenants/Create');
    }

    /** Store tenant and create admin user tied to tenant */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'database' => 'nullable|string|max:255|unique:tenants,database',
            // admin credentials
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        // create tenant
        $tenant = Tenant::create([
            'name' => $data['name'],
            'domain' => $data['domain'] ?? null,
            'database' => $data['database'] ?? null,
        ]);

        // ensure tenant database name exists; default to tenant_{id}
        if (empty($tenant->database)) {
            $tenant->database = 'tenant_'.$tenant->id;
            $tenant->save();
        }

        // create sqlite file for tenant DB
        try {
            $dir = database_path('tenants');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $dbFile = $dir.DIRECTORY_SEPARATOR.$tenant->database.'.sqlite';
            if (!file_exists($dbFile)) {
                touch($dbFile);
            }
        } catch (\Exception $e) {
            report($e);
        }

        // create admin user in central users table tied to tenant
        $user = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'is_superadmin' => false,
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('superadmin.hotels.index')->with('success', 'Instancia y administrador creados.');
    }
}
