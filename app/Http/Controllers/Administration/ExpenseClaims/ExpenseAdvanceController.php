<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseAdvance;
use Carbon\Carbon;

class ExpenseAdvanceController extends Controller
{
    // Obtener los anticipos activos/pendientes de un usuario para el Dropdown
    public function getActiveByUser($userId)
    {
        // Traemos los anticipos del usuario que aún tienen saldo por comprobar
        $advances = ExpenseAdvance::where('user_id', $userId)
            ->where('balance', '>', 0)
            ->whereIn('status', ['Pendiente', 'Aprobado', 'Entregado']) // Ajusta según tu lógica de negocio
            ->orderBy('created_at', 'desc')
            ->get(['id', 'folio_system', 'advance_type', 'balance']);

        return response()->json($advances);
    }

    // Guardar un nuevo anticipo
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

            // Generación de Folio (ej. ANT-2607-01)
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
                'balance'      => $request->amount, // Al inicio, el saldo por comprobar es el monto total
                'status'       => 'Pendiente'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de anticipo generada con éxito.',
                'folio' => $folioSystem
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al generar anticipo: ' . $e->getMessage()], 500);
        }
    }

    // Ver detalles de un anticipo específico (Para el botón de la tabla en el futuro)
    public function show($id)
    {
        $advance = ExpenseAdvance::with('user.employee.area')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $advance]);
    }
}
