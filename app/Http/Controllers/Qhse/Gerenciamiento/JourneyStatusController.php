<?php
namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use App\Models\Qhse\Gerenciamiento\Journey;
use App\Models\Qhse\Gerenciamiento\JourneyLog;
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
