<?php

namespace App\Http\Controllers\Sistemas\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Sistemas\Tickets\Ticket;
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
        return view('modulos.sistemas.sistemas.tickets.tickets_management');
    }



}
