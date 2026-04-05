<?php

namespace App\Http\Controllers;

use App\Models\CheckinEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    private function tenantConnectionForCurrentAdmin(): array
    {
        $user = auth()->user();
        $hotel = $user?->hotels()->first();

        if (!$hotel) {
            throw new \Exception('No se encontró hotel asociado al administrador.');
        }

        return ['hotel' => $hotel, 'connection' => $this->resolveRoomConnection($hotel)];
    }

    private function resolveRoomConnection(?object $hotel): \Illuminate\Database\Connection
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

    /**
     * Muestra el tablero visual del estado de las habitaciones.
     */
    public function index()
    {
        $rooms_data = [];

        try {
            $user = auth()->user();
            $hotel = $user?->hotels()->first();

            $conn = $this->resolveRoomConnection($hotel);
            $schema = $conn->getSchemaBuilder();

            if ($schema->hasTable('rooms')) {
                $select = ['id', 'room_number', 'description', 'price_per_night', 'is_available'];
                if ($schema->hasColumn('rooms', 'beds')) {
                    $select[] = 'beds';
                }
                if ($schema->hasColumn('rooms', 'services')) {
                    $select[] = 'services';
                }

                $roomRows = $conn->table('rooms')
                    ->select($select)
                    ->orderBy('room_number')
                    ->get();

                $latestEventByRoomId = CheckinEvent::where('hotel_id', $hotel?->id)
                    ->whereNotNull('room_id')
                    ->orderByDesc('id')
                    ->get()
                    ->unique('room_id')
                    ->keyBy('room_id');

                $latestEventByRoomNumber = CheckinEvent::where('hotel_id', $hotel?->id)
                    ->whereNull('room_id')
                    ->whereNotNull('room_number')
                    ->orderByDesc('id')
                    ->get()
                    ->unique('room_number')
                    ->keyBy('room_number');

                $autoReadyMinutes = max(1, (int) config('hotel.rooms.auto_ready_minutes', 30));

                $rooms_data = $roomRows->map(function ($room) use ($latestEventByRoomId, $latestEventByRoomNumber, $autoReadyMinutes) {
                    $description = (string) ($room->description ?? '');
                    $normalized = strtolower($description);
                    $type = 'Habitación';
                    if (str_contains($normalized, 'doble')) {
                        $type = 'Doble';
                    } elseif (str_contains($normalized, 'individual') || str_contains($normalized, 'single')) {
                        $type = 'Individual';
                    } elseif (!empty($description)) {
                        $type = $description;
                    }

                    $event = $latestEventByRoomId[(int) $room->id] ?? $latestEventByRoomNumber[(string) $room->room_number] ?? null;

                    $occupant = null;
                    $status = 'Libre';

                    if ($event) {
                        if ($event->action === 'checkin') {
                            $status = 'Ocupada';
                            $occupant = $event->guest_name;
                        } elseif ($event->action === 'checkout') {
                            $status = 'Limpieza';
                        }
                        if ($event->action === 'housekeeping') {
                            $eventAt = $event->happened_at ?? $event->created_at;
                            $elapsedMinutes = $eventAt ? Carbon::parse($eventAt)->diffInMinutes(now()) : 0;
                            $status = $elapsedMinutes >= $autoReadyMinutes ? 'Libre' : 'Limpieza';
                        } elseif ($event->action === 'housekeeping_done') {
                            $status = 'Libre';
                        }
                    } elseif (!(bool) ($room->is_available ?? true)) {
                        $status = 'Mantenimiento';
                    }

                    return [
                        'id' => (int) $room->id,
                        'number' => (string) $room->room_number,
                        'type' => $type,
                        'status' => $status,
                        'occupant' => $occupant,
                        'beds' => isset($room->beds) ? (int) $room->beds : null,
                        'services' => $room->services ?? null,
                        'price_per_night' => isset($room->price_per_night) ? (float) $room->price_per_night : null,
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {
            $rooms_data = [];
        }

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => $rooms_data,
        ]);
    }

    /**
     * Almacena una nueva habitación en la base de datos (se usará con un modal).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:30',
            'beds' => 'required|integer|min:1|max:10',
            'services' => 'nullable|string|max:1000',
            'price_per_night' => 'nullable|numeric|min:0',
        ]);

        try {
            $user = auth()->user();
            $hotel = $user?->hotels()->first();

            $conn = $this->resolveRoomConnection($hotel);
            $schema = $conn->getSchemaBuilder();
            if (!$schema->hasTable('rooms')) {
                throw new \Exception('No existe la tabla rooms en este hotel.');
            }

            if ($conn->table('rooms')->where('room_number', $data['number'])->exists()) {
                throw new \Exception('Ya existe una habitación con ese número.');
            }

            $insert = [
                'room_number' => $data['number'],
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($schema->hasColumn('rooms', 'beds')) {
                $insert['beds'] = (int) $data['beds'];
            }

            if ($schema->hasColumn('rooms', 'services')) {
                $insert['services'] = $data['services'] ?? null;
            }

            if ($schema->hasColumn('rooms', 'description')) {
                $insert['description'] = trim(($data['beds'] > 1 ? 'Doble' : 'Individual') . ' · ' . ($data['services'] ?? 'Sin servicios definidos'));
            }

            if ($schema->hasColumn('rooms', 'price_per_night')) {
                $insert['price_per_night'] = isset($data['price_per_night']) ? (float) $data['price_per_night'] : 0;
            }

            $conn->table('rooms')->insert($insert);

            return redirect()->route('admin.rooms.index')->with('success', 'Habitación creada con éxito.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Actualiza una habitación específica (se usará con un modal).
     */
    public function update(Request $request, $id)
    {
        // Lógica de validación y actualización futura
        return redirect()->route('admin.rooms.index')->with('success', 'Habitación actualizada con éxito.');
    }

    public function markReady(Request $request, $id)
    {
        try {
            ['hotel' => $hotel, 'connection' => $conn] = $this->tenantConnectionForCurrentAdmin();
            $schema = $conn->getSchemaBuilder();

            if (!$schema->hasTable('rooms')) {
                throw new \Exception('No existe la tabla rooms en este hotel.');
            }

            $room = $conn->table('rooms')->where('id', (int) $id)->first();
            if (!$room) {
                throw new \Exception('Habitación no encontrada.');
            }

            if ($schema->hasColumn('rooms', 'is_available')) {
                $conn->table('rooms')->where('id', (int) $id)->update(['is_available' => true]);
            }

            CheckinEvent::create([
                'hotel_id' => $hotel->id,
                'guest_id' => null,
                'room_id' => (int) $id,
                'guest_name' => null,
                'room_number' => (string) ($room->room_number ?? ''),
                'action' => 'housekeeping_done',
                'message' => 'Habitación ' . ($room->room_number ?? $id) . ' marcada como lista.',
                'happened_at' => now(),
            ]);

            return redirect()->route('admin.rooms.index')->with('success', 'Habitación marcada como lista.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina una habitación específica.
     */
    public function destroy($id)
    {
        // Lógica de eliminación futura
        return redirect()->route('admin.rooms.index')->with('success', 'Habitación eliminada con éxito.');
    }
}
