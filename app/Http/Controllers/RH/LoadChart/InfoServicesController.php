<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\RH\LoadChart\FieldBonus;
use App\Models\RH\LoadChart\Services;
use Illuminate\Http\Request;

class InfoServicesController extends Controller
{
    /**
     * Muestra una vista con los servicios y bonos.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $services = Services::getGroupedServices();
        $bonuses = FieldBonus::all();
        // Remove the `dd($services);` and return the view
        return view('modules.rh.loadchart.approval', [
            'services' => $services,
            'bonuses' => $bonuses,
        ]);
    }

    /**
     * Obtiene los servicios y bonos para la API.
     * @return \Illuminate\Http\JsonResponse
     */
   public function getServicesAndBonuses()
{
    // Get services
    $services = Services::getGroupedServices();

    // Select only the requested columns for bonuses
    $bonuses = FieldBonus::select('employee_category', 'bonus_type', 'bonus_identifier')->get();

    return response()->json([
        'services' => $services,
        'bonuses' => $bonuses,
    ]);
}
}
