<?php
namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Operations\Well;
use App\Models\RH\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RH\LoadChart\EmployeeVacationBalance;
use App\Models\RH\LoadChart\FieldBonus;
use App\Models\RH\LoadChart\FortnightlyConfig;
use App\Models\RH\LoadChart\Meal;
use App\Models\RH\LoadChart\Services;
use App\Models\Supply\Procurement\SupplyContract; // <-- IMPORTACIÓN NUEVA
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Yasumi\Yasumi;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = $request->input('employee_id') ?? Auth::user()->employee_id;
        $isForModal = $request->has('employee_id') || $request->ajax();

        $employee = Employee::with('user')->find($employeeId);

        if (! $employee) {
            if ($isForModal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado',
                ], 404);
            }
            return redirect('/dashboard')->with('error', 'Datos de empleado no encontrados.');
        }

        $currentMonth = $request->input('month', date('n'));
        $currentYear  = $request->input('year', date('Y'));
        $monthYear    = sprintf('%04d-%02d', $currentYear, $currentMonth);

        $vacationBalance = EmployeeVacationBalance::firstOrNew(['employee_id' => $employee->id]);

        if (! $vacationBalance->exists) {
            $calculatedData = $this->calculateInitialVacationData($employee);
            $vacationBalance->fill($calculatedData);
            $vacationBalance->save();
            $vacationBalance->refresh();
        }

        $vacationDays = $vacationBalance->vacation_days_available;

        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        $employeeActivities   = $monthlyLog ? $monthlyLog->daily_activities : [];
        $totalRestDaysInMonth = 0;
        foreach ($employeeActivities as $activity) {
            if (($activity['activity_type'] ?? null) === 'D') {
                $totalRestDaysInMonth++;
            }
        }

        $hire_date = $this->formatDate($employee->hire_date);
        $photo     = $employee->photo ? asset($employee->photo) : asset('assets/img/perfil.png');

        $services = Services::select(
            'operation_type', 'service_type', 'service_performed', 'identifier',
            'service_description', 'amount', 'currency'
        )
            ->orderBy('operation_type')
            ->orderBy('service_type')
            ->orderBy('identifier')
            ->get()
            ->groupBy('operation_type');

        $foodOptions = Meal::orderBy('meal_number')->get();

        $employeeBonusCategory = $employee->job_title;
        $fieldBonuses          = FieldBonus::where('employee_category', $employeeBonusCategory)
            ->orderBy('bonus_identifier')
            ->get();

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        $payrollDates = [
            'q1_start' => null,
            'q1_end'   => null,
            'q2_start' => null,
            'q2_end'   => null,
        ];

        if ($fortnightlyConfig) {
            $payrollDates = [
                'q1_start' => $fortnightlyConfig->q1_start->format('Y-m-d'),
                'q1_end'   => $fortnightlyConfig->q1_end->format('Y-m-d'),
                'q2_start' => $fortnightlyConfig->q2_start->format('Y-m-d'),
                'q2_end'   => $fortnightlyConfig->q2_end->format('Y-m-d'),
            ];
        }

        $daysInMonth  = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $monthName    = $this->getMonthName($currentMonth);
        $calendarDays = [];

        $mandatoryHolidays = $this->getMandatoryHolidays($currentYear);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date            = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $isHoliday       = isset($mandatoryHolidays[$date]);
            $holidayName     = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day'                => $i,
                'current_month'      => true,
                'date'               => $date,
                'is_holiday'         => $isHoliday,
                'holiday_name'       => $holidayName,
                'holiday_icon_type'  => $holidayIconType,
                'is_payroll_start_1' => ($fortnightlyConfig && $fortnightlyConfig->q1_start->format('Y-m-d') == $date),
                'is_payroll_end_1'   => ($fortnightlyConfig && $fortnightlyConfig->q1_end->format('Y-m-d') == $date),
                'is_payroll_start_2' => ($fortnightlyConfig && $fortnightlyConfig->q2_start->format('Y-m-d') == $date),
                'is_payroll_end_2'   => ($fortnightlyConfig && $fortnightlyConfig->q2_end->format('Y-m-d') == $date),
                'is_today'           => $date == date('Y-m-d'),
            ];
        }

        $isGuardiaEmpleado = false;
        $guardiaBonuses    = collect();

        if ($employee && $employee->job_title && stripos($employee->job_title, 'AUXILIAR PAL') !== false) {
            $isGuardiaEmpleado = true;
            $guardiaBonuses    = FieldBonus::where('employee_category', 'Auxiliar PAL')
                ->orderBy('bonus_identifier')
                ->get();
        }

        // 📦 LÓGICA DE SUMINISTRO (Actualizada para traer los contratos)
        $isSuministro = false;
        $supplyContracts = collect();
        if ($employee && $employee->department) {
            if (in_array(strtolower(trim($employee->department)), ['administracion', 'suministros', 'suministro'])) {
                $isSuministro = true;
                // Traemos los contratos ordenados por número (puedes agregar un ->where('status', 'active') si lo necesitas)
                $supplyContracts = SupplyContract::orderBy('number')->get();
            }
        }

        $operativosValidos = [
            'Operador de Campo 1', 'Operador de Campo 2', 'Operador de Campo 3',
            'Operador de Campo 4', 'Operador de Campo 5', 'Operador de Campo 6',
            'Auxiliar Mecanico', 'Auxiliar General', 'Mecánico General',
        ];
        $requiresBaseDescription = (
            $employee &&
            $employee->department === 'Operaciones' &&
            in_array($employee->job_title, $operativosValidos)
        );

        $currentUserHasServicePermission = \App\Helpers\PermissionHelper::hasDirectPermission('realiza_servicios');

        $employeeHasServicePermission = false;
        if ($isForModal && $employee->user) {
            $employeeHasServicePermission = \App\Helpers\PermissionHelper::hasDirectPermissionForUser(
                $employee->user,
                'realiza_servicios'
            );
        }

        $showServiceBonusOption = $currentUserHasServicePermission || ($isForModal && $employeeHasServicePermission);

        $departments = Employee::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->orderBy('department')
            ->pluck('department');

        $viewData = [
            'employee'                => $employee,
            'hire_date'               => $hire_date,
            'employee_photo'          => $photo,
            'services'                => $services,
            'calendarDays'            => $calendarDays,
            'monthName'               => $monthName,
            'currentYear'             => $currentYear,
            'currentMonth'            => $currentMonth,
            'payrollDates'            => $payrollDates,
            'foodOptions'             => $foodOptions,
            'fieldBonuses'            => $fieldBonuses,
            'guardiaBonuses'          => $guardiaBonuses,
            'vacationDays'            => $vacationDays,
            'restDays'                => $totalRestDaysInMonth,
            'employeeActivities'      => $employeeActivities,
            'isForModal'              => $isForModal,
            'isGuardia'               => $isGuardiaEmpleado,
            'isSuministro'            => $isSuministro,
            'supplyContracts'         => $supplyContracts, // <-- SE ENVÍA A LA VISTA
            'requiresBaseDescription' => $requiresBaseDescription,
            'showServiceBonusOption'  => $showServiceBonusOption,
            'departments'             => $departments,
        ];

        if ($isForModal) {
            try {
                $html = View::make('modulos.rh.loadchart.calendar_partial', $viewData)->render();

                return response()->json([
                    'success' => true,
                    'html'    => $html,
                ]);
            } catch (\Exception $e) {
                Log::error('Error loading employee calendar: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar el calendario del empleado: ' . $e->getMessage(),
                ], 500);
            }
        } else {
            return view('modulos.rh.loadchart.calendar', $viewData);
        }
    }

    public function getCalendarData(Request $request)
    {
        $currentMonth = $request->input('month', date('n'));
        $currentYear  = $request->input('year', date('Y'));
        $daysInMonth  = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $monthName    = $this->getMonthName($currentMonth);

        $calendarDays = [];

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        $payrollDates = [
            'q1_start' => null,
            'q1_end'   => null,
            'q2_start' => null,
            'q2_end'   => null,
        ];

        if ($fortnightlyConfig) {
            $payrollDates = [
                'q1_start' => $fortnightlyConfig->q1_start->format('Y-m-d'),
                'q1_end'   => $fortnightlyConfig->q1_end->format('Y-m-d'),
                'q2_start' => $fortnightlyConfig->q2_start->format('Y-m-d'),
                'q2_end'   => $fortnightlyConfig->q2_end->format('Y-m-d'),
            ];
        }

        $mandatoryHolidays = $this->getMandatoryHolidays($currentYear);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date            = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $isHoliday       = isset($mandatoryHolidays[$date]);
            $holidayName     = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $dayData = [
                'day'                => $i,
                'current_month'      => true,
                'date'               => $date,
                'is_holiday'         => $isHoliday,
                'holiday_name'       => $holidayName,
                'holiday_icon_type'  => $holidayIconType,
                'is_today'           => $date == date('Y-m-d'),
                'is_payroll_start_1' => false,
                'is_payroll_end_1'   => false,
                'is_payroll_start_2' => false,
                'is_payroll_end_2'   => false,
            ];

            if ($fortnightlyConfig) {
                $dayData['is_payroll_start_1'] = $fortnightlyConfig->q1_start->format('Y-m-d') == $date;
                $dayData['is_payroll_end_1']   = $fortnightlyConfig->q1_end->format('Y-m-d') == $date;
                $dayData['is_payroll_start_2'] = $fortnightlyConfig->q2_start->format('Y-m-d') == $date;
                $dayData['is_payroll_end_2']   = $fortnightlyConfig->q2_end->format('Y-m-d') == $date;
            }

            $calendarDays[] = $dayData;
        }

        return response()->json([
            'calendarDays' => $calendarDays,
            'monthName'    => $monthName,
            'currentYear'  => $currentYear,
            'currentMonth' => $currentMonth,
            'payrollDates' => $payrollDates,
        ]);
    }

    public function getEmployeeBalancesAjax(Request $request)
    {
        $employeeId = $request->input('employee_id') ?? Auth::user()->employee_id;
        $month      = $request->input('month', date('n'));
        $year       = $request->input('year', date('Y'));
        $monthYear  = sprintf('%04d-%02d', $year, $month);

        $employee = Employee::find($employeeId);

        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $vacationBalance = EmployeeVacationBalance::where('employee_id', $employee->id)->first();

        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        $employeeActivities   = $monthlyLog ? $monthlyLog->daily_activities : [];
        $totalRestDaysInMonth = 0;
        foreach ($employeeActivities as $activity) {
            if (($activity['activity_type'] ?? null) === 'D') {
                $totalRestDaysInMonth++;
            }
        }

        return response()->json([
            'success'              => true,
            'vacationDays'         => $vacationBalance->vacation_days_available ?? 0,
            'totalRestDaysInMonth' => $totalRestDaysInMonth,
        ]);
    }

    private function calculateInitialVacationData(Employee $employee): array
    {
        $hireDate = Carbon::parse($employee->hire_date);
        $today    = Carbon::now();

        $yearsOfService        = $hireDate->diffInYears($today);
        $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($yearsOfService);

        return [
            'years_of_service'        => $yearsOfService,
            'vacation_days_available' => $mandatoryVacationDays,
            'rest_days_available'     => 0,
            'rest_mode'               => '5x2',
            'work_rest_cycle_counter' => 0,
            'last_activity_date'      => null,
        ];
    }

