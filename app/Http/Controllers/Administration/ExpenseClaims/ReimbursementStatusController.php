<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;
use App\Models\Administration\ExpenseClaims\ExpenseAdvance;

class ReimbursementStatusController extends Controller
{
    private function normalizeArea(?string $area): string
    {
        return mb_strtolower(trim((string) $area));
    }

    private function getRequestLabel($type)
    {
        $type = mb_strtolower((string) $type);
        if (str_contains($type, 'reembolso')) return 'reembolso';
        if (str_contains($type, 'electronica') || str_contains($type, 'electrónica')) return 'comprobación electrónica';
        if (str_contains($type, 'directa')) return 'comprobación directa';
        return 'trámite';
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'new_status' => 'required|string|in:validado,aprobado,rechazado',
            'comments'   => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user()->loadMissing(['employee.area', 'directPermissions']);
            $permNames = $user->directPermissions->pluck('name')->toArray();

            $canValidate = in_array('validar_solicitud', $permNames, true);
            $canApprove  = in_array('aprobar_solicitud', $permNames, true);
            $canViewAll  = in_array('ver_todo_solicitudes', $permNames, true);

            $claim = ExpenseClaim::with(['expenseAdvance', 'beneficiary.employee.area'])->findOrFail($id);
            $oldReview = $claim->status_review;
            $action    = $request->new_status;

            $userAreaNorm  = $this->normalizeArea($user->employee?->area?->name ?? '');
            $claimAreaName = $claim->area ?: ($claim->beneficiary?->employee?->area?->name ?? '');
            $isSameArea    = ($userAreaNorm !== '' && $this->normalizeArea($claimAreaName) === $userAreaNorm);

            // Verificación de permisos en etapa de Validación
            if ($action === 'validado' || ($action === 'rechazado' && $oldReview === 'pendiente')) {
                if (!$canValidate || (!$canViewAll && !$isSameArea)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'No cuentas con permisos para validar solicitudes de esta área.',
                    ], 403);
                }
            }

            // Verificación de permisos en etapa de Aprobación (Siempre exige ser de su misma área)
            if ($action === 'aprobado' || ($action === 'rechazado' && $oldReview === 'validado')) {
                if (!$canApprove || !$isSameArea) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo puedes aprobar o rechazar solicitudes que pertenezcan a tu misma área.',
                    ], 403);
                }
            }

            $label = $this->getRequestLabel($claim->request_type);

            $actionTitle     = 'Cambio de Estado';
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

                $actionTitle     = 'Solicitud Rechazada';
                $friendlyComment = $request->comments ?? "Lamentablemente, el {$label} fue devuelto. Revisa las notas o contacta a tu revisor.";

            } elseif ($action === 'validado') {
                $claim->update([
                    'status_review'   => 'validado',
                    'status_approval' => 'en_revision',
                ]);
                $actionTitle     = 'Validación Exitosa';
                $friendlyComment = "El revisor verificó la solicitud del {$label} y dio el visto bueno. Ahora pasa con el aprobador.";

            } elseif ($action === 'aprobado') {
                $updateData = [
                    'status_review'   => 'validado',
                    'status_approval' => 'aprobado',
                ];

                $extraComment = '';

                if ($claim->expense_advance_id && $claim->expenseAdvance) {
                    $leftover = (float) ($claim->leftover_amount ?? 0);
                    $excess   = (float) ($claim->excess_amount ?? 0);

                    if ($leftover > 0) {
                        $updateData['status_balance'] = $claim->leftover_proof_path
                            ? 'por_verificar'
                            : 'devolucion_pendiente';
                        $extraComment = " Se habilitó el registro de devolución del sobrante por $" . number_format($leftover, 2) . ".";
                    } elseif ($excess > 0) {
                        $updateData['status_balance'] = 'pago_pendiente';
                        $extraComment = " Queda pendiente el pago del excedente a favor del colaborador por $" . number_format($excess, 2) . ".";
                    } else {
                        $updateData['status_balance'] = 'no_aplica';
                        ExpenseAdvance::where('id', $claim->expense_advance_id)->update([
                            'settlement_status' => 'comprobado',
                            'balance'           => 0,
                        ]);
                    }
                }

                $claim->update($updateData);
                $actionTitle     = 'Aprobación Final';
                $friendlyComment = "¡Todo en orden! El {$label} ha sido aprobado.{$extraComment}";
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => $user->id ?? 1,
                'action'           => $actionTitle,
                'previous_status'  => $oldReview,
                'new_status'       => $action,
                'comments'         => $friendlyComment,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El folio ha sido procesado exitosamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage(),
            ], 500);
        }
    }
}