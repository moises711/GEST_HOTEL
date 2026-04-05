<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ReservationController extends Controller
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
                    if ($schema->hasTable('reservations') || $schema->hasTable('registros')) {
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
        $user = auth()->user();
        $reservations = [];

        [$hotelForPlan, $activePlan] = $this->resolveHotelAndPlan($user);
        $enabledModules = $hotelForPlan ? $this->enabledModulesForHotel($hotelForPlan, $activePlan) : [];
        if (!$this->moduleIsEnabled($enabledModules, 'reservations')) {
            return redirect()->route('admin.dashboard')->with('error', 'Tu plan actual no incluye el módulo de Reservas.');
        }

        try {
            $hotel = $user?->hotels()->first();
            if ($hotel) {
                $conn = $this->resolvePreferredConnection($hotel);
                $schema = $conn->getSchemaBuilder();

                if ($schema->hasTable('reservations')) {
                    $reservations = $conn->table('reservations')->orderByDesc('id')->get()->map(fn ($r) => (array) $r)->toArray();
                } elseif ($schema->hasTable('registros')) {
                    $reservations = $conn->table('registros')->orderByDesc('id')->get()->map(function ($r) use ($conn, $schema) {
                        $guestName = '—';
                        if (!empty($r->id_cliente) && $schema->hasTable('clientes')) {
                            $cliente = $conn->table('clientes')->where('id', (int) $r->id_cliente)->first();
                            if ($cliente) {
                                $guestName = trim(($cliente->name ?? '') . ' ' . ($cliente->last_name ?? '')) ?: ($cliente->name ?? '—');
                            }
                        }

                        $roomNumber = null;
                        if (!empty($r->id_room) && $schema->hasTable('rooms')) {
                            $roomNumber = $conn->table('rooms')->where('id', (int) $r->id_room)->value('room_number');
                        }

                        return [
                            'id' => $r->id,
                            'guest' => $guestName,
                            'room' => $roomNumber ?: $r->id_room,
                            'checkin' => $r->check_in ?? null,
                            'checkout' => $r->check_out ?? null,
                            'status' => 'Reservada',
                        ];
                    })->toArray();
                } else {
                    $reservations = [];
                }
            }
        } catch (\Exception $e) {
            $reservations = [];
        }

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }
}
