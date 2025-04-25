<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\loginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas de autenticación
Route::get('/login', [loginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [loginController::class, 'login']);
Route::post('/logout', [loginController::class, 'logout'])->name('logout');

// Middleware personalizado para verificar sesión
Route::middleware(['web'])->group(function () {
    Route::get('/home', function () {
        // Verificar si el usuario está autenticado con nuestra sesión manual
        if (!session()->has('auth_user')) {
            return redirect()->route('login');
        }

        return view('home');
    })->name('home');
});

// Redirección de la página principal al login
Route::get('/', function () {
    return redirect()->route('login');
});
