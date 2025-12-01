<?php
namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance;
use App\Models\RecursosHumanos\LoadChart\FieldBonus;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use App\Models\RecursosHumanos\LoadChart\Meal;
use App\Models\RecursosHumanos\LoadChart\Services;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yasumi\Yasumi;

class CalendarController extends Controller
{
    /**
     * Muestra la vista del calendario
     */
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

        // --- OBTENER SALDOS de VACACIONES y CONTEO de DÍAS de DESCANSO ---
        $vacationBalance = EmployeeVacationBalance::firstOrNew(['employee_id' => $employee->id]);

        if (! $vacationBalance->exists) {
            $calculatedData = $this->calculateInitialVacationData($employee);
            $vacationBalance->fill($calculatedData);
            $vacationBalance->save();
            $vacationBalance->refresh();
        }

        $vacationDays = $vacationBalance->vacation_days_available;

        // ✅ LÓGICA: Contar los días de actividad 'D' (Descanso) en el mes
        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        $employeeActivities = $monthlyLog ? $monthlyLog->daily_activities : [];
        $totalRestDaysInMonth = 0;
        foreach ($employeeActivities as $activity) {
            if (($activity['activity_type'] ?? null) === 'D') {
                $totalRestDaysInMonth++;
            }
        }
        // -----------------------------------------------------------------

        $hire_date = $this->formatDate($employee->hire_date);
        $photo     = $employee->photo ? asset($employee->photo) : asset('assets/img/perfil.png');

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

        // 🚀 Lógica de Bonos de Campo MÁXIMAMENTE SIMPLIFICADA (SIN MAPEOS INTERMEDIOS)
        // Se usa el nombre del puesto del empleado (job_title) directamente como la categoría de bono.
        $employeeBonusCategory = $employee->job_title;

        // FILTRADO DE BONOS POR LA CATEGORÍA CALCULADA (debe ser una coincidencia exacta en FieldBonus)
        $fieldBonuses = FieldBonus::where('employee_category', $employeeBonusCategory)
            ->orderBy('bonus_identifier')
            ->get();
        // -----------------------------------------------------------------

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

