<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance;
use App\Models\RecursosHumanos\LoadChart\FieldBonus;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use App\Models\RecursosHumanos\LoadChart\Meal;
use App\Models\RecursosHumanos\LoadChart\Services;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yasumi\Yasumi;

class CalendarController extends Controller
{
    /**
     * Muestra la vista inicial del calendario.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::with('user')->find($user->employee_id);

        if (!$employee) {
            return redirect('/dashboard')->with('error', 'Datos de empleado no encontrados.');
        }

        // --- INICIO: OBTENER SALDOS DE VACACIONES Y DESCANSOS ---
        $vacationBalance = EmployeeVacationBalance::firstOrNew(['employee_id' => $employee->id]);

        // Si es un registro nuevo, calcular los días iniciales
        if (!$vacationBalance->exists) {
            $calculatedData = $this->calculateInitialVacationData($employee);
            $vacationBalance->fill($calculatedData);
            $vacationBalance->save();
            $vacationBalance->refresh();
        }

        $vacationDays = $vacationBalance->vacation_days_available;
        $restDays = $vacationBalance->rest_days_available;
        // --- FIN: OBTENER SALDOS DE VACACIONES Y DESCANSOS ---

        $hire_date = $this->formatDate($employee->hire_date);
        $photo = $employee->photo ? asset($employee->photo) : asset('assets/img/perfil.png');

        // Obtener datos de Servicios
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

        // Lógica de mapeo de Bonos de Campo
        $jobTitle = $employee->job_title;
        $bonusMappings = [
            'Ingeniero de Campo' => 'Ingeniero de Campo',
            'Ingeniero de Campo 1' => 'Ingeniero de Campo',
            'Ingeniero de Campo 2' => 'Ingeniero de Campo',
            'Ingeniero de Campo 5' => 'Ingeniero de Campo',
            'Ingeniero de Campo Trainee' => 'Ingeniero de Campo',
            'Ingeniero Especializado de Campo' => 'Ingeniero de Campo',
            'Ingeniera Geocientista Senior' => 'Ingeniero Geocientista Senior',
            'Ingeniera Geocientista' => 'Ingeniero Geocientista',
            'Ingeniero Geocientista General' => 'Ingeniero Geocientista General',
            'Ingeniero Geocientista' => 'Ingeniero Geocientista',
            'Ingeniero Geocientista Junior' => 'Ingeniero Geocientista Junior',
            'Ingeniero Electronico' => 'Ingeniero Electronico',
            'Ingeniero Electromecanico' => 'Ingeniero Electromecanico',
            'Ingeniero de Explosivos' => 'Ingeniero de Explosivos',
            'Ingeniero de Logistica' => 'Ingeniero de Logistica',
            'Ingeniero en Mantenimiento Electrónico' => 'Ingeniero Electronico',
            'ingeniero Especialista en Disparos de Producción' => 'Ingeniero de Campo',
            'Ingeniero Especialista en Disparos TCP' => 'Ingeniero de Campo',
            'Ingeniero de Calidad de Servicios' => 'Ingeniero de Campo',
            'Operador de Campo 1' => 'Operador de Campo 1',
            'Operador de Campo 2' => 'Operador de Campo 2',
            'Operador de Campo 3' => 'Operador de Campo 3',
            'Operador de Campo 4' => 'Operador de Campo 4',
            'Operador de Campo 5' => 'Operador de Campo 5',
            'Operador de Campo 6' => 'Operador de Campo 6',
            'Tecnico en Suministros' => 'Tecnico en Suministros',
            'Administrador de Explosivos' => 'Administrador de Explosivos',
            'Auxiliar de Explosivos' => 'Auxiliar de Explosivos',
            'Coordinador de Suministros' => 'Coordinador de Suministros',
            'Supervisor de Operaciones' => 'Supervisor de Operaciones',
        ];
        $employeeBonusCategory = $bonusMappings[$jobTitle] ?? $jobTitle;

        $fieldBonuses = FieldBonus::where('employee_category', $employeeBonusCategory)
            ->orderBy('bonus_identifier')
            ->get();
        // Fin de lógica de Bonos de Campo

        $currentMonth = $request->input('month', date('n'));
        $currentYear = $request->input('year', date('Y'));

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        $payrollDates = [
            'q1_start' => null,
            'q1_end' => null,
            'q2_start' => null,
            'q2_end' => null,
        ];

        if ($fortnightlyConfig) {
            $payrollDates = [
                'q1_start' => $fortnightlyConfig->q1_start->format('Y-m-d'),
                'q1_end' => $fortnightlyConfig->q1_end->format('Y-m-d'),
                'q2_start' => $fortnightlyConfig->q2_start->format('Y-m-d'),
                'q2_end' => $fortnightlyConfig->q2_end->format('Y-m-d'),
            ];
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $monthName = $this->getMonthName($currentMonth);
        $prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
        $daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

        $calendarDays = [];
        $requiredPrevDays = 5;
        $firstDayOnCalendar = $daysInPrevMonth - ($requiredPrevDays - 1);
        $firstDateOnCalendar = date('Y-m-d', mktime(0, 0, 0, $prevMonth, $firstDayOnCalendar, $prevYear));
        $firstDayOfWeek = date('N', strtotime($firstDateOnCalendar));

        for ($i = 0; $i < $firstDayOfWeek - 1; $i++) {
            $calendarDays[] = ['day' => '', 'current_month' => false, 'date' => null];
        }

        // Días del mes anterior
        $mandatoryHolidays = $this->getMandatoryHolidays($prevYear);
        for ($i = 0; $i < $requiredPrevDays; $i++) {
            $day = $firstDayOnCalendar + $i;
            $date = date('Y-m-d', mktime(0, 0, 0, $prevMonth, $day, $prevYear));
            $isHoliday = isset($mandatoryHolidays[$date]);
            $holidayName = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day' => $day,
                'current_month' => false,
                'date' => $date,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_icon_type' => $holidayIconType,
            ];
        }

        // Días del mes actual
        $mandatoryHolidays = $this->getMandatoryHolidays($currentYear);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $isHoliday = isset($mandatoryHolidays[$date]);
            $holidayName = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day' => $i,
                'current_month' => true,
                'date' => $date,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_icon_type' => $holidayIconType,
            ];
        }

        // Días del siguiente mes
        $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
        $dayCounter = 1;

        $mandatoryHolidaysNext = $this->getMandatoryHolidays($nextYear);
        while (count($calendarDays) % 7 !== 0) {
            $date = date('Y-m-d', mktime(0, 0, 0, $nextMonth, $dayCounter, $nextYear));
            $isHoliday = isset($mandatoryHolidaysNext[$date]);
            $holidayName = $isHoliday ? $mandatoryHolidaysNext[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidaysNext[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day' => $dayCounter++,
                'current_month' => false,
                'date' => $date,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_icon_type' => $holidayIconType,
            ];
        }

        return view('modulos.recursoshumanos.sistemas.loadchart.calendar', [
            'employee' => $employee,
            'hire_date' => $hire_date,
            'employee_photo' => $photo,
            'services' => $services,
            'calendarDays' => $calendarDays,
            'monthName' => $monthName,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'payrollDates' => $payrollDates,
            'foodOptions' => $foodOptions,
            'fieldBonuses' => $fieldBonuses,
            'vacationDays' => $vacationDays,
            'restDays' => $restDays,
        ]);
    }

    /**
     * Devuelve los datos de balance para ser usados en AJAX
     */
    public function getEmployeeBalancesAjax(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::find($user->employee_id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $vacationBalance = EmployeeVacationBalance::where('employee_id', $employee->id)->first();

        return response()->json([
            'success' => true,
            'vacationDays' => $vacationBalance->vacation_days_available ?? 0,
            'restDays' => $vacationBalance->rest_days_available ?? 0,
        ]);
    }

    /**
     * Función auxiliar para calcular datos iniciales de balance (para el primer index)
     */
    private function calculateInitialVacationData(Employee $employee): array
    {
        $hireDate = Carbon::parse($employee->hire_date);
        $today = Carbon::now();

        $yearsOfService = $hireDate->diffInYears($today);
        $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($yearsOfService);

        return [
            'years_of_service' => $yearsOfService,
            'vacation_days_available' => $mandatoryVacationDays,
            'rest_days_available' => 6,
            'rest_mode' => '5x2',
            'work_rest_cycle_counter' => 0,
            'last_activity_date' => null,
        ];
    }

    /**
     * Devuelve los datos del calendario en formato JSON para las solicitudes AJAX.
     */
    public function getCalendarData(Request $request)
    {
        $currentMonth = $request->input('month', date('n'));
        $currentYear = $request->input('year', date('Y'));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $monthName = $this->getMonthName($currentMonth);
        $prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
        $daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);

        $calendarDays = [];
        $requiredPrevDays = 5;
        $firstDayOnCalendar = $daysInPrevMonth - ($requiredPrevDays - 1);
        $firstDateOnCalendar = date('Y-m-d', mktime(0, 0, 0, $prevMonth, $firstDayOnCalendar, $prevYear));
        $firstDayOfWeek = date('N', strtotime($firstDateOnCalendar));

        for ($i = 0; $i < $firstDayOfWeek - 1; $i++) {
            $calendarDays[] = ['day' => '', 'current_month' => false, 'date' => null];
        }

        // Días del mes anterior
        $mandatoryHolidaysPrev = $this->getMandatoryHolidays($prevYear);
        for ($i = 0; $i < $requiredPrevDays; $i++) {
            $day = $firstDayOnCalendar + $i;
            $date = date('Y-m-d', mktime(0, 0, 0, $prevMonth, $day, $prevYear));
            $isHoliday = isset($mandatoryHolidaysPrev[$date]);
            $holidayName = $isHoliday ? $mandatoryHolidaysPrev[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidaysPrev[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day' => $day,
                'current_month' => false,
                'date' => $date,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_icon_type' => $holidayIconType,
            ];
        }

        // Días del mes actual
        $mandatoryHolidays = $this->getMandatoryHolidays($currentYear);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $isHoliday = isset($mandatoryHolidays[$date]);
            $holidayName = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day' => $i,
                'current_month' => true,
                'date' => $date,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_icon_type' => $holidayIconType,
            ];
        }

