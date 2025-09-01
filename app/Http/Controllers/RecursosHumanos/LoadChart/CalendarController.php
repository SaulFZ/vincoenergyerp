<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\Services;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use App\Models\RecursosHumanos\LoadChart\Meal;
use App\Models\RecursosHumanos\LoadChart\FieldBonus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Muestra la vista inicial del calendario.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::with('user')->find($user->employee_id);
        $hire_date = $employee ? $this->formatDate($employee->hire_date) : 'N/A';
        $photo = $employee && $employee->photo ? asset($employee->photo) : asset('assets/img/perfil.png');

        $services = Services::select(
            'operation_type',
            'service_type',
            'service_performed',
            'identifier',
            'service_description'
        )
        ->orderBy('operation_type')
        ->orderBy('service_type')
        ->orderBy('service_performed')
        ->get()
        ->groupBy('operation_type');

        $foodOptions = Meal::orderBy('meal_number')->get();
        $fieldBonuses = FieldBonus::orderBy('bonus_identifier')->get();

        $currentMonth = $request->input('month', date('n'));
        $currentYear = $request->input('year', date('Y'));

        // Obtener las configuraciones de quincenas para la vista inicial
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

        // La lógica de `calendarDays` ya no es necesaria aquí, pero la mantengo para la carga inicial.
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $monthName = $this->getMonthName($currentMonth);
        $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
        $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;
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

        $nextMonth = ($currentMonth == 12) ? 1 : $currentMonth + 1;
        $nextYear = ($currentMonth == 12) ? $currentYear + 1 : $currentYear;
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
        $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
        $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;
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

        $nextMonth = ($currentMonth == 12) ? 1 : $currentMonth + 1;
        $nextYear = ($currentMonth == 12) ? $currentYear + 1 : $currentYear;
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

    private function formatDate($date)
    {
        if (!$date) return 'N/A';
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $day = date('j', strtotime($date));
        $month = $months[date('n', strtotime($date))];
        $year = date('Y', strtotime($date));
        return "{$day} de {$month} de {$year}";
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$monthNumber];
    }
}
