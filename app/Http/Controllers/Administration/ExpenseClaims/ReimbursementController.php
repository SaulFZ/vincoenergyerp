<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Models\Auth\User;

class ReimbursementController extends Controller
{
    public function index()
    {
        // 1. Historial de Reembolsos
        $reembolsos = ExpenseClaim::with('beneficiary')
                    ->orderBy('created_at', 'desc')
                    ->get();

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

        // 2. Traer el RFC activo de la Bóveda (FslNode)
        $node = FslNode::where('is_live', true)->first();
        $rfcEmpresa = $node ? $node->g_id : 'NO CONFIGURADO';

        // 3. Traer Usuarios con su información de Empleado (Para el buscador)
        $usersList = User::with(['employee.area'])->active()->get()->map(function($u) {
            return [
                'id'     => $u->id,
                'nombre' => $u->name,
                'depto'  => ($u->employee && $u->employee->area) ? $u->employee->area->name : 'Sin Asignar',
                'rfc'    => $u->employee ? $u->employee->rfc : 'S/N'
            ];
        });

        return view('modules.administration.expense-claims.reimbursements', [
            'reembolsos'   => $reembolsos,
            'requestsData' => $requestsData,
            'rfcEmpresa'   => $rfcEmpresa,
            'usersList'    => $usersList
        ]);
    }
}