        // Generar días del calendario
        $mandatoryHolidays = $this->getMandatoryHolidays($currentYear);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date              = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $isHoliday         = isset($mandatoryHolidays[$date]);
            $holidayName       = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $calendarDays[] = [
                'day'                  => $i,
                'current_month'        => true,
                'date'                 => $date,
                'is_holiday'           => $isHoliday,
                'holiday_name'         => $holidayName,
                'holiday_icon_type'    => $holidayIconType,
                'is_payroll_start_1' => ($fortnightlyConfig && $fortnightlyConfig->q1_start->format('Y-m-d') == $date),
                'is_payroll_end_1'   => ($fortnightlyConfig && $fortnightlyConfig->q1_end->format('Y-m-d') == $date),
                'is_payroll_start_2' => ($fortnightlyConfig && $fortnightlyConfig->q2_start->format('Y-m-d') == $date),
                'is_payroll_end_2'   => ($fortnightlyConfig && $fortnightlyConfig->q2_end->format('Y-m-d') == $date),
                'is_today'           => $date == date('Y-m-d'),
            ];
        }

        // Datos comunes para ambas vistas
        $viewData = [
            'employee'           => $employee,
            'hire_date'          => $hire_date,
            'employee_photo'     => $photo,
            'services'           => $services,
            'calendarDays'       => $calendarDays,
            'monthName'          => $monthName,
            'currentYear'        => $currentYear,
            'currentMonth'       => $currentMonth,
            'payrollDates'       => $payrollDates,
            'foodOptions'        => $foodOptions,
            'fieldBonuses'       => $fieldBonuses,
            'vacationDays'       => $vacationDays,
            'restDays'           => $totalRestDaysInMonth,
            'employeeActivities' => $employeeActivities,
            'isForModal'         => $isForModal,
        ];

        // Determinar qué vista retornar
        if ($isForModal) {
            // Para el modal - retornar JSON con el HTML
            try {
                // Asegúrate de que esta vista parcial contenga el HTML/Blade modificado
                $html = View::make('modulos.recursoshumanos.sistemas.loadchart.calendar_partial', $viewData)->render();

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
            // Vista normal del calendario
            return view('modulos.recursoshumanos.sistemas.loadchart.calendar', $viewData);
        }
    }

    /**
     * Devuelve los datos del calendario en formato JSON para las solicitudes AJAX.
     */
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

        // --- Solo días del mes actual ---
        $mandatoryHolidays = $this->getMandatoryHolidays($currentYear);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date              = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
            $isHoliday         = isset($mandatoryHolidays[$date]);
            $holidayName       = $isHoliday ? $mandatoryHolidays[$date]['name'] : null;
            $holidayIconType = $isHoliday ? $mandatoryHolidays[$date]['icon_type'] : null;

            $dayData = [
                'day'                  => $i,
                'current_month'        => true,
                'date'                 => $date,
                'is_holiday'           => $isHoliday,
                'holiday_name'         => $holidayName,
                'holiday_icon_type'    => $holidayIconType,
                'is_today'             => $date == date('Y-m-d'),
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

    /**
     * Devuelve los datos de balance para ser usados en AJAX
     */
    public function getEmployeeBalancesAjax(Request $request)
    {
        $employeeId = $request->input('employee_id') ?? Auth::user()->employee_id;
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $monthYear = sprintf('%04d-%02d', $year, $month);

        $employee = Employee::find($employeeId);

        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $vacationBalance = EmployeeVacationBalance::where('employee_id', $employee->id)->first();

        // ✅ LÓGICA: Contar los días de actividad 'D' (Descanso) en el mes
        $monthlyLog = EmployeeMonthlyWorkLog::where('employee_id', $employee->id)
            ->where('month_and_year', $monthYear)
            ->first();

        $employeeActivities = $monthlyLog ? $monthlyLog->daily_activities : [];
        $totalRestDaysInMonth = 0;
        foreach ($employeeActivities as $activity) {
            if (($activity['activity_type'] ?? null) === 'D') {
                $totalRestDaysInMonth++;
            }
        }
        // -----------------------------------------------------------------


        return response()->json([
            'success'      => true,
            'vacationDays' => $vacationBalance->vacation_days_available ?? 0,
            'totalRestDaysInMonth' => $totalRestDaysInMonth,
        ]);
    }

    /**
     * Función auxiliar para calcular datos iniciales de balance
     */
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

    /**
     * Verifica si una fecha de servicio ya está utilizada por otro registro en el Load Chart.
     * * @param int $employeeId
     * @param string $serviceRealDate
     * @param string $currentActivityDate La fecha de la actividad que se está guardando.
     * @return bool True si ya existe otro servicio usando esta fecha, False en caso contrario.
     */
    private function isServiceRealDateUsedByAnotherDay(int $employeeId, string $serviceRealDate, string $currentActivityDate): bool
    {
        // 1. Buscar todos los logs del empleado que tengan actividad de servicio.
        // Optimizamos buscando solo los logs que incluyen el mes de la fecha real de servicio O el mes de la actividad actual.
        $realDateMonthYear = Carbon::parse($serviceRealDate)->format('Y-m');
        $activityDateMonthYear = Carbon::parse($currentActivityDate)->format('Y-m');

        $monthlyLogs = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
            ->where(function ($query) use ($realDateMonthYear, $activityDateMonthYear) {
                $query->where('month_and_year', $realDateMonthYear)
                      ->orWhere('month_and_year', $activityDateMonthYear);
            })
            ->get();

        // 2. Recorrer las actividades en todos los logs encontrados.
        foreach ($monthlyLogs as $log) {
            foreach ($log->daily_activities as $date => $activity) {
                // Si encontramos un servicio en un día diferente al que estamos guardando, lo validamos.
                if ($date !== $currentActivityDate && ($activity['services_list'] ?? [])) {
                    $existingService = $activity['services_list'][0];
                    $existingRealDate = $existingService['service_real_date'] ?? null;

                    if ($existingRealDate === $serviceRealDate) {
                        return true; // Fecha ya usada por un servicio en otro día.
                    }
                }
            }
        }

        return false;
    }


    /**
     * Guarda una actividad diaria con los nuevos campos.
     */
    public function saveActivity(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $employee = Employee::find($user->employee_id);
            $monthYear = Carbon::create($request->displayed_year, $request->displayed_month, 1)->format('Y-m');

            $monthlyLog = EmployeeMonthlyWorkLog::firstOrCreate(
                ['employee_id' => $employee->id, 'month_and_year' => $monthYear],
                ['user_id' => $user->id, 'daily_activities' => []]
            );

            $activityData = $monthlyLog->getDailyActivity($request->date) ?? [];

            $isWellActivity = ($request->activity_type === 'P');

            // Verificar estados existentes
            $existingFoodBonus = $activityData['food_bonuses'][0] ?? null;
            $existingFieldBonus = $activityData['field_bonuses'][0] ?? null;
            $existingService = $activityData['services_list'][0] ?? null;

            // Estados de bloqueo (solo approved y reviewed mantienen el estado)
            $foodBonusLocked = $existingFoodBonus && in_array(strtolower($existingFoodBonus['status'] ?? ''), ['approved', 'reviewed']);
            $fieldBonusLocked = $existingFieldBonus && in_array(strtolower($existingFieldBonus['status'] ?? ''), ['approved', 'reviewed']);
            $serviceLocked = $existingService && in_array(strtolower($existingService['status'] ?? ''), ['approved', 'reviewed']);

            if ($request->has('activity_type')) {
                $activityType = $request->activity_type ?? 'N';

                // Si cambia la actividad principal, resetear a under_review
                if (($activityData['activity_type'] ?? '') !== $activityType) {
                    $activityData['activity_status'] = 'under_review';
                    $activityData['rejection_reason'] = null;
                }

                $activityData['activity_type'] = $activityType;
                $activityData['activity_description'] = $this->getActivityDescription($activityType);
                $activityData['commissioned_to'] = $request->commissioned_to;
                $activityData['well_name'] = $request->well_name;
                $activityData['has_service_bonus'] = $request->has_service_bonus;

                // --- NUEVOS CAMPOS DE VIAJE ---
                $activityData['travel_destination'] = $request->travel_destination;
                $activityData['travel_reason'] = $request->travel_reason;
                // --- FIN NUEVOS CAMPOS DE VIAJE ---

                // Limpiar bonos/servicios si la actividad principal no es 'P' y no están bloqueados
                if (!$isWellActivity) {
                    $activityData['has_service_bonus'] = 'no';

                    if (!$foodBonusLocked) {
                        $activityData['food_bonuses'] = [];
                    }
                    if (!$fieldBonusLocked) {
                        $activityData['field_bonuses'] = [];
                    }
                    if (!$serviceLocked) {
                        $activityData['services_list'] = [];
                    }
                }
            }

            // Procesar bonos de comida
            if ($request->has('food_bonus_number')) {
                if ($request->filled('food_bonus_number')) {
                    $meal = Meal::where('meal_number', $request->food_bonus_number)->first();
                    if ($meal) {
                        $newStatus = 'under_review';
                        $newRejectionReason = null;

                        if ($foodBonusLocked && $existingFoodBonus) {
                            $newStatus = $existingFoodBonus['status'];
                            $newRejectionReason = $existingFoodBonus['rejection_reason'] ?? null;
                        }

                        $activityData['food_bonuses'] = [[
                            'bonus_type' => 'Bono de Comida',
                            'num_daily' => (int) $meal->meal_number,
                            'daily_amount' => (float) $meal->amount,
                            'currency' => 'MXN',
                            'status' => $newStatus,
                            'rejection_reason' => $newRejectionReason,
                        ]];
                    }
                } else if (!$foodBonusLocked) {
                    $activityData['food_bonuses'] = [];
                }
            }

            // Procesar bono de campo
            if ($request->has('field_bonus_identifier')) {
                // 1. Obtener la categoría del empleado para validación
                $employeeBonusCategory = $employee->job_title;

                if ($request->filled('field_bonus_identifier')) {
                    // 2. CORRECCIÓN CLAVE: Buscar FieldBonus por ID Y por la categoría del empleado
                    $fieldBonus = FieldBonus::where('bonus_identifier', $request->field_bonus_identifier)
                        ->where('employee_category', $employeeBonusCategory) // 👈 Validar que el bono sea del puesto
                        ->first();

                    if (!$fieldBonus && !$fieldBonusLocked) {
                        // Si el bono no existe para su categoría (y no estaba previamente aprobado/revisado)
                         DB::rollback();
                         return response()->json([
                             'success' => false,
                             'message' => "El bono de campo seleccionado no es válido para su puesto ($employeeBonusCategory).",
                         ], 422);
                    }

                    if ($fieldBonus) {
                        $newStatus = 'under_review';
                        $newRejectionReason = null;

                        if ($fieldBonusLocked && $existingFieldBonus) {
                            $newStatus = $existingFieldBonus['status'];
                            $newRejectionReason = $existingFieldBonus['rejection_reason'] ?? null;
                        }

                        $activityData['field_bonuses'] = [[
                            'bonus_identifier' => $fieldBonus->bonus_identifier,
                            'bonus_type' => $fieldBonus->bonus_type,
                            'daily_amount' => (float) $fieldBonus->amount,
                            'currency' => $fieldBonus->currency,
                            'status' => $newStatus,
                            'rejection_reason' => $newRejectionReason,
                        ]];
                    }
                } else if (!$fieldBonusLocked) {
                    $activityData['field_bonuses'] = [];
                }
            }

            // 🥇 Procesar servicios con validación de service_real_date
            if ($request->has('service_identifier')) {
                $serviceIdentifierProvided = $request->filled('service_identifier');

                if (($serviceIdentifierProvided && ($request->has_service_bonus === 'si')) || $serviceLocked) {

                    $service = null;
                    if ($serviceIdentifierProvided) {
                        $service = Services::where('identifier', $request->service_identifier)->first();
                    }

                    if ($service || $serviceLocked) {
                        $newStatus = 'under_review';
                        $newRejectionReason = null;
                        $realDateToSave = $request->service_real_date; // Valor por defecto del request

                        $serviceDataToUse = $service ?? $existingService;

                        if ($serviceLocked && $existingService) {
                            $newStatus = $existingService['status'];
                            $newRejectionReason = $existingService['rejection_reason'] ?? null;

                            // Si está bloqueado, usamos el valor que vino del JS
                            $realDateToSave = $request->service_real_date ?? $existingService['service_real_date'] ?? null;
                        }

                        // ====================================================================
                        // ⚠️ NUEVA LÓGICA DE VALIDACIÓN DE NEGOCIO (service_real_date)
                        // ====================================================================
                        if ($request->filled('service_real_date') && !$serviceLocked) {
                            // 1. Chequear si la fecha real es diferente a la fecha de la actividad actual
                            $isDateChanged = $realDateToSave !== $request->date;

                            // 2. Si la fecha real es diferente O si ya había un servicio existente
                            if ($isDateChanged || $existingService) {

                                // 3. Chequear si ESTA FECHA REAL YA ESTÁ USADA POR OTRA ACTIVIDAD/SERVICIO del mismo empleado.
                                if ($this->isServiceRealDateUsedByAnotherDay($employee->id, $realDateToSave, $request->date)) {
                                    DB::rollback();
                                    return response()->json([
                                        'success' => false,
                                        'message' => "La fecha de servicio '{$realDateToSave}' ya está registrada para otro servicio. Un servicio por día.",
                                    ], 422);
                                }
                            }
                        }
                        // ====================================================================

                        if ($serviceDataToUse) {
                            $activityData['services_list'] = [[
                                'service_identifier' => $serviceDataToUse['identifier'] ?? $serviceDataToUse['service_identifier'],
                                'service_performed' => $serviceDataToUse['service_performed'],
                                'service_name' => $serviceDataToUse['service_description'] ?? $serviceDataToUse['service_name'],
                                'amount' => (float) ($serviceDataToUse['amount']),
                                'currency' => $serviceDataToUse['currency'],
                                'status' => $newStatus,
                                'rejection_reason' => $newRejectionReason,
                                // 🥇 CAMPO ALMACENADO FINAL
                                'service_real_date' => $realDateToSave,
                            ]];
                        }
                    }
                } else if (!$serviceLocked) {
                    // Si no se quiere servicio y no está bloqueado, se limpia
                    $activityData['services_list'] = [];
                }
            }

            // El resto del código se mantiene igual...

            $isAnythingLeft = ($activityData['activity_type'] ?? 'N') !== 'N' ||
            !empty($activityData['food_bonuses']) ||
            !empty($activityData['field_bonuses']) ||
            !empty($activityData['services_list']);

            if (!$isAnythingLeft) {
                $monthlyLog->removeDailyActivity($request->date);
                $monthlyLog->save();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Actividad eliminada.']);
            }

            if (empty($monthlyLog->getDailyActivity($request->date))) {
                $activityData['date'] = $request->date;
                $activityData['payroll_period_marker'] = $this->determinePayrollPeriodMarker($request->date, $request->displayed_month, $request->displayed_year);
            }

            $activityData['day_status'] = $this->recalculateDayStatus($activityData);

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
            // 💡 Log::error mejorado para incluir más contexto del error
            Log::error('Error al guardar actividad (saveActivity): ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno del servidor. Consulte el log para más detalles.'], 500);
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

    /**
     * Obtiene las actividades de un empleado para un mes específico
     */
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
            'success'      => true,
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
        $hasRejected      = false;
        $hasUnderReview   = false;
        $hasApproved      = false;
        $hasReviewed      = false;
        $totalItems       = 0;
        $statusesToLock = ['approved', 'reviewed'];

        // 1. Verificar la actividad principal (si existe y no es 'N')
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

        // 2. Verificar bonos y servicios
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
        if ($hasReviewed && ! $hasApproved && ! $hasRejected && ! $hasUnderReview) {
            return 'reviewed';
        }

        return 'under_review'; // Caso catch-all si la lógica falla o si solo hay ítems vacíos (aunque totalItems > 0)
    }
}
