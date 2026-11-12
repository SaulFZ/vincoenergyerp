<?php
namespace App\Http\Controllers\Systems\InventoryIt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItController extends Controller
{
    /**
     * Muestra la vista principal (Dashboard) de Inventario TI.
     */
    public function index(): View
    {
        return view('modules.systems.inventory-it.dashboard');
    }

}
