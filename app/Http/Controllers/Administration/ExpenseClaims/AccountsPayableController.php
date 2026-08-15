<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\ExpenseAccountBalance;
use App\Models\Administration\ExpenseClaims\ExpenseAccountTransaction; // 👈 CORREGIDO MAYÚSCULA
use App\Models\Auth\User;
use Carbon\Carbon;

class AccountsPayableController extends Controller
{
    public function index()
    {
        // 1. Obtener empleados que tengan registros en cualquiera de las 3 tablas
        $employeeIds = ExpenseAdvance::pluck('user_id')
            ->merge(ExpenseClaim::pluck('user_id'))
            ->merge(ExpenseAccountBalance::pluck('user_id'))
            ->unique()
            ->values()
            ->toArray();

        $employees = User::whereIn('id', $employeeIds)
            ->with(['employee.area'])
            ->orderBy('name', 'asc')
            ->get();

        $advances = ExpenseAdvance::with(['expenseClaims'])->get();
        $claims = ExpenseClaim::all();
        $balances = ExpenseAccountBalance::all();
        $ledgers = ExpenseAccountTransaction::with(['advance', 'claim'])->orderBy('created_at', 'desc')->get();

        // 2. Mapeo estructurado para la vista de Cuentas por Pagar
        $advancesData = $employees->map(function ($employee) use ($advances, $claims, $balances, $ledgers) {
            $userAdvances = $advances->where('user_id', $employee->id);
            $userClaims = $claims->where('user_id', $employee->id);
            $userBalance = $balances->where('user_id', $employee->id)->first();
            $userLedgers = $ledgers->where('user_id', $employee->id);

            if ($userAdvances->isEmpty() && $userClaims->isEmpty() && !$userBalance) {
                return null;
            }

            $totalMonto = $userAdvances->sum('amount');
            $totalSaldoAnticipos = $userAdvances->sum('balance');
            $saldoCorriente = $userBalance ? (float) $userBalance->balance_amount : 0.00;
            $tramitesActivos = 0;

            $allMovements = collect([]);

            // A) Anticipos
            $userAdvances->each(function ($adv) use ($allMovements, &$tramitesActivos) {
                if (!in_array($adv->status, ['Comprobado', 'Rechazado', 'Borrador'])) {
                    $tramitesActivos++;
                }

                $allMovements->push([
                    'id'          => $adv->id,
                    'folio'       => $adv->folio_system,
                    'fecha_raw'   => Carbon::parse($adv->advance_date)->timestamp,
                    'fecha'       => Carbon::parse($adv->advance_date)->format('d/m/Y'),
                    'tipo'        => $adv->advance_type,
                    'descripcion' => $adv->description,
                    'monto'       => (float) $adv->amount,
                    'saldo'       => (float) $adv->balance,
                    'status'      => $adv->status,
                    'origen'      => 'anticipo',
                    'vinculo'     => 'N/A'
                ]);
            });

            // B) Comprobaciones y Reembolsos
            $userClaims->each(function ($claim) use ($allMovements, $advances, &$tramitesActivos) {
                if (!in_array($claim->status_review, ['Aprobado', 'Rechazado', 'Borrador'])) {
                    $tramitesActivos++;
                }

                $origen = 'reembolso';
                $vinculo = 'Independiente';

                if ($claim->expense_advance_id) {
                    $origen = 'comprobacion';
                    $parentAdv = $advances->where('id', $claim->expense_advance_id)->first();
                    $vinculo = $parentAdv ? $parentAdv->folio_system : 'Vinculado';
                } elseif (str_contains($claim->request_type, 'Comprobacion')) {
                    $origen = 'comprobacion';
                }

                $allMovements->push([
                    'id'          => $claim->id,
                    'folio'       => $claim->folio_system,
                    'fecha_raw'   => Carbon::parse($claim->claim_date)->timestamp,
                    'fecha'       => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                    'tipo'        => $claim->request_type,
                    'descripcion' => $claim->motive,
                    'monto'       => (float) $claim->total_amount,
                    'saldo'       => 0,
                    'status'      => $claim->status_review,
                    'origen'      => $origen,
                    'vinculo'     => $vinculo
                ]);
            });

            // C) Movimientos en Cuenta Corriente (Ledgers)
            $userLedgers->each(function ($ledger) use ($allMovements) {
                $vinculo = 'General / Manual';
                if ($ledger->expense_advance_id && $ledger->advance) {
                    $vinculo = 'Anticipo: ' . $ledger->advance->folio_system;
                } elseif ($ledger->expense_claim_id && $ledger->claim) {
                    $vinculo = 'Reembolso: ' . $ledger->claim->folio_system;
                }

                $allMovements->push([
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
                    'vinculo'     => $vinculo
                ]);
            });

            $allMovements = $allMovements->sortByDesc('fecha_raw')->values()->toArray();

            return [
                'user_id'          => $employee->id,
                'nombre'           => $employee->name,
                'depto'            => ($employee->employee && $employee->employee->area) ? $employee->employee->area->name : 'Sin Asignar',
                'total_monto'      => (float) $totalMonto,
                'total_saldo'      => (float) $totalSaldoAnticipos,
                'saldo_corriente'  => $saldoCorriente,
                'tramites_activos' => $tramitesActivos,
                'movimientos'      => $allMovements
            ];
        })->filter()->values()->toArray();

        usort($advancesData, function($a, $b) {
            return ($b['total_saldo'] + abs($b['saldo_corriente'])) <=> ($a['total_saldo'] + abs($a['saldo_corriente']));
        });

        return view('modules.administration.expense-claims.accountspayable', compact('advancesData'));
    }

