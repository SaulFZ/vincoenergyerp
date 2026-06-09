<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Helpers\PermissionHelper;
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
        // 1. Evaluamos los permisos usando el Helper importado
        $canSeeFiltersAndStats = PermissionHelper::hasDirectPermission('ver_estadisticas_tickets');
        $canAttendTickets = PermissionHelper::hasDirectPermission('atender_tickets');

        // 2. Pasamos las variables limpias a la vista
        return view('modules.systems.tickets.tickets_management', compact(
            'canSeeFiltersAndStats',
            'canAttendTickets'
        ));
    }

    /** Nueva Vista de Estadísticas */
    public function stats()
    {
        // Usamos el Helper importado para la red de seguridad
        if (!PermissionHelper::hasDirectPermission('ver_estadisticas_tickets')) {
            abort(403, 'Acceso Denegado. No tienes permiso para ver las estadísticas de tickets.');
        }

        return view('modules.systems.tickets.stats');
    }
}
