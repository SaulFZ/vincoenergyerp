<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class CalendarController extends Controller
{
    public function index()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener los datos del empleado con relación al usuario
        $employee = Employee::with('user')->find($user->employee_id);

        // Formatear la fecha de ingreso en formato "15 de Marzo de 2020"
        $hire_date = $employee ? $this->formatDate($employee->hire_date) : 'N/A';

        // Obtener la foto del empleado o usar una por defecto
        $photo = $employee && $employee->photo
            ? asset($employee->photo)
            : asset('assets/img/perfil.png');

        return view('modulos.recursoshumanos.sistemas.loadchart.calendar', [
            'employee' => $employee,
            'hire_date' => $hire_date,
            'employee_photo' => $photo
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