        // Días del siguiente mes
        $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
        $dayCounter = 1;

        $mandatoryHolidaysNext = $this->getMandatoryHolidays($nextYear);
        while (count($calendarDays) % 7 !== 0) {
            $date = date('Y-m-d', mktime(0, 0, 0, $nextMonth, $dayCounter, $nextYear));
            $isHoliday = isset($mandatoryHolidaysNext[$date]);
            $holidayName = $isHoliday ? $mandatoryHolidaysNext[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidaysNext[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day' => $dayCounter++,
                'current_month' => false,
                'date' => $date,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_icon_type' => $holidayIconType,
            ];
        }

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        $payrollDates = [
            'q1_start' => null,
            'q1_end' => null,
            'q2_start' => null,
            'q2_end' => null,
        ];

        if ($fortnightlyConfig) {
            $payrollDates = [
                'q1_start' => $fortnightlyConfig->q1_start->format('Y-m-d'),
                'q1_end' => $fortnightlyConfig->q1_end->format('Y-m-d'),
                'q2_start' => $fortnightlyConfig->q2_start->format('Y-m-d'),
                'q2_end' => $fortnightlyConfig->q2_end->format('Y-m-d'),
            ];
        }

        $processedDays = [];
        foreach ($calendarDays as $day) {
            $day['is_today'] = $day['date'] == date('Y-m-d');
            if ($fortnightlyConfig) {
                $day['is_payroll_start_1'] = $fortnightlyConfig->q1_start->format('Y-m-d') == $day['date'];
                $day['is_payroll_end_1'] = $fortnightlyConfig->q1_end->format('Y-m-d') == $day['date'];
                $day['is_payroll_start_2'] = $fortnightlyConfig->q2_start->format('Y-m-d') == $day['date'];
                $day['is_payroll_end_2'] = $fortnightlyConfig->q2_end->format('Y-m-d') == $day['date'];
            } else {
                $day['is_payroll_start_1'] = false;
                $day['is_payroll_end_1'] = false;
                $day['is_payroll_start_2'] = false;
                $day['is_payroll_end_2'] = false;
            }
            $processedDays[] = $day;
        }

        return response()->json([
            'calendarDays' => $processedDays,
            'monthName' => $monthName,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'payrollDates' => $payrollDates,
        ]);
    }

