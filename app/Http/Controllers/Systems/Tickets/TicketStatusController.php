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
        ]);

        // Bloqueo de seguridad: No se puede guardar si el estado es NUEVO
        if ($request->status === 'nuevo') {
            return response()->json([
                'success' => false,
                'message' => 'No puedes guardar un ticket manteniendo el estado "NUEVO". Por favor, asígnale un estado válido.'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($request, $id) {
                $ticket = Ticket::findOrFail($id);
                $userId = auth()->id();

                // Auto-Asignación del ticket
                if (in_array($request->status, ['abierto', 'en-espera', 'por-concluir', 'realizado', 'cancelado'])) {
                    $ticket->assigned_to = $userId;
                }

                $oldStatus = $ticket->status;
                $oldPriority = $ticket->priority;

                // Actualizamos el Ticket
                $ticket->status = $request->status;
                $ticket->priority = $request->priority;
                $ticket->save();

                // 1. Formateo de textos: Mayúsculas y reemplazo de guiones por espacios
                $fmtOldPriority = strtoupper(str_replace('-', ' ', $oldPriority));
                $fmtNewPriority = strtoupper(str_replace('-', ' ', $request->priority));
                $fmtOldStatus   = strtoupper(str_replace('-', ' ', $oldStatus));
                $fmtNewStatus   = strtoupper(str_replace('-', ' ', $request->status));

                // 2. Historial de Cambio de Prioridad
                if ($oldPriority !== $request->priority) {
                    $msgPrioridad = ($oldPriority === 'sin clasificar')
                        ? "El ticket ha sido <strong>CLASIFICADO</strong> con prioridad <strong>{$fmtNewPriority}</strong>."
                        : "El ticket ha sido <strong>RECLASIFICADO</strong> de prioridad <strong>{$fmtOldPriority}</strong> a <strong>{$fmtNewPriority}</strong>.";

                    TicketTracking::create([
                        'ticket_id'    => $ticket->id,
                        'user_id'      => $userId,
                        'message'      => $msgPrioridad,
                        'status_after' => $request->status
                    ]);
                }

                // 3. Historial de Cambio de Estado
                if ($oldStatus !== $request->status) {
                    TicketTracking::create([
                        'ticket_id'    => $ticket->id,
                        'user_id'      => $userId,
                        'message'      => "El estado del ticket ha cambiado de <strong>{$fmtOldStatus}</strong> a <strong>{$fmtNewStatus}</strong>.",
                        'status_after' => $request->status
                    ]);
                }

                // 4. Historial del Comentario (Con protección XSS)
                if ($request->filled('comentario')) {
                    TicketTracking::create([
                        'ticket_id'    => $ticket->id,
                        'user_id'      => $userId,
                        'message'      => htmlspecialchars($request->comentario),
                        'status_after' => $request->status
                    ]);
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
