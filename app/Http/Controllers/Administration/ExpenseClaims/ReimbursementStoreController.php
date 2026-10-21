<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLine;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;
use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use App\Models\Auth\User;
use Carbon\Carbon;

class ReimbursementStoreController extends Controller
{
    /* ==========================================================================
       ── HELPERS ──
       ========================================================================== */

    private function parseCostCenter($value)
    {
        if (!$value || $value === 'VARIOS') return ['cc' => null, 'prj' => null];
        $parts = explode('_', $value);
        if ($parts[0] === 'CC')  return ['cc' => $parts[1], 'prj' => null];
        if ($parts[0] === 'PRJ') return ['cc' => $parts[2], 'prj' => $parts[1]];
        return ['cc' => null, 'prj' => null];
    }

    private function getRequestLabel($type)
    {
        $type = mb_strtolower($type);
        if (str_contains($type, 'reembolso')) return 'reembolso';
        if (str_contains($type, 'electronica') || str_contains($type, 'electrónica')) return 'comprobación electrónica';
        if (str_contains($type, 'directa')) return 'comprobación directa';
        return 'trámite';
    }

    private function buildPdfFilename(string $folio, string $tipo, ?string $date = null, ?int $sequence = null, string $ext = 'pdf'): string
    {
        $folio = strtoupper(preg_replace('/[^A-Za-z0-9\-_]+/', '_', $folio));
        $folio = trim($folio, '_');
        $tipo = strtoupper($tipo);
        $seq = $sequence !== null ? str_pad($sequence, 2, '0', STR_PAD_LEFT) : '';
        $fecha = $date ? Carbon::parse($date)->format('Ymd') : Carbon::now()->format('Ymd');
        $random = Str::random(6);

        return "{$folio}_{$tipo}{$seq}_{$fecha}_{$random}.{$ext}";
    }

    private function getEarliestExpenseDate(array $lineas): ?string
    {
        $min = null;
        foreach ($lineas as $l) {
            if (!empty($l['fecha'])) {
                try {
                    $f = Carbon::createFromFormat('d/m/Y', $l['fecha']);
                    if ($min === null || $f->lt($min)) {
                        $min = $f;
                    }
                } catch (\Exception $e) {
                    // Ignorar fechas mal formateadas
                }
            }
        }
        return $min ? $min->toDateString() : null;
    }