    public function registerBalanceAdjustment(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'movement_type'      => 'required|in:Abono_Retencion,Cargo_Excedente,Ajuste_Manual,Liquidacion_Caja',
            'amount'             => 'required|numeric|min:0.01',
            'description'        => 'required|string|max:255',
            'expense_advance_id' => 'nullable|exists:expense_advances,id',
            'expense_claim_id'   => 'nullable|exists:expense_claims,id',
        ]);

        try {
            DB::beginTransaction();

            // 👈 CORREGIDO: Uso del nombre correcto del modelo
            $balance = ExpenseAccountBalance::lockForUpdate()->firstOrCreate(
                ['user_id' => $request->user_id],
                ['balance_amount' => 0.00]
            );

            $previousBalance = (float) $balance->balance_amount;
            $amount = (float) $request->amount;

            if (in_array($request->movement_type, ['Abono_Retencion', 'Liquidacion_Caja'])) {
                $newBalance = $previousBalance - $amount;
            } else {
                $newBalance = $previousBalance + $amount;
            }

            // 👈 CORREGIDO: Uso del nombre correcto del modelo para la transacción
            $lastLedger = ExpenseAccountTransaction::orderBy('id', 'desc')->first();
            $sysNum = $lastLedger ? $lastLedger->id + 1 : 1;
            $folioSystem = 'BAL-' . Carbon::now()->format('ym') . '-' . str_pad($sysNum, 4, '0', STR_PAD_LEFT);

            ExpenseAccountTransaction::create([
                'user_id'            => $request->user_id,
                'created_by_id'      => Auth::id() ?? 1,
                'expense_advance_id' => $request->expense_advance_id ?: null,
                'expense_claim_id'   => $request->expense_claim_id ?: null,
                'folio_system'       => $folioSystem,
                'movement_type'      => $request->movement_type,
                'amount'             => $amount,
                'previous_balance'   => $previousBalance,
                'new_balance'        => $newBalance,
                'description'        => $request->description,
            ]);

            $balance->update([
                'balance_amount'   => $newBalance,
                'last_movement_at' => Carbon::now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El movimiento de cuenta corriente ha sido registrado correctamente.',
                'folio'   => $folioSystem,
                'saldo_actual' => $newBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Falla en transacción de saldo: ' . $e->getMessage()
            ], 500);
        }
    }
}
