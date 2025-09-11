<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use App\Models\RecursosHumanos\LoadChart\LoadChartAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * Obtiene los IDs de los empleados que el usuario actual puede revisar o aprobar.
     *
     * @return \Illuminate\Support\Collection
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
     * Reglas:
     * 1. Si cualquier elemento está RECHAZADO → day_status = 'rejected'
     * 2. Si hay elementos PENDIENTES → day_status = 'pending'
     * 3. Si TODOS los elementos están APROBADOS → day_status = 'approved'
     * 4. Si hay elementos REVISADOS (y posiblemente APROBADOS, sin pendientes ni rechazados) → day_status = 'reviewed'
     */
    private function calculateDayStatus($dailyActivity)
    {
        $hasRejected = false;
        $hasPending = false;
        $hasReviewed = false;
        $hasApproved = false;
        $totalItems = 0;
        $approvedItems = 0;
        $reviewedItems = 0;

        // Verificar el estado de la actividad principal (solo si existe actividad)
        if (isset($dailyActivity['activity_type']) &&
            !empty($dailyActivity['activity_type']) &&
            $dailyActivity['activity_type'] !== 'N') {

            $totalItems++;
            $activityStatus = strtolower($dailyActivity['activity_status'] ?? 'pending');

            switch ($activityStatus) {
                case 'rejected':
                    $hasRejected = true;
                    break;
                case 'pending':
                    $hasPending = true;
                    break;
                case 'reviewed':
                    $hasReviewed = true;
                    $reviewedItems++;
                    break;
                case 'approved':
                    $hasApproved = true;
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
                    $itemStatus = strtolower($item['status'] ?? 'pending');

                    switch ($itemStatus) {
                        case 'rejected':
                            $hasRejected = true;
                            break;
                        case 'pending':
                            $hasPending = true;
                            break;
                        case 'reviewed':
                            $hasReviewed = true;
                            $reviewedItems++;
                            break;
                        case 'approved':
                            $hasApproved = true;
                            $approvedItems++;
                            break;
                    }
                }
            }
        }

        // Si no hay elementos registrados, el estado es 'pending' por defecto
        if ($totalItems === 0) {
            return 'pending';
        }

        // Aplicar la lógica de prioridad según las reglas
        // 1. Si cualquier elemento está RECHAZADO → day_status = 'rejected'
        if ($hasRejected) {
            return 'rejected';
        }

        // 2. Si hay elementos PENDIENTES → day_status = 'pending'
        if ($hasPending) {
            return 'pending';
        }

        // 3. Si TODOS los elementos están APROBADOS → day_status = 'approved'
        if ($approvedItems === $totalItems) {
            return 'approved';
        }

        // 4. Si hay elementos REVISADOS (y posiblemente APROBADOS, sin pendientes ni rechazados) → day_status = 'reviewed'
        if ($hasReviewed || ($reviewedItems + $approvedItems === $totalItems && $reviewedItems > 0)) {
            return 'reviewed';
        }

        // Por defecto, si no encaja en ninguna categoría anterior
        return 'pending';
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

    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        if (!$fortnightlyConfig) {
            $fortnightlyConfig = $this->createDefaultFortnightlyConfig($currentYear, $currentMonth);
        }

        $monthlyDays = $this->getMonthlyDaysWithFortnights($currentYear, $currentMonth, $fortnightlyConfig);

        // Obtener solo los IDs de los empleados asignados al usuario actual
        $assignedEmployeeIds = $this->getAssignedEmployeeIds();

        $employees = Employee::with(['employeeMonthlyWorkLogs' => function ($query) use ($currentMonth, $currentYear) {
            $query->where('month_and_year', Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m'));
        }])
            ->whereIn('id', $assignedEmployeeIds) // Filtrar por los IDs asignados
            ->get();

        $workLogsData = [];
        foreach ($employees as $employee) {
            $log = $employee->employeeMonthlyWorkLogs->first();
            if ($log && $log->daily_activities) {
                // Actualizar day_status para todas las actividades
                $log->daily_activities = $this->updateDayStatusForAllActivities($log->daily_activities);
                $log->save();
            }
            if ($log) {
                $workLogsData[] = [
                    'employee_id' => $employee->id,
                    'daily_activities' => $log->daily_activities,
                    'reviewed_at' => $log->reviewed_at,
                    'approved_at' => $log->approved_at,
                ];
            }
        }
        $canSeeAmounts = \App\Helpers\PermissionHelper::hasDirectPermission('ver_montos');
        $loadChartAssignments = LoadChartAssignment::whereIn('employee_id', $assignedEmployeeIds)->get();
        $userPermissions = [
            'is_reviewer' => $loadChartAssignments->contains('reviewer_id', auth()->id()),
            'is_approver' => $loadChartAssignments->contains('approver_id', auth()->id()),
        ];

        return view('modulos.recursoshumanos.sistemas.loadchart.approval', compact(
            'employees',
            'workLogsData',
            'fortnightlyConfig',
            'monthlyDays',
            'currentMonth',
            'currentYear',
            'canSeeAmounts',
            'loadChartAssignments',
            'userPermissions'
        ));
    }

    private function getMonthlyDaysWithFortnights($year, $month, $fortnightlyConfig)
    {
        $q1Start = Carbon::parse($fortnightlyConfig->q1_start);
        $q1End = Carbon::parse($fortnightlyConfig->q1_end);
        $q2Start = Carbon::parse($fortnightlyConfig->q2_start);
        $q2End = Carbon::parse($fortnightlyConfig->q2_end);

        $startDate = $q1Start->copy();
        $endDate = $q2End->copy();

        $monthlyDays = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $isQuincena1 = $date >= $q1Start && $date <= $q1End;
            $isQuincena2 = $date >= $q2Start && $date <= $q2End;
            $isCurrentMonth = $date->month == $month;

            $monthlyDays[] = [
                'day' => $date->day,
                'date' => $date->copy()->format('Y-m-d'),
                'day_name' => $date->locale('es')->shortDayName,
                'is_quincena_1' => $isQuincena1,
                'is_quincena_2' => $isQuincena2,
                'is_working_day' => $isQuincena1 || $isQuincena2,
                'is_current_month' => $isCurrentMonth,
                'month' => $date->month,
            ];
        }

        return $monthlyDays;
    }

    private function createDefaultFortnightlyConfig($year, $month)
    {
        $firstDay = Carbon::createFromDate($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $fifteenthDay = Carbon::createFromDate($year, $month, min(15, $lastDay->day));
        $sixteenthDay = $fifteenthDay->copy()->addDay();

        if ($sixteenthDay->month !== $month) {
            $sixteenthDay = $lastDay->copy();
        }

        return FortnightlyConfig::create([
            'year' => $year,
            'month' => $month,
            'q1_start' => $firstDay,
            'q1_end' => $fifteenthDay,
            'q2_start' => $sixteenthDay,
            'q2_end' => $lastDay,
        ]);
    }

    public function checkUpdates(Request $request)
    {
        try {
            $request->validate([
                'last_update' => 'required|date',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020|max:2030',
            ]);

            $lastUpdate = Carbon::parse($request->last_update);
            $month = $request->month;
            $year = $request->year;

            // Obtener los IDs de empleados asignados al usuario actual
            $assignedEmployeeIds = $this->getAssignedEmployeeIds();

            // Verificar si hay registros modificados después de last_update
            $hasUpdates = EmployeeMonthlyWorkLog::whereIn('employee_id', $assignedEmployeeIds)
                ->where('month_and_year', Carbon::createFromDate($year, $month, 1)->format('Y-m'))
                ->where(function($query) use ($lastUpdate) {
                    $query->where('updated_at', '>', $lastUpdate)
                        ->orWhere('created_at', '>', $lastUpdate);
                })
                ->exists();

            return response()->json([
                'success' => true,
                'has_updates' => $hasUpdates,
                'message' => $hasUpdates ? 'Hay actualizaciones disponibles' : 'No hay actualizaciones'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error checking updates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al verificar actualizaciones',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getApprovalData($year, $month)
    {
        try {
            if ($year < 2020 || $year > 2030 || $month < 1 || $month > 12) {
                return response()->json(['error' => 'Año o mes inválido'], 400);
            }

            $fortnightlyConfig = FortnightlyConfig::where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$fortnightlyConfig) {
                $fortnightlyConfig = $this->createDefaultFortnightlyConfig($year, $month);
            }

            $monthlyDays = $this->getMonthlyDaysWithFortnights($year, $month, $fortnightlyConfig);

            // Obtener solo los IDs de los empleados asignados al usuario actual
            $assignedEmployeeIds = $this->getAssignedEmployeeIds();

            $employees = Employee::with(['employeeMonthlyWorkLogs' => function ($query) use ($month, $year) {
                $query->where('month_and_year', Carbon::createFromDate($year, $month, 1)->format('Y-m'));
            }])
                ->whereIn('id', $assignedEmployeeIds) // Filtrar por los IDs asignados
                ->select('id', 'full_name', 'employee_number', 'position')
                ->get();

            $workLogsData = [];
            foreach ($employees as $employee) {
                $log = $employee->employeeMonthlyWorkLogs->first();
                if ($log && $log->daily_activities) {
                    // Actualizar day_status para todas las actividades
                    $log->daily_activities = $this->updateDayStatusForAllActivities($log->daily_activities);
                    $log->save();
                }

                if ($log) {
                    $workLogsData[] = [
                        'employee_id' => $employee->id,
                        'daily_activities' => $log->daily_activities ?? [],
                        'reviewed_at' => $log->reviewed_at,
                        'approved_at' => $log->approved_at,
                    ];
                } else {
                    $workLogsData[] = [
                        'employee_id' => $employee->id,
                        'daily_activities' => [],
                        'reviewed_at' => null,
                        'approved_at' => null,
                    ];
                }
            }
            $canSeeAmounts = \App\Helpers\PermissionHelper::hasDirectPermission('ver_montos');
            $loadChartAssignments = LoadChartAssignment::whereIn('employee_id', $assignedEmployeeIds)->get();
            $userPermissions = [
                'is_reviewer' => $loadChartAssignments->contains('reviewer_id', auth()->id()),
                'is_approver' => $loadChartAssignments->contains('approver_id', auth()->id()),
            ];

            return response()->json([
                'success' => true,
                'employees' => $employees,
                'workLogsData' => $workLogsData,
                'fortnightlyConfig' => $fortnightlyConfig,
                'monthlyDays' => $monthlyDays,
                'currentMonth' => $month,
                'currentYear' => $year,
                'canSeeAmounts' => $canSeeAmounts,
                'loadChartAssignments' => $loadChartAssignments,
                'userPermissions' => $userPermissions,
                'message' => "Datos cargados para {$this->getMonthName($month)} {$year}",
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading approval data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar los datos del mes',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getMonthName($month)
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        return $months[$month] ?? 'Mes desconocido';
    }

    /**
     * Actualiza masivamente el estado de revisión o aprobación para los ítems de una quincena.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateApprovalStatus(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020|max:2030',
                'status' => 'required|in:reviewed,approved',
                'fortnight' => 'required|in:quincena1,quincena2',
            ]);

            $employeeId = $request->employee_id;
            $month = $request->month;
            $year = $request->year;
            $newStatus = strtolower($request->status);
            $fortnight = $request->fortnight;
            $userId = auth()->id();

            $assignment = LoadChartAssignment::where('employee_id', $employeeId)
                ->where(function ($query) use ($userId) {
                    $query->where('reviewer_id', $userId)
                        ->orWhere('approver_id', $userId);
                })
                ->first();

            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado. No tiene permisos para modificar el estado de este empleado.'], 403);
            }

            $isReviewer = $assignment->reviewer_id === $userId;
            $isApprover = $assignment->approver_id === $userId;

            if ($newStatus === 'reviewed' && !$isReviewer) {
                return response()->json(['success' => false, 'message' => 'No tiene permisos para revisar este registro.'], 403);
            }

            if ($newStatus === 'approved' && !$isApprover) {
                return response()->json(['success' => false, 'message' => 'No tiene permisos para aprobar este registro.'], 403);
            }

            $monthAndYear = Carbon::createFromDate($year, $month, 1)->format('Y-m');
            $workLog = EmployeeMonthlyWorkLog::firstOrCreate(
                ['employee_id' => $employeeId, 'month_and_year' => $monthAndYear],
                ['user_id' => $userId, 'daily_activities' => []]
            );

            $fortnightlyConfig = FortnightlyConfig::where('year', $year)->where('month', $month)->first();
            if (!$fortnightlyConfig) {
                $fortnightlyConfig = $this->createDefaultFortnightlyConfig($year, $month);
            }

            $startDate = $fortnight === 'quincena1' ? Carbon::parse($fortnightlyConfig->q1_start) : Carbon::parse($fortnightlyConfig->q2_start);
            $endDate = $fortnight === 'quincena1' ? Carbon::parse($fortnightlyConfig->q1_end) : Carbon::parse($fortnightlyConfig->q2_end);

            $dailyActivities = collect($workLog->daily_activities);
            $updated = false;

            $dailyActivities = $dailyActivities->map(function ($dailyActivity) use ($startDate, $endDate, $newStatus, $isReviewer, $isApprover, &$updated) {
                $activityDate = Carbon::parse($dailyActivity['date']);
                if ($activityDate->between($startDate, $endDate)) {
                    // Lógica de actualización para la actividad principal
                    $currentActivityStatus = strtolower($dailyActivity['activity_status'] ?? 'pending');
                    $dailyActivity = $this->updateItemStatus($dailyActivity, 'activity_status', $currentActivityStatus, $newStatus, $isReviewer, $isApprover, $updated);

                    // Lógica de actualización para los sub-ítems
                    $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
                    foreach ($itemTypes as $type) {
                        if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                            $dailyActivity[$type] = array_map(function ($item) use ($newStatus, $isReviewer, $isApprover, &$updated) {
                                $currentItemStatus = strtolower($item['status'] ?? 'pending');
                                return $this->updateItemStatus($item, 'status', $currentItemStatus, $newStatus, $isReviewer, $isApprover, $updated);
                            }, $dailyActivity[$type]);
                        }
                    }
                }
                // Recalcular el day_status después de las modificaciones
                $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);
                return $dailyActivity;
            })->toArray();

            if ($updated) {
                $workLog->daily_activities = $dailyActivities;
                $workLog->save();
            }

            // Actualizar el estado general del log si todos los ítems están revisados/aprobados
            $this->updateLogStatus($workLog, $newStatus, $userId);

            return response()->json([
                'success' => true,
                'message' => "Estado de la {$fortnight} actualizado a '{$newStatus}' correctamente.",
                'data' => [
                    'employee_id' => $employeeId,
                    'status' => $newStatus,
                    'fortnight' => $fortnight,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating approval status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el estado de aprobación',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Función auxiliar para actualizar el estado de un ítem individual (actividad, bono, etc.).
     */
    private function updateItemStatus($item, $statusKey, $currentStatus, $newStatus, $isReviewer, $isApprover, &$updated)
    {
        // Lógica para el revisor
        if ($isReviewer && $newStatus === 'reviewed' && $currentStatus === 'pending') {
            $item[$statusKey] = 'Reviewed';
            $updated = true;
        }

        // Lógica para el aprobador
        if ($isApprover && $newStatus === 'approved') {
            if ($currentStatus === 'pending' || $currentStatus === 'reviewed') {
                $item[$statusKey] = 'Approved';
                $updated = true;
            }
        }
        return $item;
    }

    /**
     * Función auxiliar para actualizar los campos `reviewed_at` y `approved_at` del log.
     */
    private function updateLogStatus($workLog, $newStatus, $userId)
    {
        $all_items_processed = true;
        $all_items_approved = true;

        foreach ($workLog->daily_activities as $dailyActivity) {
            $dailyStatus = $this->calculateDayStatus($dailyActivity);
            if ($dailyStatus === 'pending' || $dailyStatus === 'rejected') {
                $all_items_processed = false;
            }
            if ($dailyStatus !== 'approved') {
                $all_items_approved = false;
            }
        }

        if ($newStatus === 'reviewed' && $all_items_processed) {
            $workLog->reviewed_at = now();
            $workLog->reviewed_by = $userId;
            $workLog->save();
        }

        if ($newStatus === 'approved' && $all_items_approved) {
            $workLog->approved_at = now();
            $workLog->approved_by = $userId;
            if (!$workLog->reviewed_at) {
                $workLog->reviewed_at = now();
                $workLog->reviewed_by = $userId;
            }
            $workLog->save();
        }
    }

    public function updateDailyItemStatus(Request $request)
    {
        try {
            $request->validate([
                'employee_id'=> 'required|integer|exists:employees,id',
                'date' => 'required|date_format:Y-m-d',
                'item_type'=> 'required|in:activity,food_bonuses,field_bonuses,services_list',
                'item_index'=> 'nullable|integer',
                'status'=> 'required|in:reviewed,approved,rejected',
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            $employeeId = $request->employee_id;
            $date = $request->date;
            $itemType = $request->item_type;
            $itemIndex = $request->item_index;
            $status = $request->status;
            $reason = $request->rejection_reason;
            $userId = auth()->id();

            // Validar permisos del usuario
            $assignment = LoadChartAssignment::where('employee_id', $employeeId)
                ->where(function ($query) use ($userId) {
                    $query->where('reviewer_id', $userId)->orWhere('approver_id', $userId);
                })
                ->first();

            if (!$assignment) {
                return response()->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            }

            $workLog = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
                ->where('month_and_year', Carbon::parse($date)->format('Y-m'))
                ->firstOrFail();

            $dailyActivities = collect($workLog->daily_activities);
            $activityIndex = $dailyActivities->search(function ($activity) use ($date) {
                return $activity['date'] === $date;
            });

            if ($activityIndex === false) {
                return response()->json(['success' => false, 'error' => 'Registro diario no encontrado.'], 404);
            }

            $dailyActivity = $dailyActivities[$activityIndex];

            $isReviewer = $assignment->reviewer_id === $userId;
            $isApprover = $assignment->approver_id === $userId;

            // Lógica para actualizar el estado del elemento
            if ($itemType === 'activity') {
                if (($status === 'reviewed' && !$isReviewer) || ($status === 'approved' && !$isApprover)) {
                    return response()->json(['success' => false, 'error' => 'Permisos insuficientes.'], 403);
                }
                $dailyActivity['activity_status'] = ucfirst($status);
                $dailyActivity['rejection_reason'] = ($status === 'rejected') ? $reason : null;
            } else {
                // Lógica para bonos y servicios
                if (!isset($dailyActivity[$itemType][$itemIndex])) {
                    return response()->json(['success' => false, 'error' => 'Elemento no encontrado.'], 404);
                }
                if (($status === 'reviewed' && !$isReviewer) || ($status === 'approved' && !$isApprover)) {
                    return response()->json(['success' => false, 'error' => 'Permisos insuficientes.'], 403);
                }
                $dailyActivity[$itemType][$itemIndex]['status'] = ucfirst($status);
                $dailyActivity[$itemType][$itemIndex]['rejection_reason'] = ($status === 'rejected') ? $reason : null;
            }

            // Actualizar day_status después de los cambios
            $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);

            // Actualizar la actividad en la colección
            $dailyActivities[$activityIndex] = $dailyActivity;

            $workLog->daily_activities = $dailyActivities->toArray();
            $workLog->save();

            return response()->json(['success' => true, 'message' => 'Estado actualizado correctamente.']);
        } catch (\Exception $e) {
            \Log::error('Error updating daily item status: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function updateMultipleStatuses(Request $request)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'employee_id' => 'required|exists:employee_monthly_work_logs,employee_id',
            'changes' => 'required|array',
            'changes.*.date' => 'required|date_format:Y-m-d',
            'changes.*.item_type' => 'required|string',
            'changes.*.item_index' => 'nullable|integer',
            'changes.*.status' => 'required|string|in:reviewed,approved,rejected',
            'changes.*.rejection_reason' => 'nullable|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $employeeId = $request->input('employee_id');
        $changes = $request->input('changes');
        $month = $request->input('month');
        $year = $request->input('year');
        $userId = auth()->id();

        // 2. Check user permissions
        $assignment = LoadChartAssignment::where('employee_id', $employeeId)
            ->where(function ($query) use ($userId) {
                $query->where('reviewer_id', $userId)->orWhere('approver_id', $userId);
            })
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado. No tiene permisos para modificar el estado de este empleado.'], 403);
        }

        // 3. Find the work log for the specified employee and month
        $monthAndYear = Carbon::createFromDate($year, $month, 1)->format('Y-m');
        $log = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
            ->where('month_and_year', $monthAndYear)
            ->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Work log not found.'], 404);
        }

        // 4. Get the daily activities array
        $dailyActivities = $log->daily_activities;

        $updated = false;

        // 5. Loop through the changes and apply them
        foreach ($changes as $change) {
            $date = $change['date'];
            $itemType = $change['item_type'];
            $itemIndex = $change['item_index'];
            $newStatus = $change['status'];
            $rejectionReason = $change['rejection_reason'] ?? null;

            // Find the correct daily activity entry by date
            $dailyActivityIndex = array_search($date, array_column($dailyActivities, 'date'));

            if ($dailyActivityIndex !== false) {
                // Use a reference to modify the array directly
                $dailyActivity = &$dailyActivities[$dailyActivityIndex];

                if ($itemType === 'activity') {
                    if (!isset($dailyActivity['activity_status']) || $dailyActivity['activity_status'] !== ucfirst($newStatus)) {
                        $dailyActivity['activity_status'] = ucfirst($newStatus);
                        $dailyActivity['rejection_reason'] = ($newStatus === 'rejected') ? $rejectionReason : null;
                        $updated = true;
                    }
                } else if (isset($dailyActivity[$itemType]) && is_array($dailyActivity[$itemType])) {
                    if (isset($dailyActivity[$itemType][$itemIndex])) {
                        $item = &$dailyActivity[$itemType][$itemIndex];
                        if ($item['status'] !== ucfirst($newStatus)) {
                            $item['status'] = ucfirst($newStatus);
                            $item['rejection_reason'] = ($newStatus === 'rejected') ? $rejectionReason : null;
                            $updated = true;
                        }
                    }
                }

                // Actualizar day_status después de cambios
                $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);

                // Unset the reference to avoid unexpected behavior
                unset($dailyActivity);
            }
        }

        // 6. Save the updated JSON back to the database if changes were made
        if ($updated) {
            $log->daily_activities = $dailyActivities;
            $log->save();
        }

        return response()->json(['success' => true, 'message' => 'Estados actualizados correctamente.', 'updated' => $updated]);
    }
}