    /**
     * Guarda una actividad diaria con los nuevos campos.
     */
    public function saveActivity(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'displayed_month' => 'required|integer',
                'displayed_year' => 'required|integer',
                'activity_type' => 'nullable|string|max:10',
                'commissioned_to' => 'nullable|string|max:255',
                'well_name' => 'nullable|string|max:255',
                'has_service_bonus' => 'required|string|in:si,no',
                'service_identifier' => 'nullable|string|max:50',
                'service_performed' => 'nullable|string|max:255',
                'amount' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'payroll_period_override' => 'nullable|string|max:50',
                'food_bonus_number' => 'nullable|integer|min:1',
                'field_bonus_identifier' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed for saveActivity', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $employee = Employee::find($user->employee_id);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
            }

            DB::beginTransaction();

            $displayedMonth = $request->input('displayed_month');
            $displayedYear = $request->input('displayed_year');
            $monthYear = Carbon::create($displayedYear, $displayedMonth, 1)->format('Y-m');
            $activityType = $request->activity_type ?? 'N';

            $monthlyLog = EmployeeMonthlyWorkLog::firstOrCreate(
                ['employee_id' => $employee->id, 'user_id' => $user->id, 'month_and_year' => $monthYear],
                ['daily_activities' => []]
            );

            $existingActivity = $monthlyLog->getDailyActivity($request->date);
            // Bloqueo estricto si el día entero está APROBADO o REVISADO (según la nueva regla)
            // Ya que el front-end solo enviará el request si al menos un campo es editable,
            // si el front-end permite la edición cuando el day_status es 'rejected',
            // el backend solo debe impedir la edición si el day_status es 'approved' (para evitar re-uso de estatus)
            // La validación de 'reviewed' se hace en el front-end.
            if ($existingActivity && ($existingActivity['day_status'] ?? 'under_review') === 'approved') {
                DB::rollback();
                return response()->json(['success' => false, 'message' => 'No se pueden modificar actividades aprobadas.'], 403);
            }

