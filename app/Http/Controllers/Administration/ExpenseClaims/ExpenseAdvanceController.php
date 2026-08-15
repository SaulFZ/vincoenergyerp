<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Auth\User;
use Carbon\Carbon;

class ExpenseAdvanceController extends Controller
{
    public function index()
    {
        $advances = ExpenseAdvance::with(['user.employee.area'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($adv) {
                return [
                    'id'     => $adv->id,
                    'folio'  => $adv->folio_system,
                    'fecha'  => Carbon::parse($adv->advance_date)->format('d/m/Y'),
                    'nombre' => $adv->user->name ?? 'Usuario Desconocido',
                    'depto'  => ($adv->user && $adv->user->employee && $adv->user->employee->area) ? $adv->user->employee->area->name : 'Sin Asignar',
                    'tipo'   => $adv->advance_type,
                    'motivo' => $adv->description,
                    'monto'  => (float) $adv->amount,
                    'saldo'  => (float) $adv->balance,
                    'status' => $adv->status
                ];
            });

        return view('modules.administration.expense-claims.advances', compact('advances'));
    }

    public function getActiveByUser($userId)
    {
        $advances = ExpenseAdvance::where('user_id', $userId)
            ->where('balance', '>', 0)
            ->whereIn('status', ['Pendiente', 'Aprobado', 'Entregado'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'folio_system', 'advance_type', 'balance']);

        return response()->json($advances);
    }

    public function store(Request $request)
    {
        $request->validate([
            'advance_type' => 'required|string',
            'advance_date' => 'required|date_format:d/m/Y',
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $creatorId = Auth::id();

            $lastAdv = ExpenseAdvance::orderBy('id', 'desc')->first();
            $sysNum = $lastAdv ? $lastAdv->id + 1 : 1;
            $folioSystem = 'ANT-' . Carbon::now()->format('ym') . '-' . str_pad($sysNum, 3, '0', STR_PAD_LEFT);

            $advance = ExpenseAdvance::create([
                'folio_system' => $folioSystem,
                'user_id'      => $creatorId,
                'advance_date' => Carbon::createFromFormat('d/m/Y', $request->advance_date)->toDateString(),
                'advance_type' => $request->advance_type,
                'description'  => $request->description,
                'amount'       => $request->amount,
                'balance'      => $request->amount,
                'status'       => 'Pendiente'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de anticipo generada con éxito.',
                'folio'   => $folioSystem
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al generar anticipo: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $advance = ExpenseAdvance::with(['user.employee.area', 'expenseClaims'])
            ->findOrFail($id);
        return response()->json(['success' => true, 'data' => $advance]);
    }

    public function ledger($userId)
    {
        $user = User::findOrFail($userId);

        $advances = ExpenseAdvance::where('user_id', $userId)
            ->whereIn('status', ['Entregado', 'Comprobado'])
            ->get()->map(function($item) {
                return [
                    'concepto' => 'Anticipo: ' . $item->folio_system,
                    'fecha'    => Carbon::parse($item->advance_date)->format('d/m/y'),
                    'monto'    => (float) $item->amount,
                    'tipo'     => 'cargo',
                    'orden'    => Carbon::parse($item->advance_date)->timestamp
                ];
            });

        $claims = ExpenseClaim::where('user_id', $userId)
            ->whereNotNull('expense_advance_id')
            ->whereIn('status_review', ['Validado', 'Aprobado'])
            ->get()->map(function($item) {
                return [
                    'concepto' => 'Comprobación: ' . $item->folio_system,
                    'fecha'    => Carbon::parse($item->claim_date)->format('d/m/y'),
                    'monto'    => -((float) $item->total_amount),
                    'tipo'     => 'abono',
                    'orden'    => Carbon::parse($item->claim_date)->timestamp
                ];
            });

        $ledger = $advances->concat($claims)->sortBy('orden')->values()->toArray();

        $saldoTotal = array_reduce($ledger, function($carry, $item) {
            return $carry + $item['monto'];
        }, 0);

        return response()->json([
            'success' => true,
            'user'    => $user->name,
            'saldo'   => $saldoTotal,
            'ledger'  => $ledger
        ]);
    }
}
