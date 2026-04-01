<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RH\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RH\LoadChart\EmployeeVacationBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeVacationBalanceController extends Controller
{
    public function index()
    {
        // ✅ Agregada la relación 'area' al Eager Loading
        $vacationBalances = EmployeeVacationBalance::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'area_id')->with('area');
        }])
            ->orderBy('employee_id')
            ->get();

        // ✅ Agregada la relación 'area' a los empleados cargados
        $employees = Employee::with('area')->select('id', 'full_name', 'hire_date', 'area_id', 'employee_number')->orderBy('full_name')->get();

        // ✅ Extraemos los nombres de las áreas en lugar de los departamentos
        $areas = $employees->map(function ($employee) {
            return $employee->area ? $employee->area->name : null;
        })->unique()->filter()->sort()->values();

        $vacationDaysTaken = $this->getConsolidatedVacationDaysTaken();

        return view('modules.rh.loadchart.employee_vacation_balance', [
            'vacationBalances'  => $vacationBalances,
            'employees'         => $employees,
            'departments'       => $areas, // Pasamos las áreas usando la variable esperada por la vista
            'vacationDaysTaken' => $vacationDaysTaken,
        ]);
    }

    private function getConsolidatedVacationDaysTaken(): array
    {
        // ✅ Agregada la relación 'area'
        $logsWithVacations = EmployeeMonthlyWorkLog::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'area_id')->with('area');
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
                    'area'                      => $employee->area ? $employee->area->name : 'N/A', // ✅ Usa Área
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
            'employee_id'             => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id,' . $id,
            'vacation_days_available' => 'required|integer|min:0',
            'rest_days_available'     => 'required|integer',
            'rest_mode'               => 'required|string|max:15',
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

    public function getData(): \Illuminate\Http\JsonResponse
    {
        // ✅ Carga relación area en AJAX
        $vacationBalances = EmployeeVacationBalance::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'area_id')->with('area');
        }])
            ->orderBy('employee_id')
            ->get();

        $vacationDaysTaken = $this->getConsolidatedVacationDaysTaken();

        return response()->json([
            'vacationBalances'  => $vacationBalances,
            'vacationDaysTaken' => $vacationDaysTaken,
        ]);
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
            'departments' => 'nullable|array', // Lo mantenemos como variable, pero buscará áreas
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
        $summaryByArea = []; // Renombrado lógicamente

        try {
            if ($reportType === 'AVAILABLE') {
                $title = 'Reporte de Días de Vacaciones Disponibles';
                $query = EmployeeVacationBalance::query();

                // ✅ Filtramos por nombre de Área
                $query->when($request->filled('departments'), function ($q) use ($request) {
                    $q->whereHas('employee.area', function ($q2) use ($request) {
                        $q2->whereIn('name', $request->departments);
                    });
                });

                $query->when($request->filled('employees'), function ($q) use ($request) {
                    $q->whereIn('employee_id', $request->employees);
                });

                // ✅ Cargamos Área
                $balances = $query->with(['employee' => function ($q) {
                    $q->select('id', 'full_name', 'employee_number', 'area_id')->with('area');
                }])->get();

                foreach ($balances as $balance) {
                    $area = $balance->employee->area ? $balance->employee->area->name : 'Sin Área';

                    if (!isset($summaryByArea[$area])) {
                        $summaryByArea[$area] = [
                            'total_employees' => 0,
                            'total_vacation_days' => 0,
                        ];
                    }

                    $summaryByArea[$area]['total_employees']++;
                    $summaryByArea[$area]['total_vacation_days'] += $balance->vacation_days_available;

                    $reportData[] = [
                        'employee_number' => $balance->employee->employee_number ?? 'N/A',
                        'full_name' => $balance->employee->full_name ?? 'Empleado Desconocido',
                        'area' => $area,
                        'vacation_days_available' => $balance->vacation_days_available,
                        'years_of_service' => $balance->years_of_service,
                    ];
                }

                usort($reportData, function ($a, $b) {
                    return strcmp($a['area'], $b['area']);
                });

            } else {
                $title = 'Reporte de Días de Vacaciones Tomadas';
                $query = EmployeeMonthlyWorkLog::query()->whereNotNull('daily_activities');

                $query->when($request->filled('departments') || $request->filled('employees'), function ($q) use ($request) {
                    $q->whereHas('employee', function ($q2) use ($request) {
                        if ($request->filled('departments')) {
                            // ✅ Filtro por Área en TAKEN
                            $q2->whereHas('area', function($q3) use ($request) {
                                $q3->whereIn('name', $request->departments);
                            });
                        }
                        if ($request->filled('employees')) {
                            $q2->whereIn('id', $request->employees);
                        }
                    });
                });

                $logs = $query->with(['employee' => function ($q) {
                    $q->select('id', 'full_name', 'employee_number', 'area_id')->with('area');
                }])->get();

                $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : null;
                $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to) : null;
                $statusFilter = $request->status_filter ?? ['Approved'];

                foreach ($logs as $log) {
                    $details = $log->getVacationActivities();
                    $availableDays = EmployeeVacationBalance::where('employee_id', $log->employee_id)->value('vacation_days_available');
                    $area = $log->employee->area ? $log->employee->area->name : 'Sin Área';

                    foreach ($details as $detail) {
                        $date = Carbon::parse($detail['date']);

                        if (($startDate && $date->lt($startDate)) || ($endDate && $date->gt($endDate))) {
                            continue;
                        }

                        $statusNormalized = str_replace(' ', '_', $detail['status']);

                        if (in_array($statusNormalized, $statusFilter)) {
                            if (!isset($summaryByArea[$area])) {
                                $summaryByArea[$area] = [
                                    'total_days' => 0,
                                    'approved' => 0,
                                    'reviewed' => 0,
                                    'under_review' => 0,
                                    'rejected' => 0
                                ];
                            }

                            $statusKey = strtolower($statusNormalized);
                            if (array_key_exists($statusKey, $summaryByArea[$area])) {
                                $summaryByArea[$area][$statusKey]++;
                            }

                            $summaryByArea[$area]['total_days']++;

                            $reportData[] = [
                                'employee_number' => $log->employee->employee_number ?? 'N/A',
                                'full_name' => $log->employee->full_name ?? 'Empleado Desconocido',
                                'area' => $area,
                                'date' => $date->format('d/m/Y'),
                                'status' => $detail['status'],
                                'vacation_days_available' => $availableDays ?? 'N/A',
                            ];
                        }
                    }
                }

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
                'summaryByDepartment' => $summaryByArea, // Usamos la misma clave para que tu PDF Blade no se rompa
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

            $pdf = Pdf::loadView('modulos.recursoshumanos.loadchart.reports.vacation_report_pdf', $data);

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
