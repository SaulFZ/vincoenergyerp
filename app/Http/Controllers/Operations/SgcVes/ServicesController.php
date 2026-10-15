<?php

namespace App\Http\Controllers\Operations\SgcVes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        // Datos mockeados por ahora
        $servicios        = collect([]);
        $totalOperativos  = 445;
        $totalSuministros = 24;
        $totalMostradas   = 469;

        return view('modules.operations.sgc-ves.services', compact(
            'servicios',
            'totalOperativos',
            'totalSuministros',
            'totalMostradas'
        ));
    }
}
