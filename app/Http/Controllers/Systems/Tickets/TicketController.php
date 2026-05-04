<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Employee;
use App\Models\Systems\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RH\OrgManagement\Area;
use App\Models\RH\OrgManagement\Department;

class TicketController extends Controller
{
    /** Vista Principal de Gestión de Tickets */
    public function index()
    {
        // Asegúrate de que el nombre de la vista coincida con tu archivo
        return view('modules.systems.tickets.tickets_management');
    }

    /** Nueva Vista de Estadísticas */
    public function stats()
    {
        return view('modules.systems.tickets.stats');
    }
}