            $payrollPeriodMarker = $this->determinePayrollPeriodMarker($request->date, $displayedMonth, $displayedYear);
            $activityData = [
                'date' => $request->date,
                'day_status' => 'under_review',
                'activity_status' => 'under_review',
                'payroll_period_marker' => $payrollPeriodMarker,
                'is_locked' => false,
                'activity_type' => $activityType,
                'activity_description' => $this->getActivityDescription($activityType),
                'commissioned_to' => $activityType === 'C' ? $request->commissioned_to : null,
                'well_name' => $activityType === 'P' ? $request->well_name : null,
                'has_service_bonus' => $activityType === 'P' ? $request->has_service_bonus : 'no',
                'services_list' => [],
                'field_bonuses' => [],
                'food_bonuses' => [],
                'rejection_reason' => null,
            ];

            // --- Lógica para mantener el estado de Aprobación/Revisión si NO hay cambios ---
            $statusesToMaintain = ['approved', 'reviewed'];
            if ($existingActivity) {
                $oldActivityStatus = $existingActivity['activity_status'];
                $oldCommissionedTo = $existingActivity['commissioned_to'] ?? null;
                $oldWellName = $existingActivity['well_name'] ?? null;
                $oldHasServiceBonus = $existingActivity['has_service_bonus'] ?? 'no';

                // CAMBIO: Se usa TC en lugar de H
                $isCommissionedToChanged = ($activityType === 'C' && ($oldCommissionedTo !== ($activityData['commissioned_to'] ?? null)));
                $isWellNameChanged = ($activityType === 'P' && ($oldWellName !== ($activityData['well_name'] ?? null)));
                $isHasServiceBonusChanged = ($activityType === 'P' && ($oldHasServiceBonus !== $activityData['has_service_bonus']));

                $activityFieldsChanged = (
                    $existingActivity['activity_type'] !== $activityData['activity_type'] ||
                    $isCommissionedToChanged ||
                    $isWellNameChanged ||
                    $isHasServiceBonusChanged
                );

                if (!$activityFieldsChanged && in_array($oldActivityStatus, $statusesToMaintain)) {
                    $activityData['activity_status'] = $oldActivityStatus;
                    $activityData['rejection_reason'] = $existingActivity['rejection_reason'] ?? null;
                }
            }

