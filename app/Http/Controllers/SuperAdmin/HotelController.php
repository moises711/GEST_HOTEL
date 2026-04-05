<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB as DBFacade;
use App\Services\HotelPlanService;

class HotelController extends Controller
{
    public function __construct(private HotelPlanService $hotelPlanService)
    {
    }

    private function resolveHotelConnection($hotel): \Illuminate\Database\Connection
    {
        if ($hotel && !empty($hotel->database)) {
            $tenantConn = Config::get('database.connections.tenant') ?? Config::get('database.connections.' . env('TENANT_DB_CONNECTION', 'tenant'));
            if ($tenantConn) {
                $hotelConn = $tenantConn;
                $hotelConn['schema'] = $hotel->database;
                Config::set('database.connections.hotel', $hotelConn);
                DBFacade::purge('hotel');

                try {
                    $candidate = DBFacade::connection('hotel');
                    if ($candidate->getSchemaBuilder()->hasTable('rooms')) {
                        return $candidate;
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return DBFacade::connection(config('database.default'));
    }

    private function computeHotelReports($hotel): array
    {
        $reports = [
            'occupancy_rate_monthly' => 0,
            'average_daily_rate' => 0,
            'total_revenue_monthly' => 0,
        ];

        try {
            $conn = $this->resolveHotelConnection($hotel);
            $schema = $conn->getSchemaBuilder();

            if (!$schema->hasTable('rooms')) {
                return $reports;
            }

            $totalRooms = (int) $conn->table('rooms')->count();
            $occupiedRooms = $schema->hasColumn('rooms', 'is_available')
                ? (int) $conn->table('rooms')->where('is_available', false)->count()
                : 0;

            $adr = $schema->hasColumn('rooms', 'price_per_night')
                ? (float) $conn->table('rooms')->whereNotNull('price_per_night')->avg('price_per_night')
                : 0.0;

            $monthlyRevenue = 0.0;

            if ($schema->hasTable('registros') && $schema->hasColumn('registros', 'check_in') && $schema->hasColumn('registros', 'check_out') && $schema->hasColumn('registros', 'id_room')) {
                $rows = $conn->table('registros')
                    ->whereMonth('check_in', now()->month)
                    ->whereYear('check_in', now()->year)
                    ->get();

                foreach ($rows as $row) {
                    $days = 1;
                    if (!empty($row->check_in) && !empty($row->check_out)) {
                        $start = \Carbon\Carbon::parse($row->check_in);
                        $end = \Carbon\Carbon::parse($row->check_out);
                        $days = max(1, $start->diffInDays($end));
                    }

                    $roomRate = 0.0;
                    if ($schema->hasColumn('rooms', 'price_per_night')) {
                        $roomRate = (float) ($conn->table('rooms')->where('id', (int) $row->id_room)->value('price_per_night') ?? 0);
                    }

                    $monthlyRevenue += ($roomRate * $days);
                }
            }

            if ($monthlyRevenue <= 0 && $adr > 0 && $occupiedRooms > 0) {
                $monthlyRevenue = $occupiedRooms * $adr * 30;
            }

            $reports['occupancy_rate_monthly'] = $totalRooms > 0
                ? (int) round(($occupiedRooms / $totalRooms) * 100)
                : 0;
            $reports['average_daily_rate'] = round($adr, 2);
            $reports['total_revenue_monthly'] = round($monthlyRevenue, 2);
        } catch (\Throwable $e) {
        }

        return $reports;
    }

    /**
     * Muestra una lista de todos los hoteles gestionados.
     */
    public function index()
    {
        $q = request('q');
        $planId = request('plan_id');

        $query = \App\Models\hotel::query()->with(['owners','plans']);

        if ($q) {
            $query->where(function($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('location', 'like', "%{$q}%");
            });
        }

        if ($planId) {
            $query->whereHas('plans', function($p) use ($planId) {
                $p->where('plans.id', $planId);
            });
        }

        $hotels = $query->orderBy('id','desc')->paginate(10)->withQueryString();

        $hotelsTransformed = $hotels->through(function($hotel) {
            $owner = $hotel->owners->first();
            $currentPlan = $hotel->plans->sortByDesc(function($p) {
                return $p->pivot->ends_at ?? null;
            })->first();

            $status = 'activo';
            $expiresAt = $currentPlan ? ($currentPlan->pivot->ends_at) : null;
            if ($expiresAt && \Carbon\Carbon::parse($expiresAt)->isPast()) {
                $status = 'vencido';
            }

            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'location' => $hotel->location,
                'owner' => $owner?->name,
                'owner_email' => $owner?->email,
                'status' => $status,
                'plan' => $currentPlan?->name,
                'expires_at' => $expiresAt,
            ];
        });

        return Inertia::render('SuperAdmin/Hotels/Index', [
            'hotels' => $hotelsTransformed,
            'filters' => request()->only(['q','plan_id']),
            'plans' => \App\Models\Plan::select('id','name')->get(),
        ]);
    }

    /**
     * Show form to create a hotel and its admin user.
     */
    public function create()
    {
        return Inertia::render('SuperAdmin/Tenants/Create');
    }

    /**
     * Store a new hotel and create admin user + provision DB file.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'database' => 'nullable|string|max:255|unique:hotels,database',
            // admin
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'plan_id' => 'nullable|exists:plans,id',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:start_date',
            'duration_months' => 'nullable|integer|min:1|max:60',
            'modules' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $hotel = \App\Models\hotel::create([
            'name' => $data['name'],
            'location' => $data['location'] ?? 'Sin ubicación',
            'description' => $data['description'] ?? 'Sin descripción',
            'id_cliente' => 0,
            'id_user' => 0,
            'database' => $data['database'] ?? null,
        ]);

        // save modules/settings if provided
        if (!empty($data['modules']) && is_array($data['modules'])) {
            $hotel->modules = $data['modules'];
            $hotel->save();
        }
        if (!empty($data['settings']) && is_array($data['settings'])) {
            $hotel->settings = $data['settings'];
            $hotel->save();
        }

        if (empty($hotel->database)) {
            $hotel->database = 'hotel_'.$hotel->id;
            $hotel->save();
        }

        // create sqlite file for hotel DB
        try {
            $dir = database_path('hotels');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $dbFile = $dir.DIRECTORY_SEPARATOR.$hotel->database.'.sqlite';
            if (!file_exists($dbFile)) {
                touch($dbFile);
            }
            // configure a runtime connection named 'hotel' that points to this sqlite file
            $sqliteConfig = array_merge(Config::get('database.connections.sqlite'), ['database' => $dbFile]);
            Config::set('database.connections.hotel', $sqliteConfig);
            DBFacade::purge('hotel');

            // Run migrations on the hotel's sqlite database. If you have tenant-specific migrations
            // put them under database/migrations/tenants and set the --path option accordingly.
            try {
                Artisan::call('migrate', [
                    '--database' => 'hotel',
                    '--force' => true,
                ]);
            } catch (\Exception $e) {
                report($e);
            }
        } catch (\Exception $e) {
            report($e);
        }

        // create admin user and attach to hotel
        $user = \App\Models\User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => \Hash::make($data['admin_password']),
            'type' => 'admin',
        ]);

        \DB::table('hotel_user')->insert([
            'hotel_id' => $hotel->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hotel->id_user = $user->id;
        $hotel->save();

        if (!empty($data['plan_id'])) {
            $plan = \App\Models\Plan::find($data['plan_id']);
            if ($plan) {
                $this->hotelPlanService->applyNow(
                    $hotel,
                    $plan,
                    $data['start_date'] ?? null,
                    $data['expiration_date'] ?? null,
                    isset($data['duration_months']) ? (int) $data['duration_months'] : null
                );
            }
        }

        return redirect()->route('superadmin.hotels.index')->with('success', 'Hotel y administrador creados.');
    }

    /**
     * Muestra los detalles de un hotel específico, incluyendo contrato, módulos y reportes.
     */
    public function show($id)
    {
        $hotel = \App\Models\hotel::with(['owners','plans'])->findOrFail($id);
        $plans = \App\Models\Plan::select('id','name','price','modules')->get();

        $hotelPayload = $hotel->toArray();
        $hotelPayload['reports'] = $this->computeHotelReports($hotel);

        return Inertia::render('SuperAdmin/Hotel/Show', [
            'hotel' => $hotelPayload,
            'plans' => $plans,
        ]);
    }

    /**
     * Cambiar plan de un hotel. recibe `plan_id` y `mode` (apply|pending).
     */
    public function changePlan(Request $request, $hotelId)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'mode' => 'nullable|string|in:apply,pending',
        ]);

        $hotel = \App\Models\hotel::findOrFail($hotelId);
        $plan = \App\Models\Plan::findOrFail($data['plan_id']);

        if (($data['mode'] ?? 'pending') === 'apply') {
            $this->hotelPlanService->applyNow($hotel, $plan);
            return back()->with('success', "Plan {$plan->name} aplicado a {$hotel->name}.");
        }

        $this->hotelPlanService->markPending($hotel, $plan);

        return back()->with('success', "Plan {$plan->name} marcado como pendiente para {$hotel->name}.");
    }

