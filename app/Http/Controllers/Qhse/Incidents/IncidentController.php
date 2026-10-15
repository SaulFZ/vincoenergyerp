<?php

namespace App\Http\Controllers\Qhse\Incidents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Muestra la pantalla principal del listado de incidencias.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        return view('modules.qhse.incidents.records');
    }
}