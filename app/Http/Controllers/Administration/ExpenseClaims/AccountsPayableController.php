<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseClaimLog;
use App\Models\Administration\ExpenseClaims\ExpenseAccountBalance;
use App\Models\Administration\ExpenseClaims\ExpenseAccountTransaction;
use App\Models\Auth\User;
use Carbon\Carbon;

class AccountsPayableController extends Controller
{
    private const DECREASE_TYPES = ['Liquidacion_Caja'];
    private const LEGACY_TYPES   = ['Reembolso_Pagado'];

    public function index(Request $request)
    {
        $allAdvances = ExpenseAdvance::all();
        $allClaims   = ExpenseClaim::with(['logs' => function ($q) {
            $q->whereIn('action', ['pago_reembolso', 'pago_excedente'])->latest('id');
        }])->get();
        $balances    = ExpenseAccountBalance::all()->keyBy('user_id');
        $ledgers     = ExpenseAccountTransaction::with(['advance:id,folio_system', 'claim:id,folio_system'])
            ->whereNotIn('movement_type', self::LEGACY_TYPES)
            ->orderBy('created_at', 'desc')
            ->get();

        $advancesByUser = $allAdvances->groupBy('user_id');
        $claimsByUser   = $allClaims->groupBy('user_id');
        $ledgersByUser  = $ledgers->groupBy('user_id');
        $advanceFolios  = $allAdvances->pluck('folio_system', 'id');

        $employeeIds = $advancesByUser->keys()
            ->merge($claimsByUser->keys())
            ->merge($balances->keys())
            ->merge($ledgersByUser->keys())
            ->unique()
            ->values();

        $employees = User::whereIn('id', $employeeIds)
            ->with(['employee.area'])
            ->orderBy('name', 'asc')
            ->get();

        $data = $employees->map(function ($employee) use (
            $advancesByUser, $claimsByUser, $ledgersByUser, $balances, $advanceFolios
        ) {
            $userAdvances = $advancesByUser->get($employee->id, collect());
            $userClaims   = $claimsByUser->get($employee->id, collect());
            $userLedgers  = $ledgersByUser->get($employee->id, collect());
            $userBalance  = $balances->get($employee->id);

            if ($userAdvances->isEmpty() && $userClaims->isEmpty() && $userLedgers->isEmpty() && !$userBalance) {
                return null;
            }

            $saldoAnticipos  = 0.0;
            $tramitesActivos = 0;
            $pendientes      = 0;
            $conteo          = ['anticipos' => 0, 'comprobaciones' => 0];
            $movements       = collect();

            $reemb = [
                'n'           => 0,
                'total'       => 0.0,
                'por_pagar'   => 0.0,
                'por_pagar_n' => 0,
                'pagado'      => 0.0,
                'pagado_n'    => 0,
                'revision_n'  => 0,
            ];

            // ── A) ANTICIPOS (cargo) ──
            foreach ($userAdvances as $adv) {
                $st      = $this->norm($adv->status);
                $counted = !in_array($st, ['Pendiente', 'Rechazado', 'Borrador']);

                if ($counted) {
                    $saldoAnticipos += (float) $adv->balance;
                }
                if ($st === 'Pendiente') {
                    $pendientes++;
                }
                if ($st === 'Pendiente' || ($counted && (float) $adv->balance > 0)) {
                    $tramitesActivos++;
                }
                $conteo['anticipos']++;

                $movements->push([
                    'id'                => $adv->id,
                    'folio'             => $adv->folio_system,
                    'fecha_raw'         => Carbon::parse($adv->advance_date)->timestamp,
                    'fecha'             => Carbon::parse($adv->advance_date)->format('d/m/Y'),
                    'tipo'              => $adv->advance_type,
                    'descripcion'       => $adv->description,
                    'monto'             => (float) $adv->amount,
                    'saldo'             => (float) $adv->balance,
                    'status'            => $st,
                    'status_approval'   => $st,
                    'settlement_status' => $adv->settlement_status ?? 'no_comprobado',
                    'origen'            => 'anticipo',
                    'subtipo'           => null,
                    'metodo'            => $adv->advance_type,
                    'vinculo'           => '—',
                    'advance_id'        => $adv->id,
                    'signo'             => 'cargo',
                ]);
            }

            // ── B) COMPROBACIONES Y REEMBOLSOS ──
            foreach ($userClaims as $claim) {
                $st    = $this->norm($claim->status_review);
                $stApp = $this->norm($claim->status_approval);

                // Reembolso Puro (sin anticipo)
                if ($this->isReimbursement($claim)) {
                    $reemb['n']++;
                    $amount = (float) $claim->total_amount;
                    $state  = $this->paymentState($claim);

                    if ($st === 'Pendiente') $pendientes++;

                    if ($state === 'pagado') {
                        $reemb['pagado'] += $amount;
                        $reemb['pagado_n']++;
                        $reemb['total']  += $amount;
                    } elseif ($state !== 'no_aplica') {
                        $reemb['total'] += $amount;
                        if ($this->canBePaid($claim)) {
                            $reemb['por_pagar'] += $amount;
                            $reemb['por_pagar_n']++;
                        } else {
                            $reemb['revision_n']++;
                        }
                    }

                    $payMeta = $this->extractReimbursementPaymentMeta($claim);

                    $movements->push([
                        'id'                       => $claim->id,
                        'folio'                    => $claim->folio_system,
                        'fecha_raw'                => Carbon::parse($claim->claim_date)->timestamp,
                        'fecha'                    => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                        'tipo'                     => $claim->request_type,
                        'descripcion'              => $claim->motive,
                        'monto'                    => $amount,
                        'saldo'                    => null,
                        'status'                   => $st,
                        'status_approval'          => $stApp,
                        'status_payment'           => $state,
                        'status_balance'           => 'no_aplica',
                        'puede_pagar'              => $this->canBePaid($claim),
                        'origen'                   => 'reembolso',
                        'subtipo'                  => null,
                        'metodo'                   => $payMeta['method'],
                        'payment_method'           => $payMeta['method'],
                        'payment_notes'            => $payMeta['notes'],
                        'reimbursement_proof_path' => $payMeta['proof_path'],
                        'vinculo'                  => 'Sin anticipo',
                        'advance_id'               => null,
                        'signo'                    => 'abono',
                    ]);
                    continue;
                }

                // Comprobación (Directa o Electrónica) vinculada a un anticipo
                $isElectronic = $this->isElectronic($claim);
                $conteo['comprobaciones']++;

                if ($st === 'Pendiente') $pendientes++;
                if (!in_array($st, ['Aprobado', 'Rechazado', 'Borrador']) && !in_array($stApp, ['Aprobado', 'Rechazado'])) {
                    $tramitesActivos++;
                }

                $leftoverAmount = (float) ($claim->leftover_amount ?? 0);
                $excessAmount   = (float) ($claim->excess_amount ?? 0);
                $statusBalance  = $claim->status_balance ?: 'no_aplica';

                if ($excessAmount > 0 && $st === 'Validado' && $stApp === 'Aprobado') {
                    $reemb['n']++;
                    $reemb['total'] += $excessAmount;
                    if ($statusBalance === 'pagado') {
                        $reemb['pagado'] += $excessAmount;
                        $reemb['pagado_n']++;
                    } elseif ($statusBalance === 'pago_pendiente') {
                        $reemb['por_pagar'] += $excessAmount;
                        $reemb['por_pagar_n']++;
                    }
                }

                $movements->push([
                    'id'                  => $claim->id,
                    'folio'               => $claim->folio_system,
                    'fecha_raw'           => Carbon::parse($claim->claim_date)->timestamp,
                    'fecha'               => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                    'tipo'                => $claim->request_type,
                    'descripcion'         => $claim->motive,
                    'monto'               => (float) $claim->total_amount,
                    'saldo'               => null,
                    'status'              => $st,
                    'status_approval'     => $stApp,
                    'status_balance'      => $statusBalance,
                    'origen'              => 'comprobacion',
                    'subtipo'             => $isElectronic ? 'electronica' : 'directa',
                    'metodo'              => $isElectronic ? 'Tarjeta Toka' : 'Transferencia',
                    'vinculo'             => $claim->expense_advance_id ? ($advanceFolios->get($claim->expense_advance_id) ?? 'Vinculado') : 'Sin anticipo',
                    'advance_id'          => $claim->expense_advance_id,
                    'signo'               => 'abono',
                    'leftover_amount'     => $leftoverAmount,
                    'leftover_method'     => $claim->leftover_method,
                    'leftover_proof_path' => $claim->leftover_proof_path,
                    'leftover_notes'      => $claim->leftover_notes,
                    'leftover'            => ($leftoverAmount > 0) ? [
                        'amount'         => $leftoverAmount,
                        'method'         => $claim->leftover_method,
                        'notes'          => $claim->leftover_notes,
                        'proof_path'     => $claim->leftover_proof_path,
                        'has_proof'      => !empty($claim->leftover_proof_path),
                        'status_balance' => $statusBalance,
                    ] : null,
                    'excess_amount'       => $excessAmount,
                    'excess_method'       => $claim->excess_method,
                    'excess_proof_path'   => $claim->excess_proof_path,
                    'excess_notes'        => $claim->excess_notes,
                    'excess'              => ($excessAmount > 0) ? [
                        'amount'         => $excessAmount,
                        'method'         => $claim->excess_method,
                        'notes'          => $claim->excess_notes,
                        'proof_path'     => $claim->excess_proof_path,
                        'has_proof'      => !empty($claim->excess_proof_path),
                        'status_balance' => $statusBalance,
                    ] : null,
                ]);
            }

            // ── C) AJUSTES EN LIBRO MAYOR ──
            foreach ($userLedgers as $ledger) {
                $vinculo = 'General';
                if ($ledger->expense_advance_id && $ledger->advance) {
                    $vinculo = $ledger->advance->folio_system;
                } elseif ($ledger->expense_claim_id && $ledger->claim) {
                    $vinculo = $ledger->claim->folio_system;
                }

                $movements->push([
                    'id'            => $ledger->id,
                    'folio'         => $ledger->folio_system,
                    'fecha_raw'     => Carbon::parse($ledger->created_at)->timestamp,
                    'fecha'         => Carbon::parse($ledger->created_at)->format('d/m/Y'),
                    'tipo'          => $ledger->movement_type,
                    'descripcion'   => $ledger->description,
                    'monto'         => (float) $ledger->amount,
                    'saldo'         => (float) $ledger->new_balance,
                    'status'        => 'Consolidado',
                    'origen'        => 'ajuste_balance',
                    'subtipo'       => null,
                    'metodo'        => null,
                    'vinculo'       => $vinculo,
                    'advance_id'    => $ledger->expense_advance_id,
                    'balance_delta' => round((float) $ledger->new_balance - (float) $ledger->previous_balance, 2),
                    'signo'         => in_array($ledger->movement_type, self::DECREASE_TYPES, true) ? 'abono' : 'cargo',
                ]);
            }

            return [
                'user_id'           => $employee->id,
                'nombre'            => $employee->name,
                'depto'             => ($employee->employee && $employee->employee->area) ? $employee->employee->area->name : 'Sin Asignar',
                'total_saldo'       => $saldoAnticipos,
                'saldo_corriente'   => $userBalance ? (float) $userBalance->balance_amount : 0.0,
                'tramites_activos'  => $tramitesActivos,
                'pendientes'        => $pendientes,
                'conteo'            => $conteo,
                'reembolsos'        => $reemb,
                'ultimo_movimiento' => $movements->isNotEmpty() ? $movements->sortByDesc('fecha_raw')->first()['fecha'] : null,
                'movimientos'       => $movements->sortByDesc('fecha_raw')->values()->toArray(),
            ];
        })->filter()->values()->toArray();

        usort($data, function ($a, $b) {
            $sa = $a['total_saldo'] + $a['reembolsos']['por_pagar'] + abs($a['saldo_corriente']);
            $sb = $b['total_saldo'] + $b['reembolsos']['por_pagar'] + abs($b['saldo_corriente']);
            return $sb <=> $sa;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'employees' => $data,
            ]);
        }

