<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $hotels = \App\Models\hotel::with(['plans'])->get();

        $totalHotels = $hotels->count();
        $suspended = $hotels->filter(function($h) { return ($h->settings['suspended'] ?? false) === true; })->count();
        $newThisMonth = $hotels->filter(function($h) { return $h->created_at >= now()->subMonth(); })->count();

        // estimate revenue as sum of current active plan price
        $totalRevenue = $hotels->map(function($h) {
            $current = $h->plans->filter(function($p) { return $p->pivot->active ?? false; })->first();
            return $current ? ($current->price ?? 0) : 0;
        })->sum();

        // recent hotels (simple list)
        $recent = $hotels->sortByDesc('created_at')->take(5)->map(function($h) {
            return [
                'id' => $h->id,
                'name' => $h->name,
                'status' => ($h->settings['suspended'] ?? false) ? 'Vencido' : 'Activo',
                'created_at' => $h->created_at->diffForHumans(),
            ];
        })->values();

        return Inertia::render('SuperAdmin/Dashboard', [
            'metrics' => [
                'total_hotels' => $totalHotels,
                'total_revenue' => $totalRevenue,
                'new_hotels' => $newThisMonth,
                'suspended' => $suspended,
            ],
            'recent' => $recent,
        ]);
    }
}
