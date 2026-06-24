<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;

class ReimbursementController extends Controller
{
    public function index()
{
    $reembolsos = ExpenseClaim::with('beneficiary')
                    ->orderBy('created_at', 'desc')
                    ->get();

    // Transformamos los datos en el controlador
    $requestsData = $reembolsos->map(function ($req) {
        return [
            'id'     => $req->id,
            'folioP' => $req->folio_system,
            'folioU' => $req->folio_user ?? 'N/A',
            'fecha'  => \Carbon\Carbon::parse($req->claim_date)->format('d/m/Y'),
            'nombre' => $req->beneficiary ? $req->beneficiary->name : 'Usuario Desconocido',
            'motivo' => $req->motive,
            'depto'  => $req->area ?? 'Sin Asignar',
            'amount' => (float) $req->total_amount,
            'status' => $req->status_review,
            'pago'   => $req->status_payment,
        ];
    });

    return view('modules.administration.expense-claims.reimbursements', [
        'reembolsos'    => $reembolsos,
        'requestsData'  => $requestsData // Enviamos la data ya procesada
    ]);
}
}
