<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoadchartController extends Controller
{
    /**
     * Muestra la vista del calendario del loadchart.
     * @return \Illuminate\View\View
     */
    public function showCalendar()
    {
        // Puedes pasar datos estáticos si es necesario, como en tu código original
        $employee = (object) ['full_name' => 'John Doe', 'employee_number' => '12345', 'department' => 'Ingeniería', 'job_title' => 'Ingeniero'];
        $employee_photo = 'https://via.placeholder.com/150';
        $hire_date = '2022-01-15';

        return view('loadchart.calendar', compact('employee', 'employee_photo', 'hire_date'));
    }

    /**
     * Obtiene la configuración de las quincenas para un mes y año específicos.
     * Este método será llamado por AJAX desde tu JavaScript.
     * @param int $year
     * @param int $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFortnightlyConfig($year, $month)
    {
        $config = FortnightlyConfig::where('year', $year)
                                    ->where('month', $month)
                                    ->first();

        // Si no existe una configuración, podemos generar una por defecto
        if (!$config) {
            $config = $this->generateDefaultConfig($year, $month);
        }

        return response()->json($config);
    }

    /**
     * Genera y guarda una configuración de quincenas por defecto.
     * Esto es útil para los meses en los que no hay datos preexistentes.
     * @param int $year
     * @param int $month
     * @return \App\Models\FortnightlyConfig
     */
    private function generateDefaultConfig($year, $month)
    {
        $firstDay = Carbon::create($year, $month, 1, 0, 0, 0);
        $fifteenthDay = Carbon::create($year, $month, 15, 0, 0, 0);
        $sixteenthDay = Carbon::create($year, $month, 16, 0, 0, 0);
        $lastDay = Carbon::create($year, $month)->endOfMonth();

        // Si el día 16 es en un mes diferente (ej. un mes de 30 días, y el 16 cae en el siguiente),
        // ajustamos el inicio de la segunda quincena al día 16 del mes correcto.
        if ($sixteenthDay->month != $month) {
            $sixteenthDay = Carbon::create($year, $month, 16, 0, 0, 0);
        }

        return FortnightlyConfig::create([
            'year' => $year,
            'month' => $month,
            'q1_start' => $firstDay->format('Y-m-d'),
            'q1_end' => $fifteenthDay->format('Y-m-d'),
            'q2_start' => $sixteenthDay->format('Y-m-d'),
            'q2_end' => $lastDay->format('Y-m-d'),
        ]);
    }
}
