<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Cache\RateLimiting\Limit; // <-- Importante
use Illuminate\Support\Facades\RateLimiter; // <-- Importante
use Illuminate\Http\Request; // <-- Importante

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
        // Forzar a Laravel a renderizar todo en HTTPS cuando esté en producción
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Definir límite de peticiones (Rate Limiter) para el ping de sesión
        RateLimiter::for('session_pings', function (Request $request) {
            // Permite máximo 30 peticiones por minuto por usuario (o por IP si no está logueado)
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
