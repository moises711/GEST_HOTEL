<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\HotelPlanService;

class AdminController extends Controller
{
    public function __construct(private HotelPlanService $hotelPlanService)
    {
    }

    /**
     * Muestra una lista de todos los administradores de hotel.
     */
    public function index()
    {
        $q = request('q');

        $query = \App\Models\User::where('type','admin')->with('hotels');

        if ($q) {
            $query->where(function($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            });
        }

        $admins = $query->orderBy('id','desc')->paginate(10)->withQueryString();

        $adminsTransformed = $admins->through(function($admin) {
            $hotel = $admin->hotels->first();
            $planInfo = null;
            if ($hotel) {
                $planRow = \DB::table('hotel_plan')->where('hotel_id', $hotel->id)->orderByDesc('ends_at')->first();
                if ($planRow) {
                    $plan = \App\Models\Plan::find($planRow->plan_id);
                    $planInfo = [
                        'name' => $plan?->name,
                        'starts_at' => $planRow->starts_at,
                        'ends_at' => $planRow->ends_at,
                    ];
                }
            }

            return [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'hotel' => $hotel ? ['id' => $hotel->id, 'name' => $hotel->name] : null,
                'plan' => $planInfo,
            ];
        });

        return Inertia::render('SuperAdmin/AdminIndex', [
            'admins' => $adminsTransformed,
            'filters' => request()->only('q'),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo administrador de hotel.
     */
    public function create()
    {
        // Avoid selecting `settings` column directly because it may not exist
        // in some DB setups. Load id and name; the `settings` attribute
        // will be available after migrations are applied or when the model
        // is accessed (it will return null if column missing).
        $hotels = \App\Models\hotel::select('id','name')->get();
        $plans = \App\Models\Plan::select('id','name','price')->get();
        return Inertia::render('SuperAdmin/CreateAdmin', [
            'hotels' => $hotels,
            'plans' => $plans,
        ]);
    }

    /**
     * Almacena un nuevo administrador de hotel en la base de datos.
     */
    public function store(Request $request)
    {
        $request->merge([
            'hotel_id' => $request->filled('hotel_id') ? (int) $request->input('hotel_id') : null,
            'plan_id' => $request->filled('plan_id') ? (int) $request->input('plan_id') : null,
            'duration_months' => $request->filled('duration_months') ? (int) $request->input('duration_months') : null,
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'hotel_id' => 'nullable|exists:hotels,id',
            'hotel_name' => 'required_without:hotel_id|string|max:255',
            'hotel_location' => 'nullable|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:60',
        ]);

        DB::beginTransaction();
        try {
            $hotelId = $data['hotel_id'] ?? null;

            if (!$hotelId) {
                if (empty($data['hotel_name'])) {
                    throw new \Exception('Debe proporcionar un nombre para el hotel nuevo');
                }

                $hotel = new \App\Models\hotel();
                $hotel->name = $data['hotel_name'];
                $hotel->location = $data['hotel_location'] ?? 'Sin ubicación';
                $hotel->description = 'Creado desde alta de administrador';
                $hotel->id_cliente = 0;
                $hotel->id_user = 0;
                $hotel->save();

                // Provision tenant schema in Postgres using the configured `tenant` connection.
                // This step should not block admin creation if tenant provisioning fails.
                $schemaName = 'hotel_' . $hotel->id;
                try {
                    // Ensure schema exists on tenant Postgres
                    DB::connection('tenant')->statement('CREATE SCHEMA IF NOT EXISTS "' . $schemaName . '"');
                } catch (\Exception) {
                    $tenantConnName = env('TENANT_DB_CONNECTION', 'tenant');
                    DB::connection($tenantConnName)->statement('CREATE SCHEMA IF NOT EXISTS "' . $schemaName . '"');
                }

                $hotel->database = $schemaName;
                $hotel->save();

                try {
                    $tenantConnConfig = Config::get('database.connections.tenant') ?? Config::get('database.connections.' . env('TENANT_DB_CONNECTION', 'tenant'));
                    if ($tenantConnConfig) {
                        $hotelConn = $tenantConnConfig;
                        $hotelConn['schema'] = $schemaName;
                        Config::set('database.connections.hotel', $hotelConn);
                        DB::purge('hotel');

                        Artisan::call('migrate', ['--database' => 'hotel', '--force' => true]);
                    }
                } catch (\Exception $tenantProvisionError) {
                    report($tenantProvisionError);
                }

                $hotelId = $hotel->id;
            }

            // Create user (fill required not-null fields with defaults)
            // Before creating, enforce hotel's max_users limit if present
            $hotel = \App\Models\hotel::find($hotelId);
            if ($hotel) {
                $max = $hotel->settings['max_users'] ?? null;
                if (!is_null($max)) {
                    $currentCount = DB::table('hotel_user')->where('hotel_id', $hotelId)->count();
                    if ($currentCount >= $max) {
                        throw new \Exception('El hotel alcanzó el número máximo de usuarios permitido por su plan.');
                    }
                }
            }

            $user = \App\Models\User::create([
                'name' => $data['name'],
                'last_name' => $data['name'],
                'cell_phone' => '000000000',
                'DNI' => 'N/A',
                'ubication' => $data['hotel_location'] ?? 'N/A',
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'type' => 'admin',
                'is_superadmin' => false,
            ]);

            // Assign role if roles system present
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('admin');
            }

            // Attach user to hotel (owner/manager)
            \DB::table('hotel_user')->insert([
                'hotel_id' => $hotelId,
                'user_id' => $user->id,
                'role' => $data['role'] ?? 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $hotel = \App\Models\hotel::find($hotelId);
            if ($hotel) {
                $hotel->id_user = $user->id;
                $hotel->save();
            }

            // Assign and apply plan in a unified way
            $plan = \App\Models\Plan::find($data['plan_id']);
            if ($hotel && $plan) {
                $this->hotelPlanService->applyNow(
                    $hotel,
                    $plan,
                    null,
                    null,
                    (int) $data['duration_months']
                );
            }

            DB::commit();
            return redirect()->route('superadmin.admins.index')->with('success', 'Administrador creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario para editar un administrador existente.
     */
    public function edit($id)
    {
        $admin = User::where('type', 'admin')->with('hotels')->findOrFail($id);

        $planName = null;
        $durationMonths = 1;

        $hotel = $admin->hotels->first();
        if ($hotel) {
            $planRow = DB::table('hotel_plan')
                ->where('hotel_id', $hotel->id)
                ->orderByDesc('ends_at')
                ->first();

            if ($planRow) {
                $plan = \App\Models\Plan::find($planRow->plan_id);
                $planName = $plan?->name;
                if (!empty($planRow->starts_at) && !empty($planRow->ends_at)) {
                    $durationMonths = max(1, \Carbon\Carbon::parse($planRow->starts_at)->diffInMonths(\Carbon\Carbon::parse($planRow->ends_at)));
                } elseif ($plan?->duration_months) {
                    $durationMonths = (int) $plan->duration_months;
                }
            }
        }

        return Inertia::render('SuperAdmin/EditAdmin', [
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'plan_name' => $planName,
                'duration_months' => $durationMonths,
            ],
        ]);
    }

    /**
     * Actualiza un administrador existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('type', 'admin')->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|confirmed|min:6',
        ]);

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->save();

        return redirect()->route('superadmin.admins.index')->with('success', 'Administrador actualizado exitosamente.');
    }

    /**
     * Elimina un administrador.
     */
    public function destroy($id)
    {
        $admin = User::where('type', 'admin')->findOrFail($id);

        DB::transaction(function () use ($admin) {
            DB::table('hotel_user')->where('user_id', $admin->id)->delete();
            DB::table('role_user')->where('user_id', $admin->id)->delete();
            $admin->delete();
        });

        return redirect()->route('superadmin.admins.index')->with('success', 'Administrador eliminado exitosamente.');
    }
}
