<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckinEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ModulePageController extends Controller
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
                    $schema = $candidate->getSchemaBuilder();
                    if ($schema->hasTable('rooms') || $schema->hasTable('guests') || $schema->hasTable('clientes')) {
                        return $candidate;
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return DB::connection(config('database.default'));
    }

    private function resolveGuestTableName($connection): ?string
    {
        $schema = $connection->getSchemaBuilder();
        if ($schema->hasTable('guests')) {
            return 'guests';
        }
        if ($schema->hasTable('clientes')) {
            return 'clientes';
        }
        return null;
    }

    private function tenantConnectionForCurrentAdmin(): array
    {
        $user = auth()->user();
        $hotel = $user?->hotels()->first();

        if (!$hotel) {
            throw new \Exception('No se encontró hotel asociado al administrador.');
        }

        return ['hotel' => $hotel, 'connection' => $this->resolvePreferredConnection($hotel)];
    }

    public function checkin()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'reservations')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Check-in/Check-out.');
        }

        $guests = [];
        $notifications = [];

        try {
            ['hotel' => $hotelModel, 'connection' => $conn] = $this->tenantConnectionForCurrentAdmin();
            $schema = $conn->getSchemaBuilder();

            $roomsById = [];
            $roomsByNumber = [];
            if ($schema->hasTable('rooms')) {
                $roomRows = $conn->table('rooms')->select(['id', 'room_number'])->get();
                foreach ($roomRows as $room) {
                    $roomsById[(int) $room->id] = (string) $room->room_number;
                    $roomsByNumber[(string) $room->room_number] = (int) $room->id;
                }
            }

            $guestTable = $this->resolveGuestTableName($conn);
            if ($guestTable) {
                $guestSelect = ['id'];
                foreach (['first_name', 'last_name', 'name', 'room_id', 'room_number'] as $col) {
                    if ($schema->hasColumn($guestTable, $col)) {
                        $guestSelect[] = $col;
                    }
                }

                if ($guestTable === 'clientes') {
                    foreach (['name', 'last_name'] as $col) {
                        if ($schema->hasColumn('clientes', $col) && !in_array($col, $guestSelect, true)) {
                            $guestSelect[] = $col;
                        }
                    }
                }

                $guestRows = $conn->table($guestTable)->select($guestSelect)->orderBy('id')->get();
                $guestIds = $guestRows->pluck('id')->map(fn ($v) => (int) $v)->all();

                $events = CheckinEvent::where('hotel_id', $hotelModel->id)
                    ->when(!empty($guestIds), fn ($q) => $q->whereIn('guest_id', $guestIds))
                    ->orderByDesc('id')
                    ->get();

                $latestEventByGuest = [];
                foreach ($events as $event) {
                    if (!is_null($event->guest_id) && !isset($latestEventByGuest[(int) $event->guest_id])) {
                        $latestEventByGuest[(int) $event->guest_id] = $event;
                    }
                }

                $guests = $guestRows->map(function ($g) use ($latestEventByGuest, $roomsById, $roomsByNumber) {
                    $guestId = (int) $g->id;
                    $fullName = trim(($g->first_name ?? $g->name ?? '') . ' ' . ($g->last_name ?? ''));
                    if (empty($fullName)) {
                        $fullName = $g->name ?? ('Huésped #' . $guestId);
                    }

                    $roomId = isset($g->room_id) ? (int) $g->room_id : null;
                    if (!$roomId && !empty($g->room_number) && isset($roomsByNumber[(string) $g->room_number])) {
                        $roomId = $roomsByNumber[(string) $g->room_number];
                    }

                    $roomNumber = $roomId && isset($roomsById[$roomId])
                        ? $roomsById[$roomId]
                        : ($g->room_number ?? null);

                    $last = $latestEventByGuest[$guestId] ?? null;
                    $status = 'pendiente';
                    if ($last) {
                        if ($last->action === 'checkin') {
                            $status = 'ingresado';
                        } elseif ($last->action === 'checkout') {
                            $status = 'salio';
                        } elseif ($last->action === 'housekeeping') {
                            $status = 'limpieza';
                        }
                    }

                    return [
                        'id' => $guestId,
                        'name' => $fullName,
                        'room_id' => $roomId,
                        'room_number' => $roomNumber,
                        'status' => $status,
                    ];
                })->values()->toArray();
            }

            $notifications = CheckinEvent::where('hotel_id', $hotelModel->id)
                ->orderByDesc('id')
                ->limit(20)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'action' => $e->action,
                    'message' => $e->message,
                    'guest_name' => $e->guest_name,
                    'room_number' => $e->room_number,
                    'happened_at' => optional($e->happened_at ?? $e->created_at)->format('Y-m-d H:i'),
                ])
                ->toArray();
        } catch (\Throwable $e) {
            $guests = [];
            $notifications = [];
        }

        return Inertia::render('Admin/Checkin/Index', [
            'guests' => $guests,
            'notifications' => $notifications,
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }

    public function checkInGuest(Request $request)
    {
        $data = $request->validate([
            'guest_id' => 'required|integer|min:1',
        ]);

        try {
            ['hotel' => $hotel, 'connection' => $conn] = $this->tenantConnectionForCurrentAdmin();
            $schema = $conn->getSchemaBuilder();
            $guestTable = $this->resolveGuestTableName($conn);

            if (!$guestTable) {
                throw new \Exception('No existe tabla de huéspedes en el hotel.');
            }

            $guestSelect = ['id'];
            foreach (['first_name', 'last_name', 'name', 'room_id', 'room_number'] as $col) {
                if ($schema->hasColumn($guestTable, $col)) {
                    $guestSelect[] = $col;
                }
            }

            $guest = $conn->table($guestTable)->select($guestSelect)->where('id', (int) $data['guest_id'])->first();
            if (!$guest) {
                throw new \Exception('Huésped no encontrado.');
            }

            $guestName = trim(($guest->first_name ?? $guest->name ?? '') . ' ' . ($guest->last_name ?? ''));
            if (empty($guestName)) {
                $guestName = $guest->name ?? ('Huésped #' . $guest->id);
            }

            $roomId = isset($guest->room_id) ? (int) $guest->room_id : null;
            $roomNumber = $guest->room_number ?? null;

            if (!$roomId && !empty($roomNumber) && $schema->hasTable('rooms')) {
                $room = $conn->table('rooms')->select(['id', 'room_number'])->where('room_number', $roomNumber)->first();
                $roomId = $room?->id;
            }

            if ($roomId && $schema->hasTable('rooms') && $schema->hasColumn('rooms', 'is_available')) {
                $conn->table('rooms')->where('id', $roomId)->update(['is_available' => false]);
                if (!$roomNumber) {
                    $roomNumber = $conn->table('rooms')->where('id', $roomId)->value('room_number');
                }
            }

            CheckinEvent::create([
                'hotel_id' => $hotel->id,
                'guest_id' => (int) $guest->id,
                'room_id' => $roomId,
                'guest_name' => $guestName,
                'room_number' => $roomNumber,
                'action' => 'checkin',
                'message' => "{$guestName} ingresó a la habitación {$roomNumber}.",
                'happened_at' => now(),
            ]);

            return back()->with('success', 'Check-in registrado correctamente.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function checkOutGuest(Request $request)
    {
        $data = $request->validate([
            'guest_id' => 'required|integer|min:1',
        ]);

        try {
            ['hotel' => $hotel, 'connection' => $conn] = $this->tenantConnectionForCurrentAdmin();
            $schema = $conn->getSchemaBuilder();
            $guestTable = $this->resolveGuestTableName($conn);

            if (!$guestTable) {
                throw new \Exception('No existe tabla de huéspedes en el hotel.');
            }

            $guestSelect = ['id'];
            foreach (['first_name', 'last_name', 'name', 'room_id', 'room_number'] as $col) {
                if ($schema->hasColumn($guestTable, $col)) {
                    $guestSelect[] = $col;
                }
            }

            $guest = $conn->table($guestTable)->select($guestSelect)->where('id', (int) $data['guest_id'])->first();
            if (!$guest) {
                throw new \Exception('Huésped no encontrado.');
            }

            $guestName = trim(($guest->first_name ?? $guest->name ?? '') . ' ' . ($guest->last_name ?? ''));
            if (empty($guestName)) {
                $guestName = $guest->name ?? ('Huésped #' . $guest->id);
            }

            $roomId = isset($guest->room_id) ? (int) $guest->room_id : null;
            $roomNumber = $guest->room_number ?? null;

            if (!$roomId && !empty($roomNumber) && $schema->hasTable('rooms')) {
                $room = $conn->table('rooms')->select(['id', 'room_number'])->where('room_number', $roomNumber)->first();
                $roomId = $room?->id;
            }

            CheckinEvent::create([
                'hotel_id' => $hotel->id,
                'guest_id' => (int) $guest->id,
                'room_id' => $roomId,
                'guest_name' => $guestName,
                'room_number' => $roomNumber,
                'action' => 'checkout',
                'message' => "{$guestName} salió de la habitación {$roomNumber}.",
                'happened_at' => now(),
            ]);

            CheckinEvent::create([
                'hotel_id' => $hotel->id,
                'guest_id' => (int) $guest->id,
                'room_id' => $roomId,
                'guest_name' => $guestName,
                'room_number' => $roomNumber,
                'action' => 'housekeeping',
                'message' => "Notificación a limpieza: preparar habitación {$roomNumber} tras salida de {$guestName}.",
                'happened_at' => now(),
            ]);

            if ($roomId && $schema->hasTable('rooms') && $schema->hasColumn('rooms', 'is_available')) {
                $conn->table('rooms')->where('id', $roomId)->update(['is_available' => false]);
            }

            return back()->with('success', 'Check-out registrado y limpieza notificada.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function finances()
    {
        [, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $planConfig = $this->resolveExperienceConfiguration($activePlan);
        if ($planConfig['tier'] === 'basico') {
            return redirect()->route('admin.dashboard')->with('error', 'Finanzas detalladas está disponible desde el plan Intermedio.');
        }

        $stats = [
            'daily_total' => 0,
            'monthly_total' => 0,
            'daily_count' => 0,
        ];
        $dailyPayments = [];

        try {
            ['hotel' => $hotel, 'connection' => $conn] = $this->tenantConnectionForCurrentAdmin();
            $schema = $conn->getSchemaBuilder();

            if ($schema->hasTable('registros') && $schema->hasTable('rooms')) {
                $today = now()->toDateString();
                $monthStart = now()->startOfMonth()->toDateString();
                $monthEnd = now()->endOfMonth()->toDateString();

                $guestTable = $this->resolveGuestTableName($conn);
                $guestNamesById = [];

                if ($guestTable && $schema->hasColumn($guestTable, 'id')) {
                    $guestSelect = ['id'];
                    foreach (['first_name', 'last_name', 'name'] as $col) {
                        if ($schema->hasColumn($guestTable, $col)) {
                            $guestSelect[] = $col;
                        }
                    }

                    $guestRows = $conn->table($guestTable)->select($guestSelect)->get();
                    foreach ($guestRows as $g) {
                        $fullName = trim(($g->first_name ?? $g->name ?? '') . ' ' . ($g->last_name ?? ''));
                        $guestNamesById[(int) $g->id] = $fullName ?: ($g->name ?? ('Huésped #' . $g->id));
                    }
                }

                $roomRates = $conn->table('rooms')->select(['id', 'room_number', 'price_per_night'])->get()->keyBy('id');

                $dailyRows = $conn->table('registros')
                    ->when($schema->hasColumn('registros', 'id_hotel'), fn ($q) => $q->where('id_hotel', (int) $hotel->id))
                    ->when($schema->hasColumn('registros', 'check_in'), fn ($q) => $q->whereDate('check_in', $today))
                    ->orderByDesc('id')
                    ->get();

                foreach ($dailyRows as $row) {
                    $room = $roomRates->get((int) ($row->id_room ?? 0));
                    $pricePerNight = (float) ($room->price_per_night ?? 0);

                    $checkIn = !empty($row->check_in) ? \Carbon\Carbon::parse($row->check_in) : now();
                    $checkOut = !empty($row->check_out) ? \Carbon\Carbon::parse($row->check_out) : $checkIn->copy()->addDay();
                    $nights = max(1, $checkIn->diffInDays($checkOut));
                    $amount = round($pricePerNight * $nights, 2);

                    $dailyPayments[] = [
                        'id' => (int) ($row->id ?? 0),
                        'guest_name' => $guestNamesById[(int) ($row->id_cliente ?? 0)] ?? ('Huésped #' . ($row->id_cliente ?? 'N/D')),
                        'room_number' => (string) ($room->room_number ?? ($row->id_room ?? 'N/D')),
                        'nights' => $nights,
                        'amount' => $amount,
                        'date' => $checkIn->format('Y-m-d'),
                    ];
                }

                $monthlyRows = $conn->table('registros')
                    ->when($schema->hasColumn('registros', 'id_hotel'), fn ($q) => $q->where('id_hotel', (int) $hotel->id))
                    ->when($schema->hasColumn('registros', 'check_in'), fn ($q) => $q->whereBetween('check_in', [$monthStart, $monthEnd]))
                    ->get();

                $monthlyTotal = 0;
                foreach ($monthlyRows as $row) {
                    $room = $roomRates->get((int) ($row->id_room ?? 0));
                    $pricePerNight = (float) ($room->price_per_night ?? 0);
                    $checkIn = !empty($row->check_in) ? \Carbon\Carbon::parse($row->check_in) : now();
                    $checkOut = !empty($row->check_out) ? \Carbon\Carbon::parse($row->check_out) : $checkIn->copy()->addDay();
                    $nights = max(1, $checkIn->diffInDays($checkOut));
                    $monthlyTotal += round($pricePerNight * $nights, 2);
                }

                $stats['daily_total'] = round(collect($dailyPayments)->sum('amount'), 2);
                $stats['monthly_total'] = round($monthlyTotal, 2);
                $stats['daily_count'] = count($dailyPayments);
            }
        } catch (\Throwable $e) {
            $stats = [
                'daily_total' => 0,
                'monthly_total' => 0,
                'daily_count' => 0,
            ];
            $dailyPayments = [];
        }

        return Inertia::render('Admin/Finances/Index', [
            'planConfig' => $planConfig,
            'stats' => $stats,
            'dailyPayments' => $dailyPayments,
        ]);
    }

    public function reports()
    {
        [, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $planConfig = $this->resolveExperienceConfiguration($activePlan);
        if ($planConfig['tier'] === 'basico') {
            return redirect()->route('admin.dashboard')->with('error', 'Reportes avanzados está disponible desde el plan Intermedio.');
        }

        return Inertia::render('Admin/Reports/Index', [
            'planConfig' => $planConfig,
        ]);
    }

    public function loyalty()
    {
        [, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $planConfig = $this->resolveExperienceConfiguration($activePlan);
        if ($planConfig['tier'] === 'basico') {
            return redirect()->route('admin.dashboard')->with('error', 'Fidelización está disponible desde el plan Intermedio.');
        }

        return Inertia::render('Admin/Loyalty/Index', [
            'planConfig' => $planConfig,
        ]);
    }

    public function channelManager()
    {
        [, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $planConfig = $this->resolveExperienceConfiguration($activePlan);
        if ($planConfig['tier'] !== 'alto_empresarial') {
            return redirect()->route('admin.dashboard')->with('error', 'Channel Manager está disponible solo en plan Alto / Empresarial.');
        }

        return Inertia::render('Admin/ChannelManager/Index', [
            'planConfig' => $planConfig,
        ]);
    }

    public function analytics()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'analytics')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Analítica avanzada.');
        }

        return Inertia::render('Admin/Analytics/Index', [
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }

    public function users()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'users')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Usuarios.');
        }

        return Inertia::render('Admin/Users/Index', [
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }

    public function notes()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'notes')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Notas.');
        }

        return Inertia::render('Admin/Notes/Index', [
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }

    public function maintenance()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'maintenance')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Mantenimiento.');
        }

        return Inertia::render('Admin/Maintenance/Index', [
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }

    public function customization()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'customization')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Personalización.');
        }

        return Inertia::render('Admin/Customization/Index', [
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }

    public function darkMode()
    {
        [$hotel, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $modules = $hotel ? $this->enabledModulesForHotel($hotel, $activePlan) : [];
        if (!$this->moduleIsEnabled($modules, 'dark_mode')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye Modo oscuro.');
        }

        return Inertia::render('Admin/DarkMode/Index', [
            'planConfig' => $this->resolveExperienceConfiguration($activePlan),
        ]);
    }
}
