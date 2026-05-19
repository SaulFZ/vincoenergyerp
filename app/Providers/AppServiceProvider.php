<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- Revisa que esta línea esté presente

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
    }
}
