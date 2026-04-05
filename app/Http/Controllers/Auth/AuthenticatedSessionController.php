<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // If the user uses roles (e.g., Spatie) and has a superadmin role, redirect to superadmin dashboard.
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
            return redirect()->route('superadmin.dashboard');
        }

        // Or check a boolean flag on the user model: 'is_superadmin'.
        if ($user && (($user->is_superadmin ?? false) || ($user->type ?? '') === 'superadmin')) {
            return redirect()->route('superadmin.dashboard');
        }

        // If the user is tied to a tenant (tenant_id), send them to the hotel admin dashboard
        if ($user && !empty($user->tenant_id)) {
            return redirect()->route('admin.dashboard');
        }

        // Default: global dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