    /**
     * Actualiza los módulos de un hotel (activar/desactivar módulos desde UI).
     */
    public function updateModules(Request $request, $hotelId)
    {
        $data = $request->validate([
            'modules' => 'required|array',
        ]);

        $hotel = \App\Models\hotel::findOrFail($hotelId);
        // modules form is an object { moduleId: true/false }
        $modules = array_keys(array_filter($data['modules']));
        $hotel->modules = $modules;
        $hotel->save();

        return back()->with('success', 'Módulos actualizados.');
    }

    /**
     * Renueva el contrato activo del hotel (extiende el ends_at en +1 mes).
     */
    public function renewContract(Request $request, $hotelId)
    {
        $hotel = \App\Models\hotel::with('plans')->findOrFail($hotelId);
        $current = $hotel->plans()->wherePivot('active', true)->orderByDesc('hotel_plan.ends_at')->first();
        if (!$current) {
            return back()->withErrors(['error' => 'No hay contrato activo para renovar.']);
        }
        $pivot = DBFacade::table('hotel_plan')->where('hotel_id', $hotel->id)->where('plan_id', $current->id)->orderByDesc('ends_at')->first();
        $newEnd = \Carbon\Carbon::parse($pivot->ends_at)->addMonth()->toDateString();
        $hotel->plans()->updateExistingPivot($current->id, ['ends_at' => $newEnd]);
        return back()->with('success', 'Contrato renovado hasta ' . $newEnd);
    }

