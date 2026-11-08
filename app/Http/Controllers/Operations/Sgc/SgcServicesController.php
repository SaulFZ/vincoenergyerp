<?php

namespace App\Http\Controllers\Operations\Sgc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SgcServicesController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos qué pestaña quieren ver (por defecto 'list')
        $view = $request->query('view', 'list');
        abort_unless(in_array($view, ['list', 'staff', 'new_service'], true), 404);
        abort_unless(in_array($request->query('mode', 'create'), ['create', 'edit', 'view'], true), 422);

        // para crear, editar o visualizar un servcio se abre un modal fullscreen
        if ($view === 'new_service' && !$request->ajax()) {
            return redirect()->route('operations.sgc.services', ['view' => 'list']);
        }

        // 2. LA MAGIA AQUÍ: Si la petición viene por Javascript (AJAX)
        if ($request->ajax()) {
            // Devolvemos SOLAMENTE el blade del tab específico
            return view("modules.operations.sgc.tabs.serv.{$view}");
        }

        // 3. Si es carga normal (F5 o entrar por primera vez), devolvemos toda la página
        return view('modules.operations.sgc.services', compact('view'));
    }
}
