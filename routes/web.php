<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminAdminController;
use App\Http\Controllers\SuperAdmin\HotelController as SuperAdminHotelController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->is_superadmin) {
        return redirect()->route('superadmin.dashboard');
    }
    // Si es admin (por rol o tipo) redirigir a su dashboard
    if ($user) {
        if (method_exists($user, 'hasRole') && $user->hasRole(['admin'])) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->type === 'admin') {
            return redirect()->route('admin.dashboard');
        }
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- Rutas de Roles ---
Route::middleware(['auth', 'verified'])->group(function () {

    // -- Rutas de Administrador de Hotel --
    Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Admin modules
        Route::get('reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('checkin', [\App\Http\Controllers\Admin\ModulePageController::class, 'checkin'])->name('checkin.index');
        Route::post('checkin/check-in', [\App\Http\Controllers\Admin\ModulePageController::class, 'checkInGuest'])->name('checkin.checkin');
        Route::post('checkin/check-out', [\App\Http\Controllers\Admin\ModulePageController::class, 'checkOutGuest'])->name('checkin.checkout');
        Route::get('guests', [\App\Http\Controllers\Admin\GuestController::class, 'index'])->name('guests.index');
        Route::post('guests', [\App\Http\Controllers\Admin\GuestController::class, 'store'])->name('guests.store');
        Route::post('guests/import', [\App\Http\Controllers\Admin\GuestController::class, 'import'])->name('guests.import');
        Route::get('finances', [\App\Http\Controllers\Admin\ModulePageController::class, 'finances'])->name('finances.index');
        Route::get('reports', [\App\Http\Controllers\Admin\ModulePageController::class, 'reports'])->name('reports.index');
        Route::get('loyalty', [\App\Http\Controllers\Admin\ModulePageController::class, 'loyalty'])->name('loyalty.index');
        Route::get('channel-manager', [\App\Http\Controllers\Admin\ModulePageController::class, 'channelManager'])->name('channel-manager.index');
        Route::get('analytics', [\App\Http\Controllers\Admin\ModulePageController::class, 'analytics'])->name('analytics.index');
        Route::get('users', [\App\Http\Controllers\Admin\ModulePageController::class, 'users'])->name('users.index');
        Route::get('notes', [\App\Http\Controllers\Admin\ModulePageController::class, 'notes'])->name('notes.index');
        Route::get('maintenance', [\App\Http\Controllers\Admin\ModulePageController::class, 'maintenance'])->name('maintenance.index');
        Route::get('customization', [\App\Http\Controllers\Admin\ModulePageController::class, 'customization'])->name('customization.index');
        Route::get('dark-mode', [\App\Http\Controllers\Admin\ModulePageController::class, 'darkMode'])->name('dark-mode.index');

        Route::post('rooms/{room}/ready', [RoomController::class, 'markReady'])->name('rooms.ready');
        Route::resource('rooms', RoomController::class)->except(['show']);
    });

    // -- Rutas de Super Administrador --
    Route::prefix('superadmin')->name('superadmin.')->middleware(['role:superadmin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('admins', SuperAdminAdminController::class)->except(['show']);
        Route::resource('hotels', SuperAdminHotelController::class);
        Route::post('hotels/{hotel}/plan', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'changePlan'])->name('hotels.change_plan');
        Route::post('hotels/{hotel}/modules', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'updateModules'])->name('hotels.modules.update');
        Route::post('hotels/{hotel}/renew', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'renewContract'])->name('hotels.renew');
        Route::post('hotels/{hotel}/deactivate', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'deactivateHotel'])->name('hotels.deactivate');
        Route::post('hotels/apply-pending', [\App\Http\Controllers\SuperAdmin\HotelController::class, 'applyPending'])->name('hotels.apply_pending');

        // Nuevas rutas del dashboard de Super Admin
        Route::get('billing', function () {
            $hotels = \App\Models\hotel::with('plans')->get();

            $invoices = $hotels->map(function ($hotel) {
                $activePlan = $hotel->plans
                    ->filter(fn ($plan) => (bool) ($plan->pivot->active ?? false))
                    ->sortByDesc(fn ($plan) => $plan->pivot->ends_at ?? null)
                    ->first();

                $currentPlan = $activePlan ?: $hotel->plans->sortByDesc(fn ($plan) => $plan->pivot->ends_at ?? null)->first();
                $endsAt = $activePlan?->pivot?->ends_at;
                $daysLeft = $endsAt ? now()->diffInDays(\Carbon\Carbon::parse($endsAt), false) : null;

                $status = 'Vencido';
                if (!is_null($daysLeft) && $daysLeft > 15) {
                    $status = 'Pagado';
                } elseif (!is_null($daysLeft) && $daysLeft >= 0) {
                    $status = 'Pendiente';
                }

                return [
                    'id' => 'INV-' . str_pad((string) $hotel->id, 4, '0', STR_PAD_LEFT),
                    'hotel' => $hotel->name,
                    'amount' => (float) ($currentPlan?->price ?? 0),
                    'date' => $currentPlan?->pivot?->updated_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    'status' => $status,
                ];
            })->values();

            $paid = $invoices->where('status', 'Pagado');
            $totalMonth = (float) $paid->sum('amount');
            $renewalRate = $invoices->count() > 0
                ? round(($paid->count() / $invoices->count()) * 100, 1)
                : 0;

            $topPlan = \App\Models\Plan::query()
                ->leftJoin('hotel_plan', 'plans.id', '=', 'hotel_plan.plan_id')
                ->select('plans.name', \Illuminate\Support\Facades\DB::raw('COUNT(hotel_plan.hotel_id) as hotels_count'))
                ->groupBy('plans.id', 'plans.name')
                ->orderByDesc('hotels_count')
                ->first();

            $monthly = collect(range(0, 5))->map(function ($offset) use ($invoices) {
                $date = now()->subMonths(5 - $offset);
                $monthKey = $date->format('Y-m');
                $monthAmount = $invoices
                    ->filter(fn ($inv) => str_starts_with((string) $inv['date'], $monthKey))
                    ->sum('amount');

                return [
                    'label' => $date->locale('es')->translatedFormat('M'),
                    'amount' => (float) $monthAmount,
                ];
            })->values();

            return Inertia::render('SuperAdmin/Billing/Index', [
                'metrics' => [
                    'total_month' => $totalMonth,
                    'top_plan' => $topPlan?->name ?? 'Sin datos',
                    'renewal_rate' => $renewalRate,
                ],
                'invoices' => $invoices,
                'monthly' => $monthly,
            ]);
        })->name('billing.index');

        Route::get('subscriptions', function () {
            $hotels = \App\Models\hotel::with('plans')->get();
            $plans = \App\Models\Plan::select('id', 'name')->orderBy('id')->get();

            $subscriptions = $hotels->map(function ($hotel) {
                $activePlan = $hotel->plans
                    ->filter(fn ($plan) => (bool) ($plan->pivot->active ?? false))
                    ->sortByDesc(function ($plan) {
                        return $plan->pivot->ends_at ?? null;
                    })
                    ->first();

                $pendingPlan = $hotel->plans
                    ->filter(fn ($plan) => (bool) ($plan->pivot->pending ?? false))
                    ->sortByDesc(function ($plan) {
                        return $plan->pivot->updated_at ?? null;
                    })
                    ->first();

                $currentPlan = $activePlan ?: $hotel->plans->sortByDesc(function ($plan) {
                    return $plan->pivot->ends_at ?? null;
                })->first();

                $endsAt = $activePlan?->pivot?->ends_at;
                $daysLeft = null;
                if ($endsAt) {
                    $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($endsAt), false);
                }

                $status = 'activo';
                if (is_null($daysLeft)) {
                    $status = 'vencido';
                } elseif ($daysLeft < 0) {
                    $status = 'vencido';
                } elseif ($daysLeft <= 15) {
                    $status = 'por_vencer';
                }

                return [
                    'id' => $hotel->id,
                    'hotel_id' => $hotel->id,
                    'hotel' => $hotel->name,
                    'current_plan_id' => $currentPlan?->id,
                    'plan' => $currentPlan?->name ?? 'Sin plan',
                    'expires_at' => $endsAt,
                    'days_left' => is_null($daysLeft) ? 0 : max($daysLeft, 0),
                    'status' => $status,
                    'has_pending' => (bool) $pendingPlan,
                    'pending_plan' => $pendingPlan?->name,
                    'pending_plan_id' => $pendingPlan?->id,
                ];
            })->values();

            return Inertia::render('SuperAdmin/Subscriptions/Index', [
                'subscriptions' => $subscriptions,
                'plans' => $plans,
            ]);
        })->name('subscriptions.index');

        Route::get('modules', function () {
            $plans = \App\Models\Plan::select('id', 'name', 'modules')->orderBy('id')->get();

            $allModules = $plans
                ->flatMap(function ($plan) {
                    return $plan->modules ?? [];
                })
                ->unique()
                ->values();

            return Inertia::render('SuperAdmin/Modules/Index', [
                'plans' => $plans,
                'modules' => $allModules,
            ]);
        })->name('modules.index');

        Route::get('plans', function () {
            $plans = \App\Models\Plan::select('id', 'name', 'price', 'features')->orderBy('id')->get();

            return Inertia::render('SuperAdmin/Plans/Index', [
                'plans' => $plans,
            ]);
        })->name('plans.index');

        // Tenant routes replaced by hotels resource. Use SuperAdmin\HotelController for provisioning.

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
