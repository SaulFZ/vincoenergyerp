<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\Services;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use App\Models\RecursosHumanos\LoadChart\Meal;
use App\Models\RecursosHumanos\LoadChart\FieldBonus;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CalendarController extends Controller
{
    /**
     * Muestra la vista inicial del calendario.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::with('user')->find($user->employee_id);
        $hire_date = $employee
            ? $this->formatDate($employee->hire_date)
            : 'N/A';
        $photo = $employee && $employee->photo ? asset($employee->photo) : asset('assets/img/perfil.png');

        // Obtener servicios de manera simple y plana para pasar a la vista
        $services = Services::select(
            'operation_type',
            'service_type',
            'service_performed',
            'identifier',
            'service_description'
        )
            ->orderBy('operation_type')
            ->orderBy('identifier')
            ->get()
            ->groupBy('operation_type');

        $foodOptions = Meal::orderBy('meal_number')->get();
        $fieldBonuses = FieldBonus::orderBy('bonus_identifier')->get();

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

        for ($i = 0; $i < $requiredPrevDays; $i++) {
            $day = $firstDayOnCalendar + $i;
            $date = date('Y-m-d', mktime(0, 0, 0, $prevMonth, $day, $prevYear));
            $calendarDays[] = ['day' => $day, 'current_month' => false, 'date' => $date];
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $calendarDays[] = ['day' => $i, 'current_month' => true, 'date' => $date];
        }

        $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
        $dayCounter = 1;

        while (count($calendarDays) % 7 !== 0) {
            $date = date('Y-m-d', mktime(0, 0, 0, $nextMonth, $dayCounter, $nextYear));
            $calendarDays[] = ['day' => $dayCounter++, 'current_month' => false, 'date' => $date];
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
        ]);
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

        for ($i = 0; $i < $requiredPrevDays; $i++) {
            $day = $firstDayOnCalendar + $i;
            $date = date('Y-m-d', mktime(0, 0, 0, $prevMonth, $day, $prevYear));
            $calendarDays[] = ['day' => $day, 'current_month' => false, 'date' => $date];
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $calendarDays[] = ['day' => $i, 'current_month' => true, 'date' => $date];
        }

        $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
        $dayCounter = 1;

        while (count($calendarDays) % 7 !== 0) {
            $date = date('Y-m-d', mktime(0, 0, 0, $nextMonth, $dayCounter, $nextYear));
            $calendarDays[] = ['day' => $dayCounter++, 'current_month' => false, 'date' => $date];
        }

        $fortnightlyConfig = FortnightlyConfig::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

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
        ]);
    }

    /**
     * Guarda una actividad diaria.
     */
    public function saveActivity(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'displayed_month' => 'required|integer',
                'displayed_year' => 'required|integer',
                'activity_type' => 'required|string|max:10',
                'commissioned_to' => 'nullable|string|max:255',
                'service_identifier' => 'nullable|string|max:50',
                'service_performed' => 'nullable|string|max:255',
                'service_name' => 'nullable|string|max:255',
                'amount' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'food_bonus_number' => 'nullable|integer|min:1',
                'field_bonus_identifier' => 'nullable|string|max:50',
                'payroll_bonus_days' => 'nullable|integer|min:1|max:31',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $employee = Employee::find($user->employee_id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $activityDate = Carbon::parse($request->date);
            $displayedMonth = $request->input('displayed_month');
            $displayedYear = $request->input('displayed_year');
            $monthYear = Carbon::create($displayedYear, $displayedMonth, 1)->format('Y-m');

            $monthlyLog = EmployeeMonthlyWorkLog::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'user_id' => $user->id,
                    'month_and_year' => $monthYear
                ],
                [
                    'daily_activities' => []
                ]
            );

            // Obtener la actividad existente si la hay
            $existingActivity = $monthlyLog->getDailyActivity($request->date);
            $payrollPeriodMarker = $this->determinePayrollPeriodMarker($request->date, $displayedMonth, $displayedYear);

            // Preparar datos de la actividad
            $activityData = [
                'date' => $request->date,
                'day_status' => null,
                'activity_status' => 'Pending',
                'payroll_period_marker' => $payrollPeriodMarker,
                'is_locked' => !is_null($monthlyLog->approved_at),
                'activity_type' => $request->activity_type,
                'activity_description' => $this->getActivityDescription($request->activity_type),
                'commissioned_to' => $request->commissioned_to,
                'services_list' => [],
                'field_bonuses' => [],
                'food_bonuses' => [],
                'payroll_bonuses' => [],
                'rejection_reason' => null
            ];

            // Comparar con la actividad existente y aplicar lógica de estado
            if ($existingActivity) {
                // Si la actividad principal ha cambiado, su estado vuelve a 'Pending'
                $isActivityChanged = ($existingActivity['activity_type'] !== $activityData['activity_type'] || $existingActivity['commissioned_to'] !== $activityData['commissioned_to']);
                if ($isActivityChanged) {
                    $activityData['activity_status'] = 'Pending';
                    $activityData['rejection_reason'] = null; // Limpiar razón de rechazo si se modifica
                } else {
                    // Si no hay cambios en la actividad, preservar el estado existente
                    $activityData['activity_status'] = $existingActivity['activity_status'];
                    $activityData['rejection_reason'] = $existingActivity['rejection_reason'] ?? null;
                }
            }

            // Lógica para el servicio
            $currentService = null;
            if ($request->filled('service_identifier') && $request->filled('service_performed') && $request->filled('amount')) {
                $service = Services::where('identifier', $request->service_identifier)->first();
                $currentService = [
                    'service_identifier' => $request->service_identifier,
                    'service_performed' => $request->service_performed,
                    'service_name' => $service ? $service->service_description : $request->service_name,
                    'amount' => (float) $request->amount,
                    'currency' => $request->currency ?? 'MXN',
                    'status' => 'Pending',
                    'rejection_reason' => null
                ];

                // Preservar estado si el servicio no ha cambiado
                if ($existingActivity && isset($existingActivity['services_list'][0])) {
                    $oldService = $existingActivity['services_list'][0];
                    $isServiceChanged = ($oldService['service_identifier'] !== $currentService['service_identifier'] || $oldService['service_performed'] !== $currentService['service_performed'] || $oldService['amount'] !== $currentService['amount']);

                    if (!$isServiceChanged) {
                        $currentService['status'] = $oldService['status'];
                        $currentService['rejection_reason'] = $oldService['rejection_reason'];
                    }
                }
                $activityData['services_list'][] = $currentService;
            }

            // Lógica para el bono de campo
            $currentFieldBonus = null;
            if ($request->field_bonus_identifier) {
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
                            Log::warning('No se pudo obtener el tipo de cambio USD a MXN. No se realizará la conversión.');
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
                        'status' => 'Pending',
                        'rejection_reason' => null
                    ];

                    // Preservar estado si el bono no ha cambiado
                    if ($existingActivity && isset($existingActivity['field_bonuses'][0])) {
                        $oldBonus = $existingActivity['field_bonuses'][0];
                        $isFieldBonusChanged = ($oldBonus['bonus_identifier'] !== $currentFieldBonus['bonus_identifier']);

                        if (!$isFieldBonusChanged) {
                            $currentFieldBonus['status'] = $oldBonus['status'];
                            $currentFieldBonus['rejection_reason'] = $oldBonus['rejection_reason'];
                        }
                    }
                    $activityData['field_bonuses'][] = $currentFieldBonus;
                }
            }

            // Lógica para el bono de comida
            $currentFoodBonus = null;
            if ($request->food_bonus_number) {
                $meal = Meal::where('meal_number', $request->food_bonus_number)->first();
                if ($meal) {
                    $currentFoodBonus = [
                        'bonus_type' => 'Bono de Comida',
                        'num_daily' => $request->food_bonus_number,
                        'daily_amount' => (float) $meal->amount,
                        'currency' => 'MXN',
                        'status' => 'Pending',
                        'rejection_reason' => null
                    ];

                    // Preservar estado si el bono no ha cambiado
                    if ($existingActivity && isset($existingActivity['food_bonuses'][0])) {
                        $oldBonus = $existingActivity['food_bonuses'][0];
                        $isFoodBonusChanged = ($oldBonus['num_daily'] !== (int) $currentFoodBonus['num_daily']);

                        if (!$isFoodBonusChanged) {
                            $currentFoodBonus['status'] = $oldBonus['status'];
                            $currentFoodBonus['rejection_reason'] = $oldBonus['rejection_reason'];
                        }
                    }
                    $activityData['food_bonuses'][] = $currentFoodBonus;
                }
            }

            // Lógica para el bono de nómina
            $currentPayrollBonus = null;
            if ($request->payroll_bonus_days && $request->payroll_bonus_days > 0) {
                $dailyRate = 800.00;
                $totalAmount = $dailyRate * (int) $request->payroll_bonus_days;
                $currentPayrollBonus = [
                    'bonus_name' => 'Bono de nómina',
                    'days' => (int) $request->payroll_bonus_days,
                    'total_amount' => $totalAmount,
                    'status' => 'Pending',
                    'rejection_reason' => null
                ];

                // Preservar estado si el bono no ha cambiado
                if ($existingActivity && isset($existingActivity['payroll_bonuses'][0])) {
                    $oldBonus = $existingActivity['payroll_bonuses'][0];
                    $isPayrollBonusChanged = ($oldBonus['days'] !== (int) $currentPayrollBonus['days']);
                    if (!$isPayrollBonusChanged) {
                        $currentPayrollBonus['status'] = $oldBonus['status'];
                        $currentPayrollBonus['rejection_reason'] = $oldBonus['rejection_reason'];
                    }
                }
                $activityData['payroll_bonuses'][] = $currentPayrollBonus;
            }

            // Eliminar bonos y servicios que se hayan deseleccionado
            $activityData['services_list'] = $currentService ? [$currentService] : [];
            $activityData['field_bonuses'] = $currentFieldBonus ? [$currentFieldBonus] : [];
            $activityData['food_bonuses'] = $currentFoodBonus ? [$currentFoodBonus] : [];
            $activityData['payroll_bonuses'] = $currentPayrollBonus ? [$currentPayrollBonus] : [];


            $monthlyLog->addDailyActivity($request->date, $activityData);
            $monthlyLog->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actividad guardada exitosamente',
                'data' => $activityData
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar actividad: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
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
            'H' => 'Home Office',
            'V' => 'Viaje',
            'D' => 'Descanso',
            'VAC' => 'Vacaciones',
            'E' => 'Entrenamiento',
            'M' => 'Médico'
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
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado'
            ], 404);
        }

        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        return response()->json([
            'success' => true,
            'activities' => $monthlyLog ? $monthlyLog->daily_activities : []
        ]);
    }

    private function formatDate($date)
    {
        if (!$date) return 'N/A';
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $day = date('j', strtotime($date));
        $month = $months[date('n', strtotime($date))];
        $year = date('Y', strtotime($date));
        return "{$day} de {$month} de {$year}";
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$monthNumber];
    }

    /**
     * Obtiene el tipo de cambio actual de USD a MXN desde la API de Banxico.
     * @return float|null
     */
    private function getUsdToMxnExchangeRate()
    {
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
}
