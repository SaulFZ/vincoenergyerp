<?php

namespace App\Http\Controllers\Operations\SgcVes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SgcVesController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos qué pestaña quieren ver (por defecto 'executive')
        $view = $request->query('view', 'executive');

        // 2. LA MAGIA AQUÍ: Si la petición viene por Javascript (AJAX)
        if ($request->ajax()) {
            // Devolvemos SOLAMENTE el blade del tab específico, sin el menú ni los filtros
            return view("modules.operations.sgc-ves.tabs.{$view}");
        }

        // 3. Si es carga normal (F5 o entrar por primera vez), devolvemos toda la página
        return view('modules.operations.sgc-ves.dashboard', compact('view'));
    }
}
