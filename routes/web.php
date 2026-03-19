<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminAdminController;
use App\Http\Controllers\SuperAdmin\HotelController as SuperAdminHotelController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- Rutas de Roles ---
Route::middleware(['auth', 'verified'])->group(function () {

    // -- Rutas de Administrador de Hotel --
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');
        
        Route::resource('rooms', RoomController::class)->except(['show']);
    });

    // -- Rutas de Super Administrador --
    Route::prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('SuperAdmin/Dashboard');
        })->name('dashboard');

        Route::resource('admins', SuperAdminAdminController::class)->except(['show']);
        Route::resource('hotels', SuperAdminHotelController::class);

        // Nuevas rutas del dashboard de Super Admin
        Route::get('billing', function () {
            return Inertia::render('SuperAdmin/Billing/Index');
        })->name('billing.index');

        Route::get('subscriptions', function () {
            return Inertia::render('SuperAdmin/Subscriptions/Index');
        })->name('subscriptions.index');

        Route::get('modules', function () {
            return Inertia::render('SuperAdmin/Modules/Index');
        })->name('modules.index');

        Route::get('plans', function () {
            return Inertia::render('SuperAdmin/Plans/Index');
        })->name('plans.index');

        Route::get('tenants', function () {
            return Inertia::render('SuperAdmin/Tenants/Index');
        })->name('tenants.index');

        Route::get('notifications', function () {
            return Inertia::render('SuperAdmin/Notifications/Index');
        })->name('notifications.index');
    });
});

// --- Rutas de Perfil y Generales ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
