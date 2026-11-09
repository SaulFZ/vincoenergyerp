<?php

namespace App\Http\Controllers\Operations\Sgc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SgcOperationsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Pestaña por defecto.
        $view = $request->query('view', 'list');

        // 2. AJAX: solamente el hijo.
        if ($request->ajax()) {
            return view("modules.operations.sgc.tabs.oper.{$view}");
        }

        // 3. Carga normal: padre completo.
        return view('modules.operations.sgc.operations', compact('view'));
    }
}
