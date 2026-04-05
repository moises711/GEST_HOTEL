<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckinEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    use ResolvesPlanConfiguration;

    private function normalizeIdentifier(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return Str::lower($normalized);
    }

    private function csvHeadersMap(array $headers): array
    {
        $aliases = [
            'first_name' => ['first_name', 'nombre', 'name', 'nombres'],
            'last_name' => ['last_name', 'apellido', 'apellidos', 'lastname'],
            'email' => ['email', 'correo', 'correo_electronico'],
            'phone' => ['phone', 'telefono', 'cell_phone', 'celular'],
            'document' => ['document', 'dni', 'cedula', 'doc', 'documento'],
            'room_id' => ['room_id', 'habitacion_id', 'id_habitacion'],
            'room_number' => ['room_number', 'habitacion', 'numero_habitacion'],
        ];

        $normalizedHeaders = array_map(function ($header) {
            $header = Str::ascii((string) $header);
            $header = Str::lower(trim($header));
            return preg_replace('/[^a-z0-9_]+/', '_', $header);
        }, $headers);

        $result = [];
        foreach ($aliases as $target => $possible) {
            foreach ($possible as $candidate) {
                $index = array_search($candidate, $normalizedHeaders, true);
                if ($index !== false) {
                    $result[$target] = (int) $index;
                    break;
                }
            }
        }

        return $result;
    }

    private function flushGuestImportBatch(
        \Illuminate\Database\Connection $connection,
        string $guestTable,
        int $hotelId,
        array $batch
    ): array {
        if (empty($batch)) {
            return ['inserted' => 0, 'skipped' => 0];
        }

        $schema = $connection->getSchemaBuilder();
        $docColumn = null;
        foreach (['document', 'DNI', 'dni'] as $column) {
            if ($schema->hasColumn($guestTable, $column)) {
                $docColumn = $column;
                break;
            }
        }

        $emailColumn = null;
        foreach (['email', 'email_address'] as $column) {
            if ($schema->hasColumn($guestTable, $column)) {
                $emailColumn = $column;
                break;
            }
        }

        return $connection->transaction(function () use ($connection, $guestTable, $hotelId, $batch, $schema, $docColumn, $emailColumn) {
            $existingDocs = [];
            $existingEmails = [];

            $docValues = [];
            if ($docColumn) {
                foreach ($batch as $payload) {
                    $value = $this->normalizeIdentifier($payload[$docColumn] ?? null);
                    if ($value) {
                        $docValues[$value] = $value;
                    }
                }
            }

            $emailValues = [];
            if ($emailColumn) {
                foreach ($batch as $payload) {
                    $value = $this->normalizeIdentifier($payload[$emailColumn] ?? null);
                    if ($value) {
                        $emailValues[$value] = $value;
                    }
                }
            }

            if (!empty($docValues) || !empty($emailValues)) {
                $query = $connection->table($guestTable);
                if ($schema->hasColumn($guestTable, 'hotel_id')) {
                    $query->where('hotel_id', $hotelId);
                }

                $query->where(function ($q) use ($docColumn, $emailColumn, $docValues, $emailValues) {
                    if ($docColumn && !empty($docValues)) {
                        $q->whereIn($docColumn, array_values($docValues));
                    }
                    if ($emailColumn && !empty($emailValues)) {
                        if ($docColumn && !empty($docValues)) {
                            $q->orWhereIn($emailColumn, array_values($emailValues));
                        } else {
                            $q->whereIn($emailColumn, array_values($emailValues));
                        }
                    }
                });

                $select = [];
                if ($docColumn) {
                    $select[] = $docColumn;
                }
                if ($emailColumn) {
                    $select[] = $emailColumn;
                }

                $rows = $query->select($select)->get();
                foreach ($rows as $row) {
                    if ($docColumn) {
                        $doc = $this->normalizeIdentifier($row->{$docColumn} ?? null);
                        if ($doc) {
                            $existingDocs[$doc] = true;
                        }
                    }
                    if ($emailColumn) {
                        $email = $this->normalizeIdentifier($row->{$emailColumn} ?? null);
                        if ($email) {
                            $existingEmails[$email] = true;
                        }
                    }
                }
            }

            $seenDocs = [];
            $seenEmails = [];
            $insertRows = [];
            $skipped = 0;

            foreach ($batch as $payload) {
                $doc = $docColumn ? $this->normalizeIdentifier($payload[$docColumn] ?? null) : null;
                $email = $emailColumn ? $this->normalizeIdentifier($payload[$emailColumn] ?? null) : null;

                $duplicate = false;
                if ($doc && (isset($existingDocs[$doc]) || isset($seenDocs[$doc]))) {
                    $duplicate = true;
                }
                if ($email && (isset($existingEmails[$email]) || isset($seenEmails[$email]))) {
                    $duplicate = true;
                }

                if ($duplicate) {
                    $skipped++;
                    continue;
                }

                if ($doc) {
                    $seenDocs[$doc] = true;
                }
                if ($email) {
                    $seenEmails[$email] = true;
                }

                $insertRows[] = $payload;
            }

            if (!empty($insertRows)) {
                $connection->table($guestTable)->insert($insertRows);
            }

            return [
                'inserted' => count($insertRows),
                'skipped' => $skipped,
            ];
        });
    }

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
                    if (
                        $candidate->getSchemaBuilder()->hasTable('rooms') ||
                        $candidate->getSchemaBuilder()->hasTable('guests') ||
                        $candidate->getSchemaBuilder()->hasTable('clientes')
                    ) {
                        return $candidate;
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return DB::connection(config('database.default'));
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

    private function guestInsertPayload($connection, string $guestTable, array $data, ?int $hotelId = null): array
    {
        $schema = $connection->getSchemaBuilder();
        $payload = [];

        if (!is_null($hotelId) && $schema->hasColumn($guestTable, 'hotel_id')) {
            $payload['hotel_id'] = $hotelId;
        }

        if ($schema->hasColumn($guestTable, 'first_name')) {
            $payload['first_name'] = $data['first_name'];
        } elseif ($schema->hasColumn($guestTable, 'name')) {
            $payload['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        if ($schema->hasColumn($guestTable, 'last_name')) {
            $payload['last_name'] = $data['last_name'];
        }

        if ($schema->hasColumn($guestTable, 'email')) {
            $payload['email'] = $data['email'] ?? null;
        } elseif ($schema->hasColumn($guestTable, 'email_address')) {
            $payload['email_address'] = $data['email'] ?? null;
        }

        if ($schema->hasColumn($guestTable, 'phone')) {
            $payload['phone'] = $data['phone'] ?? null;
        } elseif ($schema->hasColumn($guestTable, 'cell_phone')) {
            $payload['cell_phone'] = $data['phone'] ?? null;
        }

        if ($schema->hasColumn($guestTable, 'document')) {
            $payload['document'] = $data['document'] ?? null;
        } elseif ($schema->hasColumn($guestTable, 'DNI')) {
            $payload['DNI'] = $data['document'] ?? null;
        } elseif ($schema->hasColumn($guestTable, 'dni')) {
            $payload['dni'] = $data['document'] ?? null;
        }

        if (!empty($data['room_id']) && $schema->hasColumn($guestTable, 'room_id')) {
            $payload['room_id'] = (int) $data['room_id'];
        } elseif (!empty($data['room_number']) && $schema->hasColumn($guestTable, 'room_number')) {
            $payload['room_number'] = $data['room_number'];
        }

        if (array_key_exists('room_price', $data)) {
            if ($schema->hasColumn($guestTable, 'nightly_rate')) {
                $payload['nightly_rate'] = $data['room_price'];
            } elseif ($schema->hasColumn($guestTable, 'rate_per_night')) {
                $payload['rate_per_night'] = $data['room_price'];
            } elseif ($schema->hasColumn($guestTable, 'room_price')) {
                $payload['room_price'] = $data['room_price'];
            } elseif ($schema->hasColumn($guestTable, 'price_per_night')) {
                $payload['price_per_night'] = $data['room_price'];
            }
        }

        if (array_key_exists('stay_nights', $data)) {
            if ($schema->hasColumn($guestTable, 'stay_nights')) {
                $payload['stay_nights'] = (int) $data['stay_nights'];
            } elseif ($schema->hasColumn($guestTable, 'nights')) {
                $payload['nights'] = (int) $data['stay_nights'];
            }
        }

        if (array_key_exists('discount_percent', $data) && $schema->hasColumn($guestTable, 'discount_percent')) {
            $payload['discount_percent'] = $data['discount_percent'];
        }

        if (array_key_exists('estimated_total', $data)) {
            if ($schema->hasColumn($guestTable, 'estimated_total')) {
                $payload['estimated_total'] = $data['estimated_total'];
            } elseif ($schema->hasColumn($guestTable, 'total_amount')) {
                $payload['total_amount'] = $data['estimated_total'];
            } elseif ($schema->hasColumn($guestTable, 'total_price')) {
                $payload['total_price'] = $data['estimated_total'];
            }
        }

        if ($guestTable === 'clientes') {
            if ($schema->hasColumn('clientes', 'name') && empty($payload['name'] ?? null)) {
                $payload['name'] = $data['first_name'];
            }
            if ($schema->hasColumn('clientes', 'last_name') && empty($payload['last_name'] ?? null)) {
                $payload['last_name'] = $data['last_name'];
            }
            if ($schema->hasColumn('clientes', 'cell_phone')) {
                $cellPhone = $payload['cell_phone'] ?? ($data['phone'] ?? null);
                $cellPhone = is_string($cellPhone) ? trim($cellPhone) : $cellPhone;
                $payload['cell_phone'] = empty($cellPhone) ? '-' : (string) $cellPhone;
            }
            if ($schema->hasColumn('clientes', 'DNI') && !array_key_exists('DNI', $payload)) {
                $payload['DNI'] = $data['document'] ?? null;
            } elseif (
                $schema->hasColumn('clientes', 'dni') &&
                !array_key_exists('dni', $payload) &&
                !array_key_exists('DNI', $payload)
            ) {
                $payload['dni'] = $data['document'] ?? null;
            }
            if ($schema->hasColumn('clientes', 'time')) {
                $payload['time'] = now()->format('H:i:s');
            }
            if ($schema->hasColumn('clientes', 'date')) {
                $payload['date'] = now()->toDateString();
            }
        }

        if ($schema->hasColumn($guestTable, 'created_at')) {
            $payload['created_at'] = now();
        }
        if ($schema->hasColumn($guestTable, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        return $payload;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $guests = [];
        $rooms = [];
        $guestsPagination = null;

        [$hotelForPlan, $activePlan] = $this->resolveHotelAndPlan($user);
        $enabledModules = $hotelForPlan ? $this->enabledModulesForHotel($hotelForPlan, $activePlan) : [];
        if (!$this->moduleIsEnabled($enabledModules, 'guests')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye el módulo de Clientes.');
        }

        try {
            $hotel = $user?->hotels()->first();
            if ($hotel) {
                $conn = $this->resolvePreferredConnection($hotel);

                if ($conn->getSchemaBuilder()->hasTable('rooms')) {
                    $roomSchema = $conn->getSchemaBuilder();

                    $roomSelect = ['id', 'room_number', 'is_available'];
                    if ($roomSchema->hasColumn('rooms', 'price_per_night')) {
                        $roomSelect[] = 'price_per_night';
                    }
                    if ($roomSchema->hasColumn('rooms', 'description')) {
                        $roomSelect[] = 'description';
                    }
                    if ($roomSchema->hasColumn('rooms', 'type')) {
                        $roomSelect[] = 'type';
                    }
                    if ($roomSchema->hasColumn('rooms', 'room_type')) {
                        $roomSelect[] = 'room_type';
                    }
                    if ($roomSchema->hasColumn('rooms', 'beds')) {
                        $roomSelect[] = 'beds';
                    }
                    if ($roomSchema->hasColumn('rooms', 'bed_count')) {
                        $roomSelect[] = 'bed_count';
                    }

                    $rooms = $conn->table('rooms')
                        ->select($roomSelect)
                        ->orderBy('room_number')
                        ->get()
                        ->map(function ($room) {
                            $typeRaw = $room->type ?? $room->room_type ?? null;
                            $description = $room->description ?? '';
                            $typeNormalized = $typeRaw ? strtolower((string) $typeRaw) : strtolower((string) $description);

                            $roomType = 'otro';
                            if (str_contains($typeNormalized, 'doble')) {
                                $roomType = 'doble';
                            } elseif (str_contains($typeNormalized, 'individual') || str_contains($typeNormalized, 'single')) {
                                $roomType = 'individual';
                            }

                            $beds = $room->beds ?? $room->bed_count ?? null;
                            if (is_null($beds)) {
                                $beds = $roomType === 'doble' ? 2 : ($roomType === 'individual' ? 1 : null);
                            }

                            return [
                                'id' => (int) $room->id,
                                'room_number' => (string) $room->room_number,
                                'is_available' => (bool) ($room->is_available ?? true),
                                'room_type' => $roomType,
                                'beds' => is_null($beds) ? null : (int) $beds,
                                'price_per_night' => isset($room->price_per_night) ? (float) $room->price_per_night : null,
                            ];
                        })
                        ->toArray();
                }

                $guestTable = $this->resolveGuestTableName($conn);
                if (!$guestTable) {
                    $guests = [];
                } else {
                    $query = $conn->table($guestTable);
                    if ($conn->getSchemaBuilder()->hasColumn($guestTable, 'hotel_id')) {
                        $query->where('hotel_id', $hotel->id);
                    }

                    $perPage = (int) $request->integer('per_page', 50);
                    if ($perPage < 20) {
                        $perPage = 20;
                    }
                    if ($perPage > 200) {
                        $perPage = 200;
                    }

                    $paginator = $query->orderByDesc('id')->simplePaginate($perPage)->withQueryString();

                    $roomsById = collect($rooms)->keyBy('id');

                    $guests = collect($paginator->items())->map(function($g) use ($roomsById){
                        $row = (array) $g;
                        if (array_key_exists('room_id', $row) && !empty($row['room_id']) && $roomsById->has((int) $row['room_id'])) {
                            $row['room_label'] = $roomsById[(int) $row['room_id']]['room_number'];
                        } elseif (array_key_exists('room_number', $row) && !empty($row['room_number'])) {
                            $row['room_label'] = $row['room_number'];
                        } else {
                            $row['room_label'] = null;
                        }
                        return $row;
                    })->values()->toArray();

                    $guestsPagination = [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'next_page_url' => $paginator->nextPageUrl(),
                        'prev_page_url' => $paginator->previousPageUrl(),
                    ];
                }
            }
        } catch (\Exception $e) {
            $guests = [
                ['id' => 1, 'first_name' => 'Ana', 'last_name' => 'Gómez', 'email' => 'ana@example.com'],
            ];
            $rooms = [];
            $guestsPagination = null;
        }

        return Inertia::render('Admin/Guests/Index', [
            'guests' => $guests,
            'rooms' => $rooms,
            'guestsPagination' => $guestsPagination,
        ]);
    }

    public function import(Request $request)
    {
        [$hotelForPlan, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $enabledModules = $hotelForPlan ? $this->enabledModulesForHotel($hotelForPlan, $activePlan) : [];
        if (!$this->moduleIsEnabled($enabledModules, 'guests')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye el módulo de Clientes.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:51200',
        ]);

        try {
            ['hotel' => $hotel, 'connection' => $connection] = $this->tenantConnectionForCurrentAdmin();

            $guestTable = $this->resolveGuestTableName($connection);
            if (!$guestTable) {
                throw new \Exception('No existe tabla de huéspedes compatible (guests o clientes).');
            }

            $path = $validated['file']->getRealPath();
            if (!$path) {
                throw new \Exception('No se pudo leer el archivo cargado.');
            }

            $handle = fopen($path, 'r');
            if ($handle === false) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            $headers = fgetcsv($handle);
            if (!$headers || count($headers) === 0) {
                fclose($handle);
                return back()->withErrors(['file' => 'El CSV no contiene encabezados.']);
            }

            $headerMap = $this->csvHeadersMap($headers);
            if (!isset($headerMap['first_name']) || !isset($headerMap['last_name']) || !isset($headerMap['document'])) {
                fclose($handle);
                return back()->withErrors([
                    'file' => 'El CSV debe incluir como mínimo: first_name, last_name, document (acepta alias como nombre, apellido, dni).',
                ]);
            }

            $batchSize = 1000;
            $batch = [];
            $inserted = 0;
            $skipped = 0;
            $invalid = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $firstName = trim((string) ($row[$headerMap['first_name']] ?? ''));
                $lastName = trim((string) ($row[$headerMap['last_name']] ?? ''));
                $document = trim((string) ($row[$headerMap['document']] ?? ''));

                if ($firstName === '' || $lastName === '' || $document === '') {
                    $invalid++;
                    continue;
                }

                $payloadData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => trim((string) ($row[$headerMap['email']] ?? '')) ?: null,
                    'phone' => trim((string) ($row[$headerMap['phone']] ?? '')) ?: null,
                    'document' => $document,
                    'room_id' => isset($headerMap['room_id']) ? (int) ($row[$headerMap['room_id']] ?? 0) : null,
                    'room_number' => isset($headerMap['room_number']) ? (trim((string) ($row[$headerMap['room_number']] ?? '')) ?: null) : null,
                ];

                $payload = $this->guestInsertPayload($connection, $guestTable, $payloadData, (int) $hotel->id);
                if (empty($payload)) {
                    $invalid++;
                    continue;
                }

                $batch[] = $payload;

                if (count($batch) >= $batchSize) {
                    $result = $this->flushGuestImportBatch($connection, $guestTable, (int) $hotel->id, $batch);
                    $inserted += (int) $result['inserted'];
                    $skipped += (int) $result['skipped'];
                    $batch = [];
                }
            }

            fclose($handle);

            if (!empty($batch)) {
                $result = $this->flushGuestImportBatch($connection, $guestTable, (int) $hotel->id, $batch);
                $inserted += (int) $result['inserted'];
                $skipped += (int) $result['skipped'];
            }

            return back()->with('success', "Importación finalizada. Insertados: {$inserted}, duplicados omitidos: {$skipped}, inválidos: {$invalid}.");
        } catch (\Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        [$hotelForPlan, $activePlan] = $this->resolveHotelAndPlan(auth()->user());
        $enabledModules = $hotelForPlan ? $this->enabledModulesForHotel($hotelForPlan, $activePlan) : [];
        if (!$this->moduleIsEnabled($enabledModules, 'guests')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye el módulo de Clientes.');
        }

        $experience = $this->resolveExperienceConfiguration($activePlan);
        $tier = $experience['tier'] ?? 'basico';

        $data = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'document' => 'required|string|max:40',
            'room_id' => 'required|integer|min:1',
            'stay_nights' => 'nullable|integer|min:1|max:365',
            'discount_percent' => 'nullable|numeric|min:0|max:50',
        ]);

        $requestedNights = (int) ($data['stay_nights'] ?? 1);
        $requestedDiscount = (float) ($data['discount_percent'] ?? 0);

        if ($tier === 'basico' && $requestedNights > 1) {
            return back()->withErrors([
                'stay_nights' => 'En el plan Básico la estadía se registra con 1 noche. Mejora a Intermedio para habilitar múltiples noches.',
            ])->withInput();
        }

        if ($tier !== 'alto_empresarial' && $requestedDiscount > 0) {
            return back()->withErrors([
                'discount_percent' => 'El descuento está disponible solo en el plan Alto / Empresarial.',
            ])->withInput();
        }

        try {
            ['hotel' => $hotel, 'connection' => $connection] = $this->tenantConnectionForCurrentAdmin();

            $room = null;
            if ($connection->getSchemaBuilder()->hasTable('rooms')) {
                $room = $connection->table('rooms')->where('id', (int) $data['room_id'])->first();
            }

            if (!$room) {
                throw new \Exception('La habitación seleccionada no existe en este hotel.');
            }

            if ($connection->getSchemaBuilder()->hasColumn('rooms', 'is_available') && !(bool) ($room->is_available ?? false)) {
                throw new \Exception('La habitación seleccionada no está libre.');
            }

            $data['room_number'] = $room->room_number ?? null;
            $roomPrice = isset($room->price_per_night) ? (float) $room->price_per_night : 0.0;
            $nights = $tier === 'basico' ? 1 : (int) ($data['stay_nights'] ?? 1);
            $discountPercent = $tier === 'alto_empresarial' ? (float) ($data['discount_percent'] ?? 0) : 0.0;
            $discountFactor = max(0, min(50, $discountPercent)) / 100;
            $estimatedTotal = round(($roomPrice * $nights) * (1 - $discountFactor), 2);

            $data['room_price'] = $roomPrice;
            $data['stay_nights'] = $nights;
            $data['discount_percent'] = $discountPercent;
            $data['estimated_total'] = $estimatedTotal;

            $guestTable = $this->resolveGuestTableName($connection);
            if (!$guestTable) {
                throw new \Exception('No existe tabla de huéspedes compatible (guests o clientes).');
            }

            $payload = $this->guestInsertPayload($connection, $guestTable, $data, (int) $hotel->id);

            if (empty($payload)) {
                throw new \Exception('No se encontraron columnas compatibles en la tabla de huéspedes para guardar el huésped.');
            }

            $guestId = null;
            if ($connection->getSchemaBuilder()->hasColumn($guestTable, 'id')) {
                $guestId = (int) $connection->table($guestTable)->insertGetId($payload);
            } else {
                $connection->table($guestTable)->insert($payload);
            }

            if ($connection->getSchemaBuilder()->hasTable('registros')) {
                $registroPayload = [];
                $registroSchema = $connection->getSchemaBuilder();
                if ($registroSchema->hasColumn('registros', 'id_cliente') && !is_null($guestId)) {
                    $registroPayload['id_cliente'] = $guestId;
                }
                if ($registroSchema->hasColumn('registros', 'id_hotel')) {
                    $registroPayload['id_hotel'] = (int) $hotel->id;
                }
                if ($registroSchema->hasColumn('registros', 'id_room')) {
                    $registroPayload['id_room'] = (int) $data['room_id'];
                }
                if ($registroSchema->hasColumn('registros', 'check_in')) {
                    $registroPayload['check_in'] = now()->toDateString();
                }
                if ($registroSchema->hasColumn('registros', 'check_out')) {
                    $registroPayload['check_out'] = now()->addDays($nights)->toDateString();
                }
                if ($registroSchema->hasColumn('registros', 'created_at')) {
                    $registroPayload['created_at'] = now();
                }
                if ($registroSchema->hasColumn('registros', 'updated_at')) {
                    $registroPayload['updated_at'] = now();
                }

                if (!empty($registroPayload)) {
                    $connection->table('registros')->insert($registroPayload);
                }
            }

            if ($connection->getSchemaBuilder()->hasTable('rooms') && $connection->getSchemaBuilder()->hasColumn('rooms', 'is_available')) {
                $connection->table('rooms')->where('id', (int) $data['room_id'])->update(['is_available' => false]);
            }

            CheckinEvent::create([
                'hotel_id' => (int) $hotel->id,
                'guest_id' => $guestId,
                'room_id' => (int) $data['room_id'],
                'guest_name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                'room_number' => $data['room_number'] ?? null,
                'action' => 'checkin',
                'message' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) . ' ingresó a la habitación ' . ($data['room_number'] ?? ''),
                'happened_at' => now(),
            ]);

            return back()->with('success', 'Huésped registrado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }
}
