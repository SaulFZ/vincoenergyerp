<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Systems\Tickets\Ticket;
use App\Models\Systems\Tickets\TicketTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketStatusController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'status'     => 'required|string',
            'priority'   => 'required|string',
            'comentario' => 'nullable|string',
            'subject'    => 'required|string',
            'description'=> 'required|string',
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $ticket = Ticket::findOrFail($id);
                $userId = auth()->id(); // El técnico de TI conectado

                // 1. Auto-Asignación del ticket
                // Si alguien lo mueve y no tenía dueño, o si lo cierran, se queda con él
                if (in_array($request->status, ['abierto', 'en-espera', 'por-concluir', 'realizado', 'cancelado'])) {
                    $ticket->assigned_to = $userId;
                }

                // 2. Actualizamos el Ticket
                $ticket->status = $request->status;
                $ticket->priority = $request->priority;
                $ticket->subject = $request->subject;
                $ticket->description = $request->description;
                $ticket->save();

                // 3. Guardamos la bitácora SOLO si hay un comentario o cambio importante
                if ($request->filled('comentario')) {
                    TicketTracking::create([
                        'ticket_id'    => $ticket->id,
                        'user_id'      => $userId,
                        'message'      => $request->comentario,
                        'status_after' => $request->status
                    ]);

                    // Aquí iría el disparador del correo de SEGUIMIENTO (Job/Mailable)
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Gestión de ticket guardada con éxito.'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