public function searchWells(Request $request)
    {
        $term = trim($request->input('q'));

        if (! $term) {
            return response()->json([]);
        }

        // 1. Limpiamos el término: cambiamos guiones por espacios
        // para estandarizar la búsqueda del usuario.
        $cleanTerm = str_replace('-', ' ', $term);

        // 2. Dividimos la búsqueda en palabras clave (ej. "ogarrio 14" -> ["ogarrio", "14"])
        $keywords = array_filter(explode(' ', $cleanTerm));

        $query = Well::where('status', 'active');

        // 3. Aplicamos un filtro dinámico por cada palabra clave
        // Esto permite encontrar "Ogarrio-1452D" aunque el usuario teclee "oga 14"
        foreach ($keywords as $keyword) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        // 4. Aumentamos el límite a 20 y ordenamos alfabéticamente
        // para que sea más fácil de leer si hay muchos resultados
        $wells = $query->orderBy('name', 'asc')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json($wells);
    }

    private function isServiceRealDateUsedByAnotherDay(int $employeeId, ?string $serviceRealDate, ?string $currentActivityDate): bool
    {
        if (! $serviceRealDate || ! $currentActivityDate) {
            return false;
        }

        $realDateMonthYear     = Carbon::parse($serviceRealDate)->format('Y-m');
        $activityDateMonthYear = Carbon::parse($currentActivityDate)->format('Y-m');

        $monthlyLogs = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
            ->where(function ($query) use ($realDateMonthYear, $activityDateMonthYear) {
                $query->where('month_and_year', $realDateMonthYear)
                    ->orWhere('month_and_year', $activityDateMonthYear);
            })
            ->get();

        foreach ($monthlyLogs as $log) {
            foreach ($log->daily_activities as $date => $activity) {
                if ($date !== $currentActivityDate && ($activity['services_list'] ?? [])) {
                    $existingService  = $activity['services_list'][0];
                    $existingRealDate = $existingService['service_real_date'] ?? null;

                    if ($existingRealDate === $serviceRealDate) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function saveActivity(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $targetEmployeeId = $request->input('employee_id') ?? $user->employee_id;

            if ($targetEmployeeId != $user->employee_id) {
                $hasPermission = \App\Models\RH\LoadChart\LoadChartAssignment::where('employee_id', $targetEmployeeId)
                    ->where(function ($q) use ($user) {
                        $q->where('reviewer_id', $user->id)
                            ->orWhere('approver_id', $user->id);
                    })->exists();

                if (! $hasPermission && ! \App\Helpers\PermissionHelper::hasDirectPermission('editar_loadchart_empleado')) {
                    return response()->json(['success' => false, 'message' => 'No tienes permiso para editar este calendario.'], 403);
                }
            }

            $employee = Employee::find($targetEmployeeId);
            if (! $employee) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado.'], 404);
            }

            $monthYear = Carbon::create($request->displayed_year, $request->displayed_month, 1)->format('Y-m');

            $monthlyLog = EmployeeMonthlyWorkLog::firstOrCreate(
                ['employee_id' => $employee->id, 'month_and_year' => $monthYear],
                ['user_id' => $user->id, 'daily_activities' => []]
            );

            $activityData = $monthlyLog->getDailyActivity($request->date) ?? [];

            $existingFoodBonus  = $activityData['food_bonuses'][0] ?? null;
            $existingFieldBonus = $activityData['field_bonuses'][0] ?? null;
            $existingService    = $activityData['services_list'][0] ?? null;

            $foodBonusLocked  = $existingFoodBonus && in_array(strtolower($existingFoodBonus['status'] ?? ''), ['approved', 'reviewed']);
            $fieldBonusLocked = $existingFieldBonus && in_array(strtolower($existingFieldBonus['status'] ?? ''), ['approved', 'reviewed']);
            $serviceLocked    = $existingService && in_array(strtolower($existingService['status'] ?? ''), ['approved', 'reviewed']);

            if ($request->has('activity_type')) {
                $activityType = $request->activity_type ?? 'N';

                if (($activityData['activity_type'] ?? '') !== $activityType) {
                    $activityData['activity_status']  = 'under_review';
                    $activityData['rejection_reason'] = null;
                }

                $activityData['activity_type']        = $activityType;
                $activityData['activity_description'] = $this->getActivityDescription($activityType);
                $activityData['commissioned_to']      = $request->commissioned_to;
                $activityData['well_name']            = $request->well_name;

                // Validación pozo backend
                if ($activityType === 'P' && $request->filled('well_name')) {
                    $validWell = \App\Models\Operations\Well::where('name', $request->well_name)
                        ->where('status', 'active')
                        ->exists();

                    if (! $validWell) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => "El pozo '{$request->well_name}' no existe o no está activo. Seleccione uno de la lista sugerida.",
                        ], 422);
                    }
                }

                $activityData['has_service_bonus']         = $request->has_service_bonus;
                $activityData['travel_destination']        = $request->travel_destination;
                $activityData['travel_reason']             = $request->travel_reason;

                // --- NUEVOS CAMPOS DE VIAJE / SUMINISTRO ---
                $activityData['contract_number']           = $activityType === 'V' ? $request->contract_number : null;
                $activityData['travel_service_type']       = $activityType === 'V' ? $request->travel_service_type : null;
                $activityData['is_continuation']           = $activityType === 'V' ? ($request->is_continuation ?? false) : false;
                // -------------------------------------------

                $activityData['base_activity_description'] = $activityType === 'B' ? $request->base_activity_description : null;

                if ($request->has('activity_type_vespertina')) {
                    $activityData['activity_type_vespertina']        = $request->activity_type_vespertina;
                    $activityData['activity_description_vespertina'] = $this->getActivityDescription($request->activity_type_vespertina);
                }

                $isWellActivity        = ($activityType === 'P');
                $isBaseSpecialActivity = ($activityType === 'B' && in_array($request->base_activity_description, ['Movimiento o eventos con gerencias', 'Mantenimiento a polvorin Vinco']));

                if (! $isWellActivity) {
                    $activityData['has_service_bonus'] = 'no';

                    if (! $isBaseSpecialActivity && ! $foodBonusLocked) {
                        $activityData['food_bonuses'] = [];
                    }
                    if (! $serviceLocked) {
                        $activityData['services_list'] = [];
                    }
                }
            }

            // Procesar bonos de comida
            if ($request->has('food_bonus_number')) {
                if ($request->filled('food_bonus_number')) {
                    $meal = Meal::where('meal_number', $request->food_bonus_number)->first();
                    if ($meal) {
                        $newStatus          = 'under_review';
                        $newRejectionReason = null;

                        if ($foodBonusLocked && $existingFoodBonus) {
                            $newStatus          = $existingFoodBonus['status'];
                            $newRejectionReason = $existingFoodBonus['rejection_reason'] ?? null;
                        }

                        $activityData['food_bonuses'] = [[
                            'bonus_type'       => 'Bono de Comida',
                            'num_daily'        => (int) $meal->meal_number,
                            'daily_amount'     => (float) $meal->amount,
                            'currency'         => 'MXN',
                            'status'           => $newStatus,
                            'rejection_reason' => $newRejectionReason,
                        ]];
                    }
                } else if (! $foodBonusLocked) {
                    $activityData['food_bonuses'] = [];
                }
            }

            // Procesar bono de campo
            if ($request->has('field_bonus_identifier')) {
                $employeeBonusCategory = $employee->job_title;
                $quantity              = 1;

                if (stripos($employee->job_title, 'AUXILIAR PAL') !== false) {
                    $employeeBonusCategory = 'Auxiliar PAL';
                    if ($request->has('guardia_bonus_quantity')) {
                        $quantity = max(1, (int) $request->guardia_bonus_quantity);
                    }
                }

                if ($request->filled('field_bonus_identifier')) {
                    $fieldBonus = FieldBonus::where('bonus_identifier', $request->field_bonus_identifier)
                        ->where('employee_category', $employeeBonusCategory)
                        ->first();

                    if (! $fieldBonus && ! $fieldBonusLocked) {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => "El bono de campo seleccionado no es válido para su puesto ($employeeBonusCategory).",
                        ], 422);
                    }

                    if ($fieldBonus) {
                        $newStatus          = 'under_review';
                        $newRejectionReason = null;

                        if ($fieldBonusLocked && $existingFieldBonus) {
                            $newStatus          = $existingFieldBonus['status'];
                            $newRejectionReason = $existingFieldBonus['rejection_reason'] ?? null;
                        }

                        $finalDailyAmount = (float) $fieldBonus->amount * $quantity;

                        $activityData['field_bonuses'] = [[
                            'bonus_identifier' => $fieldBonus->bonus_identifier,
                            'bonus_type'       => $fieldBonus->bonus_type,
                            'daily_amount'     => $finalDailyAmount,
                            'base_amount'      => (float) $fieldBonus->amount,
                            'quantity'         => $quantity,
                            'currency'         => $fieldBonus->currency,
                            'status'           => $newStatus,
                            'rejection_reason' => $newRejectionReason,
                        ]];
                    }
                } else if (! $fieldBonusLocked) {
                    $activityData['field_bonuses'] = [];
                }
            }

            // Procesar servicios
            if ($request->has('service_identifier')) {
                $serviceIdentifierProvided = $request->filled('service_identifier');

                if (($serviceIdentifierProvided && ($request->has_service_bonus === 'si')) || $serviceLocked) {

                    $service = null;
                    if ($serviceIdentifierProvided) {
                        $service = Services::where('identifier', $request->service_identifier)->first();
                    }

                    if ($service || $serviceLocked) {
                        $newStatus          = 'under_review';
                        $newRejectionReason = null;
                        $realDateToSave     = $request->service_real_date;

                        $serviceDataToUse = $service ?? $existingService;

                        if ($serviceLocked && $existingService) {
                            $newStatus          = $existingService['status'];
                            $newRejectionReason = $existingService['rejection_reason'] ?? null;
                            $realDateToSave     = $request->service_real_date ?? $existingService['service_real_date'] ?? null;
                        }

                        if ($request->filled('service_real_date') && ! $serviceLocked) {
                            $isDateChanged = $realDateToSave !== $request->date;

                            if ($isDateChanged || $existingService) {
                                if ($this->isServiceRealDateUsedByAnotherDay($employee->id, $realDateToSave, $request->date)) {
                                    DB::rollback();
                                    return response()->json([
                                        'success' => false,
                                        'message' => "La fecha de servicio '{$realDateToSave}' ya está registrada para otro servicio. Un servicio por día.",
                                    ], 422);
                                }
                            }
                        }

                        if ($serviceDataToUse) {
                            $activityData['services_list'] = [[
                                'service_identifier' => $serviceDataToUse['identifier'] ?? $serviceDataToUse['service_identifier'],
                                'service_performed'  => $serviceDataToUse['service_performed'],
                                'service_name'       => $serviceDataToUse['service_description'] ?? $serviceDataToUse['service_name'],
                                'amount'             => (float) ($serviceDataToUse['amount']),
                                'currency'           => $serviceDataToUse['currency'],
                                'status'             => $newStatus,
                                'rejection_reason'   => $newRejectionReason,
                                'service_real_date'  => $realDateToSave,
                            ]];
                        }
                    }
                } else if (! $serviceLocked) {
                    $activityData['services_list'] = [];
                }
            }

            $isAnythingLeft = ($activityData['activity_type'] ?? 'N') !== 'N' ||
            (! empty($activityData['activity_type_vespertina']) && $activityData['activity_type_vespertina'] !== 'N') ||
            ! empty($activityData['food_bonuses']) ||
            ! empty($activityData['field_bonuses']) ||
            ! empty($activityData['services_list']);

            if (! $isAnythingLeft) {
                $monthlyLog->removeDailyActivity($request->date);
                $monthlyLog->save();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Actividad eliminada.']);
            }

            if (empty($monthlyLog->getDailyActivity($request->date))) {
                $activityData['date']                  = $request->date;
                $activityData['payroll_period_marker'] = $this->determinePayrollPeriodMarker($request->date, $request->displayed_month, $request->displayed_year);
            }

            $activityData['day_status'] = $this->recalculateDayStatus($activityData);

            $monthlyLog->addDailyActivity($request->date, $activityData);
            $monthlyLog->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actividad guardada exitosamente',
                'data'    => $activityData,
            ]);

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Error al guardar actividad (saveActivity): ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace'        => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno del servidor. Consulte el log para más detalles.'], 500);
        }
    }

    private function determinePayrollPeriodMarker($date, $month, $year)
    {
        $fortnightlyConfig = FortnightlyConfig::where('year', $year)
            ->where('month', $month)
            ->first();

        if (! $fortnightlyConfig) {
            return null;
        }

        $dateObj = Carbon::parse($date);
        if ($dateObj->isSameDay($fortnightlyConfig->q1_start)) {
            return 'start_of_period_1';
        } elseif ($dateObj->isSameDay($fortnightlyConfig->q1_end)) {
            return 'end_of_period_1';
        } elseif ($dateObj->isSameDay($fortnightlyConfig->q2_start)) {
            return 'start_of_period_2';
        } elseif ($dateObj->isSameDay($fortnightlyConfig->q2_end)) {
            return 'end_of_period_2';
        }

        return null;
    }

    private function getActivityDescription($activityType)
    {
        $descriptions = [
            'B'   => 'Trabajo en Base',
            'P'   => 'Trabajo en Pozo',
            'C'   => 'Comisionado',
            'TC'  => 'Trabajo en Casa',
            'V'   => 'Viaje',
            'D'   => 'Descanso',
            'VAC' => 'Vacaciones',
            'E'   => 'Entrenamiento',
            'M'   => 'Médico',
            'A'   => 'Ausencia',
            'PE'  => 'Permiso',
            'N'   => 'Ninguna',
        ];

        return $descriptions[$activityType] ?? 'Actividad desconocida';
    }

    public function getMonthlyActivities(Request $request)
    {
        $employeeId = $request->input('employee_id') ?? Auth::user()->employee_id;

        $employee  = Employee::find($employeeId);
        $month     = $request->input('month', date('n'));
        $year      = $request->input('year', date('Y'));
        $monthYear = sprintf('%04d-%02d', $year, $month);

        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        return response()->json([
            'success'    => true,
            'activities' => $monthlyLog ? $monthlyLog->daily_activities : [],
        ]);
    }

    private function formatDate($date)
    {
        if (! $date) {
            return 'N/A';
        }
        $months = [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $day   = date('j', strtotime($date));
        $month = $months[date('n', strtotime($date))];
        $year  = date('Y', strtotime($date));
        return "{$day} de {$month} de {$year}";
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        return $months[$monthNumber];
    }

    private function getUsdToMxnExchangeRate()
    {
        $token = '9aa4c5d4ea07cf4a3bd54f4f38908c77ad74092d0be9d915f8fb7b7eadc6a1a3';
        $url   = "https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno?token={$token}";

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $data         = $response->json();
                $exchangeRate = $data['bmx']['series'][0]['datos'][0]['dato'];
                return (float) $exchangeRate;
            }
            Log::error('Banxico API Error: ' . $response->status());
            return null;
        } catch (\Exception $e) {
            Log::error('Error al conectar con la API de Banxico: ' . $e->getMessage());
            return null;
        }
    }

    private function getMandatoryHolidays(int $year): array
    {
        try {
            $holidays          = Yasumi::create('Mexico', $year);
            $mandatoryHolidays = [];
            foreach ($holidays->getHolidays() as $holiday) {
                $translatedName = null;
                $iconType       = 'default';
                switch ($holiday->shortName) {
                    case 'newYearsDay':
                        $translatedName = 'Año Nuevo';
                        break;
                    case 'constitutionDay':
                        $translatedName = 'Día de la Constitución Mexicana';
                        break;
                    case 'benitoJuarezBirthday':
                        $translatedName = 'Natalicio de Benito Juárez';
                        break;
                    case 'labourDay':
                        $translatedName = 'Día del Trabajo';
                        break;
                    case 'independenceDay':
                        $translatedName = 'Día de la Independencia de México';
                        break;
                    case 'revolutionDay':
                        $translatedName = 'Día de la Revolución Mexicana';
                        break;
                    case 'christmasDay':
                        $translatedName = 'Navidad';
                        $iconType       = 'christmas_tree';
                        break;
                    case 'presidentialInaugurationDay':
                        $translatedName = 'Transmisión del Poder Ejecutivo Federal';
                        break;
                    case 'electionDay':
                        $translatedName = 'Jornada Electoral';
                        break;
                }
                if ($translatedName) {
                    $mandatoryHolidays[$holiday->format('Y-m-d')] = [
                        'name'      => $translatedName,
                        'icon_type' => $iconType,
                        'date'      => $holiday->format('Y-m-d'),
                    ];
                }
            }
            return $mandatoryHolidays;
        } catch (\Exception $e) {
            Log::error('Error al obtener días festivos con Yasumi: ' . $e->getMessage());
            return [];
        }
    }

    private function recalculateDayStatus($dailyActivity)
    {
        $hasRejected    = false;
        $hasUnderReview = false;
        $hasApproved    = false;
        $hasReviewed    = false;
        $totalItems     = 0;

        if (isset($dailyActivity['activity_type']) && ! empty($dailyActivity['activity_type']) && $dailyActivity['activity_type'] !== 'N') {
            $totalItems++;
            $activityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');
            if ($activityStatus == 'rejected') {
                $hasRejected = true;
            }

            if ($activityStatus == 'under_review') {
                $hasUnderReview = true;
            }

            if ($activityStatus == 'approved') {
                $hasApproved = true;
            }

            if ($activityStatus == 'reviewed') {
                $hasReviewed = true;
            }
        }

        if (isset($dailyActivity['activity_type_vespertina']) && ! empty($dailyActivity['activity_type_vespertina']) && $dailyActivity['activity_type_vespertina'] !== 'N') {
            $totalItems++;
        }

        $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
        foreach ($itemTypes as $type) {
            if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                foreach ($dailyActivity[$type] as $item) {
                    $totalItems++;
                    $itemStatus = strtolower($item['status'] ?? 'under_review');
                    if ($itemStatus == 'rejected') {
                        $hasRejected = true;
                    }

                    if ($itemStatus == 'under_review') {
                        $hasUnderReview = true;
                    }

                    if ($itemStatus == 'approved') {
                        $hasApproved = true;
                    }

                    if ($itemStatus == 'reviewed') {
                        $hasReviewed = true;
                    }

                }
            }
        }

        if ($totalItems === 0) {
            return 'under_review';
        }

        if ($hasRejected) {
            return 'rejected';
        }

        if ($hasUnderReview) {
            return 'under_review';
        }

        if ($hasApproved) {
            return 'approved';
        }

        if ($hasReviewed && ! $hasApproved && ! $hasRejected && ! $hasUnderReview) {
            return 'reviewed';
        }

        return 'under_review';
    }

}