            // Servicio (solo si es P y has_service_bonus es 'si')
            $isActivityP = $activityType === 'P';
            if ($isActivityP && $request->has_service_bonus === 'si' && $request->filled('service_identifier')) {
                $service = Services::where('identifier', $request->service_identifier)->first();
                if (!$service) {
                    throw new \Exception("Service with identifier {$request->service_identifier} not found.");
                }

                $currentService = [
                    'service_identifier' => $request->service_identifier,
                    'service_performed' => $service->service_performed,
                    'service_name' => $service->service_description,
                    'amount' => (float) $service->amount,
                    'currency' => $service->currency,
                    'payroll_period_override' => $request->payroll_period_override,
                    'status' => 'under_review',
                    'rejection_reason' => null,
                ];
                if ($existingActivity && isset($existingActivity['services_list'][0])) {
                    $oldService = $existingActivity['services_list'][0];
                    $isServiceChanged = ($oldService['service_identifier'] !== $currentService['service_identifier'] || ($oldService['payroll_period_override'] ?? null) !== $currentService['payroll_period_override']);
                    $oldServiceStatus = $oldService['status'];
                    if (!$isServiceChanged && in_array($oldServiceStatus, $statusesToMaintain)) {
                        $currentService['status'] = $oldServiceStatus;
                        $currentService['rejection_reason'] = $oldService['rejection_reason'] ?? null;
                    }
                }
                $activityData['services_list'][] = $currentService;
            }

            // Bono de Campo (solo si es P y se seleccionó)
            if ($isActivityP && $request->filled('field_bonus_identifier')) {
                $fieldBonus = FieldBonus::where('bonus_identifier', $request->field_bonus_identifier)->first();
                if ($fieldBonus) {
                    $daily_amount_mxn = null;
                    $usd_to_mxn_rate = null;
                    $daily_currency_mxn = null;
                    if (strtoupper($fieldBonus->currency) === 'USD') {
                        $usd_to_mxn_rate = $this->getUsdToMxnExchangeRate();
                        if ($usd_to_mxn_rate) {
                            $daily_amount_mxn = (float) $fieldBonus->amount * $usd_to_mxn_rate;
                            $daily_currency_mxn = 'MXN';
                        } else {
                            Log::warning('No se pudo obtener el tipo de cambio USD a MXN.');
                        }
                    }
                    $currentFieldBonus = [
                        'bonus_identifier' => $fieldBonus->bonus_identifier,
                        'bonus_type' => $fieldBonus->bonus_type,
                        'daily_amount' => (float) $fieldBonus->amount,
                        'currency' => $fieldBonus->currency,
                        'daily_amount_mxn' => $daily_amount_mxn,
                        'daily_currency_mxn' => $daily_currency_mxn,
                        'usd_to_mxn_rate' => $usd_to_mxn_rate,
                        'days' => 1,
                        'status' => 'under_review',
                        'rejection_reason' => null,
                    ];
                    if ($existingActivity && isset($existingActivity['field_bonuses'][0])) {
                        $oldBonus = $existingActivity['field_bonuses'][0];
                        $isFieldBonusChanged = ($oldBonus['bonus_identifier'] !== $currentFieldBonus['bonus_identifier']);
                        $oldBonusStatus = $oldBonus['status'];
                        if (!$isFieldBonusChanged && in_array($oldBonusStatus, $statusesToMaintain)) {
                            $currentFieldBonus['status'] = $oldBonusStatus;
                            $currentFieldBonus['rejection_reason'] = $oldBonus['rejection_reason'] ?? null;
                        }
                    }
                    $activityData['field_bonuses'][] = $currentFieldBonus;
                }
            }

