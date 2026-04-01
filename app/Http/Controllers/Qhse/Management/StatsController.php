<?php

namespace App\Http\Controllers\Qhse\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Aquí importarás tus modelos más adelante
// use App\Models\Journey;
// use App\Models\Empleado;

class StatsController extends Controller
{
    /**
     * Muestra el dashboard de estadísticas del gerenciamiento de viajes.
     */
    public function index()
    {
        // ---------------------------------------------------------
        // Aquí armarás tu lógica en el futuro. Ejemplo:
        // $totalViajes = Journey::count();
        // $viajesActivos = Journey::where('estatus', 'En Curso')->count();
        // ---------------------------------------------------------

        return view('modules.qhse.management.stats');
    }
}
