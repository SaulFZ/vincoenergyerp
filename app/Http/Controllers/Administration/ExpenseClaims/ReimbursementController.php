<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Administration\ExpenseClaims\ExpenseClaim;
use App\Models\Administration\ExpenseClaims\FslNode;
use App\Models\Administration\ExpenseClaims\CostCenter;
use App\Models\Auth\User;
use Carbon\Carbon;

class ReimbursementController extends Controller
{
    private function normalizeArea(?string $area): string
    {
        return mb_strtolower(trim((string) $area));
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user()->loadMissing(['employee.area', 'directPermissions']);

        $permNames = $user->directPermissions->pluck('name')->toArray();

        $canValidate   = in_array('validar_solicitud', $permNames, true);
        $canApprove    = in_array('aprobar_solicitud', $permNames, true);
        $canViewAll    = in_array('ver_todo_solicitudes', $permNames, true);
        $canBellAlerts = in_array('ver_notificaciones_anticipos', $permNames, true);

        $userAreaName = ($user->employee && $user->employee->area)
            ? $user->employee->area->name
            : 'Sin Asignar';
        $userAreaNorm = $this->normalizeArea($userAreaName);

        $reembolsos = ExpenseClaim::with(['beneficiary.employee.area', 'expenseAdvance'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Filtrado de visibilidad según permisos directos
        $visibleClaims = $reembolsos->filter(function (ExpenseClaim $req) use (
            $user, $userAreaNorm, $canValidate, $canApprove, $canViewAll
        ) {
            $isOwn = ((int) $req->user_id === (int) $user->id)
                  || ((int) $req->created_by_id === (int) $user->id);

            // Los borradores privados de otros usuarios no se muestran hasta que se envían
            if ($req->status_review === 'borrador' && !$isOwn) {
                return false;
            }

            // Si tiene permiso global de visualización
            if ($canViewAll) {
                return true;
            }

            $claimAreaName = $req->area
                ?: ($req->beneficiary?->employee?->area?->name ?? 'Sin Asignar');
            $isSameArea = ($userAreaNorm !== '' && $this->normalizeArea($claimAreaName) === $userAreaNorm);

            // Revisor o Aprobador sin ver_todo_solicitudes: ve las de su área + las propias
            if ($canValidate || $canApprove) {
                return $isSameArea || $isOwn;
            }

            // Usuario normal: únicamente sus propias solicitudes
            return $isOwn;
        });

        $requestsData = $visibleClaims->map(function (ExpenseClaim $req) use (
            $user, $userAreaNorm, $canValidate, $canApprove, $canViewAll
        ) {
            $claimAreaName = $req->area
                ?: ($req->beneficiary?->employee?->area?->name ?? 'Sin Asignar');
            $isSameArea = ($userAreaNorm !== '' && $this->normalizeArea($claimAreaName) === $userAreaNorm);
            $isOwn      = ((int) $req->user_id === (int) $user->id)
                       || ((int) $req->created_by_id === (int) $user->id);

            // Regla para Validar: requiere validar_solicitud y (misma área o ver_todo_solicitudes)
            $canValidateThis = $canValidate
                && $req->status_review === 'pendiente'
                && ($canViewAll || $isSameArea);

            // Regla para Aprobar: requiere aprobar_solicitud y SIEMPRE ser de su misma área
            $canApproveThis = $canApprove
                && $req->status_review === 'validado'
                && $req->status_approval === 'en_revision'
                && $isSameArea;

            return [
                'id'              => $req->id,
                'user_id'         => $req->user_id,
                'created_by_id'   => $req->created_by_id,
                'is_own'          => $isOwn,
                'is_same_area'    => $isSameArea,
                'can_validate'    => $canValidateThis,
                'can_approve'     => $canApproveThis,
                'can_evaluate'    => ($canValidateThis || $canApproveThis),
                'folioP'          => $req->folio_system,
                'folioU'          => $req->folio_user ?? 'N/A',
                'advance_id'      => $req->expense_advance_id,
                'advance_folio'   => $req->expenseAdvance ? $req->expenseAdvance->folio_system : null,
                'advance_status'  => $req->expenseAdvance ? $req->expenseAdvance->status : null,
                'fecha'           => Carbon::parse($req->claim_date)->format('d/m/Y'),
                'nombre'          => $req->beneficiary ? $req->beneficiary->name : 'Usuario Desconocido',
                'motivo'          => $req->motive,
                'depto'           => $claimAreaName,
                'amount'          => (float) $req->total_amount,
                'leftover_amount' => (float) ($req->leftover_amount ?? 0),
                'excess_amount'   => (float) ($req->excess_amount ?? 0),
                'status'          => $req->status_review,
                'aprobacion'      => $req->status_approval,
                'status_payment'  => $req->status_payment ?? 'no_aplica',
                'status_balance'  => $req->status_balance ?? 'no_aplica',
                'tipo'            => $req->request_type,
            ];
        })->values()->toArray();

        $userPermissions = [
            'validar_solicitud'            => $canValidate,
            'aprobar_solicitud'            => $canApprove,
            'ver_todo_solicitudes'         => $canViewAll,
            'ver_notificaciones_anticipos' => $canBellAlerts,
        ];

        // Respuesta ligera JSON para actualización de tabla sin recargar página
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'         => true,
                'requestsData'    => $requestsData,
                'userPermissions' => $userPermissions,
            ]);
        }

        $node = FslNode::where('is_live', true)->first();
        $rfcEmpresa = $node ? $node->g_id : 'NO CONFIGURADO';

        $usersList = User::with(['employee.area'])->active()->get()->map(function ($u) {
            return [
                'id'     => $u->id,
                'nombre' => $u->name,
                'depto'  => ($u->employee && $u->employee->area) ? $u->employee->area->name : 'Sin Asignar',
                'rfc'    => $u->employee ? $u->employee->rfc : 'S/N',
            ];
        })->values()->toArray();

        $costCenters = CostCenter::with(['projects' => function ($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get()->map(function ($cc) {
            return [
                'id'       => $cc->id,
                'code'     => $cc->code,
                'name'     => $cc->name,
                'projects' => $cc->projects->map(function ($p) {
                    return ['id' => $p->id, 'code' => $p->code, 'name' => $p->name];
                })->toArray(),
            ];
        })->toArray();

        $approvers = User::active()->whereHas('directPermissions', function ($query) {
            $query->where('name', 'aprobar_anticipo');
        })->select('id', 'name')->get()->toArray();

        return view('modules.administration.expense_claims.reimbursements', [
            'reembolsos'      => $visibleClaims,
            'requestsData'    => $requestsData,
            'userPermissions' => $userPermissions,
            'rfcEmpresa'      => $rfcEmpresa,
            'usersList'       => $usersList,
            'costCenters'     => $costCenters,
            'approvers'       => $approvers,
        ]);
    }
}