<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HotelController extends Controller
{
    /**
     * Muestra una lista de todos los hoteles gestionados.
     */
    public function index()
    {
        // Más adelante, aquí obtendremos los hoteles de la base de datos.
        return Inertia::render('SuperAdmin/Hotel/Index', [
            // 'hotels' => Hotel::with('plan')->get(),
        ]);
    }

    /**
     * Muestra los detalles de un hotel específico, incluyendo contrato, módulos y reportes.
     */
    public function show($id)
    {
        // Más adelante, aquí obtendremos el hotel de la base de datos.
        // $hotel = Hotel::with('plan', 'modules')->findOrFail($id);
        return Inertia::render('SuperAdmin/Hotel/Show', [
            // 'hotel' => $hotel,
        ]);
    }
}
