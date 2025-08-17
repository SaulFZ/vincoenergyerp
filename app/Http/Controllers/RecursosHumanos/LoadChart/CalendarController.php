<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\Services;

class CalendarController extends Controller
{
    public function index()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener los datos del empleado con relación al usuario
        $employee = Employee::with('user')->find($user->employee_id);

        // Formatear la fecha de ingreso
        $hire_date = $employee ? $this->formatDate($employee->hire_date) : 'N/A';

        // Obtener la foto del empleado
        $photo = $employee && $employee->photo
            ? asset($employee->photo)
            : asset('assets/img/perfil.png');

        // Obtener servicios en formato plano (sin agrupamiento complejo)
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
            ->groupBy('operation_type'); // Solo agrupar por operation_type

        return view('modulos.recursoshumanos.sistemas.loadchart.calendar', [
            'employee' => $employee,
            'hire_date' => $hire_date,
            'employee_photo' => $photo,
            'services' => $services // Cambiado de groupedServices a services
        ]);
    }

    private function formatDate($date)
    {
        if (!$date) return 'N/A';

        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $day = date('j', strtotime($date));
        $month = $months[date('n', strtotime($date))];
        $year = date('Y', strtotime($date));

        return "{$day} de {$month} de {$year}";
    }
}
