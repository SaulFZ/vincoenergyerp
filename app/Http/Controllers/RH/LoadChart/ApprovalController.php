<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Mail\RH\LoadChart\DayRejectedMail;
use App\Models\RH\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RH\LoadChart\EmployeeVacationBalance;
use App\Models\RH\LoadChart\FortnightlyConfig;
use App\Models\RH\LoadChart\LoadChartAssignment;
use App\Models\RH\OrgManagement\Area;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApprovalController extends Controller
{
    /**
     * Devuelve los IDs de empleados asignados al usuario actual
     * ya sea como reviewer o approver.
     */
    private function getAssignedEmployeeIds()
    {
        $userId = auth()->id();

        return LoadChartAssignment::where('reviewer_id', $userId)
            ->orWhere('approver_id', $userId)
            ->pluck('employee_id')
            ->unique()
            ->values();
    }

    private function calculateDayStatus($dailyActivity)
    {
        $hasRejected = false;
        $hasUnderReview = false;
        $hasReviewed = false;
        $hasApproved = false;
        $totalItems = 0;
        $approvedItems = 0;
        $reviewedItems = 0;

        if (isset($dailyActivity['activity_type']) && !empty($dailyActivity['activity_type']) && $dailyActivity['activity_type'] !== 'N') {
            $totalItems++;
            $activityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');
            $this->incrementStatusCounters($activityStatus, $hasRejected, $hasUnderReview, $hasReviewed, $hasApproved, $reviewedItems, $approvedItems);
        }

        if (isset($dailyActivity['activity_type_vespertina']) && !empty($dailyActivity['activity_type_vespertina']) && $dailyActivity['activity_type_vespertina'] !== 'N') {
            $totalItems++;
            $vStatus = strtolower($dailyActivity['activity_status_vespertina'] ?? 'under_review');
            $this->incrementStatusCounters($vStatus, $hasRejected, $hasUnderReview, $hasReviewed, $hasApproved, $reviewedItems, $approvedItems);
        }

        $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
        foreach ($itemTypes as $type) {
            if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                foreach ($dailyActivity[$type] as $item) {
                    $totalItems++;
                    $itemStatus = strtolower($item['status'] ?? 'under_review');
                    $this->incrementStatusCounters($itemStatus, $hasRejected, $hasUnderReview, $hasReviewed, $hasApproved, $reviewedItems, $approvedItems);
                }
            }
        }

        if ($totalItems === 0) return 'under_review';
        if ($hasRejected) return 'rejected';
        if ($hasUnderReview) return 'under_review';
        if ($approvedItems === $totalItems) return 'approved';
        if ($hasReviewed || ($reviewedItems + $approvedItems === $totalItems && $reviewedItems > 0)) return 'reviewed';

        return 'under_review';
    }

    private function incrementStatusCounters($status, &$hasRejected, &$hasUnderReview, &$hasReviewed, &$hasApproved, &$reviewedItems, &$approvedItems)
    {
        switch ($status) {
            case 'rejected':
                $hasRejected = true;
                break;
            case 'under_review':
                $hasUnderReview = true;
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

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)->where('month', $currentMonth)->first();
        if (!$fortnightlyConfig) {
            $fortnightlyConfig = $this->createDefaultFortnightlyConfig($currentYear, $currentMonth);
        }

        $monthlyDays = $this->getMonthlyDaysWithFortnights($currentYear, $currentMonth, $fortnightlyConfig);
        $areas = Area::where('is_active', 1)->orderBy('name')->pluck('name');

        $positions = Employee::select('position')
            ->whereNotNull('position')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');

        // ver_filtros permite consultar todos los empleados activos.
        // La interfaz muestra inicialmente solo los asignados.
        $canSeeFilters = PermissionHelper::hasDirectPermission('ver_filtros');
        $assignedEmployeeIds = $this->getAssignedEmployeeIds();

        $employees = Employee::with([
            'employeeMonthlyWorkLogs' => function ($query) use ($currentMonth, $currentYear) {
                $query->where('month_and_year', Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m'));
            },
            'squads',
            'area',
            'department'
        ])
            ->where('employment_status', 'active')
            ->when(!$canSeeFilters, function ($query) use ($assignedEmployeeIds) {
                $query->whereIn('id', $assignedEmployeeIds);
            })
            ->select('id', 'full_name', 'employee_number', 'position', 'area_id', 'department_id', 'job_title')
            ->get();

        $workLogsData = [];
        foreach ($employees as $employee) {
            $log = $employee->employeeMonthlyWorkLogs->first();
            if ($log && $log->daily_activities) {
                $log->daily_activities = $this->updateDayStatusForAllActivities($log->daily_activities);
            }
            $workLogsData[] = [
                'employee_id' => $employee->id,
                'daily_activities' => $log->daily_activities ?? [],
                'reviewed_at' => $log->reviewed_at ?? null,
                'approved_at' => $log->approved_at ?? null
            ];
        }

        $loadChartAssignments = LoadChartAssignment::all();
        $canSeeAmounts = PermissionHelper::hasDirectPermission('ver_montos');


        $userPermissions = [
            'is_reviewer' => $loadChartAssignments->contains('reviewer_id', auth()->id()),
            'is_approver' => $loadChartAssignments->contains('approver_id', auth()->id())
        ];

        return view('modules.rh.loadchart.approval', compact(
            'employees',
            'workLogsData',
            'fortnightlyConfig',
            'monthlyDays',
            'currentMonth',
            'currentYear',
            'canSeeAmounts',
            'canSeeFilters',           // ✅ ahora lo pasamos a la vista
            'loadChartAssignments',
            'userPermissions',
            'areas',
            'positions'
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
                'year' => 'required|integer|min:2020|max:2030'
            ]);
            $lastUpdate = Carbon::parse($request->last_update);

            // Mantener el mismo alcance de lectura que en index y getApprovalData.
            $canSeeFilters = PermissionHelper::hasDirectPermission('ver_filtros');
            $assignedEmployeeIds = $this->getAssignedEmployeeIds();

            $hasUpdates = EmployeeMonthlyWorkLog::query()
                ->when(!$canSeeFilters, function ($query) use ($assignedEmployeeIds) {
                    $query->whereIn('employee_id', $assignedEmployeeIds);
                })
                ->where('month_and_year', Carbon::createFromDate($request->year, $request->month, 1)->format('Y-m'))
                ->where(function ($query) use ($lastUpdate) {
                    $query->where('updated_at', '>', $lastUpdate)->orWhere('created_at', '>', $lastUpdate);
                })
                ->exists();

            return response()->json([
                'success' => true,
                'has_updates' => $hasUpdates,
                'message' => $hasUpdates ? 'Hay actualizaciones disponibles' : 'No hay actualizaciones'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking updates: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error al verificar actualizaciones'], 500);
        }
    }

    public function getApprovalData($year, $month)
    {
        try {
            if ($year < 2020 || $year > 2030 || $month < 1 || $month > 12) {
                return response()->json(['error' => 'Año o mes inválido'], 400);
            }

            $fortnightlyConfig = FortnightlyConfig::where('year', $year)->where('month', $month)->first();
            if (!$fortnightlyConfig) {
                $fortnightlyConfig = $this->createDefaultFortnightlyConfig($year, $month);
            }

            $monthlyDays = $this->getMonthlyDaysWithFortnights($year, $month, $fortnightlyConfig);
            $canSeeAmounts = PermissionHelper::hasDirectPermission('ver_montos');
            $canSeeFilters = PermissionHelper::hasDirectPermission('ver_filtros');

            // Los IDs limitan la consulta únicamente cuando no tiene ver_filtros.
            $assignedEmployeeIds = $this->getAssignedEmployeeIds();

            // Con ver_filtros también puede consultar sin tener asignaciones propias.
            if (!$canSeeFilters && $assignedEmployeeIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'employees' => [],
                    'workLogsData' => [],
                    'fortnightlyConfig' => $fortnightlyConfig,
                    'monthlyDays' => $monthlyDays,
                    'currentMonth' => $month,
                    'currentYear' => $year,
                    'canSeeAmounts' => $canSeeAmounts,
                    'canSeeFilters' => $canSeeFilters,
                    'loadChartAssignments' => [],
                    'userPermissions' => ['is_reviewer' => false, 'is_approver' => false],
                    'message' => 'No tiene empleados asignados.',
                ]);
            }

            // Con ver_filtros se cargan todos los activos, incluidos los no asignados.
            $employeeQuery = Employee::with([
                'employeeMonthlyWorkLogs' => function ($query) use ($month, $year) {
                    $query->where('month_and_year', Carbon::createFromDate($year, $month, 1)->format('Y-m'));
                },
                'squads',
                'area',
                'department'
            ])
                ->where('employment_status', 'active')
                ->when(!$canSeeFilters, function ($query) use ($assignedEmployeeIds) {
                    $query->whereIn('id', $assignedEmployeeIds);
                })
                ->select('id', 'full_name', 'employee_number', 'position', 'area_id', 'department_id', 'job_title');

            $employees = $employeeQuery->get();

            $workLogsData = [];
            foreach ($employees as $employee) {
                $log = $employee->employeeMonthlyWorkLogs->first();
                if ($log && $log->daily_activities) {
                    $log->daily_activities = $this->updateDayStatusForAllActivities($log->daily_activities);
                }
                $workLogsData[] = [
                    'employee_id' => $employee->id,
                    'daily_activities' => $log->daily_activities ?? [],
                    'reviewed_at' => $log->reviewed_at ?? null,
                    'approved_at' => $log->approved_at ?? null
                ];
            }

            $loadChartAssignments = LoadChartAssignment::all();
            $userPermissions = [
                'is_reviewer' => $loadChartAssignments->contains('reviewer_id', auth()->id()),
                'is_approver' => $loadChartAssignments->contains('approver_id', auth()->id())
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
                'canSeeFilters' => $canSeeFilters,
                'loadChartAssignments' => $loadChartAssignments,
                'userPermissions' => $userPermissions,
                'message' => 'Datos cargados correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading approval data: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error al cargar los datos del mes'], 500);
        }
    }

    private function updateItemStatus($item, $statusKey, $currentStatus, $newStatus, $isReviewer, $isApprover, &$updated)
    {
        if ($isReviewer && $newStatus === 'reviewed' && $currentStatus === 'under_review') {
            $item[$statusKey] = 'Reviewed';
            $updated = true;
        }
        if ($isApprover && $newStatus === 'approved' && ($currentStatus === 'under_review' || $currentStatus === 'reviewed')) {
            $item[$statusKey] = 'Approved';
            $updated = true;
        }
        if ($isApprover && $newStatus === 'rejected' && $currentStatus !== 'rejected') {
            $item[$statusKey] = 'Rejected';
            $updated = true;
        }
        if (($isReviewer || $isApprover) && $newStatus === 'under_review' && $currentStatus !== 'approved' && $currentStatus !== 'rejected') {
            $item[$statusKey] = 'Under_review';
            $updated = true;
        }
        return $item;
    }

    private function updateLogStatus($workLog, $newStatus, $userId)
    {
        $all_items_reviewed = true;
        $all_items_approved = true;

        foreach ($workLog->daily_activities as $dailyActivity) {
            $dailyStatus = $this->calculateDayStatus($dailyActivity);
            if ($dailyStatus !== 'reviewed' && $dailyStatus !== 'approved') $all_items_reviewed = false;
            if ($dailyStatus !== 'approved') $all_items_approved = false;
        }

        if ($newStatus === 'reviewed' && $all_items_reviewed) {
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

    private function getEmployeeBalances(int $employeeId): array
    {
        $balance = EmployeeVacationBalance::where('employee_id', $employeeId)->first();
        return [
            'vacationDays' => $balance ? $balance->vacation_days_available : 0,
            'restDays' => $balance ? $balance->rest_days_available : 0,
        ];
    }

    public function updateApprovalStatus(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020|max:2030',
                'status' => 'required|in:reviewed,approved',
                'fortnight' => 'required|in:quincena1,quincena2,full-month',
            ]);

            $employeeId = $request->employee_id;
            $month = $request->month;
            $year = $request->year;
            $newStatus = strtolower($request->status);
            $fortnight = $request->fortnight;
            $userId = auth()->id();

            $assignment = LoadChartAssignment::where('employee_id', $employeeId)
                ->where(function ($query) use ($userId) {
                    $query->where('reviewer_id', $userId)->orWhere('approver_id', $userId);
                })
                ->first();

            if (!$assignment) {
                return response()->json(['success' => false, 'message' => 'Acceso denegado. No tiene permisos.'], 403);
            }

            $isReviewer = $assignment->reviewer_id === $userId;
            $isApprover = $assignment->approver_id === $userId;

            if ($newStatus === 'reviewed' && !$isReviewer) return response()->json(['success' => false, 'message' => 'No tiene permisos para revisar.'], 403);
            if ($newStatus === 'approved' && !$isApprover) return response()->json(['success' => false, 'message' => 'No tiene permisos para aprobar.'], 403);

            $monthAndYear = Carbon::createFromDate($year, $month, 1)->format('Y-m');
            $workLog = EmployeeMonthlyWorkLog::firstOrCreate(
                ['employee_id' => $employeeId, 'month_and_year' => $monthAndYear],
                ['user_id' => $userId, 'daily_activities' => []]
            );

            $fortnightlyConfig = FortnightlyConfig::where('year', $year)->where('month', $month)->first();
            if (!$fortnightlyConfig) $fortnightlyConfig = $this->createDefaultFortnightlyConfig($year, $month);

            if ($fortnight === 'quincena1') {
                $startDate = Carbon::parse($fortnightlyConfig->q1_start);
                $endDate = Carbon::parse($fortnightlyConfig->q1_end);
            } elseif ($fortnight === 'quincena2') {
                $startDate = Carbon::parse($fortnightlyConfig->q2_start);
                $endDate = Carbon::parse($fortnightlyConfig->q2_end);
            } else {
                $startDate = Carbon::createFromDate($year, $month, 1);
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            }

            DB::beginTransaction();
            $dailyActivities = collect($workLog->daily_activities);
            $updated = false;
            $balance = EmployeeVacationBalance::firstOrNew(['employee_id' => $employeeId]);
            $employee = Employee::find($employeeId);
            $isGuardia = stripos($employee->job_title, 'AUXILIAR PAL') !== false;

            $dailyActivities = $dailyActivities->map(function ($dailyActivity) use ($startDate, $endDate, $newStatus, $isReviewer, $isApprover, &$updated, $employeeId, $isGuardia, $balance) {
                $activityDate = Carbon::parse($dailyActivity['date']);

                if ($activityDate->between($startDate, $endDate)) {
                    $oldActivityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');
                    $oldVespertinaStatus = strtolower($dailyActivity['activity_status_vespertina'] ?? 'under_review');
                    $activityType = $dailyActivity['activity_type'] ?? 'N';
                    $vType = $dailyActivity['activity_type_vespertina'] ?? 'N';
                    $tempUpdated = false;

                    $dailyActivity = $this->updateItemStatus($dailyActivity, 'activity_status', $oldActivityStatus, $newStatus, $isReviewer, $isApprover, $tempUpdated);

                    if (isset($dailyActivity['activity_type_vespertina']) && $dailyActivity['activity_type_vespertina'] !== 'N') {
                        $dailyActivity = $this->updateItemStatus($dailyActivity, 'activity_status_vespertina', $oldVespertinaStatus, $newStatus, $isReviewer, $isApprover, $tempUpdated);
                    }

                    $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
                    foreach ($itemTypes as $type) {
                        if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                            $dailyActivity[$type] = array_map(function ($item) use ($newStatus, $isReviewer, $isApprover, &$tempUpdated) {
                                $currentItemStatus = strtolower($item['status'] ?? 'under_review');
                                $tempSubUpdated = false;
                                $item = $this->updateItemStatus($item, 'status', $currentItemStatus, $newStatus, $isReviewer, $isApprover, $tempSubUpdated);
                                if ($tempSubUpdated) $tempUpdated = true;
                                return $item;
                            }, $dailyActivity[$type]);
                        }
                    }

                    if ($tempUpdated) {
                        $updated = true;
                        $newActivityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');
                        $newVespertinaStatus = strtolower($dailyActivity['activity_status_vespertina'] ?? 'under_review');
                        $refund = 0;
                        $deduct = 0;

                        if ($activityType === 'VAC') {
                            if ($oldActivityStatus !== 'rejected' && $newActivityStatus === 'rejected') {
                                $refund += $isGuardia ? 0.5 : 1;
                            } elseif ($oldActivityStatus === 'rejected' && $newActivityStatus !== 'rejected') {
                                $deduct += $isGuardia ? 0.5 : 1;
                            }
                        }

                        if ($isGuardia && $vType === 'VAC') {
                            if ($oldVespertinaStatus !== 'rejected' && $newVespertinaStatus === 'rejected') {
                                $refund += 0.5;
                            } elseif ($oldVespertinaStatus === 'rejected' && $newVespertinaStatus !== 'rejected') {
                                $deduct += 0.5;
                            }
                        }

                        $diff = $deduct - $refund;
                        if ($diff != 0) {
                            if ($diff > 0 && $balance->vacation_days_available < $diff) {
                                throw new \Exception("No hay días de vacaciones suficientes.");
                            }
                            $balance->vacation_days_available -= $diff;
                            $balance->save();
                        }
                    }
                    $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);
                }
                return $dailyActivity;
            })->toArray();

            if ($updated) {
                $workLog->daily_activities = $dailyActivities;
                $workLog->save();
            }

            $this->updateLogStatus($workLog, $newStatus, $userId);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Estado actualizado a '{$newStatus}' correctamente.",
                'new_balances' => $this->getEmployeeBalances($employeeId),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating approval status: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error al actualizar el estado', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMultipleStatuses(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'changes' => 'required|array',
                'changes.*.date' => 'required|date_format:Y-m-d',
                'changes.*.item_type' => 'required|string',
                'changes.*.item_index' => 'nullable|integer',
                'changes.*.status' => 'required|string|in:reviewed,approved,rejected,under_review',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020|max:2030',
            ]);

            $employeeId = $request->input('employee_id');
            $changes = $request->input('changes');
            $month = $request->input('month');
            $year = $request->input('year');
            $userId = auth()->id();

            $employee = Employee::find($employeeId);
            $isGuardia = stripos($employee->job_title, 'AUXILIAR PAL') !== false;

            $assignment = LoadChartAssignment::where('employee_id', $employeeId)->where(function ($query) use ($userId) {
                $query->where('reviewer_id', $userId)->orWhere('approver_id', $userId);
            })->first();

            if (!$assignment) return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);

            $isReviewer = $assignment->reviewer_id === $userId;
            $isApprover = $assignment->approver_id === $userId;

            $monthAndYear = Carbon::createFromDate($year, $month, 1)->format('Y-m');
            $log = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)->where('month_and_year', $monthAndYear)->first();

            if (!$log) return response()->json(['success' => false, 'message' => 'Work log not found.'], 404);

            DB::beginTransaction();
            $dailyActivities = $log->daily_activities;
            $updated = false;
            $balance = EmployeeVacationBalance::firstOrNew(['employee_id' => $employeeId]);
            $rejectionData = [];

            foreach ($changes as $change) {
                $date = $change['date'];
                $itemType = $change['item_type'];
                $itemIndex = $change['item_index'];
                $newStatus = strtolower($change['status']);
                $rejectionReason = ($newStatus === 'rejected') ? ($change['rejection_reason'] ?? 'Sin especificar') : null;
                $dailyActivityIndex = array_search($date, array_column($dailyActivities, 'date'));

                if ($dailyActivityIndex !== false) {
                    $dailyActivity = &$dailyActivities[$dailyActivityIndex];
                    $activityType = $dailyActivity['activity_type'] ?? null;
                    $vType = $dailyActivity['activity_type_vespertina'] ?? null;
                    $oldStatusMat = strtolower($dailyActivity['activity_status'] ?? 'under_review');
                    $oldStatusVes = strtolower($dailyActivity['activity_status_vespertina'] ?? 'under_review');

                    $oldStatus = 'under_review';
                    if ($itemType === 'activity') $oldStatus = $oldStatusMat;
                    elseif ($itemType === 'activity_vespertina') $oldStatus = $oldStatusVes;
                    else $oldStatus = strtolower(($dailyActivity[$itemType][$itemIndex]['status'] ?? 'under_review'));

                    $tempUpdated = false;

                    if ($itemType === 'activity' || $itemType === 'activity_vespertina') {
                        $canUpdate = ($newStatus === 'rejected')
                            ? ($isReviewer || $isApprover)
                            : (($newStatus === 'reviewed' && ($isReviewer || $isApprover)) || ($newStatus === 'approved' && $isApprover) || ($newStatus === 'under_review' && ($isReviewer || $isApprover)));

                        if ($oldStatus === 'approved' && ($newStatus === 'reviewed' || $newStatus === 'under_review')) {
                            $canUpdate = false;
                        }

                        if ($canUpdate && $oldStatus !== $newStatus) {
                            if ($itemType === 'activity_vespertina') {
                                $dailyActivity['activity_status_vespertina'] = ucfirst($newStatus);
                                $dailyActivity['rejection_reason_vespertina'] = $rejectionReason;
                            } else {
                                $dailyActivity['activity_status'] = ucfirst($newStatus);
                                $dailyActivity['rejection_reason'] = $rejectionReason;
                            }

                            $newStatusMat = strtolower($dailyActivity['activity_status'] ?? 'under_review');
                            $newStatusVes = strtolower($dailyActivity['activity_status_vespertina'] ?? 'under_review');
                            $refund = 0;
                            $deduct = 0;

                            if ($activityType === 'VAC') {
                                if ($oldStatusMat !== 'rejected' && $newStatusMat === 'rejected') $refund += $isGuardia ? 0.5 : 1;
                                elseif ($oldStatusMat === 'rejected' && $newStatusMat !== 'rejected') $deduct += $isGuardia ? 0.5 : 1;
                            }
                            if ($isGuardia && $vType === 'VAC') {
                                if ($oldStatusVes !== 'rejected' && $newStatusVes === 'rejected') $refund += 0.5;
                                elseif ($oldStatusVes === 'rejected' && $newStatusVes !== 'rejected') $deduct += 0.5;
                            }

                            $diff = $deduct - $refund;
                            if ($diff != 0) {
                                if ($diff > 0 && $balance->vacation_days_available < $diff) {
                                    DB::rollback();
                                    return response()->json(['success' => false, 'message' => 'No hay días de vacaciones suficientes.'], 422);
                                }
                                $balance->vacation_days_available -= $diff;
                                $balance->save();
                            }

                            $tempUpdated = true;
                            if ($newStatus === 'rejected') {
                                $rejectionData[$date][] = [
                                    'item_type' => $itemType,
                                    'item_index' => null,
                                    'label' => ($itemType === 'activity_vespertina') ? 'Actividad Vespertina' : 'Actividad Principal',
                                    'reason' => $rejectionReason,
                                ];
                            }
                        }
                    } else if (isset($dailyActivity[$itemType]) && is_array($dailyActivity[$itemType]) && isset($dailyActivity[$itemType][$itemIndex])) {
                        $item = &$dailyActivity[$itemType][$itemIndex];
                        $canUpdate = ($newStatus === 'rejected')
                            ? ($isReviewer || $isApprover)
                            : (($newStatus === 'reviewed' && ($isReviewer || $isApprover)) || ($newStatus === 'approved' && $isApprover) || ($newStatus === 'under_review' && ($isReviewer || $isApprover)));

                        if ($oldStatus === 'approved' && ($newStatus === 'reviewed' || $newStatus === 'under_review')) $canUpdate = false;

                        if ($canUpdate && $oldStatus !== $newStatus) {
                            $item['status'] = ucfirst($newStatus);
                            $item['rejection_reason'] = $rejectionReason;
                            $tempUpdated = true;

                            if ($newStatus === 'rejected') {
                                $rejectionData[$date][] = [
                                    'item_type' => $itemType,
                                    'item_index' => $itemIndex,
                                    'label' => $this->getItemTypeLabel($itemType),
                                    'reason' => $rejectionReason,
                                ];
                            }
                        }
                        unset($item);
                    }

                    if ($tempUpdated) $updated = true;
                    $dailyActivity['day_status'] = $this->calculateDayStatus($dailyActivity);
                    unset($dailyActivity);
                }
            }

            if ($updated) {
                $log->daily_activities = $dailyActivities;
                $log->save();
            }

            $rejectionOccurred = !empty($rejectionData);
            if ($rejectionOccurred) $this->sendRejectionEmails($employeeId, $rejectionData, $userId);

            $this->updateLogStatus($log, 'approved', $userId);
            $this->updateLogStatus($log, 'reviewed', $userId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estados actualizados correctamente.',
                'updated' => $updated,
                'new_balances' => $this->getEmployeeBalances($employeeId),
                'rejections_sent' => $rejectionOccurred,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating multiple statuses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
        }
    }

    private function getItemTypeLabel($itemType)
    {
        $labels = [
            'activity' => 'Actividad Principal',
            'activity_vespertina' => 'Actividad Vespertina',
            'food_bonuses' => 'Bono de Comida',
            'field_bonuses' => 'Bono de Campo',
            'services_list' => 'Servicio',
        ];
        return $labels[$itemType] ?? $itemType;
    }

    private function sendRejectionEmails($employeeId, array $rejectionData, $rejectedByUserId)
    {
        try {
            $employee = Employee::with('user')->find($employeeId);
            $rejectedByUser = \App\Models\Auth\User::find($rejectedByUserId);
            $recipientEmail = ($employee && $employee->user && !empty($employee->user->email)) ? $employee->user->email : ($employee->personal_email ?? null);

            if (!$recipientEmail) return;

            $rejectedByName = $rejectedByUser ? ($rejectedByUser->full_name ?? $rejectedByUser->name) : 'Sistema ERP';

            foreach ($rejectionData as $date => $itemsRejected) {
                $formattedDate = Carbon::parse($date)->format('d/m/Y');
                $mainReason = collect($itemsRejected)->pluck('reason')->filter()->unique()->implode(' | ') ?: 'Sin motivo especificado';

                Mail::to($recipientEmail)->send(new DayRejectedMail($employee->full_name, $formattedDate, $mainReason, $itemsRejected, $rejectedByName));
            }
        } catch (\Exception $e) {
            Log::error('MAIL_RECHAZO_ERROR_CRITICO: ' . $e->getMessage());
        }
    }
}
