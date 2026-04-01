<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Systems\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Muestra la vista principal del módulo.
     */
    public function index()
    {
        // Esta ruta debe coincidir con tu carpeta de vistas
        return view('modules.systems.tickets.tickets_management');
    }



}
