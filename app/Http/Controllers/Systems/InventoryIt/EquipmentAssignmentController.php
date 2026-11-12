<?php

namespace App\Http\Controllers\Systems\InventoryIt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentAssignmentController  extends Controller
{
    public function index(): View
    {
        return view('modules.systems.inventory-it.assignments');
    }
}
