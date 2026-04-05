<?php

namespace App\Http\Controllers\Admin;
    
use App\Http\Controllers\Controller;
use App\Models\CheckinEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ResolvesPlanConfiguration;

    private function resolvePreferredConnection(?object $hotel): \Illuminate\Database\Connection
    {
        if ($hotel && !empty($hotel->database)) {
            $tenantConn = Config::get('database.connections.tenant') ?? Config::get('database.connections.' . env('TENANT_DB_CONNECTION', 'tenant'));
            if ($tenantConn) {
                $hotelConn = $tenantConn;
                $hotelConn['schema'] = $hotel->database;
                Config::set('database.connections.hotel', $hotelConn);
                DB::purge('hotel');

                try {
                    $candidate = DB::connection('hotel');
                    if ($candidate->getSchemaBuilder()->hasTable('rooms')) {
                        return $candidate;
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return DB::connection(config('database.default'));
    }

    public function index(Request $request)
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $enabledModules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        $experience = $this->resolveExperienceConfiguration($activePlan);

        $kpis = [
            'occupancy_rate' => 0,
            'daily_revenue' => 0,
            'monthly_revenue' => 0,
            'next_checkins' => 0,
            'next_checkouts' => 0,
            'rooms_available' => 0,
            'rooms_occupied' => 0,
            'rooms_maintenance' => 0,
        ];

        $features = $this->planFeatures($activePlan);
        $alerts = [];
        $todayReservations = [];
        $roomStatus = [];

        try {
            $conn = $this->resolvePreferredConnection($hotel);
            $schema = $conn->getSchemaBuilder();

            if ($schema->hasTable('rooms')) {
                $totalRooms = (int) $conn->table('rooms')->count();
                
                // Contar habitaciones por estado
                if ($schema->hasColumn('rooms', 'status')) {
                    $kpis['rooms_available'] = (int) $conn->table('rooms')->where('status', 'available')->count();
                    $kpis['rooms_occupied'] = (int) $conn->table('rooms')->where('status', 'occupied')->count();
                    $kpis['rooms_maintenance'] = (int) $conn->table('rooms')->where('status', 'maintenance')->count();
                } else if ($schema->hasColumn('rooms', 'is_available')) {
                    $kpis['rooms_occupied'] = (int) $conn->table('rooms')->where('is_available', false)->count();
                    $kpis['rooms_available'] = $totalRooms - $kpis['rooms_occupied'];
                }

                $kpis['occupancy_rate'] = $totalRooms > 0
                    ? (int) round(($kpis['rooms_occupied'] / $totalRooms) * 100)
                    : 0;

                if ($schema->hasColumn('rooms', 'price_per_night') || $schema->hasColumn('rooms', 'price')) {
                    $priceColumn = $schema->hasColumn('rooms', 'price_per_night') ? 'price_per_night' : 'price';
                    $dailyRevenue = (float) $conn->table('rooms')
                        ->where($schema->hasColumn('rooms', 'is_available') ? 'is_available' : 'status', 
                               $schema->hasColumn('rooms', 'is_available') ? false : 'occupied')
                        ->sum($priceColumn);
                    $kpis['daily_revenue'] = $dailyRevenue;
                    $kpis['monthly_revenue'] = $dailyRevenue * 30;
                }
            }

            if ($hotel) {
                // Check-ins de hoy
                $kpis['next_checkins'] = (int) CheckinEvent::where('hotel_id', $hotel->id)
                    ->where('action', 'checkin')
                    ->whereDate('happened_at', now()->toDateString())
                    ->count();

                // Check-outs de hoy
                $kpis['next_checkouts'] = (int) CheckinEvent::where('hotel_id', $hotel->id)
                    ->where('action', 'checkout')
                    ->whereDate('happened_at', now()->toDateString())
                    ->count();

                // Obtener reservas/registros de hoy
                if ($schema->hasTable('registros')) {
                    $todayReservations = $conn->table('registros')
                        ->whereDate('check_in', now()->toDateString())
                        ->orWhereDate('check_out', now()->toDateString())
                        ->limit(10)
                        ->get()
                        ->map(function ($reg) {
                            return [
                                'id_room' => $reg->id_room ?? 'N/A',
                                'check_in' => $reg->check_in ?? 'N/A',
                                'check_out' => $reg->check_out ?? 'N/A',
                                'id_cliente' => $reg->id_cliente ?? 'N/A',
                                'status' => $this->getReservationStatus($reg->check_in, $reg->check_out),
                            ];
                        })
                        ->toArray();
                }

                // Estado detallado de habitaciones
                if ($schema->hasTable('rooms')) {
                    $roomStatus = $conn->table('rooms')
                        ->select('number', 'type', 'status', DB::raw("CASE WHEN status = 'occupied' THEN 'Ocupada' WHEN status = 'maintenance' THEN 'Mantenimiento' ELSE 'Disponible' END as status_label"))
                        ->orderBy('number')
                        ->get()
                        ->map(function ($room) {
                            return [
                                'number' => $room->number ?? 'N/A',
                                'type' => $room->type ?? 'Estándar',
                                'status' => $room->status ?? 'available',
                                'status_label' => $room->status_label ?? 'Disponible',
                            ];
                        })
                        ->toArray();
                }

                if (in_array('notifications_checkin_checkout', $features, true) || in_array('smart_alerts', $features, true)) {
                    $alerts[] = [
                        'id' => 1,
                        'type' => 'info',
                        'message' => 'Check-ins hoy: ' . $kpis['next_checkins'],
                    ];
                    
                    if ($kpis['next_checkouts'] > 0) {
                        $alerts[] = [
                            'id' => 4,
                            'type' => 'info',
                            'message' => 'Check-outs hoy: ' . $kpis['next_checkouts'],
                        ];
                    }
                }

                if (in_array('notifications_housekeeping', $features, true) || in_array('smart_alerts', $features, true)) {
                    $housekeepingPending = (int) CheckinEvent::where('hotel_id', $hotel->id)
                        ->where('action', 'housekeeping')
                        ->whereDate('happened_at', now()->toDateString())
                        ->count();

                    $alerts[] = [
                        'id' => 2,
                        'type' => $housekeepingPending > 0 ? 'warning' : 'info',
                        'message' => $housekeepingPending > 0
                            ? "Tareas de limpieza pendientes: {$housekeepingPending}."
                            : 'Sin tareas de limpieza pendientes hoy.',
                    ];
                }

                // Alerta de habitaciones en mantenimiento
                if ($kpis['rooms_maintenance'] > 0) {
                    $alerts[] = [
                        'id' => 3,
                        'type' => 'warning',
                        'message' => "Habitaciones en mantenimiento: " . $kpis['rooms_maintenance'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            $alerts[] = ['id' => 99, 'type' => 'warning', 'message' => 'No se pudieron cargar métricas en tiempo real.'];
        }

        $activeModules = [
            'plan' => $activePlan?->name ?? 'Sin plan',
            'modules' => [
                ['name' => 'Habitaciones', 'active' => $this->moduleIsEnabled($enabledModules, 'rooms'), 'route' => route('admin.rooms.index')],
                ['name' => 'Reservas', 'active' => $this->moduleIsEnabled($enabledModules, 'reservations'), 'route' => route('admin.reservations.index')],
                ['name' => 'Check-in/Out', 'active' => $this->moduleIsEnabled($enabledModules, 'reservations'), 'route' => route('admin.checkin.index')],
                ['name' => 'Clientes', 'active' => $this->moduleIsEnabled($enabledModules, 'guests'), 'route' => route('admin.guests.index')],
                ['name' => 'Limpieza', 'active' => $this->moduleIsEnabled($enabledModules, 'housekeeping'), 'route' => route('admin.dashboard')],
                ['name' => 'Usuarios', 'active' => $this->moduleIsEnabled($enabledModules, 'users'), 'route' => route('admin.users.index')],
                ['name' => 'Notas', 'active' => $this->moduleIsEnabled($enabledModules, 'notes'), 'route' => route('admin.notes.index')],
                ['name' => 'Estadísticas avanzadas', 'active' => $this->moduleIsEnabled($enabledModules, 'analytics'), 'route' => route('admin.analytics.index')],
                ['name' => 'Mantenimiento', 'active' => $this->moduleIsEnabled($enabledModules, 'maintenance'), 'route' => route('admin.maintenance.index')],
            ]
        ];

        return Inertia::render('Admin/Dashboard', [
            'kpis' => $kpis,
            'alerts' => $alerts,
            'activeModules' => $activeModules,
            'todayReservations' => $todayReservations,
            'roomStatus' => array_slice($roomStatus, 0, 12), // Mostrar primeras 12 habitaciones
            'planConfig' => array_merge($experience, [
                'max_users' => $activePlan?->max_users,
                'enabled_modules' => $enabledModules,
            ]),
        ]);
    }

    private function getReservationStatus($checkIn, $checkOut): string
    {
        $now = now();
        $checkInDate = \Carbon\Carbon::parse($checkIn);
        $checkOutDate = \Carbon\Carbon::parse($checkOut);

        if ($now->isBefore($checkInDate)) {
            return 'pending';
        } elseif ($now->isBetween($checkInDate, $checkOutDate)) {
            return 'active';
        } else {
            return 'completed';
        }
    }
}
