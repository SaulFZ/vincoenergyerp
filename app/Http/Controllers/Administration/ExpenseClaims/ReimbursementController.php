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
        // 1. Añadimos la relación 'expenseAdvance' al Query
        $reembolsos = ExpenseClaim::with(['beneficiary', 'expenseAdvance'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        $requestsData = $reembolsos->map(function ($req) {
            return [
                'id'            => $req->id,
                'folioP'        => $req->folio_system,
                'folioU'        => $req->folio_user ?? 'N/A',
                'advance_id'    => $req->expense_advance_id,
                'advance_folio' => $req->expenseAdvance ? $req->expenseAdvance->folio_system : null,
                'advance_status' => $req->expenseAdvance ? $req->expenseAdvance->status : null,
                'fecha'         => \Carbon\Carbon::parse($req->claim_date)->format('d/m/Y'),
                'nombre'        => $req->beneficiary ? $req->beneficiary->name : 'Usuario Desconocido',
                'motivo'        => $req->motive,
                'depto'         => $req->area ?? 'Sin Asignar',
                'amount'        => (float) $req->total_amount,
                'status'        => $req->status_review,
                'pago'          => $req->status_payment,
                'tipo'          => $req->request_type
            ];
        })->values()->toArray();

        // 2. Traer el RFC activo
        $node = FslNode::where('is_live', true)->first();
        $rfcEmpresa = $node ? $node->g_id : 'NO CONFIGURADO';

        // 3. Traer Usuarios
        $usersList = User::with(['employee.area'])->active()->get()->map(function($u) {
            return [
                'id'     => $u->id,
                'nombre' => $u->name,
                'depto'  => ($u->employee && $u->employee->area) ? $u->employee->area->name : 'Sin Asignar',
                'rfc'    => $u->employee ? $u->employee->rfc : 'S/N'
            ];
        })->values()->toArray();

        return view('modules.administration.expense-claims.reimbursements', [
            'reembolsos'   => $reembolsos,
            'requestsData' => $requestsData,
            'rfcEmpresa'   => $rfcEmpresa,
            'usersList'    => $usersList
        ]);
    }
}