    private function getNextFacturaIndex(array $paths): int
    {
        $max = 0;
        foreach ($paths as $p) {
            if (preg_match('/_FAC(\d+)_/', basename($p), $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        return $max + 1;
    }

    private function getRemovedPaths(array $old, array $new): array
    {
        return array_values(array_diff($old, $new));
    }

    private function migrateDraftFilesToFinalFolio(ExpenseClaim $claim, string $oldFolio, string $newFolio): void
    {
        if ($oldFolio === $newFolio) {
            return;
        }

        $baseOld = "private/administration/expense-claims/pdf/{$oldFolio}";
        $baseNew = "private/administration/expense-claims/pdf/{$newFolio}";

        // 1. FACTURAS (invoices)
        $newEvidencePaths = [];
        $oldEvidence = is_array($claim->evidence_documents) ? $claim->evidence_documents : [];

        foreach ($oldEvidence as $oldPath) {
            if (!$oldPath) continue;
            if (!Storage::exists($oldPath)) continue;

            $basename = basename($oldPath);
            $newName = preg_replace('/^' . preg_quote($oldFolio, '/') . '/', $newFolio, $basename);
            $newPath = "{$baseNew}/invoices/{$newName}";

            Storage::move($oldPath, $newPath);
            $newEvidencePaths[] = $newPath;
        }

        $claim->evidence_documents = $newEvidencePaths;

        // 2. SOBRANTE (leftovers)
        if ($claim->leftover_proof_path && Storage::exists($claim->leftover_proof_path)) {
            $basename = basename($claim->leftover_proof_path);
            $newName = preg_replace('/^' . preg_quote($oldFolio, '/') . '/', $newFolio, $basename);
            $newProof = "{$baseNew}/leftovers/{$newName}";

            Storage::move($claim->leftover_proof_path, $newProof);
            $claim->leftover_proof_path = $newProof;
        }

        // 3. EXCEDENTE (excesses)
        if ($claim->excess_proof_path && Storage::exists($claim->excess_proof_path)) {
            $basename = basename($claim->excess_proof_path);
            $newName = preg_replace('/^' . preg_quote($oldFolio, '/') . '/', $newFolio, $basename);
            $newExcessProof = "{$baseNew}/excesses/{$newName}";

            Storage::move($claim->excess_proof_path, $newExcessProof);
            $claim->excess_proof_path = $newExcessProof;
        }

        // 4. Limpiar carpetas viejas si quedaron vacías
        foreach (["{$baseOld}/invoices", "{$baseOld}/leftovers", "{$baseOld}/excesses", $baseOld] as $dir) {
            if (Storage::exists($dir)) {
                $files = Storage::files($dir);
                $dirs  = Storage::directories($dir);
                if (empty($files) && empty($dirs)) {
                    Storage::deleteDirectory($dir);
                }
            }
        }
    }

    /* ==========================================================================
       ── CÁLCULO DE DIFERENCIAS EN ETAPA 1 (Sin exigir PDF hasta Aprobación) ──
       ========================================================================== */

    private function buildBalancePayload(Request $request, ?ExpenseClaim $existing = null): array
    {
        $isComprobacion = in_array($request->tipo_solicitud, ['Comprobacion Electronica', 'Comprobacion Directa']);

        if (!$isComprobacion || !$request->advance_id) {
            if ($existing?->leftover_proof_path && Storage::exists($existing->leftover_proof_path)) {
                Storage::delete($existing->leftover_proof_path);
            }
            return [
                'leftover_amount'     => null,
                'leftover_method'     => null,
                'leftover_notes'      => null,
                'leftover_proof_path' => null,
                'excess_amount'       => null,
                'excess_method'       => null,
                'excess_notes'        => null,
                'excess_proof_path'   => null,
                'status_balance'      => 'no_aplica',
            ];
        }

        $advance       = ExpenseAdvance::find($request->advance_id);
        $advanceAmount = $advance ? (float) $advance->amount : 0;
        $totalAmount   = (float) $request->total_amount;
        $diff          = round($advanceAmount - $totalAmount, 2);

        // ── CASO A: SOBRANTE (Anticipo > Comprobado -> diff > 0) ──
        // En Etapa 1 solo calculamos el monto y dejamos status_balance en 'por_definir'
        // hasta que el Aprobador autorice la comprobación y el monto quede firme.
        if ($diff > 0) {
            return [
                'leftover_amount'     => $diff,
                'leftover_method'     => $existing?->leftover_method,
                'leftover_notes'      => $existing?->leftover_notes,
                'leftover_proof_path' => $existing?->leftover_proof_path,
                'excess_amount'       => null,
                'excess_method'       => null,
                'excess_notes'        => null,
                'excess_proof_path'   => null,
                'status_balance'      => 'por_definir',
            ];
        }

        // ── CASO B: EXCEDENTE (Comprobado > Anticipo -> diff < 0) ──
        if ($diff < 0) {
            if ($existing?->leftover_proof_path && Storage::exists($existing->leftover_proof_path)) {
                Storage::delete($existing->leftover_proof_path);
            }

            $request->validate([
                'excess_method' => 'nullable|string|max:100',
                'excess_notes'  => 'nullable|string|max:2000',
            ]);

            return [
                'leftover_amount'     => null,
                'leftover_method'     => null,
                'leftover_notes'      => null,
                'leftover_proof_path' => null,
                'excess_amount'       => abs($diff),
                'excess_method'       => $request->excess_method ?? 'Transferencia',
                'excess_notes'        => $request->excess_notes,
                'excess_proof_path'   => $existing?->excess_proof_path,
                'status_balance'      => 'por_definir',
            ];
        }

        // ── CASO C: CUADRÓ EXACTO (diff == 0) ──
        if ($existing?->leftover_proof_path && Storage::exists($existing->leftover_proof_path)) {
            Storage::delete($existing->leftover_proof_path);
        }

        return [
            'leftover_amount'     => null,
            'leftover_method'     => null,
            'leftover_notes'      => null,
            'leftover_proof_path' => null,
            'excess_amount'       => null,
            'excess_method'       => null,
            'excess_notes'        => null,
            'excess_proof_path'   => null,
            'status_balance'      => 'no_aplica',
        ];
    }

    /* ==========================================================================
       ── STORE ──
       ========================================================================== */

    public function store(Request $request)
    {
        $request->validate([
            'motivo'        => 'required|string|max:255',
            'centro_costo'  => 'required|string',
            'tipo_gasto'    => 'required|string',
            'total_amount'  => 'required|numeric|min:0.01',
            'lineas'        => 'required|json',
            'is_deductible' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            $isDraft       = $request->input('is_draft') == 'true';
            $creatorId     = Auth::id();
            $beneficiaryId = $request->beneficiary_id ?? $creatorId;
            $beneficiary   = User::find($beneficiaryId);

            if ($isDraft) {
                $folioSystem = 'TMP-' . strtoupper(substr(uniqid(), -6));
                $folioUser   = 'TMP-USR';
            } else {
                $lastSys = ExpenseClaim::where('folio_system', 'like', 'VES-%')
                    ->orderByRaw('CAST(SUBSTRING(folio_system, 5) AS UNSIGNED) DESC')->first();
                $sysNum      = $lastSys ? (int) str_replace('VES-', '', $lastSys->folio_system) + 1 : 1;
                $folioSystem = 'VES-' . str_pad($sysNum, 2, '0', STR_PAD_LEFT);

                $words = explode(' ', trim($beneficiary->name));
                $initials = '';
                foreach ($words as $w) { $initials .= strtoupper(substr($w, 0, 1)); }

                $lastUser = ExpenseClaim::where('user_id', $beneficiaryId)
                    ->where('folio_user', 'like', $initials . '-%')
                    ->orderByRaw('CAST(SUBSTRING(folio_user, LENGTH("' . $initials . '") + 2) AS UNSIGNED) DESC')
                    ->first();
                $userNum   = $lastUser ? (int) str_replace($initials . '-', '', $lastUser->folio_user) + 1 : 1;
                $folioUser = $initials . '-' . str_pad($userNum, 2, '0', STR_PAD_LEFT);
            }

            $statusReview   = $isDraft ? 'borrador' : 'pendiente';
            $statusApproval = $isDraft ? 'n_a' : 'en_espera';
            $statusPayment  = $isDraft ? 'no_aplica' : ($request->tipo_solicitud === 'Reembolso' ? 'no_pagado' : 'no_aplica');

            $headerImputation = $this->parseCostCenter($request->centro_costo);

            $expenseAdvanceId = ($request->tipo_solicitud !== 'Reembolso' && $request->advance_id)
                ? $request->advance_id
                : null;

            $balancePayload = $this->buildBalancePayload($request, null);

            $claim = ExpenseClaim::create(array_merge([
                'folio_system'       => $folioSystem,
                'folio_user'         => $folioUser,
                'claim_date'         => Carbon::now()->toDateString(),
                'request_type'       => $request->tipo_solicitud,
                'category'           => $request->tipo_gasto,
                'is_deductible'      => $request->boolean('is_deductible'),
                'expense_advance_id' => $expenseAdvanceId,
                'user_id'            => $beneficiaryId,
                'created_by_id'      => $creatorId,
                'area'               => $request->depto,
                'cost_center_id'     => $headerImputation['cc'],
                'project_id'         => $headerImputation['prj'],
                'emission_place'     => $request->lugar_emision ?? 'VHSA, TAB.',
                'motive'             => $request->motivo,
                'total_subtotal'     => $request->total_subtotal,
                'total_iva'          => $request->total_iva,
                'total_ish'          => $request->total_ish,
                'total_amount'       => $request->total_amount,
                'status_review'      => $statusReview,
                'status_approval'    => $statusApproval,
                'status_payment'     => $statusPayment,
                'evidence_documents' => [],
            ], $balancePayload));

            if (!$isDraft && $expenseAdvanceId) {
                ExpenseAdvance::where('id', $expenseAdvanceId)->update([
                    'settlement_status' => 'comprobando'
                ]);
            }

            $lineas = json_decode($request->input('lineas'), true) ?: [];
            $fechaFacturaIso = $this->getEarliestExpenseDate($lineas);

            $rutasPdf = [];
            if ($request->hasFile('evidencias')) {
                $carpetaInvoices = "private/administration/expense-claims/pdf/{$folioSystem}/invoices";
                $i = 1;
                foreach ($request->file('evidencias') as $pdf) {
                    $filename = $this->buildPdfFilename($folioSystem, 'FAC', $fechaFacturaIso, $i);
                    $rutasPdf[] = $pdf->storeAs($carpetaInvoices, $filename);
                    $i++;
                }
                $claim->update(['evidence_documents' => $rutasPdf]);
            }

            foreach ($lineas as $linea) {
                $lineImputation = $this->parseCostCenter($linea['centro_costo'] ?? null);

                ExpenseClaimLine::create([
                    'expense_claim_id' => $claim->id,
                    'expense_cfdi_id'  => !empty($linea['cfdi_id']) ? $linea['cfdi_id'] : null,
                    'load_method'      => $linea['load_method'] ?? 'manual_entry',
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
                    'cost_center_id'   => $lineImputation['cc'],
                    'project_id'       => $lineImputation['prj'],
                ]);
            }

            $label = $this->getRequestLabel($request->tipo_solicitud);

            if ($isDraft) {
                $actionTitle     = 'Borrador Guardado';
                $friendlyComment = "Guardaste esta solicitud de {$label} como borrador. Aún no se ha enviado a revisión.";
            } else {
                $actionTitle     = 'Solicitud Creada';
                $friendlyComment = "Creaste la solicitud de {$label} y ya está en la fila para ser revisada.";
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => $creatorId,
                'action'           => $actionTitle,
                'new_status'       => $statusReview,
                'comments'         => $friendlyComment,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'El documento fue procesado.',
                'folio'   => $folioSystem,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /* ==========================================================================
       ── UPDATE ──
       ========================================================================== */

    public function update(Request $request, $id)
    {
        $request->validate([
            'motivo'       => 'required|string|max:255',
            'centro_costo' => 'required|string',
            'tipo_gasto'   => 'required|string',
            'total_amount' => 'required|numeric|min:0.01',
            'lineas'       => 'required|json',
        ]);

        try {
            DB::beginTransaction();

            $claim     = ExpenseClaim::findOrFail($id);
            $isDraft   = $request->input('is_draft') == 'true';
            $creatorId = Auth::id();

            $oldStatus = $claim->status_review;

            $newStatus   = $isDraft ? 'borrador' : 'pendiente';
            $newApproval = $isDraft ? 'n_a' : 'en_espera';
            $newPayment  = $isDraft ? 'no_aplica' : ($request->tipo_solicitud === 'Reembolso' ? 'no_pagado' : 'no_aplica');

            if ($oldStatus === 'borrador' && !$isDraft) {
                $beneficiary = User::find($claim->user_id);
                $oldFolio = $claim->folio_system;

                $lastSys = ExpenseClaim::where('folio_system', 'like', 'VES-%')
                    ->orderByRaw('CAST(SUBSTRING(folio_system, 5) AS UNSIGNED) DESC')->first();
                $sysNum = $lastSys ? (int) str_replace('VES-', '', $lastSys->folio_system) + 1 : 1;
                $claim->folio_system = 'VES-' . str_pad($sysNum, 2, '0', STR_PAD_LEFT);

                $words = explode(' ', trim($beneficiary->name));
                $initials = '';
                foreach ($words as $w) { $initials .= strtoupper(substr($w, 0, 1)); }

                $lastUser = ExpenseClaim::where('user_id', $claim->user_id)
                    ->where('folio_user', 'like', $initials . '-%')
                    ->orderByRaw('CAST(SUBSTRING(folio_user, LENGTH("' . $initials . '") + 2) AS UNSIGNED) DESC')
                    ->first();
                $userNum = $lastUser ? (int) str_replace($initials . '-', '', $lastUser->folio_user) + 1 : 1;
                $claim->folio_user = $initials . '-' . str_pad($userNum, 2, '0', STR_PAD_LEFT);

                $this->migrateDraftFilesToFinalFolio($claim, $oldFolio, $claim->folio_system);
            }

            $headerImputation = $this->parseCostCenter($request->centro_costo);

            $expenseAdvanceId = ($request->tipo_solicitud !== 'Reembolso' && $request->advance_id)
                ? $request->advance_id
                : null;

            $lineas = json_decode($request->input('lineas'), true) ?: [];
            $fechaFacturaIso = $this->getEarliestExpenseDate($lineas);

            $balancePayload = $this->buildBalancePayload($request, $claim);

            $claim->update(array_merge([
                'request_type'       => $request->tipo_solicitud,
                'category'           => $request->tipo_gasto,
                'is_deductible'      => $request->boolean('is_deductible'),
                'expense_advance_id' => $expenseAdvanceId,
                'cost_center_id'     => $headerImputation['cc'],
                'project_id'         => $headerImputation['prj'],
                'emission_place'     => $request->lugar_emision ?? 'VHSA, TAB.',
                'motive'             => $request->motivo,
                'total_subtotal'     => $request->total_subtotal,
                'total_iva'          => $request->total_iva,
                'total_ish'          => $request->total_ish,
                'total_amount'       => $request->total_amount,
                'status_review'      => $newStatus,
                'status_approval'    => $newApproval,
                'status_payment'     => $newPayment,
            ], $balancePayload));

            if (!$isDraft && $expenseAdvanceId) {
                ExpenseAdvance::where('id', $expenseAdvanceId)->update([
                    'settlement_status' => 'comprobando'
                ]);
            }

            $existingFromRequest = $request->input('existing_evidences', []);
            if (!is_array($existingFromRequest)) $existingFromRequest = [];
            $rutasPdf = array_values(array_filter($existingFromRequest));

            $nextIndex = $this->getNextFacturaIndex($rutasPdf);

            if ($request->hasFile('evidencias')) {
                $carpetaInvoices = "private/administration/expense-claims/pdf/{$claim->folio_system}/invoices";

                foreach ($request->file('evidencias') as $pdf) {
                    $filename = $this->buildPdfFilename($claim->folio_system, 'FAC', $fechaFacturaIso, $nextIndex);
                    $rutasPdf[] = $pdf->storeAs($carpetaInvoices, $filename);
                    $nextIndex++;
                }
            }

            if ($request->has('existing_evidences') || $request->hasFile('evidencias')) {
                $oldPaths = is_array($claim->evidence_documents) ? $claim->evidence_documents : [];
                $removed  = $this->getRemovedPaths($oldPaths, $rutasPdf);
                foreach ($removed as $oldPath) {
                    if ($oldPath && Storage::exists($oldPath)) {
                        Storage::delete($oldPath);
                    }
                }
                $claim->update(['evidence_documents' => $rutasPdf]);
            }

            ExpenseClaimLine::where('expense_claim_id', $claim->id)->delete();

            foreach ($lineas as $linea) {
                $lineImputation = $this->parseCostCenter($linea['centro_costo'] ?? null);

                ExpenseClaimLine::create([
                    'expense_claim_id' => $claim->id,
                    'expense_cfdi_id'  => !empty($linea['cfdi_id']) ? $linea['cfdi_id'] : null,
                    'load_method'      => $linea['load_method'] ?? 'manual_entry',
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
                    'cost_center_id'   => $lineImputation['cc'],
                    'project_id'       => $lineImputation['prj'],
                ]);
            }

            $label = $this->getRequestLabel($request->tipo_solicitud);

            if ($oldStatus === 'borrador' && !$isDraft) {
                $actionTitle     = 'Solicitud Creada';
                $friendlyComment = "Creaste la solicitud de {$label} y ya está en la fila para ser revisada.";
            } elseif ($isDraft) {
                $actionTitle     = 'Borrador Actualizado';
                $friendlyComment = "Se guardaron los cambios en tu borrador de {$label}.";
            } else {
                $actionTitle     = 'Solicitud Corregida';
                $friendlyComment = "Corregiste los detalles de la solicitud de {$label} y la enviaste a revisión otra vez. ¡Suerte!";
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => $creatorId,
                'action'           => $actionTitle,
                'previous_status'  => $oldStatus,
                'new_status'       => $newStatus,
                'comments'         => $friendlyComment,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'El documento fue actualizado con éxito.',
                'folio'   => $claim->folio_system,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /* ==========================================================================
       ── ETAPA 2: SUBIDA DEL COMPROBANTE DE SOBRANTE (POST-APROBACIÓN) ──
       ========================================================================== */

    public function submitLeftoverProof(Request $request, $id)
    {
        $request->validate([
            'leftover_method' => 'required|string|max:100',
            'leftover_notes'  => 'nullable|string|max:2000',
            'leftover_proof'  => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $claim = ExpenseClaim::findOrFail($id);

            if ($claim->status_approval !== 'aprobado') {
                return response()->json([
                    'success' => false,
                    'message' => 'La comprobación debe estar aprobada antes de adjuntar el comprobante de devolución del sobrante.'
                ], 422);
            }

            if ((float) ($claim->leftover_amount ?? 0) <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este expediente no registra un monto sobrante por devolver.'
                ], 422);
            }

            if ($claim->leftover_proof_path && Storage::exists($claim->leftover_proof_path)) {
                Storage::delete($claim->leftover_proof_path);
            }

            $folder    = "private/administration/expense-claims/pdf/{$claim->folio_system}/leftovers";
            $filename  = $this->buildPdfFilename($claim->folio_system, 'SOB', null, null);
            $proofPath = $request->file('leftover_proof')->storeAs($folder, $filename);

            $oldBalance = $claim->status_balance;

            $claim->update([
                'leftover_method'     => $request->leftover_method,
                'leftover_notes'      => $request->leftover_notes,
                'leftover_proof_path' => $proofPath,
                'status_balance'      => 'por_verificar',
            ]);

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => Auth::id() ?? 1,
                'action'           => 'Devolución de Sobrante Enviada',
                'previous_status'  => $oldBalance,
                'new_status'       => 'por_verificar',
                'comments'         => 'Se adjuntó el comprobante PDF de devolución del sobrante ($' . number_format((float)$claim->leftover_amount, 2) . '). En espera de verificación por Tesorería.',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El comprobante de devolución fue registrado y enviado a Tesorería para su verificación.',
                'folio'   => $claim->folio_system,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el comprobante: ' . $e->getMessage()
            ], 500);
        }
    }
}











