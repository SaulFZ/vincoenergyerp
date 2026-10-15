<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;

class ReimbursementStatusController extends Controller
{
    private function getRequestLabel($type)
    {
        $type = mb_strtolower($type);
        if (str_contains($type, 'reembolso')) return 'reembolso';
        if (str_contains($type, 'electronica') || str_contains($type, 'electrónica')) return 'comprobación electrónica';
        if (str_contains($type, 'directa')) return 'comprobación directa';
        return 'trámite';
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'new_status' => 'required|string',
            'comments'   => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $claim = ExpenseClaim::findOrFail($id);
            $oldReview = $claim->status_review;
            $oldApproval = $claim->status_approval;
            $action = $request->new_status;

            $label = $this->getRequestLabel($claim->request_type);

            $actionTitle = 'Cambio de Estado';
            $friendlyComment = "Se ha registrado una actualización en el estatus del {$label}.";

            if ($action === 'rechazado') {
                if ($oldReview === 'pendiente') {
                    $claim->update([
                        'status_review'   => 'rechazado',
                        'status_approval' => 'en_espera',
                    ]);
                } elseif ($oldReview === 'validado') {
                    $claim->update([
                        'status_review'   => 'validado',
                        'status_approval' => 'rechazado',
                    ]);
                }

                $actionTitle = 'Solicitud Rechazada';
                $friendlyComment = $request->comments ?? "Lamentablemente, el {$label} fue devuelto. Revisa las notas o contacta a tu revisor.";

            } elseif ($action === 'validado') {
                $claim->update([
                    'status_review'   => 'validado',
                    'status_approval' => 'en_revision',
                ]);
                $actionTitle = 'Validación Exitosa';
                $friendlyComment = "El revisor verifico la solicitud del {$label} y les dio el visto bueno. Ahora pasa con el aprobador.";

            } elseif ($action === 'aprobado') {
                $claim->update([
                    'status_review'   => 'validado',
                    'status_approval' => 'aprobado',
                ]);
                $actionTitle = 'Aprobación Final';
                $friendlyComment = "¡Todo en orden! El {$label} ha sido aprobado.";
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => auth()->id() ?? 1,
                'action'           => $actionTitle,
                'previous_status'  => $oldReview,
                'new_status'       => $action,
                'comments'         => $friendlyComment,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => "El folio ha sido procesado exitosamente."]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()], 500);
        }
    }
}




