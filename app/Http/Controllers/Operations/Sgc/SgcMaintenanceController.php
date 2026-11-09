<?php

namespace App\Http\Controllers\Operations\Sgc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SgcMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Pestaña por defecto 'list'
        $view = $request->query('view', 'list');

        // 2. Si viene por AJAX, devolvemos solo el tab dentro de 'maint'
        if ($request->ajax()) {
            return view("modules.operations.sgc.tabs.maint.{$view}");
        }

        // 3. Carga normal: devolvemos la vista padre maintenance.blade.php
        return view('modules.operations.sgc.maintenance', compact('view'));
    }
}
