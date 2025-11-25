<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Mail\DayRejectedMail;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use App\Models\RecursosHumanos\LoadChart\LoadChartAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Helpers\PermissionHelper; // Importamos el helper de permisos

class ApprovalController extends Controller
{
    /**
     * Obtiene los IDs de los empleados que el usuario actual puede revisar o aprobar.
     */
    private function getAssignedEmployeeIds()
    {
        $userId = auth()->id();

        return LoadChartAssignment::where('reviewer_id', $userId)
            ->orWhere('approver_id', $userId)
            ->pluck('employee_id');
    }

    /**
     * Calcula el day_status basado en todos los elementos de un día específico
     */
    private function calculateDayStatus($dailyActivity)
    {
        $hasRejected    = false;
        $hasUnderReview = false;
        $hasReviewed    = false;
        $hasApproved    = false;
        $totalItems     = 0;
        $approvedItems  = 0;
        $reviewedItems  = 0;

        // Verificar el estado de la actividad principal
        if (isset($dailyActivity['activity_type']) &&
            ! empty($dailyActivity['activity_type']) &&
            $dailyActivity['activity_type'] !== 'N') {

            $totalItems++;
            $activityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');

            switch ($activityStatus) {
                case 'rejected':$hasRejected = true;
                    break;
                case 'under_review':$hasUnderReview = true;
                    break;
                case 'reviewed':$hasReviewed = true;
                    $reviewedItems++;
                    break;
                case 'approved':$hasApproved = true;
                    $approvedItems++;
                    break;
            }
        }

        // Verificar todos los sub-elementos
        $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];