        return view('modules.administration.expense_claims.accountspayable', ['advancesData' => $data]);
    }

    public function showReembolsos($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $claims = ExpenseClaim::where('user_id', $userId)
                ->with(['expenseAdvance:id,folio_system', 'logs' => function ($q) {
                    $q->whereIn('action', ['pago_reembolso', 'pago_excedente'])->latest('id')->with('user:id,name');
                }])
                ->orderBy('claim_date', 'desc')
                ->get()
                ->filter(fn ($c) => $this->isReimbursement($c) || ((float) $c->excess_amount > 0 && $this->norm($c->status_approval) === 'Aprobado'))
                ->values();

            $data = $claims->map(function ($claim) {
                $isExcess = !$this->isReimbursement($claim) && (float) $claim->excess_amount > 0;
                $state    = $isExcess
                    ? ($claim->status_balance === 'pagado' ? 'pagado' : 'no_pagado')
                    : $this->paymentState($claim);
                $log      = $claim->logs->first();
                $monto    = $isExcess ? (float) $claim->excess_amount : (float) $claim->total_amount;
                $payMeta  = $isExcess ? null : $this->extractReimbursementPaymentMeta($claim);

                return [
                    'id'                       => $claim->id,
                    'folio'                    => $claim->folio_system,
                    'fecha'                    => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                    'tipo'                     => $isExcess ? 'Excedente de anticipo' : $claim->request_type,
                    'motivo'                   => $claim->motive,
                    'monto'                    => $monto,
                    'status'                   => $this->norm($claim->status_review),
                    'status_approval'          => $this->norm($claim->status_approval),
                    'origen'                   => $isExcess ? 'excedente' : 'reembolso',
                    'advance_id'               => $claim->expense_advance_id,
                    'advance_folio'            => $claim->expenseAdvance?->folio_system,
                    'status_payment'           => $state,
                    'status_balance'           => $claim->status_balance ?? 'no_aplica',
                    'puede_pagar'              => $isExcess ? $this->canPayExcess($claim) : $this->canBePaid($claim),
                    'pagado_el'                => ($state === 'pagado' && $log) ? Carbon::parse($log->created_at)->format('d/m/Y') : null,
                    'pagado_por'               => ($state === 'pagado' && $log && $log->user) ? $log->user->name : null,
                    'pago_detalle'             => ($state === 'pagado' && $log) ? $log->comments : null,
                    'reimbursement_proof_path' => $payMeta['proof_path'] ?? null,
                ];
            });

            return response()->json([
                'success'   => true,
                'user_id'   => $user->id,
                'user'      => $user->name,
                'total'     => (float) $data->where('status_payment', '!=', 'no_aplica')->sum('monto'),
                'por_pagar' => (float) $data->where('puede_pagar', true)->sum('monto'),
                'pagado'    => (float) $data->where('status_payment', 'pagado')->sum('monto'),
                'data'      => $data->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markReembolsoAsPaid(Request $request, $id)
    {
        $request->validate([
            'payment_method'      => 'required|in:Transferencia,Efectivo,Tarjeta Toka',
            'reference'           => 'nullable|string|max:120',
            'reimbursement_proof' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();
            $claim = ExpenseClaim::lockForUpdate()->findOrFail($id);

            if (!$this->canBePaid($claim)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Solo se pueden pagar reembolsos validados, aprobados y pendientes de pago.'], 422);
            }

            $proofPath = $claim->reimbursement_proof_path ?? $claim->payment_proof_path ?? null;
            if ($request->hasFile('reimbursement_proof')) {
                if ($proofPath && Storage::exists($proofPath)) {
                    Storage::delete($proofPath);
                }
                $folioClean = strtoupper(trim(preg_replace('/[^A-Za-z0-9\-_]+/', '_', $claim->folio_system), '_'));
                $filename   = "{$folioClean}_REEMB_" . Carbon::now()->format('Ymd') . '_' . Str::random(6) . '.pdf';
                $folder     = "private/administration/expense-claims/pdf/{$claim->folio_system}/reimbursements";
                $proofPath  = $request->file('reimbursement_proof')->storeAs($folder, $filename);
            }

            $previous = $claim->status_payment ?: 'no_pagado';
            $claim->status_payment = 'pagado';

            $table = $claim->getTable();
            if (Schema::hasColumn($table, 'reimbursement_proof_path')) {
                $claim->reimbursement_proof_path = $proofPath;
            } elseif (Schema::hasColumn($table, 'payment_proof_path')) {
                $claim->payment_proof_path = $proofPath;
            }
            if (Schema::hasColumn($table, 'reimbursement_method')) {
                $claim->reimbursement_method = $request->payment_method;
            }
            if (Schema::hasColumn($table, 'reimbursement_notes') && $request->filled('reference')) {
                $claim->reimbursement_notes = trim($request->reference);
            }

            $claim->save();

            $comment = "Pago de reembolso vía {$request->payment_method}";
            if ($request->filled('reference')) {
                $comment .= ' · Ref: ' . trim($request->reference);
            }
            if ($proofPath) {
                $comment .= " [PROOF:{$proofPath}]";
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => Auth::id() ?? 1,
                'action'           => 'pago_reembolso',
                'previous_status'  => $previous,
                'new_status'       => 'pagado',
                'comments'         => Str::limit($comment, 255, ''),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Reembolso marcado como pagado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyLeftover(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $claim = ExpenseClaim::lockForUpdate()->findOrFail($id);

            if ((float) $claim->leftover_amount <= 0 || $this->norm($claim->status_approval) !== 'Aprobado') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Esta comprobación no tiene un sobrante aprobado por verificar.'], 422);
            }

            $previous = $claim->status_balance ?: 'por_verificar';
            $claim->status_balance = 'devuelto';
            $claim->save();

            if ($claim->expense_advance_id) {
                $adv = ExpenseAdvance::lockForUpdate()->find($claim->expense_advance_id);
                if ($adv && $this->remainingCents($adv) <= 0) {
                    $adv->update(['settlement_status' => 'comprobado', 'balance' => 0]);
                }
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => Auth::id() ?? 1,
                'action'           => 'Sobrante Verificado en Tesorería',
                'previous_status'  => $previous,
                'new_status'       => 'devuelto',
                'comments'         => 'Tesorería verificó y confirmó el ingreso de la devolución del sobrante ($' . number_format((float) $claim->leftover_amount, 2) . ').',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Devolución de sobrante verificada y conciliada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payExcess(Request $request, $id)
    {
        $request->validate([
            'excess_method' => 'required|in:Transferencia,Efectivo,Tarjeta Toka',
            'excess_notes'  => 'nullable|string|max:500',
            'excess_proof'  => 'nullable|file|mimes:pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();
            $claim = ExpenseClaim::lockForUpdate()->findOrFail($id);

            if (!$this->canPayExcess($claim)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Solo se pueden liquidar excedentes de comprobaciones aprobadas con pago pendiente.'], 422);
            }

            $proofPath = $claim->excess_proof_path;
            if ($request->hasFile('excess_proof')) {
                if ($proofPath && Storage::exists($proofPath)) {
                    Storage::delete($proofPath);
                }
                $folioClean = strtoupper(trim(preg_replace('/[^A-Za-z0-9\-_]+/', '_', $claim->folio_system), '_'));
                $filename   = "{$folioClean}_EXC_" . Carbon::now()->format('Ymd') . '_' . Str::random(6) . '.pdf';
                $folder     = "private/administration/expense-claims/pdf/{$claim->folio_system}/excesses";
                $proofPath  = $request->file('excess_proof')->storeAs($folder, $filename);
            }

            $previous = $claim->status_balance ?: 'pago_pendiente';
            $claim->update([
                'excess_method'     => $request->excess_method,
                'excess_notes'      => $request->excess_notes ?: $claim->excess_notes,
                'excess_proof_path' => $proofPath,
                'status_balance'    => 'pagado',
            ]);

            if ($claim->expense_advance_id) {
                $adv = ExpenseAdvance::lockForUpdate()->find($claim->expense_advance_id);
                if ($adv && $this->remainingCents($adv) <= 0) {
                    $adv->update(['settlement_status' => 'comprobado', 'balance' => 0]);
                }
            }

            $comment = "Pago de excedente ($" . number_format((float) $claim->excess_amount, 2) . ") vía {$request->excess_method}";
            if ($request->filled('excess_notes')) {
                $comment .= ' · ' . trim($request->excess_notes);
            }

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => Auth::id() ?? 1,
                'action'           => 'pago_excedente',
                'previous_status'  => $previous,
                'new_status'       => 'pagado',
                'comments'         => Str::limit($comment, 255, ''),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Excedente pagado y conciliado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markAdvanceAsSettled($id)
    {
        return DB::transaction(function () use ($id) {
            $adv = ExpenseAdvance::lockForUpdate()->findOrFail($id);
            if (in_array($this->norm($adv->status), ['Pendiente', 'Borrador', 'Rechazado'], true)) {
                return response()->json(['success' => false, 'message' => 'El anticipo aún no puede cerrarse.'], 422);
            }
            if ($this->remainingCents($adv) > 0) {
                return response()->json(['success' => false, 'message' => 'Existe saldo por comprobar, devolver o retener.'], 422);
            }
            $adv->update(['settlement_status' => 'comprobado', 'balance' => 0]);
            return response()->json(['success' => true, 'message' => 'El anticipo ha quedado registrado y cerrado en tesorería.']);
        });
    }

    private function remainingCents(ExpenseAdvance $advance): int
    {
        $claims = ExpenseClaim::where('expense_advance_id', $advance->id)->get();
        $used = 0;
        foreach ($claims as $claim) {
            if ($this->norm($claim->status_review) !== 'Validado'
                || $this->norm($claim->status_approval) !== 'Aprobado') continue;
            $used += (int) round((float) $claim->total_amount * 100);
            $used += (int) round((float) $claim->leftover_amount * 100);
        }
        $retained = ExpenseAccountTransaction::where('expense_advance_id', $advance->id)
            ->whereIn('movement_type', ['Abono_Retencion', 'Liquidacion_Caja'])->get()
            ->sum(fn ($row) => (int) round((float) $row->amount * 100));
        return (int) round((float) $advance->amount * 100) - $used - $retained;
    }

    public function registerBalanceAdjustment(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'movement_type'      => 'required|in:Abono_Retencion',
            'amount'             => 'required|numeric|min:0.01',
            'description'        => 'required|string|max:255',
            'expense_advance_id' => 'required|exists:expense_advances,id',
        ]);

        try {
            DB::beginTransaction();

            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            $advance = ExpenseAdvance::where('user_id', $request->user_id)
                ->lockForUpdate()->findOrFail($request->expense_advance_id);

            if (in_array($this->norm($advance->status), ['Pendiente', 'Borrador', 'Rechazado'], true)
                || $advance->settlement_status === 'comprobado') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'El anticipo no admite una retención.'], 422);
            }

            $available = $this->remainingCents($advance);
            $requested = (int) round((float) $request->amount * 100);
            if ($requested > $available) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'El monto supera el sobrante disponible. Actualiza la página.'], 422);
            }

            $balance = ExpenseAccountBalance::lockForUpdate()->firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance_amount' => 0.00]
            );

            $previousBalance = (float) $balance->balance_amount;
            $amount          = (float) $request->amount;
            $newBalance      = round($previousBalance + $amount, 2);

            $folioSystem = $this->buildFolio('BAL', (int) $request->user_id);

            ExpenseAccountTransaction::create([
                'user_id'            => $request->user_id,
                'created_by_id'      => Auth::id() ?? 1,
                'expense_advance_id' => $request->expense_advance_id ?: null,
                'folio_system'       => $folioSystem,
                'movement_type'      => $request->movement_type,
                'amount'             => $amount,
                'previous_balance'   => $previousBalance,
                'new_balance'        => $newBalance,
                'description'        => $request->description,
            ]);

            $balance->update([
                'balance_amount'   => $newBalance,
                'last_movement_at' => Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'La retención fue registrada exitosamente.',
                'folio'   => $folioSystem,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── HELPERS ──
    private function extractReimbursementPaymentMeta(ExpenseClaim $claim): array
    {
        $proofPath = $claim->reimbursement_proof_path ?? $claim->payment_proof_path ?? null;
        $method    = $claim->reimbursement_method ?? 'Transferencia';
        $notes     = $claim->reimbursement_notes ?? '';

        $log = $claim->relationLoaded('logs')
            ? $claim->logs->firstWhere('action', 'pago_reembolso')
            : null;

        if ($log && $log->comments) {
            $raw = $log->comments;
            if (!$proofPath && preg_match('/\[PROOF:([^\]]+)\]/', $raw, $m)) {
                $proofPath = trim($m[1]);
            }
            $clean = trim(preg_replace('/\s*\[PROOF:[^\]]+\]/', '', $raw));
            if (preg_match('/vía\s+([^·]+?)(?:\s*·\s*Ref:\s*(.*))?$/u', $clean, $m)) {
                $method = trim($m[1]);
                if (!empty($m[2])) {
                    $notes = trim($m[2]);
                }
            }
        }

        return [
            'method'     => $method ?: 'Transferencia',
            'notes'      => $notes,
            'proof_path' => $proofPath,
        ];
    }

    private function norm($value): string
    {
        return ucfirst(Str::lower(trim((string) $value)));
    }

    private function typeKey(ExpenseClaim $claim): string
    {
        return Str::lower(Str::ascii((string) $claim->request_type));
    }

    private function isReimbursement(ExpenseClaim $claim): bool
    {
        return !$claim->expense_advance_id && !str_contains($this->typeKey($claim), 'comprobacion');
    }

    private function isElectronic(ExpenseClaim $claim): bool
    {
        return str_contains($this->typeKey($claim), 'electron');
    }

    private function paymentState(ExpenseClaim $claim): string
    {
        if (Str::lower(trim((string) $claim->status_payment)) === 'pagado') return 'pagado';
        if ($this->norm($claim->status_review) === 'Rechazado' || $this->norm($claim->status_approval) === 'Rechazado') return 'no_aplica';
        return 'no_pagado';
    }

    private function canBePaid(ExpenseClaim $claim): bool
    {
        return $this->isReimbursement($claim)
            && $this->norm($claim->status_review) === 'Validado'
            && $this->norm($claim->status_approval) === 'Aprobado'
            && $this->paymentState($claim) === 'no_pagado';
    }

    private function canPayExcess(ExpenseClaim $claim): bool
    {
        return !$this->isReimbursement($claim)
            && (float) ($claim->excess_amount ?? 0) > 0
            && $this->norm($claim->status_review) === 'Validado'
            && $this->norm($claim->status_approval) === 'Aprobado'
            && ($claim->status_balance ?? 'pago_pendiente') !== 'pagado';
    }

    private function buildFolio(string $prefix, int $userId): string
    {
        $user  = User::find($userId);
        $words = preg_split('/\s+/', trim(Str::ascii((string) ($user->name ?? ''))));

        $initials = '';
        foreach ($words as $w) {
            if ($w !== '') $initials .= strtoupper(substr($w, 0, 1));
        }
        $initials = substr($initials, 0, 3) ?: 'USR';

        $base = "{$prefix}-" . Carbon::now()->format('ym') . "-{$initials}-";

        $last = ExpenseAccountTransaction::where('user_id', $userId)
            ->where('folio_system', 'like', $base . '%')
            ->orderBy('id', 'desc')
            ->first();

        $next = $last ? ((int) Str::afterLast($last->folio_system, '-')) + 1 : 1;
        return $base . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}