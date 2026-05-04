<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Systems\Tickets\Ticket;
use Illuminate\Http\Request;

class TicketQueryController extends Controller
{
    /** Obtiene los datos para la tabla principal */
    public function getTickets(Request $request)
    {
        // Cargamos la relación del área a través del empleado del usuario
        $tickets = Ticket::with(['user.employee.area', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id'              => $ticket->id,
                    'display_id'      => $ticket->display_id, // TK-001
                    'folio'           => $ticket->folio,
                    'created_at'      => $ticket->created_at->format('d/m/Y'),
                    'subject'         => $ticket->subject,
                    'user_name'       => $ticket->user->name ?? 'N/A',
                    // Buscamos el nombre del área; si falla, mostramos el código como respaldo
                    'department_name' => $ticket->user->employee->area->name ?? $ticket->department_code,
                    'priority'        => $ticket->priority,
                    'status'          => $ticket->status,
                ];
            });

        return response()->json($tickets);
    }

    /** Obtiene 1 solo ticket para llenar el Panel Lateral */
    public function show($id)
    {
        // Cargamos la misma relación para el detalle
        $ticket = Ticket::with(['user.employee.area', 'trackings.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'ticket'  => [
                'id'              => $ticket->id,
                'display_id'      => $ticket->display_id,
                'folio'           => $ticket->folio,
                'status'          => $ticket->status,
                'priority'        => $ticket->priority,
                'subject'         => $ticket->subject,
                'description'     => $ticket->description,
                // Agregamos el nombre del departamento aquí también
                'department_name' => $ticket->user->employee->area->name ?? $ticket->department_code,
                'user_name'       => $ticket->user->name ?? 'N/A',
                'created_at'      => $ticket->created_at->format('d/m/Y'),
                'trackings'       => $ticket->trackings // Historial de comentarios
            ]
        ]);
    }
}