            // Bono de Comida (solo si es P y se seleccionó)
            if ($isActivityP && $request->filled('food_bonus_number')) {
                $meal = Meal::where('meal_number', $request->food_bonus_number)->first();
                if ($meal) {
                    $currentFoodBonus = [
                        'bonus_type' => 'Bono de Comida',
                        'num_daily' => (int) $request->food_bonus_number,
                        'daily_amount' => (float) $meal->amount,
                        'currency' => 'MXN',
                        'status' => 'under_review',
                        'rejection_reason' => null,
                    ];
                    if ($existingActivity && isset($existingActivity['food_bonuses'][0])) {
                        $oldBonus = $existingActivity['food_bonuses'][0];
                        $isFoodBonusChanged = ($oldBonus['num_daily'] !== (int) $currentFoodBonus['num_daily']);
                        $oldBonusStatus = $oldBonus['status'];
                        if (!$isFoodBonusChanged && in_array($oldBonusStatus, $statusesToMaintain)) {
                            $currentFoodBonus['status'] = $oldBonusStatus;
                            $currentFoodBonus['rejection_reason'] = $oldBonus['rejection_reason'] ?? null;
                        }
                    }
                    $activityData['food_bonuses'][] = $currentFoodBonus;
                }
            }

            // Lógica para el caso de ELIMINAR todo
            $isActivityRegistered = ($activityType !== 'N' || count($activityData['services_list']) > 0 || count($activityData['field_bonuses']) > 0 || count($activityData['food_bonuses']) > 0);

            if (!$isActivityRegistered) {
                   if ($existingActivity) {
                       $monthlyLog->removeDailyActivity($request->date);
                       $monthlyLog->save();
                       DB::commit();
                       return response()->json([
                           'success' => true,
                           'message' => 'Actividad eliminada exitosamente',
                           'data' => null,
                       ]);
                   }
                   DB::commit();
                   return response()->json([
                       'success' => true,
                       'message' => 'No se registró actividad, no se realizó ninguna acción.',
                       'data' => null,
                   ]);
            }

            // 4. Recalcular el estado general del día (day_status)
            $dailyActivityStatus = $this->recalculateDayStatus($activityData);
            $activityData['day_status'] = $dailyActivityStatus;

            $monthlyLog->addDailyActivity($request->date, $activityData);
            $monthlyLog->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actividad guardada exitosamente',
                'data' => $activityData,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar actividad: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determina el marcador de período de nómina para una fecha específica
     */
    private function determinePayrollPeriodMarker($date, $month, $year)
    {
        $fortnightlyConfig = FortnightlyConfig::where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$fortnightlyConfig) {
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

    /**
     * Obtiene la descripción de la actividad basada en el tipo
     */
    private function getActivityDescription($activityType)
    {
        $descriptions = [
            'B' => 'Trabajo en Base',
            'P' => 'Trabajo en Pozo',
            'C' => 'Comisionado',
            'TC' => 'Trabajo en Casa', // CAMBIO: H -> TC
            'V' => 'Viaje',
            'D' => 'Descanso',
            'VAC' => 'Vacaciones',
            'E' => 'Entrenamiento',
            'M' => 'Médico',
            'A' => 'Ausencia',
            'PE' => 'Permiso',
            'N' => 'Ninguna'
        ];

        return $descriptions[$activityType] ?? 'Actividad desconocida';
    }

    /**
     * Obtiene las actividades de un empleado para un mes específico
     */
    public function getMonthlyActivities(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::find($user->employee_id);
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $monthYear = sprintf('%04d-%02d', $year, $month);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        return response()->json([
            'success' => true,
            'activities' => $monthlyLog ? $monthlyLog->daily_activities : [],
        ]);
    }

    private function formatDate($date)
    {
        if (!$date) {
            return 'N/A';
        }
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
        $day = date('j', strtotime($date));
        $month = $months[date('n', strtotime($date))];
        $year = date('Y', strtotime($date));
        return "{$day} de {$month} de {$year}";
    }

    private function getMonthName($monthNumber)
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
        return $months[$monthNumber];
    }

