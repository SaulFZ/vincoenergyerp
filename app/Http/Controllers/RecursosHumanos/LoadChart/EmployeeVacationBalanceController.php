<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;


class EmployeeVacationBalanceController extends Controller
{
    public function index()
    {
        $vacationBalances = EmployeeVacationBalance::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'department');
        }])
            ->orderBy('employee_id')
            ->get();

        $employees = Employee::select('id', 'full_name', 'hire_date', 'department', 'employee_number')->orderBy('full_name')->get();
        $departments = $employees->pluck('department')->unique()->filter()->sort()->values();

        $vacationDaysTaken = $this->getConsolidatedVacationDaysTaken();

        return view('modulos.recursoshumanos.sistemas.loadchart.employee_vacation_balance', [
            'vacationBalances'  => $vacationBalances,
            'employees'         => $employees,
            'departments'       => $departments,
            'vacationDaysTaken' => $vacationDaysTaken,
        ]);
    }

    private function getConsolidatedVacationDaysTaken(): array
    {
        $logsWithVacations = EmployeeMonthlyWorkLog::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'department');
        }])
            ->whereNotNull('daily_activities')
            ->get();

        $consolidatedData = [];

        foreach ($logsWithVacations as $log) {
            $employee = $log->employee;
            if (! $employee) {
                continue;
            }

            $vacationActivities = $log->getVacationActivities();

            if (empty($vacationActivities)) {
                continue;
            }

            $employeeId = $employee->id;

            if (! isset($consolidatedData[$employeeId])) {
                $consolidatedData[$employeeId] = [
                    'employee_number'           => $employee->employee_number ?? 'N/A',
                    'full_name'                 => $employee->full_name,
                    'hire_date'                 => $employee->hire_date,
                    'area'                      => $employee->department,
                    'total_vacation_days_count' => 0,
                    'vacation_days_details'     => [],
                ];
            }

            $consolidatedData[$employeeId]['total_vacation_days_count'] += count($vacationActivities);
            $consolidatedData[$employeeId]['vacation_days_details'] = array_merge(
                $consolidatedData[$employeeId]['vacation_days_details'],
                $vacationActivities
            );
        }

        foreach ($consolidatedData as &$data) {
            usort($data['vacation_days_details'], function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
        }
        unset($data);

        return array_values($consolidatedData);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'           => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id',
            'rest_mode'             => 'required|string|max:15',
            'rest_days_available'   => 'required|integer',
        ], [
            'employee_id.unique'    => 'Este empleado ya tiene un balance de vacaciones registrado.',
            'employee_id.required'  => 'Debe seleccionar un empleado.',
            'employee_id.exists'    => 'El empleado seleccionado no existe.',
            'rest_mode.required'    => 'Debe seleccionar una modalidad de descanso.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        try {
            $employee           = Employee::findOrFail($request->employee_id);
            $calculatedData = $this->calculateInitialVacationData($employee);

            $dataToStore = [
                'employee_id'               => $request->employee_id,
                'vacation_days_available'   => $calculatedData['vacation_days_available'],
                'rest_days_available'       => $request->rest_days_available,
                'years_of_service'          => $calculatedData['years_of_service'],
                'rest_mode'                 => $request->rest_mode,
                'work_rest_cycle_counter'   => $calculatedData['work_rest_cycle_counter'],
                'last_activity_date'        => $calculatedData['last_activity_date'],
            ];

            EmployeeVacationBalance::create($dataToStore);

            return response()->json(['success' => true, 'message' => '¡Balance de vacaciones creado exitosamente!']);
        } catch (\Exception $e) {
            Log::error("Error al crear balance: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado al crear el balance: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $balance = EmployeeVacationBalance::with('employee')->findOrFail($id);

        if ($balance->employee) {
            $this->updateYearsOfService($balance);
            $balance->refresh();
        }

        return response()->json($balance);
    }

    public function update(Request $request, $id)
    {
        $balance = EmployeeVacationBalance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee_id'           => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id,' . $id,
            'vacation_days_available' => 'required|integer|min:0',
            'rest_days_available'     => 'required|integer',
            'rest_mode'                 => 'required|string|max:15',
        ], [
            'employee_id.required'      => 'El ID del empleado es obligatorio.',
            'employee_id.exists'        => 'El empleado seleccionado no existe.',
            'employee_id.unique'        => 'Este empleado ya tiene un balance de vacaciones registrado en otro registro.',
            'rest_mode.required'        => 'La modalidad de descanso es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        try {
            $employee = Employee::findOrFail($request->employee_id);
            $currentYearsOfService = Carbon::parse($employee->hire_date)->diffInYears(Carbon::now());

            $balance->update([
                'employee_id'               => $request->employee_id,
                'vacation_days_available' => $request->vacation_days_available,
                'rest_days_available'     => $request->rest_days_available,
                'years_of_service'          => $currentYearsOfService,
                'rest_mode'                 => $request->rest_mode,
            ]);

            return response()->json(['success' => true, 'message' => '¡Balance de vacaciones actualizado exitosamente!']);
        } catch (\Exception $e) {
            Log::error("Error al actualizar balance: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado al actualizar el balance: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $balance = EmployeeVacationBalance::findOrFail($id);
            $balance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Balance de vacaciones eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el balance: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:AVAILABLE,TAKEN',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'departments' => 'nullable|array',
            'departments.*' => 'string',
            'employees' => 'nullable|array',
            'employees.*' => 'exists:employees,id',
            'status_filter' => 'required_if:report_type,TAKEN|nullable|array',
            'status_filter.*' => 'in:Approved,Reviewed,Under_Review,Rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los filtros del reporte.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reportType = $request->report_type;
        $reportData = [];
        $title = '';
        $summaryByDepartment = [];

        try {
            if ($reportType === 'AVAILABLE') {
                $title = 'Reporte de Días de Vacaciones Disponibles';
                $query = EmployeeVacationBalance::query();

                $query->when($request->filled('departments'), function ($q) use ($request) {
                    $q->whereHas('employee', function ($q2) use ($request) {
                        $q2->whereIn('department', $request->departments);
                    });
                });

                $query->when($request->filled('employees'), function ($q) use ($request) {
                    $q->whereIn('employee_id', $request->employees);
                });

                $balances = $query->with(['employee' => function ($q) {
                    $q->select('id', 'full_name', 'employee_number', 'department');
                }])->get();

                foreach ($balances as $balance) {
                    $department = $balance->employee->department ?? 'Sin Departamento';

                    if (!isset($summaryByDepartment[$department])) {
                        $summaryByDepartment[$department] = [
                            'total_employees' => 0,
                            'total_vacation_days' => 0,
                        ];
                    }

                    $summaryByDepartment[$department]['total_employees']++;
                    $summaryByDepartment[$department]['total_vacation_days'] += $balance->vacation_days_available;

                    $reportData[] = [
                        'employee_number' => $balance->employee->employee_number ?? 'N/A',
                        'full_name' => $balance->employee->full_name ?? 'Empleado Desconocido',
                        'area' => $department,
                        'vacation_days_available' => $balance->vacation_days_available,
                        'years_of_service' => $balance->years_of_service,
                    ];
                }

                // Ordenar el reporte AVAILABLE por Área (Departamento)
                usort($reportData, function ($a, $b) {
                    return strcmp($a['area'], $b['area']);
                });

            } else {
                $title = 'Reporte de Días de Vacaciones Tomadas';
                $query = EmployeeMonthlyWorkLog::query()->whereNotNull('daily_activities');

                $query->when($request->filled('departments') || $request->filled('employees'), function ($q) use ($request) {
                    $q->whereHas('employee', function ($q2) use ($request) {
                        if ($request->filled('departments')) {
                            $q2->whereIn('department', $request->departments);
                        }
                        if ($request->filled('employees')) {
                            $q2->whereIn('id', $request->employees);
                        }
                    });
                });

                $logs = $query->with(['employee' => function ($q) {
                    $q->select('id', 'full_name', 'employee_number', 'department');
                }])->get();

                $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : null;
                $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to) : null;
                $statusFilter = $request->status_filter ?? ['Approved'];

                foreach ($logs as $log) {
                    $details = $log->getVacationActivities();
                    $availableDays = EmployeeVacationBalance::where('employee_id', $log->employee_id)->value('vacation_days_available');
                    $department = $log->employee->department ?? 'Sin Departamento';

                    foreach ($details as $detail) {
                        $date = Carbon::parse($detail['date']);

                        if (($startDate && $date->lt($startDate)) || ($endDate && $date->gt($endDate))) {
                            continue;
                        }

                        // Normalizar estatus para la comparación con el filtro
                        $statusNormalized = str_replace(' ', '_', $detail['status']);

                        if (in_array($statusNormalized, $statusFilter)) {
                            if (!isset($summaryByDepartment[$department])) {
                                $summaryByDepartment[$department] = [
                                    'total_days' => 0,
                                    'approved' => 0,
                                    'reviewed' => 0,
                                    'under_review' => 0,
                                    'rejected' => 0
                                ];
                            }

                            $statusKey = strtolower($statusNormalized);
                            if (array_key_exists($statusKey, $summaryByDepartment[$department])) {
                                $summaryByDepartment[$department][$statusKey]++;
                            }

                            $summaryByDepartment[$department]['total_days']++;

                            $reportData[] = [
                                'employee_number' => $log->employee->employee_number ?? 'N/A',
                                'full_name' => $log->employee->full_name ?? 'Empleado Desconocido',
                                'area' => $department,
                                'date' => $date->format('d/m/Y'),
                                'status' => $detail['status'],
                                'vacation_days_available' => $availableDays ?? 'N/A',
                            ];
                        }
                    }
                }

                // Ordenar los datos por departamento y luego por fecha (para el reporte TAKEN)
                usort($reportData, function ($a, $b) {
                    $deptCompare = strcmp($a['area'], $b['area']);
                    if ($deptCompare !== 0) {
                        return $deptCompare;
                    }
                    $dateA = Carbon::createFromFormat('d/m/Y', $a['date'])->timestamp;
                    $dateB = Carbon::createFromFormat('d/m/Y', $b['date'])->timestamp;
                    return $dateA - $dateB;
                });
            }

            $data = [
                'title' => $title,
                'reportData' => $reportData,
                'summaryByDepartment' => $summaryByDepartment,
                'reportType' => $reportType,
                'filters' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'departments' => $request->departments,
                    'employees' => $request->employees,
                    'status_filter' => $request->status_filter,
                ],
                'allEmployees' => Employee::select('id', 'full_name', 'employee_number')->get()->keyBy('id')->toArray(),
            ];

            $pdf = Pdf::loadView('modulos.recursoshumanos.sistemas.loadchart.reports.vacation_report_pdf', $data);

            // 🌟 LÍNEA CORREGIDA: Eliminamos el tipo de reporte del nombre
            return $pdf->download('reporte_vacaciones_' . Carbon::now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            Log::error("Error al generar reporte de vacaciones: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar el reporte: ' . $e->getMessage()], 500);
        }
    }

    private function updateYearsOfService(EmployeeVacationBalance $balance)
    {
        if (! $balance->employee || ! $balance->employee->hire_date) {
            return;
        }

        $hireDate = Carbon::parse($balance->employee->hire_date);
        $today    = Carbon::now();

        $currentYearsOfService = $hireDate->diffInYears($today);
        $dbYearsOfService = (int) $balance->years_of_service;

        $isAnniversary = $hireDate->format('m-d') === $today->format('m-d');

        $updates = [];

        if ($dbYearsOfService != $currentYearsOfService) {
            $updates['years_of_service'] = $currentYearsOfService;
        }

        if ($isAnniversary) {
            $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($currentYearsOfService);
            if ($balance->vacation_days_available != $mandatoryVacationDays) {
                $updates['vacation_days_available'] = $mandatoryVacationDays;
            }
        }

        if (! empty($updates)) {
            $balance->update($updates);
        }
    }

    private function calculateInitialVacationData(Employee $employee): array
    {
        $hireDate = Carbon::parse($employee->hire_date);
        $today    = Carbon::now();

        $yearsOfService = $hireDate->diffInYears($today);
        $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($yearsOfService);

        return [
            'years_of_service'          => $yearsOfService,
            'vacation_days_available' => $mandatoryVacationDays,
            'rest_days_available'       => 6,
            'work_rest_cycle_counter' => 0,
            'last_activity_date'        => null,
        ];
    }
}
