<?php

namespace App\Http\Controllers\Systems\InventoryIT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryITController extends Controller
{
    /**
     * Muestra la vista principal (Dashboard) de Inventario TI.
     */
    public function index(): View
    {
        return view('modules.systems.inventory-it.dashboard');
    }

     public function equipos()
    {

        return view('modules.systems.inventory-it.equipos', [

        ]);
    }
}
