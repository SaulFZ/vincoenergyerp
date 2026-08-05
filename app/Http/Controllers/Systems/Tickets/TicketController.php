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
        $canSeeFiltersAndStats = PermissionHelper::hasDirectPermission('ver_estadisticas_tickets');
        $canAttendTickets = PermissionHelper::hasDirectPermission('atender_tickets');

        return view('modules.systems.tickets.tickets_management', compact(
            'canSeeFiltersAndStats',
            'canAttendTickets'
        ));
    }

    /** Nueva Vista de Estadísticas */
    public function stats()
    {
        // 1. Validamos que tenga permiso (Red de seguridad)
        if (!PermissionHelper::hasDirectPermission('ver_estadisticas_tickets')) {
            abort(403, 'Acceso Denegado. No tienes permiso para ver las estadísticas de tickets.');
        }

        // 2. ¡AQUÍ ESTÁ LA MAGIA! Declaramos la variable que le faltaba a tu menú
        $canSeeFiltersAndStats = true; // Ya sabemos que es true porque pasó el if de arriba

        // (Opcional) Si tu menú también usa $canAttendTickets en algún momento, pásala también:
        // $canAttendTickets = PermissionHelper::hasDirectPermission('atender_tickets');

        // 3. Pasamos la variable a la vista usando compact()
        return view('modules.systems.tickets.stats', compact('canSeeFiltersAndStats'));
    }
}
