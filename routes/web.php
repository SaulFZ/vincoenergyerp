<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Rutas de autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirección de la página principal al login o al home según la autenticación
Route::get('/', function () {
    return session()->has('auth_user') ? redirect()->route('home') : redirect()->route('login');
});

// Rutas protegidas por verificación de sesión
Route::middleware(['web'])->group(function () {
    // Verificación de autenticación para todas las rutas en este grupo
    Route::middleware(['auth.session'])->group(function () {
        Route::get('/home', function () {
            return view('home');
        })->name('home');

        Route::get('/splash', function () {
            return view('components.ui.splash');
        })->name('splash');

        // Rutas para los módulos individuales - Simplificadas para URLs más limpias
        Route::get('/administracion', function () {
            return view('modulos.administracion.administracionhome');
        })->name('modulo.administracion');

      // QHSE y sus subsistemas
      Route::prefix('qhse')->group(function () {
        Route::get('/', function () {
            return view('modulos.qhse.qhsehome');
        })->name('modulo.qhse');

        Route::get('/vescap', function () {
            return view('modulos.qhse.sistemas.vescap.index');
        })->name('qhse.vescap');

        Route::get('/incidencias', function () {
            return view('modulos.qhse.sistemas.tacisqhse.index');
        })->name('qhse.incidencias');

        Route::get('/auditorias', function () {
            return view('modulos.qhse.sistemas.auditorias.index');
        })->name('qhse.auditorias');
    });


        Route::get('/ventas', function () {
            return view('modulos.ventas.ventashome');
        })->name('modulo.ventas');

        Route::get('/recursoshumanos', function () {
            return view('modulos.recursoshumanos.recursoshumanoshome');
        })->name('modulo.recursoshumanos');

        Route::get('/suministro', function () {
            return view('modulos.suministros.suministroshome');
        })->name('modulo.suministro');

        Route::get('/operaciones', function () {
            return view('modulos.operaciones.operacioneshome');
        })->name('modulo.operaciones');

        Route::get('/sistemas', function () {
            return view('modulos.sistemas.sistemashome');
        })->name('modulo.sistemas');

        Route::get('/almacen', function () {
            return view('modulos.almacen.almacenhome');
        })->name('modulo.almacen');

        Route::get('/geociencias', function () {
            return view('modulos.geociencias.geocienciashome');
        })->name('modulo.geociencias');
    });
    Route::get('/transition', function () {
        return view('components.ui.transition');
    })->name('transition');
});