    /**
     * Marca el hotel como desactivado en settings.
     */
    public function deactivateHotel(Request $request, $hotelId)
    {
        $hotel = \App\Models\hotel::findOrFail($hotelId);
        $settings = $hotel->settings ?? [];
        $settings['suspended'] = true;
        $hotel->settings = $settings;
        $hotel->save();
        return back()->with('success', 'Hotel desactivado.');
    }

    /**
     * Ejecuta el comando para aplicar planes pendientes desde UI.
     */
    public function applyPending()
    {
        try {
            \Artisan::call('hotel:apply-pending-plans');
        } catch (\Exception $e) {
            report($e);
            return back()->withErrors(['error' => 'Error al aplicar planes pendientes.']);
        }
        return back()->with('success', 'Planes pendientes aplicados.');
    }

    /**
     * Edit form for hotel (uses Show view with edit flag).
     */
    public function edit($id)
    {
        $hotel = \App\Models\hotel::with(['owners','plans'])->findOrFail($id);
        $plans = \App\Models\Plan::select('id','name','price','modules')->get();

        $hotelPayload = $hotel->toArray();
        $hotelPayload['reports'] = $this->computeHotelReports($hotel);

        return Inertia::render('SuperAdmin/Hotel/Show', [
            'hotel' => $hotelPayload,
            'plans' => $plans,
            'editing' => true,
        ]);
    }

    /**
     * Update hotel basic info.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'modules' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $hotel = \App\Models\hotel::findOrFail($id);
        $hotel->name = $data['name'];
        $hotel->location = $data['location'] ?? $hotel->location;
        $hotel->description = $data['description'] ?? $hotel->description;
        if (array_key_exists('modules', $data)) {
            $hotel->modules = $data['modules'];
        }
        if (array_key_exists('settings', $data)) {
            $hotel->settings = $data['settings'];
        }
        $hotel->save();

        return redirect()->route('superadmin.hotels.show', $hotel->id)->with('success', 'Hotel actualizado.');
    }

    /**
     * Delete hotel (soft delete not implemented — this will remove record).
     */
    public function destroy($id)
    {
        $hotel = \App\Models\hotel::findOrFail($id);
        // optionally add checks (linked data)
        $hotel->delete();
        return redirect()->route('superadmin.hotels.index')->with('success', 'Hotel eliminado.');
    }
}