        foreach ($itemTypes as $type) {
            if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                foreach ($dailyActivity[$type] as $item) {
                    $totalItems++;
                    $itemStatus = strtolower($item['status'] ?? 'under_review');

                    switch ($itemStatus) {
                        case 'rejected':$hasRejected = true;
                            break;
                        case 'under_review':$hasUnderReview = true;
                            break;
                        case 'reviewed':$hasReviewed = true;
                            $reviewedItems++;
                            break;
                        case 'approved':$hasApproved = true;
                            $approvedItems++;
                            break;
                    }
                }
            }
        }

        if ($totalItems === 0) {return 'under_review';}
        if ($hasRejected) {return 'rejected';}
        if ($hasUnderReview) {return 'under_review';}
        if ($approvedItems === $totalItems) {return 'approved';}
        if ($hasReviewed || ($reviewedItems + $approvedItems === $totalItems && $reviewedItems > 0)) {return 'reviewed';}

        return 'under_review';
    }

    /**
     * Actualiza el day_status para todas las actividades diarias
     */
    private function updateDayStatusForAllActivities($dailyActivities)
    {
        return array_map(function ($dailyActivity) {
            $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);
            return $dailyActivity;
        }, $dailyActivities);
    }

    /**
     * Muestra la vista principal.
     */
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)->where('month', $currentMonth)->first();
        if (! $fortnightlyConfig) {$fortnightlyConfig = $this->createDefaultFortnightlyConfig($currentYear, $currentMonth);}
        $monthlyDays          = $this->getMonthlyDaysWithFortnights($currentYear, $currentMonth, $fortnightlyConfig);
        $assignedEmployeeIds = $this->getAssignedEmployeeIds();
        $canSeeFilters = PermissionHelper::hasDirectPermission('ver_filtros');


        // Obtener la lista de departamentos para el filtro de la vista
        // Se carga para todos, ya que el select de filtros se construye en el Blade si tienen permiso.
        $departments = Employee::select("department")
            ->whereNotNull("department")
            ->distinct()
            ->orderBy("department")
            ->pluck("department");

        // MODIFICACIÓN CLAVE (index): Cargamos TODOS los empleados si tiene 'ver_filtros',
        // de lo contrario, solo cargamos a los asignados.
        $employeeQuery = Employee::with(['employeeMonthlyWorkLogs' => function ($query) use ($currentMonth, $currentYear) {
            $query->where('month_and_year', Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m'));
        }])
        ->with('squads')
        ->select('id', 'full_name', 'employee_number', 'position', 'department');

        if (!$canSeeFilters) {
            $employeeQuery->whereIn('id', $assignedEmployeeIds);
        }

        $employees = $employeeQuery->get();
        // FIN MODIFICACIÓN

        $workLogsData = [];
        foreach ($employees as $employee) {
            $log = $employee->employeeMonthlyWorkLogs->first();
            if ($log && $log->daily_activities) {
                $log->daily_activities = $this->updateDayStatusForAllActivities($log->daily_activities);
            }
            if ($log) {
                $workLogsData[] = ['employee_id' => $employee->id, 'daily_activities' => $log->daily_activities, 'reviewed_at' => $log->reviewed_at, 'approved_at' => $log->approved_at];
            } else {
                $workLogsData[] = ['employee_id' => $employee->id, 'daily_activities' => [], 'reviewed_at' => null, 'approved_at' => null];
            }
        }

        // Obtenemos todas las asignaciones para poder determinar los permisos en el Blade/JS
        $loadChartAssignments = LoadChartAssignment::all();
        $canSeeAmounts          = PermissionHelper::hasDirectPermission('ver_montos');
        $userPermissions        = ['is_reviewer' => $loadChartAssignments->contains('reviewer_id', auth()->id()), 'is_approver' => $loadChartAssignments->contains('approver_id', auth()->id())];


        return view('modulos.recursoshumanos.sistemas.loadchart.approval', compact(
            'employees', 'workLogsData', 'fortnightlyConfig', 'monthlyDays', 'currentMonth', 'currentYear',
            'canSeeAmounts', 'loadChartAssignments', 'userPermissions', 'departments'
        ));
    }

    private function getMonthlyDaysWithFortnights($year, $month, $fortnightlyConfig)
    {
        $q1Start     = Carbon::parse($fortnightlyConfig->q1_start);
        $q1End       = Carbon::parse($fortnightlyConfig->q1_end);
        $q2Start     = Carbon::parse($fortnightlyConfig->q2_start);
        $q2End       = Carbon::parse($fortnightlyConfig->q2_end);
        $startDate   = $q1Start->copy();
        $endDate     = $q2End->copy();
        $monthlyDays = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $isQuincena1      = $date >= $q1Start && $date <= $q1End;
            $isQuincena2      = $date >= $q2Start && $date <= $q2End;
            $isCurrentMonth = $date->month == $month;
            $monthlyDays[]    = [
                'day'                 => $date->day, 'date' => $date->copy()->format('Y-m-d'), 'day_name' => $date->locale('es')->shortDayName,
                'is_quincena_1'       => $isQuincena1, 'is_quincena_2' => $isQuincena2, 'is_working_day' => $isQuincena1 || $isQuincena2,
                'is_current_month' => $isCurrentMonth, 'month' => $date->month,
            ];
        }
        return $monthlyDays;
    }

    private function createDefaultFortnightlyConfig($year, $month)
    {
        $firstDay     = Carbon::createFromDate($year, $month, 1);
        $lastDay      = $firstDay->copy()->endOfMonth();
        $fifteenthDay = Carbon::createFromDate($year, $month, min(15, $lastDay->day));
        $sixteenthDay = $fifteenthDay->copy()->addDay();
        if ($sixteenthDay->month !== $month) {$sixteenthDay = $lastDay->copy();}

        return FortnightlyConfig::create([
            'year'     => $year, 'month' => $month, 'q1_start' => $firstDay, 'q1_end' => $fifteenthDay,
            'q2_start' => $sixteenthDay, 'q2_end' => $lastDay,
        ]);
    }

    public function checkUpdates(Request $request)
    {
        try {
            $request->validate(['last_update' => 'required|date', 'month' => 'required|integer|min:1|max:12', 'year' => 'required|integer|min:2020|max:2030']);
            $lastUpdate          = Carbon::parse($request->last_update);
            $month               = $request->month;
            $year                = $request->year;

            // Usamos las asignaciones de todos para verificar si CUALQUIER log se actualizó
            $hasUpdates          = EmployeeMonthlyWorkLog::query()
                ->where('month_and_year', Carbon::createFromDate($year, $month, 1)->format('Y-m'))
                ->where(function ($query) use ($lastUpdate) {$query->where('updated_at', '>', $lastUpdate)->orWhere('created_at', '>', $lastUpdate);})
                ->exists();

            return response()->json(['success' => true, 'has_updates' => $hasUpdates, 'message' => $hasUpdates ? 'Hay actualizaciones disponibles' : 'No hay actualizaciones']);
        } catch (\Exception $e) {Log::error('Error checking updates: ' . $e->getMessage());return response()->json(['success' => false, 'error' => 'Error al verificar actualizaciones', 'message' => $e->getMessage()], 500);}
    }

    public function getApprovalData($year, $month)
    {
        try {
            if ($year < 2020 || $year > 2030 || $month < 1 || $month > 12) {return response()->json(['error' => 'Año o mes inválido'], 400);}
            $fortnightlyConfig = FortnightlyConfig::where('year', $year)->where('month', $month)->first();
            if (! $fortnightlyConfig) {$fortnightlyConfig = $this->createDefaultFortnightlyConfig($year, $month);}
            $monthlyDays          = $this->getMonthlyDaysWithFortnights($year, $month, $fortnightlyConfig);
            $canSeeFilters = PermissionHelper::hasDirectPermission('ver_filtros');
            $assignedEmployeeIds = $this->getAssignedEmployeeIds();

            // MODIFICACIÓN CLAVE (getApprovalData): Cargamos TODOS los empleados si tiene 'ver_filtros',
            // de lo contrario, solo cargamos a los asignados.
            $employeeQuery = Employee::with(['employeeMonthlyWorkLogs' => function ($query) use ($month, $year) {
                $query->where('month_and_year', Carbon::createFromDate($year, $month, 1)->format('Y-m'));
            }])
                ->with('squads') // Agregamos 'with('squads')'
                ->select('id', 'full_name', 'employee_number', 'position', 'department'); // Agregamos 'department'

            if (!$canSeeFilters) {
                $employeeQuery->whereIn('id', $assignedEmployeeIds);
            }

            $employees = $employeeQuery->get();
            // FIN MODIFICACIÓN

            $workLogsData = [];
            foreach ($employees as $employee) {
                $log = $employee->employeeMonthlyWorkLogs->first();
                if ($log && $log->daily_activities) {
                    $log->daily_activities = $this->updateDayStatusForAllActivities($log->daily_activities);
                }
                if ($log) {$workLogsData[] = ['employee_id' => $employee->id, 'daily_activities' => $log->daily_activities ?? [], 'reviewed_at' => $log->reviewed_at, 'approved_at' => $log->approved_at];} else { $workLogsData[] = ['employee_id' => $employee->id, 'daily_activities' => [], 'reviewed_at' => null, 'approved_at' => null];}
            }

            // Obtenemos todas las asignaciones para poder determinar los permisos en el JS
            $loadChartAssignments = LoadChartAssignment::all();
            $canSeeAmounts          = PermissionHelper::hasDirectPermission('ver_montos');
            $userPermissions        = ['is_reviewer' => $loadChartAssignments->contains('reviewer_id', auth()->id()), 'is_approver' => $loadChartAssignments->contains('approver_id', auth()->id())];

            // ⚠️ La respuesta de AJAX AHORA INCLUYE LOS DATOS DEL EMPLEADO
            return response()->json([
                'success' => true, 'employees' => $employees, 'workLogsData' => $workLogsData,
                'fortnightlyConfig' => $fortnightlyConfig, 'monthlyDays' => $monthlyDays,
                'currentMonth' => $month, 'currentYear' => $year, 'canSeeAmounts' => $canSeeAmounts,
                'loadChartAssignments' => $loadChartAssignments, 'userPermissions' => $userPermissions,
                'message' => 'Datos cargados para ' . $this->getMonthName($month) . ' ' . $year,
            ]);

        } catch (\Exception $e) {Log::error('Error loading approval data: ' . $e->getMessage());return response()->json(['success' => false, 'error' => 'Error al cargar los datos del mes', 'message' => $e->getMessage()], 500);}
    }

    private function getMonthName($month)
    {
        $months = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        return $months[$month] ?? 'Mes desconocido';
    }

    /**
     * Función auxiliar para actualizar el estado de un ítem individual (actividad, bono, etc.).
     */
    private function updateItemStatus($item, $statusKey, $currentStatus, $newStatus, $isReviewer, $isApprover, &$updated)
    {
        // Reglas de actualización del ítem (se mantiene la lógica base)
        if ($isReviewer && $newStatus === 'reviewed' && $currentStatus === 'under_review') {$item[$statusKey] = 'Reviewed';
            $updated = true;}
        if ($isApprover && $newStatus === 'approved' && ($currentStatus === 'under_review' || $currentStatus === 'reviewed')) {$item[$statusKey] = 'Approved';
            $updated = true;}
        if ($isApprover && $newStatus === 'rejected' && $currentStatus !== 'rejected') {$item[$statusKey] = 'Rejected';
            $updated = true;}
        // Para "under_review", se permite si el usuario tiene rol y el estatus actual no es aprobado/rechazado
        if (($isReviewer || $isApprover) && $newStatus === 'under_review' && $currentStatus !== 'approved' && $currentStatus !== 'rejected') {$item[$statusKey] = 'Under_review';
            $updated = true;}

        return $item;
    }

    /**
     * Función auxiliar para actualizar los campos `reviewed_at` y `approved_at` del log.
     */
    private function updateLogStatus($workLog, $newStatus, $userId)
    {
        $all_items_reviewed = true;
        $all_items_approved = true;
        foreach ($workLog->daily_activities as $dailyActivity) {
            $dailyStatus = $this->calculateDayStatus($dailyActivity);
            if ($dailyStatus !== 'reviewed' && $dailyStatus !== 'approved') {$all_items_reviewed = false;}
            if ($dailyStatus !== 'approved') {$all_items_approved = false;}
        }

        if ($newStatus === 'reviewed' && $all_items_reviewed) {$workLog->reviewed_at = now();
            $workLog->reviewed_by = $userId;
            $workLog->save();}
        if ($newStatus === 'approved' && $all_items_approved) {$workLog->approved_at = now();
            $workLog->approved_by = $userId;
            if (! $workLog->reviewed_at) {$workLog->reviewed_at = now();
                $workLog->reviewed_by = $userId;}
            $workLog->save();}
    }

    /**
     * Obtiene los saldos de días de un empleado.
     */
    private function getEmployeeBalances(int $employeeId): array
    {
        $balance = EmployeeVacationBalance::where('employee_id', $employeeId)->first();
        return [
            'vacationDays' => $balance ? $balance->vacation_days_available : 0,
            'restDays'     => $balance ? $balance->rest_days_available : 0,
        ];
    }

    /**
     * Actualiza masivamente el estado de revisión o aprobación para los ítems de una quincena.
     */
    public function updateApprovalStatus(Request $request)
    {
        try {
            $request->validate(['employee_id' => 'required|integer|exists:employees,id', 'month' => 'required|integer|min:1|max:12', 'year' => 'required|integer|min:2020|max:2030', 'status' => 'required|in:reviewed,approved', 'fortnight' => 'required|in:quincena1,quincena2,full-month']);

            $employeeId = $request->employee_id;
            $month      = $request->month;
            $year       = $request->year;
            $newStatus  = strtolower($request->status);
            $fortnight  = $request->fortnight;
            $userId     = auth()->id();
            $assignment = LoadChartAssignment::where('employee_id', $employeeId)->where(function ($query) use ($userId) {$query->where('reviewer_id', $userId)->orWhere('approver_id', $userId);})->first();

            // Validación de permisos
            if (! $assignment) {return response()->json(['success' => false, 'message' => 'Acceso denegado. No tiene permisos para modificar el estado de este empleado.'], 403);}
            $isReviewer = $assignment->reviewer_id === $userId;
            $isApprover = $assignment->approver_id === $userId;

            if ($newStatus === 'reviewed' && ! $isReviewer) {return response()->json(['success' => false, 'message' => 'No tiene permisos para revisar este registro.'], 403);}
            if ($newStatus === 'approved' && ! $isApprover) {return response()->json(['success' => false, 'message' => 'No tiene permisos para aprobar este registro.'], 403);}

            $monthAndYear      = Carbon::createFromDate($year, $month, 1)->format('Y-m');
            $workLog           = EmployeeMonthlyWorkLog::firstOrCreate(['employee_id' => $employeeId, 'month_and_year' => $monthAndYear], ['user_id' => $userId, 'daily_activities' => []]);
            $fortnightlyConfig = FortnightlyConfig::where('year', $year)->where('month', $month)->first();
            if (! $fortnightlyConfig) {$fortnightlyConfig = $this->createDefaultFortnightlyConfig($year, $month);}

            $startDate = null;
            $endDate   = null;
            if ($fortnight === 'quincena1') {$startDate = Carbon::parse($fortnightlyConfig->q1_start);
                $endDate = Carbon::parse($fortnightlyConfig->q1_end);} elseif ($fortnight === 'quincena2') {$startDate = Carbon::parse($fortnightlyConfig->q2_start);
                $endDate = Carbon::parse($fortnightlyConfig->q2_end);} else { $startDate = Carbon::createFromDate($year, $month, 1);
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();}

            DB::beginTransaction();
            $dailyActivities = collect($workLog->daily_activities);
            $updated         = false;
            $balance         = EmployeeVacationBalance::firstOrNew(['employee_id' => $employeeId]);

            // Validación de saldo de VACACIONES (MANTENIDA)
            if ($newStatus === 'approved') {
                $vacationDaysToApprove = $dailyActivities->filter(function ($dailyActivity) use ($startDate, $endDate) {
                    $activityDate = Carbon::parse($dailyActivity['date']);
                    $activityType = $dailyActivity['activity_type'] ?? null;
                    $oldStatus    = strtolower($dailyActivity['activity_status'] ?? 'under_review');
                    return $activityDate->between($startDate, $endDate) && $activityType === 'VAC' && $oldStatus !== 'approved';
                })->count();

                if ($balance->vacation_days_available < $vacationDaysToApprove) {
                    DB::rollback();
                    return response()->json(['success' => false, 'message' => "No hay suficientes días de vacaciones disponibles para aprobar."], 422);
                }

                // ❌ SE ELIMINA LA VALIDACIÓN DE SALDO DE DESCANSOS ('D')
            }

            // Recorrer y aplicar cambios individuales y actualizar saldos
            $dailyActivities = $dailyActivities->map(function ($dailyActivity) use ($startDate, $endDate, $newStatus, $isReviewer, $isApprover, &$updated, $employeeId) {
                $activityDate = Carbon::parse($dailyActivity['date']);
                if ($activityDate->between($startDate, $endDate)) {
                    $oldActivityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');
                    $tempUpdated       = false;

                    // 1. Actividad principal
                    $dailyActivity = $this->updateItemStatus($dailyActivity, 'activity_status', $oldActivityStatus, $newStatus, $isReviewer, $isApprover, $tempUpdated);

                    // 2. Sub-ítems
                    $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
                    foreach ($itemTypes as $type) {
                        if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                            $dailyActivity[$type] = array_map(function ($item) use ($newStatus, $isReviewer, $isApprover, &$updated) {
                                $currentItemStatus = strtolower($item['status'] ?? 'under_review');
                                $tempSubUpdated    = false;
                                $item              = $this->updateItemStatus($item, 'status', $currentItemStatus, $newStatus, $isReviewer, $isApprover, $tempSubUpdated);
                                if ($tempSubUpdated) {
                                    $updated = true;
                                }

                                return $item;
                            }, $dailyActivity[$type]);
                        }
                    }
                    if ($tempUpdated) {
                        $updated = true;
                    }

                    $newActivityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');

                    // 3. Ajuste de Saldos (SOLO VACACIONES)
                    if ($newActivityStatus !== $oldActivityStatus) {
                        $activityType = $dailyActivity['activity_type'] ?? null;
                        $balance      = EmployeeVacationBalance::where('employee_id', $employeeId)->first();

                        // Lógica VACACIONES (MANTENIDA)
                        if ($activityType === 'VAC') {
                            if ($newActivityStatus === 'approved' && $oldActivityStatus !== 'approved') {
                                $balance->decrement('vacation_days_available');
                            } elseif ($newActivityStatus === 'rejected' && $oldActivityStatus === 'approved') {
                                $balance->increment('vacation_days_available');
                            }
                        }

                        // ❌ SE ELIMINA LÓGICA DESCANSOS (D)

                        if ($balance) {
                            $balance->save();
                        }
                    }

                    $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);
                }
                return $dailyActivity;
            })->toArray();

            // ... (Guardado y commit) ...
            if ($updated) {$workLog->daily_activities = $dailyActivities;
                $workLog->save();}
            $this->updateLogStatus($workLog, $newStatus, $userId);
            DB::commit();

            return response()->json([
                'success' => true, 'message' => "Estado de la {$fortnight} actualizado a '{$newStatus}' correctamente.",
                'new_balances' => $this->getEmployeeBalances($employeeId),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating approval status: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error al actualizar el estado de aprobación', 'message' => $e->getMessage()], 500);
        }
    }


