<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    public function showPublicStorage($path)
    {
        // Seguridad: solo usuarios autenticados pueden ver archivos
        if (!Auth::check()) {
            abort(403, 'Acceso denegado');
        }

        // Buscamos en storage/app/public/...
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
