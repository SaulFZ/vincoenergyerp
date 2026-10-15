<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
    private const DECREASE_TYPES = ['Abono_Retencion', 'Liquidacion_Caja'];
    private const LEGACY_TYPES = ['Reembolso_Pagado'];

    public function index()
    {
        $allAdvances = ExpenseAdvance::all();
        $allClaims   = ExpenseClaim::all();
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

            if ($userAdvances->isEmpty() && $userClaims->isEmpty() && !$userBalance) {
                return null;
            }

            $saldoAnticipos  = 0.0;
            $tramitesActivos = 0;
            $pendientes      = 0;
            $conteo          = ['anticipos' => 0, 'comprobaciones' => 0];
            $movements       = collect();

            $reemb = [
                'n' => 0, 'total' => 0.0,
                'por_pagar' => 0.0, 'por_pagar_n' => 0,
                'pagado' => 0.0, 'pagado_n' => 0,
                'revision_n' => 0,
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
                    'settlement_status' => $adv->settlement_status ?? 'no_comprobado',
                    'origen'            => 'anticipo',
                    'subtipo'           => null,
                    'metodo'            => null,
                    'vinculo'           => '—',
                    'advance_id'        => $adv->id,
                    'signo'             => 'cargo',
                ]);
            }

            // ── B) COMPROBACIONES Y REEMBOLSOS ──
            foreach ($userClaims as $claim) {
                $st    = $this->norm($claim->status_review);
                $stApp = $this->norm($claim->status_approval);

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

                    $movements->push([
                        'id'              => $claim->id,
                        'folio'           => $claim->folio_system,
                        'fecha_raw'       => Carbon::parse($claim->claim_date)->timestamp,
                        'fecha'           => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                        'tipo'            => $claim->request_type,
                        'descripcion'     => $claim->motive,
                        'monto'           => $amount,
                        'saldo'           => null,
                        'status'          => $st,
                        'status_approval' => $stApp,
                        'status_payment'  => $state,
                        'puede_pagar'     => $this->canBePaid($claim),
                        'origen'          => 'reembolso',
                        'subtipo'         => null,
                        'metodo'          => null,
                        'vinculo'         => $claim->related_advance_id ? ($advanceFolios->get($claim->related_advance_id) ?? 'Vinculado') : 'Sin anticipo',
                        'advance_id'      => $claim->related_advance_id,
                        'signo'           => 'abono',
                    ]);
                    continue;
                }

                $isElectronic = $this->isElectronic($claim);
                $conteo['comprobaciones']++;

                if ($st === 'Pendiente') $pendientes++;
                if (!in_array($st, ['Aprobado', 'Rechazado', 'Borrador'])) $tramitesActivos++;

                $movements->push([
                    'id'              => $claim->id,
                    'folio'           => $claim->folio_system,
                    'fecha_raw'       => Carbon::parse($claim->claim_date)->timestamp,
                    'fecha'           => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                    'tipo'            => $claim->request_type,
                    'descripcion'     => $claim->motive,
                    'monto'           => (float) $claim->total_amount,
                    'saldo'           => null,
                    'status'          => $st,
                    'status_approval' => $stApp,
                    'origen'          => 'comprobacion',
                    'subtipo'         => $isElectronic ? 'electronica' : 'directa',
                    'metodo'          => $isElectronic ? 'Tarjeta Toka' : 'Transferencia',
                    'vinculo'         => $claim->expense_advance_id ? ($advanceFolios->get($claim->expense_advance_id) ?? 'Vinculado') : 'Sin anticipo',
                    'advance_id'      => $claim->expense_advance_id,
                    'signo'           => 'abono',
                    // ── SOBRANTE DEL ANTICIPO ──
                    'leftover'        => ((float) $claim->leftover_amount > 0) ? [
                        'amount'    => (float) $claim->leftover_amount,
                        'method'    => $claim->leftover_method,
                        'notes'     => $claim->leftover_notes,
                        'has_proof' => !empty($claim->leftover_proof_path),
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
                    'id'          => $ledger->id,
                    'folio'       => $ledger->folio_system,
                    'fecha_raw'   => Carbon::parse($ledger->created_at)->timestamp,
                    'fecha'       => Carbon::parse($ledger->created_at)->format('d/m/Y'),
                    'tipo'        => $ledger->movement_type,
                    'descripcion' => $ledger->description,
                    'monto'       => (float) $ledger->amount,
                    'saldo'       => (float) $ledger->new_balance,
                    'status'      => 'Consolidado',
                    'origen'      => 'ajuste_balance',
                    'subtipo'     => null,
                    'metodo'      => null,
                    'vinculo'     => $vinculo,
                    'advance_id'  => $ledger->expense_advance_id,
                    'signo'       => in_array($ledger->movement_type, self::DECREASE_TYPES, true) ? 'abono' : 'cargo',
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

        return view('modules.administration.expense_claims.accountspayable', ['advancesData' => $data]);
    }

    public function showReembolsos($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $claims = ExpenseClaim::where('user_id', $userId)
                ->with(['logs' => function ($q) {
                    $q->where('action', 'pago_reembolso')->latest('id')->with('user:id,name');
                }])
                ->orderBy('claim_date', 'desc')
                ->get()
                ->filter(fn ($c) => $this->isReimbursement($c))
                ->values();

            $data = $claims->map(function ($claim) {
                $state = $this->paymentState($claim);
                $log   = $claim->logs->first();

                return [
                    'id'             => $claim->id,
                    'folio'          => $claim->folio_system,
                    'fecha'          => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                    'tipo'           => $claim->request_type,
                    'motivo'         => $claim->motive,
                    'monto'          => (float) $claim->total_amount,
                    'status'         => $this->norm($claim->status_review),
                    'status_payment' => $state,
                    'puede_pagar'    => $this->canBePaid($claim),
                    'pagado_el'      => ($state === 'pagado' && $log) ? Carbon::parse($log->created_at)->format('d/m/Y') : null,
                    'pagado_por'     => ($state === 'pagado' && $log && $log->user) ? $log->user->name : null,
                    'pago_detalle'   => ($state === 'pagado' && $log) ? $log->comments : null,
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
            'payment_method' => 'required|in:Transferencia,Efectivo,Tarjeta Toka',
            'reference'      => 'nullable|string|max:120',
        ]);

        try {
            DB::beginTransaction();
            $claim = ExpenseClaim::lockForUpdate()->findOrFail($id);

            if (!$this->canBePaid($claim)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Solo se pueden pagar reembolsos validados, aprobados y pendientes de pago.'], 422);
            }

            $previous = $claim->status_payment ?: 'no_pagado';
            $claim->status_payment = 'pagado';
            $claim->save();

            $comment = "Pago de reembolso vía {$request->payment_method}";
            if ($request->filled('reference')) $comment .= ' · Ref: ' . trim($request->reference);

            ExpenseClaimLog::create([
                'expense_claim_id' => $claim->id,
                'user_id'          => Auth::id(),
                'action'           => 'pago_reembolso',
                'previous_status'  => $previous,
                'new_status'       => 'pagado',
                'comments'         => Str::limit($comment, 255, ''),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => "Reembolso marcado como pagado."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markAdvanceAsSettled($id)
    {
        try {
            $adv = ExpenseAdvance::findOrFail($id);
            $adv->settlement_status = 'comprobado';
            $adv->save();
            return response()->json(['success' => true, 'message' => 'El anticipo ha quedado registrado y cerrado en tesorería.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function registerBalanceAdjustment(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'movement_type'      => 'required|in:Abono_Retencion',
            'amount'             => 'required|numeric|min:0.01',
            'description'        => 'required|string|max:255',
            'expense_advance_id' => 'nullable|exists:expense_advances,id',
        ]);

        try {
            DB::beginTransaction();

            $balance = ExpenseAccountBalance::lockForUpdate()->firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance_amount' => 0.00]
            );

            $previousBalance = (float) $balance->balance_amount;
            $amount          = (float) $request->amount;
            $newBalance      = $previousBalance + $amount; // La retención de sobrante SUMA al saldo

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
        if ($this->norm($claim->status_review) === 'Rechazado') return 'no_aplica';
        return 'no_pagado';
    }

    private function canBePaid(ExpenseClaim $claim): bool
    {
        // VALIDACIÓN DOBLE EXIGIDA: Debe estar validado, aprobado y no pagado
        return $this->norm($claim->status_review) === 'Validado'
            && $this->norm($claim->status_approval) === 'Aprobado'
            && $this->paymentState($claim) === 'no_pagado';
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