/**
 * Updates multiple daily work log items (individual modal save)
 */
    public function updateMultipleStatuses(Request $request)
    {
        try {
            $request->validate([
                'employee_id'                  => 'required|exists:employees,id',
                'changes'                      => 'required|array',
                'changes.*.date'               => 'required|date_format:Y-m-d',
                'changes.*.item_type'          => 'required|string',
                'changes.*.item_index'         => 'nullable|integer',
                'changes.*.status'             => 'required|string|in:reviewed,approved,rejected,under_review',
                'changes.*.rejection_reason'   => 'nullable|string',
                'month'                        => 'required|integer|min:1|max:12',
                'year'                         => 'required|integer|min:2020|max:2030',
            ]);

            $employeeId = $request->input('employee_id');
            $changes    = $request->input('changes');
            $month      = $request->input('month');
            $year       = $request->input('year');
            $userId     = auth()->id();

            // Obtener empleado y asignación
            $assignment = LoadChartAssignment::where('employee_id', $employeeId)->where(function ($query) use ($userId) {
                $query->where('reviewer_id', $userId)->orWhere('approver_id', $userId);
            })->first();

            // Validación de Permisos
            if (! $assignment) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado. No tiene permisos para modificar el estado de este empleado.'], 403);
            }

            $isReviewer = $assignment->reviewer_id === $userId;
            $isApprover = $assignment->approver_id === $userId;

            $monthAndYear = Carbon::createFromDate($year, $month, 1)->format('Y-m');
            $log          = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)->where('month_and_year', $monthAndYear)->first();

            if (! $log) {
                return response()->json(['success' => false, 'message' => 'Work log not found.'], 404);
            }

            DB::beginTransaction();
            $dailyActivities = $log->daily_activities;
            $updated         = false;
            $balance         = EmployeeVacationBalance::firstOrNew(['employee_id' => $employeeId]);

            // 👇 ESTRUCTURA PARA EL CORREO: Agrupar todos los rechazos por fecha
            $rejectionData = [];
            // 👆

            foreach ($changes as $change) {
                $date                 = $change['date'];
                $itemType             = $change['item_type'];
                $itemIndex            = $change['item_index'];
                $newStatus            = strtolower($change['status']);
                $rejectionReason      = ($newStatus === 'rejected') ? ($change['rejection_reason'] ?? 'Sin especificar') : null;
                $dailyActivityIndex = array_search($date, array_column($dailyActivities, 'date'));

                if ($dailyActivityIndex !== false) {
                    $dailyActivity = &$dailyActivities[$dailyActivityIndex];
                    $activityType  = $dailyActivity['activity_type'] ?? null;
                    $oldStatus     = ($itemType === 'activity') ? strtolower($dailyActivity['activity_status'] ?? 'under_review') : strtolower(($dailyActivity[$itemType][$itemIndex]['status'] ?? 'under_review'));

                    // --- VALIDACIÓN DE SALDO DE VACACIONES (MANTENIDA) ---
                    if ($activityType === 'VAC' && $itemType === 'activity' && $newStatus === 'approved' && $oldStatus !== 'approved') {
                        if ($balance->vacation_days_available <= 0) {
                            DB::rollback();
                            return response()->json(['success' => false, 'message' => 'No hay días de vacaciones disponibles para aprobar la actividad del día ' . $date . '.'], 422);
                        }
                    }

                    // ❌ SE ELIMINA LA VALIDACIÓN DE SALDO DE DESCANSOS ('D')

                    $tempUpdated = false;

                    if ($itemType === 'activity') {
                        // REGLAS: Se mantiene la lógica de bloqueo para no degradar Aprobado
                        $canUpdate = ($newStatus === 'rejected') ? ($isReviewer || $isApprover) : (($newStatus === 'reviewed' && $isReviewer) || ($newStatus === 'approved' && $isApprover) || ($newStatus === 'under_review' && ($isReviewer || $isApprover)));
                        if ($oldStatus === 'approved' && ($newStatus === 'reviewed' || $newStatus === 'under_review')) {$canUpdate = false;}

                        if ($canUpdate && $oldStatus !== $newStatus) {
                            $dailyActivity['activity_status']  = ucfirst($newStatus);
                            $dailyActivity['rejection_reason'] = $rejectionReason;
                            $tempUpdated                       = true;

                            // 👇 REGISTRAR RECHAZO PARA CORREO (Activity)
                            if ($newStatus === 'rejected') {
                                $itemTypeLabel = 'Actividad Principal (' . ($activityType ?: 'N') . ')';

                                $rejectionData[$date][] = [
                                    'item_type'  => $itemType,
                                    'item_index' => null,
                                    'label'      => $itemTypeLabel,
                                    'reason'     => $rejectionReason,
                                ];
                            }
                            // 👆
                        }

                    } else if (isset($dailyActivity[$itemType]) && is_array($dailyActivity[$itemType]) && isset($dailyActivity[$itemType][$itemIndex])) {
                        $item      = &$dailyActivity[$itemType][$itemIndex];
                        // REGLAS: Se mantiene la lógica de bloqueo para no degradar Aprobado
                        $canUpdate = ($newStatus === 'rejected') ? ($isReviewer || $isApprover) : (($newStatus === 'reviewed' && $isReviewer) || ($newStatus === 'approved' && $isApprover) || ($newStatus === 'under_review' && ($isReviewer || $isApprover)));
                        if ($oldStatus === 'approved' && ($newStatus === 'reviewed' || $newStatus === 'under_review')) {$canUpdate = false;}

                        if ($canUpdate && $oldStatus !== $newStatus) {
                            $item['status']              = ucfirst($newStatus);
                            $item['rejection_reason']    = $rejectionReason;
                            $tempUpdated                 = true;

                            // 👇 REGISTRAR RECHAZO PARA CORREO (Sub-item)
                            if ($newStatus === 'rejected') {
                                $itemTypeLabel = $this->getItemTypeLabel($itemType);

                                // Para servicios, mostrar el nombre del servicio si está disponible
                                if ($itemType === 'services_list' && isset($item['service_name'])) {
                                    $itemTypeLabel .= ' - ' . $item['service_name'];
                                }
                                // Para bonos, mostrar el tipo si está disponible
                                elseif (($itemType === 'food_bonuses' || $itemType === 'field_bonuses') && isset($item['bonus_type'])) {
                                    $baseLabel = $this->getItemTypeLabel($itemType);
                                    $bonusType = $item['bonus_type'] ?? 'Genérico';
                                    if ($bonusType !== $baseLabel) {
                                        $itemTypeLabel .= ' - ' . $bonusType;
                                    }
                                }

                                $rejectionData[$date][] = [
                                    'item_type'  => $itemType,
                                    'item_index' => $itemIndex,
                                    'label'      => $itemTypeLabel,
                                    'reason'     => $rejectionReason,
                                ];
                            }
                            // 👆

                        }

                        unset($item);
                    }

                    // --- AJUSTE DE SALDOS ---
                    if ($tempUpdated) {
                        $updated = true;
                        // Lógica VACACIONES (MANTENIDA)
                        if ($activityType === 'VAC' && $itemType === 'activity') {
                            if ($newStatus === 'approved' && $oldStatus !== 'approved') {$balance->decrement('vacation_days_available');} elseif ($newStatus === 'rejected' && $oldStatus === 'approved') {$balance->increment('vacation_days_available');}
                            $balance->save();
                        }

                        // ❌ SE ELIMINA LÓGICA DESCANSOS (D)

                    }

                    $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);
                    unset($dailyActivity);
                }
            }

            if ($updated) {
                $log->daily_activities = $dailyActivities;
                $log->save();
            }

            // 👇 ENVIAR CORREOS DE NOTIFICACIÓN POR RECHAZOS - CAMBIO CLAVE
            $rejectionOccurred = ! empty($rejectionData);
            if ($rejectionOccurred) {
                $this->sendRejectionEmails($employeeId, $rejectionData, $userId);
            }
            // 👆

            // Actualizar status del log (reviewed_at, approved_at)
            $this->updateLogStatus($log, 'approved', $userId);
            $this->updateLogStatus($log, 'reviewed', $userId);

            DB::commit();

            return response()->json([
                'success'           => true,
                'message'           => 'Estados actualizados correctamente.',
                'updated'           => $updated,
                'new_balances'      => $this->getEmployeeBalances($employeeId),
                'rejections_sent' => $rejectionOccurred, // <-- Bandera enviada al frontend
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating multiple item statuses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene la etiqueta del tipo de ítem para el correo
     */
    private function getItemTypeLabel($itemType)
        {
        $labels = [
            'activity'      => 'Actividad Principal',
            'food_bonuses'  => 'Bono de Comida',
            'field_bonuses' => 'Bono de Campo',
            'services_list' => 'Servicio',
        ];

        return $labels[$itemType] ?? $itemType;
    }

    /**
     * Envía correos de notificación por rechazos - MEJORADO CON INFORMACIÓN DETALLADA
     */
    private function sendRejectionEmails($employeeId, array $rejectionData, $rejectedByUserId)
    {
        try {
            $employee       = Employee::find($employeeId);
            $rejectedByUser = \App\Models\Auth\User::find($rejectedByUserId);

            // 1. Verificar si el empleado tiene un email válido para recibir notificaciones
            if (! $employee || ! $employee->getRecipientEmailAttribute()) {
                Log::warning('MAIL_RECHAZO_FALLO: Empleado ' . ($employee ? $employee->full_name : $employeeId) . ' no tiene email válido para notificar.', ['employee_id' => $employeeId]);
                return;
            }

            $rejectedByName = $rejectedByUser ? ($rejectedByUser->full_name ?? $rejectedByUser->name) : 'Sistema ERP';

            // 2. Iterar sobre cada fecha (grupo de rechazo)
            foreach ($rejectionData as $date => $itemsRejected) {
                $formattedDate = Carbon::parse($date)->format('d/m/Y');

                // Obtenemos los ítems rechazados con su información y motivo individual
                $rejectedItemsWithDetails = $this->getRejectedItemsWithDetails($itemsRejected, $employeeId, $date);

                // Unificamos las razones en un texto principal (para el cuerpo del correo)
                $uniqueReasons = collect($rejectedItemsWithDetails)->pluck('rejection_reason')->filter()->unique()->implode(' | ');
                $mainReason    = ! empty($uniqueReasons) ? $uniqueReasons : 'Sin motivo especificado';

                Mail::to($employee->getRecipientEmailAttribute())
                    ->send(new DayRejectedMail(
                        $employee->full_name,
                        $formattedDate,
                        $mainReason, // Este motivo se usa en el header del correo
                        $rejectedItemsWithDetails,
                        $rejectedByName
                    ));

                Log::info('MAIL_RECHAZO_ENVIO: Correo de rechazo enviado con detalles', [
                    'employee' => $employee->full_name,
                    'email'    => $employee->getRecipientEmailAttribute(),
                    'date'     => $formattedDate,
                    'items'    => count($rejectedItemsWithDetails),
                    'reasons'  => $uniqueReasons,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('MAIL_RECHAZO_ERROR_CRITICO: Error al enviar correo de rechazo: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene información detallada de los elementos rechazados
     * * @param array $rejectedItemsByDay Contiene ['item_type', 'item_index', 'label', 'reason']
     */
    private function getRejectedItemsWithDetails(array $rejectedItemsByDay, int $employeeId, string $date)
    {
        $detailedItems = [];

        // Obtener el work log para esta fecha
        $monthAndYear = Carbon::parse($date)->format('Y-m');
        $workLog      = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
            ->where('month_and_year', $monthAndYear)
            ->first();

        // Si no hay log, devolvemos un array con información mínima
        if (! $workLog) {
            return collect($rejectedItemsByDay)->map(function ($item) {
                return [
                    'type'             => $item['label'] ?? 'Ítem desconocido',
                    'description'      => 'Log mensual no encontrado',
                    'details'          => null,
                    'rejection_reason' => $item['reason'] ?? 'Sin motivo especificado',
                ];
            })->toArray();
        }

        $dailyActivities = collect($workLog->daily_activities);
        $dailyActivity   = $dailyActivities->firstWhere('date', $date);

        // Si no hay actividad para el día, devolvemos un array con información mínima
        if (! $dailyActivity) {
            return collect($rejectedItemsByDay)->map(function ($item) {
                return [
                    'type'             => $item['label'] ?? 'Ítem desconocido',
                    'description'      => 'Actividad diaria no encontrada',
                    'details'          => null,
                    'rejection_reason' => $item['reason'] ?? 'Sin motivo especificado',
                ];
            })->toArray();
        }

        // Recorremos los ítems que fueron rechazados (provienen de updateMultipleStatuses)
        foreach ($rejectedItemsByDay as $rejectedItemInfo) {
            $itemType  = $rejectedItemInfo['item_type'];
            $itemIndex = $rejectedItemInfo['item_index'];
            $itemLabel = $rejectedItemInfo['label'];
            $reason    = $rejectedItemInfo['reason'];

            $detailedItem = [
                'type'             => $itemLabel, // Ej: Actividad Principal (B) o Bono de Comida - Desayuno
                'description'      => null,
                'details'          => null,
                'rejection_reason' => $reason,
            ];

            // Para actividad principal
            if ($itemType === 'activity') {
                $activityType = $dailyActivity['activity_type'] ?? 'N';

                // AJUSTE 1: Unimos la descripción a la etiqueta principal para mayor cohesión.
                $activityDesc                        = $this->getActivityTypeDescription($activityType);
                $detailedItem['type']                = 'Actividad: ' . $activityDesc;
                $detailedItem['description']         = null; // Eliminamos la descripción separada

                // Los detalles adicionales (proyecto, work_description) van en 'details'
                $simpleActivities = ['D', 'VAC', 'M', 'PE', 'A', 'N'];
                if (! in_array($activityType, $simpleActivities)) {
                    if (isset($dailyActivity['activity_details'])) {
                        $detailedItem['details'] = 'Detalles: ' . $dailyActivity['activity_details'];
                    } elseif (isset($dailyActivity['project_name'])) {
                        $detailedItem['details'] = 'Proyecto: ' . $dailyActivity['project_name'];
                    } elseif (isset($dailyActivity['work_description'])) {
                        $detailedItem['details'] = 'Descripción: ' . $dailyActivity['work_description'];
                    }
                }
            }

            // Para bonos de comida y campo
            elseif ($itemType === 'food_bonuses' || $itemType === 'field_bonuses') {
                // CORRECCIÓN CLAVE: Extraer solo el detalle del bono y usar solo 'Bono: ' como prefijo.
                if (isset($rejectedItemInfo['label'])) {
                    $itemLabel = $rejectedItemInfo['label'];
                    $baseFood  = $this->getItemTypeLabel('food_bonuses');  // 'Bono de Comida'
                    $baseField = $this->getItemTypeLabel('field_bonuses'); // 'Bono de Campo'

                    $detalle = $itemLabel;

                    // 1. Eliminar la etiqueta genérica y el separador ' - ' para obtener solo el detalle/tipo.
                    $detalle = str_replace($baseFood . ' - ', '', $detalle);
                    $detalle = str_replace($baseField . ' - ', '', $detalle);

                    // 2. Si el detalle es idéntico a la etiqueta base (caso Bono de Comida - Bono de Comida), usar solo la base.
                    if ($detalle === $baseFood || $detalle === $baseField) {
                        $detalle = $detalle; // Se deja el nombre del bono (ej: "Bono de Comida")
                    }

                    // 3. Crear el formato final: Bono: [Detalle del Bono]
                    $detailedItem['type'] = 'Bono: ' . $detalle;
                }
                $detailedItem['description'] = null;
                $detailedItem['details']     = null;
            }

            // Para servicios (se mantiene el formato Servicio: [Nombre])
            elseif ($itemType === 'services_list') {
                // AJUSTE 3: Hacemos la etiqueta más concisa para Servicios.
                if (isset($rejectedItemInfo['label'])) {
                    $detailedItem['type'] = str_replace('Servicio - ', 'Servicio: ', $rejectedItemInfo['label']);
                }
                $detailedItem['description'] = null;
                $detailedItem['details']     = null;
            }

            $detailedItems[] = $detailedItem;
        }

        return $detailedItems;
    }

    /**
     * Obtiene la descripción completa del tipo de actividad
     */
    private function getActivityTypeDescription($activityType)
    {
        $descriptions = [
            'B'   => 'Trabajo en base',
            'P'   => 'Pozo - Trabajo en pozo',
            'TC'  => 'Trabajo en Casa',
            'C'   => 'Trabajo por comisión',
            'V'   => 'Viaje de trabajo',
            'E'   => 'Entrenamiento',
            'D'   => 'Descanso',
            'VAC' => 'Vacaciones',
            'M'   => 'Médico',
            'PE'  => 'Permiso',
            'A'   => 'Ausencia',
            'N'   => 'Sin Actividad Registrada',
        ];

        return $descriptions[$activityType] ?? 'Actividad tipo ' . $activityType;
    }
}
