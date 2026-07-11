<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLine;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;
use App\Models\Auth\User;
use Carbon\Carbon;

class ReimbursementStoreController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'motivo'        => 'required|string|max:255',
            'centro_costo'  => 'required|string',
            'tipo_gasto'    => 'required|string',
            'total_amount'  => 'required|numeric|min:0.01',
            'lineas'        => 'required|json',
            'is_deductible' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $isDraft = $request->input('is_draft') == 'true';
            $creatorId = Auth::id();
            $beneficiaryId = $request->beneficiary_id ?? $creatorId;
            $beneficiary = User::find($beneficiaryId);

            if ($isDraft) {
                $folioSystem = 'TMP-' . strtoupper(substr(uniqid(), -6));
                $folioUser = 'TMP-USR';
            } else {
                $lastSys = ExpenseClaim::where('folio_system', 'like', 'VES-%')->orderByRaw('CAST(SUBSTRING(folio_system, 5) AS UNSIGNED) DESC')->first();
                $sysNum = $lastSys ? (int) str_replace('VES-', '', $lastSys->folio_system) + 1 : 1;
                $folioSystem = 'VES-' . str_pad($sysNum, 2, '0', STR_PAD_LEFT);

                $words = explode(' ', trim($beneficiary->name));
                $initials = '';
                foreach ($words as $w) { $initials .= strtoupper(substr($w, 0, 1)); }

                $lastUser = ExpenseClaim::where('user_id', $beneficiaryId)->where('folio_user', 'like', $initials . '-%')->orderByRaw('CAST(SUBSTRING(folio_user, LENGTH("'.$initials.'") + 2) AS UNSIGNED) DESC')->first();
                $userNum = $lastUser ? (int) str_replace($initials . '-', '', $lastUser->folio_user) + 1 : 1;
                $folioUser = $initials . '-' . str_pad($userNum, 2, '0', STR_PAD_LEFT);
            }

            $statusReview = $isDraft ? 'Borrador' : 'Pendiente';
            $statusPayment = $isDraft ? 'N/A' : 'En espera';

            $claim = ExpenseClaim::create([
                'folio_system'   => $folioSystem,
                'folio_user'     => $folioUser,
                'claim_date'     => Carbon::now()->toDateString(),
                'request_type'   => $request->tipo_solicitud,
                'category'       => $request->tipo_gasto,
                'is_deductible'  => $request->boolean('is_deductible'),
                'expense_advance_id' => $request->advance_id ?: null,
                'user_id'        => $beneficiaryId,
                'created_by_id'  => $creatorId,
                'area'           => $request->depto,
                'cost_center'    => $request->centro_costo,
                'emission_place' => $request->lugar_emision ?? 'VHSA, TAB.',
                'motive'         => $request->motivo,
                'total_subtotal' => $request->total_subtotal,
                'total_iva'      => $request->total_iva,
                'total_ish'      => $request->total_ish,
                'total_amount'   => $request->total_amount,
                'status_review'  => $statusReview,
                'status_payment' => $statusPayment,
                'evidence_documents' => []
            ]);

            // Guardado de PDFs
            $rutasPdf = [];
            if ($request->hasFile('evidencias')) {
                $carpetaDestino = "private/administration/expense-claims/pdf/{$folioSystem}";
                foreach ($request->file('evidencias') as $pdf) {
                    $rutasPdf[] = $pdf->store($carpetaDestino);
                }
                $claim->update(['evidence_documents' => $rutasPdf]);
            }

            // Guardado de Líneas (AQUÍ CORREGIMOS EL CFDI_ID)
            $lineas = json_decode($request->input('lineas'), true);
            foreach ($lineas as $linea) {
                ExpenseClaimLine::create([
                    'expense_claim_id' => $claim->id,
                    'expense_cfdi_id'  => !empty($linea['cfdi_id']) ? $linea['cfdi_id'] : null, // Se vincula al XML validado
                    'concept_group'    => $linea['categoria'],
                    'expense_date'     => Carbon::createFromFormat('d/m/Y', $linea['fecha'])->toDateString(),
                    'document_number'  => $linea['folio'],
                    'description'      => $linea['descripcion'],
                    'amount_fiscal'    => $linea['monto_fiscal'] ?? 0,
                    'amount_simple'    => $linea['monto_simple'] ?? 0,
                    'amount_none'      => $linea['monto_sin'] ?? 0,
                    'tax_ish'          => $linea['ish'] ?? 0,
                    'tax_iva'          => $linea['iva'] ?? 0,
                    'line_total'       => $linea['total_linea'] ?? 0,
                ]);
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => $creatorId,
                'action'           => 'Creación',
                'new_status'       => $statusReview,
                'comments'         => 'Reembolso generado desde el portal web.'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'El reembolso fue procesado.', 'folio' => $folioSystem]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ── NUEVO: EDICIÓN (Borradores y Reenvíos de Rechazados) ──
    public function update(Request $request, $id)
    {
        $request->validate([
            'motivo'        => 'required|string|max:255',
            'total_amount'  => 'required|numeric|min:0.01',
            'lineas'        => 'required|json'
        ]);

        try {
            DB::beginTransaction();

            $claim = ExpenseClaim::findOrFail($id);
            $isDraft = $request->input('is_draft') == 'true';
            $creatorId = Auth::id();

            $oldStatus = $claim->status_review;
            $newStatus = $isDraft ? 'Borrador' : 'Pendiente';
            $newPayment = $isDraft ? 'N/A' : 'En espera';

            // Si es un borrador que por fin se enviará a revisión, asignamos los folios reales
            if ($oldStatus === 'Borrador' && !$isDraft) {
                $beneficiary = User::find($claim->user_id);
                $lastSys = ExpenseClaim::where('folio_system', 'like', 'VES-%')->orderByRaw('CAST(SUBSTRING(folio_system, 5) AS UNSIGNED) DESC')->first();
                $sysNum = $lastSys ? (int) str_replace('VES-', '', $lastSys->folio_system) + 1 : 1;
                $claim->folio_system = 'VES-' . str_pad($sysNum, 2, '0', STR_PAD_LEFT);

                $words = explode(' ', trim($beneficiary->name));
                $initials = '';
                foreach ($words as $w) { $initials .= strtoupper(substr($w, 0, 1)); }

                $lastUser = ExpenseClaim::where('user_id', $claim->user_id)->where('folio_user', 'like', $initials . '-%')->orderByRaw('CAST(SUBSTRING(folio_user, LENGTH("'.$initials.'") + 2) AS UNSIGNED) DESC')->first();
                $userNum = $lastUser ? (int) str_replace($initials . '-', '', $lastUser->folio_user) + 1 : 1;
                $claim->folio_user = $initials . '-' . str_pad($userNum, 2, '0', STR_PAD_LEFT);
            }

            $claim->update([
                'request_type'   => $request->tipo_solicitud,
                'category'       => $request->tipo_gasto,
                'is_deductible'  => $request->boolean('is_deductible'),
                'expense_advance_id' => $request->advance_id ?: null,
                'cost_center'    => $request->centro_costo,
                'emission_place' => $request->lugar_emision ?? 'VHSA, TAB.',
                'motive'         => $request->motivo,
                'total_subtotal' => $request->total_subtotal,
                'total_iva'      => $request->total_iva,
                'total_ish'      => $request->total_ish,
                'total_amount'   => $request->total_amount,
                'status_review'  => $newStatus,
                'status_payment' => $newPayment,
            ]);

            // Borramos las líneas viejas y creamos las nuevas (Arquitectura más segura para matrices dinámicas)
            ExpenseClaimLine::where('expense_claim_id', $claim->id)->delete();

            $lineas = json_decode($request->input('lineas'), true);
            foreach ($lineas as $linea) {
                ExpenseClaimLine::create([
                    'expense_claim_id' => $claim->id,
                    'expense_cfdi_id'  => !empty($linea['cfdi_id']) ? $linea['cfdi_id'] : null,
                    'concept_group'    => $linea['categoria'],
                    'expense_date'     => Carbon::createFromFormat('d/m/Y', $linea['fecha'])->toDateString(),
                    'document_number'  => $linea['folio'],
                    'description'      => $linea['descripcion'],
                    'amount_fiscal'    => $linea['monto_fiscal'] ?? 0,
                    'amount_simple'    => $linea['monto_simple'] ?? 0,
                    'amount_none'      => $linea['monto_sin'] ?? 0,
                    'tax_ish'          => $linea['ish'] ?? 0,
                    'tax_iva'          => $linea['iva'] ?? 0,
                    'line_total'       => $linea['total_linea'] ?? 0,
                ]);
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => $creatorId,
                'action'           => 'Edición',
                'previous_status'  => $oldStatus,
                'new_status'       => $newStatus,
                'comments'         => $oldStatus === 'Rechazado' ? 'El usuario corrigió y reenvió la solicitud a revisión.' : 'Se guardó la edición del documento.'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'El reembolso fue actualizado con éxito.', 'folio' => $claim->folio_system]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
