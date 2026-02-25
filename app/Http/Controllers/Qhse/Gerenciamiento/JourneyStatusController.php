<?php

namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use App\Models\Qhse\Gerenciamiento\Journey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JourneyStatusController extends Controller
{
    /**
     * Actualizar estado de aprobación
     */
    public function updateApprovalStatus(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);

            $request->validate([
                'approval_status'
            ]);

            $journey->approval_status = $request->approval_status;

            if ($request->approval_status === 'approved' || $request->approval_status === 'rejected') {
                $journey->approver_id = auth()->id();
            }

            $journey->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado de aprobación actualizado',
                'data' => $journey
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado'
            ], 500);
        }
    }

    /**
     * Actualizar estado del viaje (seguimiento)
     */
    public function updateJourneyStatus(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);

            $request->validate([
                'journey_status',
                'event_type' => 'sometimes|string',
                'event_description' => 'sometimes|string',
                'event_location' => 'sometimes|string',
                'event_driver' => 'sometimes|string'
            ]);

            $journey->journey_status = $request->journey_status;
            $journey->save();

            // Aquí podrías registrar el evento en una tabla de historial
            // $this->logJourneyEvent($journey, $request);

            return response()->json([
                'success' => true,
                'message' => 'Estado del viaje actualizado',
                'data' => $journey
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando estado del viaje: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado del viaje'
            ], 500);
        }
    }

    /**
     * Registrar evento en bitácora de viaje
     */
    public function logEvent(Request $request, $id)
    {
        try {
            $journey = Journey::findOrFail($id);

            $request->validate([
                'event_type' => 'required|string',
                'event_description' => 'required|string',
                'event_location' => 'sometimes|string',
                'event_driver' => 'sometimes|string'
            ]);

            // Aquí implementar guardado en tabla de historial
            // Por ahora solo retornamos éxito

            return response()->json([
                'success' => true,
                'message' => 'Evento registrado'
            ]);

        } catch (\Exception $e) {
            Log::error('Error registrando evento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar evento'
            ], 500);
        }
    }
}