    private function getUsdToMxnExchangeRate()
    {
        // ... (API call to Banxico remains the same)
        $token = '9aa4c5d4ea07cf4a3bd54f4f38908c77ad74092d0be9d915f8fb7b7eadc6a1a3';
        $url = "https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno?token={$token}";

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $data = $response->json();
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
        // ... (Yasumi logic remains the same)
        try {
            $holidays = Yasumi::create('Mexico', $year);
            $mandatoryHolidays = [];
            foreach ($holidays->getHolidays() as $holiday) {
                $translatedName = null;
                $iconType = 'default';
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
                        $iconType = 'christmas_tree';
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
                        'name' => $translatedName,
                        'icon_type' => $iconType,
                        'date' => $holiday->format('Y-m-d'),
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
        // ... (This function remains the same as it relies on internal statuses, not the activity type code itself)
        $hasRejected = false;
        $hasUnderReview = false;
        $hasApproved = false;
        $hasReviewed = false;
        $totalItems = 0;
        $statusesToLock = ['approved', 'reviewed'];

        // 1. Verificar la actividad principal (si existe y no es 'N')
        if (isset($dailyActivity['activity_type']) && !empty($dailyActivity['activity_type']) && $dailyActivity['activity_type'] !== 'N') {
            $totalItems++;
            $activityStatus = strtolower($dailyActivity['activity_status'] ?? 'under_review');
            if ($activityStatus == 'rejected')
                $hasRejected = true;
            if ($activityStatus == 'under_review')
                $hasUnderReview = true;
            if ($activityStatus == 'approved')
                $hasApproved = true;
            if ($activityStatus == 'reviewed')
                $hasReviewed = true;
        }

        // 2. Verificar bonos y servicios
        $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
        foreach ($itemTypes as $type) {
            if (isset($dailyActivity[$type]) && is_array($dailyActivity[$type])) {
                foreach ($dailyActivity[$type] as $item) {
                    $totalItems++;
                    $itemStatus = strtolower($item['status'] ?? 'under_review');
                    if ($itemStatus == 'rejected')
                        $hasRejected = true;
                    if ($itemStatus == 'under_review')
                        $hasUnderReview = true;
                    if ($itemStatus == 'approved')
                        $hasApproved = true;
                    if ($itemStatus == 'reviewed')
                        $hasReviewed = true;
                }
            }
        }

        // 3. Determinar el estado general
        if ($totalItems === 0) {
            return 'under_review'; // Un día "vacío" se considera bajo revisión hasta que se borre.
        }

        if ($hasRejected) {
            return 'rejected';
        }

        // Si hay items bajo revisión, el día está bajo revisión
        if ($hasUnderReview) {
            return 'under_review';
        }

        // Si no hay rejected ni under_review, todos los items están Approved o Reviewed.

        // Si al menos uno es APROBADO, el estado del día es APROBADO.
        if ($hasApproved) {
            return 'approved';
        }

        // Si todos son REVISADOS (y no hay approved, rejected, ni under_review)
        if ($hasReviewed && !$hasApproved && !$hasRejected && !$hasUnderReview) {
            return 'reviewed';
        }

        return 'under_review'; // Caso catch-all si la lógica falla o si solo hay ítems vacíos (aunque totalItems > 0)
    }
}
