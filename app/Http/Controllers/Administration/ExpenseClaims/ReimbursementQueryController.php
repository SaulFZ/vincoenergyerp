<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReimbursementQueryController extends Controller
{
    public function show($id)
    {
        $claim = ExpenseClaim::with([
            'lines',
            'beneficiary.employee.area',
            'creator',
            'expenseAdvance',
            'logs' => function($q) {
                $q->with('user')->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $claim
        ]);
    }

    /**
     * Sirve un PDF privado para verlo en el navegador.
     * Solo acepta rutas que estén dentro de private/administration/expense-claims/.
     */
    public function viewEvidence(Request $request)
    {
        $path = $request->query('path');

        if (!$path || !str_starts_with($path, 'private/administration/expense-claims/')) {
            abort(403, 'Ruta no permitida');
        }

        if (!Storage::exists($path)) {
            abort(404, 'Archivo no encontrado');
        }

        $absolute = storage_path('app/' . $path);
        $filename = basename($path);

        return response()->file($absolute, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}











