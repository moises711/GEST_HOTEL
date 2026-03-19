<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Room; // Se usará en el futuro

class RoomController extends Controller
{
    /**
     * Muestra el tablero visual del estado de las habitaciones.
     */
    public function index()
    {
        // DATOS DE EJEMPLO: En el futuro, esto vendrá de la base de datos.
        $rooms_data = [
            // Libres
            ['id' => 1, 'number' => '101', 'type' => 'Doble Estándar', 'status' => 'Libre'],
            ['id' => 2, 'number' => '102', 'type' => 'Doble Estándar', 'status' => 'Libre'],
            ['id' => 3, 'number' => '103', 'type' => 'Individual', 'status' => 'Libre'],
            ['id' => 9, 'number' => '204', 'type' => 'Suite Junior', 'status' => 'Libre'],

            // Ocupadas
            ['id' => 4, 'number' => '104', 'type' => 'Doble Estándar', 'status' => 'Ocupada', 'occupant' => 'Familia García'],
            ['id' => 5, 'number' => '201', 'type' => 'Suite Deluxe', 'status' => 'Ocupada', 'occupant' => 'Sr. Martinez (VIP)'],
            ['id' => 6, 'number' => '202', 'type' => 'Suite Deluxe', 'status' => 'Ocupada', 'occupant' => 'Sra. López'],

            // Limpieza
            ['id' => 7, 'number' => '105', 'type' => 'Doble Estándar', 'status' => 'Limpieza'],
            ['id' => 8, 'number' => '203', 'type' => 'Suite Junior', 'status' => 'Limpieza'],

            // Mantenimiento
            ['id' => 10, 'number' => '301', 'type' => 'Suite Presidencial', 'status' => 'Mantenimiento', 'notes' => 'Fuga en el grifo del lavabo.'],
        ];

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => $rooms_data,
        ]);
    }

    /**
     * Almacena una nueva habitación en la base de datos (se usará con un modal).
     */
    public function store(Request $request)
    {
        // Lógica de validación y creación futura
        return redirect()->route('admin.rooms.index')->with('success', 'Habitación creada con éxito.');
    }

    /**
     * Actualiza una habitación específica (se usará con un modal).
     */
    public function update(Request $request, $id)
    {
        // Lógica de validación y actualización futura
        return redirect()->route('admin.rooms.index')->with('success', 'Habitación actualizada con éxito.');
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
