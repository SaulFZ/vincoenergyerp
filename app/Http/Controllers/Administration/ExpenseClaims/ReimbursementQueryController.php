<?php
namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;

class ReimbursementQueryController extends Controller
{
    public function show($id)
    {
        $claim = ExpenseClaim::with([
            'lines',
            'beneficiary.employee.area',
            'creator',
            // Traemos el historial ordenado desde el más reciente
            'logs' => function($q) { $q->orderBy('created_at', 'desc'); }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $claim
        ]);
    }
}
