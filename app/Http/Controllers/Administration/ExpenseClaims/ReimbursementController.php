<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Models\Administration\ExpenseClaims\CostCenter;
use App\Models\Auth\User;

class ReimbursementController extends Controller
{
    public function index()
    {
        $reembolsos = ExpenseClaim::with(['beneficiary', 'expenseAdvance'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        $requestsData = $reembolsos->map(function ($req) {
            return [
                'id'             => $req->id,
                'folioP'         => $req->folio_system,
                'folioU'         => $req->folio_user ?? 'N/A',
                'advance_id'     => $req->expense_advance_id,
                'advance_folio'  => $req->expenseAdvance ? $req->expenseAdvance->folio_system : null,
                'advance_status' => $req->expenseAdvance ? $req->expenseAdvance->status : null,
                'fecha'          => \Carbon\Carbon::parse($req->claim_date)->format('d/m/Y'),
                'nombre'         => $req->beneficiary ? $req->beneficiary->name : 'Usuario Desconocido',
                'motivo'         => $req->motive,
                'depto'          => $req->area ?? 'Sin Asignar',
                'amount'         => (float) $req->total_amount,
                'status'         => $req->status_review,
                'aprobacion'     => $req->status_approval,
                'status_payment' => $req->status_payment ?? 'no_aplica',
                'tipo'           => $req->request_type
            ];
        })->values()->toArray();

        $node = FslNode::where('is_live', true)->first();
        $rfcEmpresa = $node ? $node->g_id : 'NO CONFIGURADO';

        $usersList = User::with(['employee.area'])->active()->get()->map(function($u) {
            return [
                'id'     => $u->id,
                'nombre' => $u->name,
                'depto'  => ($u->employee && $u->employee->area) ? $u->employee->area->name : 'Sin Asignar',
                'rfc'    => $u->employee ? $u->employee->rfc : 'S/N'
            ];
        })->values()->toArray();

        $costCenters = CostCenter::with(['projects' => function($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get()->map(function($cc) {
            return [
                'id'   => $cc->id,
                'code' => $cc->code,
                'name' => $cc->name,
                'projects' => $cc->projects->map(function($p) {
                    return ['id' => $p->id, 'code' => $p->code, 'name' => $p->name];
                })->toArray()
            ];
        })->toArray();

        $approvers = User::active()->whereHas('directPermissions', function($query) {
            $query->where('name', 'aprobar_anticipo');
        })->select('id', 'name')->get()->toArray();

        return view('modules.administration.expense_claims.reimbursements', [
            'reembolsos'   => $reembolsos,
            'requestsData' => $requestsData,
            'rfcEmpresa'   => $rfcEmpresa,
            'usersList'    => $usersList,
            'costCenters'  => $costCenters,
            'approvers'    => $approvers
        ]);
    }
}
