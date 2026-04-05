<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Registrar alias de middleware 'role' para usar ->middleware('role:...') en rutas
        if ($this->app->bound('router')) {
            $this->app['router']->aliasMiddleware('role', \App\Http\Middleware\EnsureRole::class);
        }
    }
}
