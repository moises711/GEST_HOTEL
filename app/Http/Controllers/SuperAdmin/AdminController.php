<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Muestra una lista de todos los administradores de hotel.
     */
    public function index()
    {
        // Más adelante, aquí obtendremos los administradores de la base de datos.
        return Inertia::render('SuperAdmin/Index', [
            // 'admins' => User::role('admin')->get(),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo administrador de hotel.
     */
    public function create()
    {
        return Inertia::render('SuperAdmin/CreateAdmin');
    }

    /**
     * Almacena un nuevo administrador de hotel en la base de datos.
     */
    public function store(Request $request)
    {
        // Lógica de validación y creación.
        return redirect()->route('superadmin.admins.index')->with('success', 'Administrador creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un administrador existente.
     */
    public function edit($id)
    {
        return Inertia::render('SuperAdmin/EditAdmin', [
            // 'admin' => User::findOrFail($id), // Se activará después
        ]);
    }

    /**
     * Actualiza un administrador existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        // Lógica de validación y actualización.
        return redirect()->route('superadmin.admins.index')->with('success', 'Administrador actualizado exitosamente.');
    }

    /**
     * Elimina un administrador.
     */
    public function destroy($id)
    {
        // Lógica para eliminar.
        return redirect()->route('superadmin.admins.index')->with('success', 'Administrador eliminado exitosamente.');
    }
}
