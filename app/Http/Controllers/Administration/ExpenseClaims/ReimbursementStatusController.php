<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;

class ReimbursementStatusController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'new_status' => 'required|string',
            'comments'   => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $claim = ExpenseClaim::findOrFail($id);
            $oldStatus = $claim->status_review;
            $newStatus = $request->new_status;

            $newPaymentStatus = $claim->status_payment;
            if ($newStatus === 'Rechazado') $newPaymentStatus = 'No procede';
            if ($newStatus === 'Validado') $newPaymentStatus = 'Por autorizar';
            if ($newStatus === 'Aprobado') $newPaymentStatus = 'Por pagar';

            $claim->update([
                'status_review'  => $newStatus,
                'status_payment' => $newPaymentStatus,
            ]);

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => auth()->id() ?? 1,
                'action'           => 'Dictamen de Revisión',
                'previous_status'  => $oldStatus,
                'new_status'       => $newStatus,
                'comments'         => $request->comments ?? 'Cambio de estado procesado por el sistema.', // Aquí se guarda el motivo
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => "El folio ha sido procesado como {$newStatus}."]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()], 500);
        }
    }
}
