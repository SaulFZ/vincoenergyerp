<?php

namespace App\Http\Controllers\Operations\Sgc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SgcController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos qué pestaña quieren ver (por defecto 'executive')
        $view = $request->query('view', 'executive');

        // 2. LA MAGIA AQUÍ: Si la petición viene por Javascript (AJAX)
        if ($request->ajax()) {
            // CORRECCIÓN: Se agregó ".dash." a la ruta para que coincida con tus includes
            return view("modules.operations.sgc.tabs.dash.{$view}");
        }

        // 3. Si es carga normal (F5 o entrar por primera vez), devolvemos toda la página
        return view('modules.operations.sgc.dashboard', compact('view'));
    }
}
