<?php
namespace App\Http\Controllers\Qhse\Management;

use App\Http\Controllers\Controller;
use App\Models\Qhse\Management\Journey;
use App\Models\Qhse\Management\JourneyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JourneyStatusController extends Controller
{

    /**
     * 1. Actualizar estado de aprobación (Aprobar/Rechazar/Cancelar)
     */
    public function updateApprovalStatus(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);
            $request->validate(['approval_status' => 'required|string']);

            $journey->approval_status = $request->approval_status;

            // Textos para el historial
            $titulo      = "";
            $descripcion = "";

            if ($request->approval_status === 'approved') {
                $journey->approver_id = auth()->id();
                $titulo               = "Solicitud Aprobada";
                $descripcion          = "El viaje ha sido autorizado por Gerencia y está listo para iniciar.";

            } elseif ($request->approval_status === 'rejected') {
                $journey->approver_id = auth()->id();
                $titulo               = "Solicitud Rechazada";
                $descripcion          = "El viaje ha sido rechazado por Gerencia.";

                // 👇 NUEVO: Sincronizar el estado del viaje a "no_procede"
                $journey->journey_status = 'no_procede';

            } elseif ($request->approval_status === 'cancelled') {
                $titulo      = "Viaje Cancelado";
                $descripcion = "La solicitud ha sido cancelada.";

                // 👇 NUEVO: Sincronizar el estado del viaje a "cancelled"
                $journey->journey_status = 'cancelled';
            }

            $journey->save();

            // Guardar en Historial
            JourneyLog::create([
                'journey_id'  => $journey->id,
                'user_id'     => auth()->id(),
                'event_type'  => $request->approval_status,
                'title'       => $titulo,
                'description' => $descripcion,
                'event_time'  => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Estado actualizado']);
        } catch (\Exception $e) {
            Log::error('Error actualizando estado GV: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }
/**
 * Cambiar el aprobador de un viaje (solo si está pendiente y lo solicita el creador)
 */
    public function changeApprover(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);

            // Validaciones de seguridad
            if ($journey->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el creador del viaje puede cambiar el aprobador.',
                ], 403);
            }

            if ($journey->approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede cambiar el aprobador cuando el GV está Pendiente.',
                ], 422);
            }

            $request->validate([
                'approver_id' => 'required|exists:users,id',
            ]);

            $nuevoAprobadorId = $request->approver_id;

            // Evitar asignar el mismo aprobador
            if ($journey->approver_id == $nuevoAprobadorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nuevo aprobador es el mismo que el actual.',
                ], 422);
            }

            $journey->approver_id = $nuevoAprobadorId;
            $journey->save();

            // Registrar en bitácora
            JourneyLog::create([
                'journey_id'  => $journey->id,
                'user_id'     => auth()->id(),
                'event_type'  => 'approver_changed',
                'title'       => 'Aprobador Reasignado',
                'description' => 'El solicitante ha reasignado el aprobador del viaje.',
                'event_time'  => now(),
            ]);

            // Reenviar correo al nuevo aprobador
            try {
                $newApprover = \App\Models\Auth\User::find($nuevoAprobadorId);
                if ($newApprover && $newApprover->email) {
                    \Illuminate\Support\Facades\Mail::to($newApprover->email)
                        ->send(new \App\Mail\Qhse\Management\JourneyApprovalMail($journey));
                }
            } catch (\Exception $mailEx) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo cambio aprobador: ' . $mailEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Aprobador actualizado correctamente.',
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error cambiando aprobador: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar aprobador: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * 2. Actualizar estado del viaje operativo (Iniciar / Finalizar)
     */
    // En JourneyStatusController.php, método updateJourneyStatus
    public function updateJourneyStatus(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);
            $request->validate(['journey_status' => 'required|string']);

            $journey->journey_status = $request->journey_status;
            $journey->save();

            // Guardar en Historial
            $titulo = $request->journey_status === 'in_progress' ? 'Viaje Iniciado' : 'Viaje Finalizado';
            $desc   = $request->journey_status === 'in_progress'
                ? 'La unidad ha comenzado la ruta operativa.'
                : 'El viaje ha concluido y la unidad llegó a su destino.';

            JourneyLog::create([
                'journey_id'  => $journey->id,
                'user_id'     => auth()->id(),
                'event_type'  => $request->journey_status,
                'title'       => $titulo,
                'description' => $desc,
                'event_time'  => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error actualizando estado Ruta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * 3. Registrar eventos manuales (Paradas, Relevos, Detenciones)
     */
    public function logEvent(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);
            $request->validate([
                'event_type'  => 'required|string',
                'title'       => 'required|string',
                'description' => 'required|string',
            ]);

            // 1. Guardar el evento en la bitácora
            $log = JourneyLog::create([
                'journey_id'  => $journey->id,
                'user_id'     => auth()->id(),
                'event_type'  => $request->event_type,
                'title'       => $request->title,
                'description' => $request->description,
                'event_time'  => now(),
            ]);

            // 2. 👇 NUEVO: Actualizar el estado del viaje si es detención o reanudación
            if ($request->event_type === 'detencion') {
                $journey->journey_status = 'stopped';
                $journey->save();
            } elseif ($request->event_type === 'reanudacion') {
                $journey->journey_status = 'in_progress';
                $journey->save();
            }

            return response()->json(['success' => true, 'data' => $log]);
        } catch (\Exception $e) {
            Log::error('Error registrando evento: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
