<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * StatsController
 *
 * Sirve la vista de estadísticas y expone un endpoint AJAX que agrega
 * todos los datos de bonos del año directamente desde los JSON diarios
 * guardados en EmployeeMonthlyWorkLog.daily_activities.
 *
 * ──────────────────────────────────────────────────────────────────────
 * RUTAS (dentro del group de loadchart en web.php):
 *
 *   // Sustituye la closure actual de /stats:
 *   Route::controller(StatsController::class)->group(function () {
 *       Route::get('/stats',      'index')->name('loadchart.stats');
 *       Route::get('/stats/data', 'getData')->name('loadchart.stats.data');
 *   });
 *
 *   // Añade el use al bloque de imports del archivo de rutas:
 *   use App\Http\Controllers\RecursosHumanos\LoadChart\StatsController;
 * ──────────────────────────────────────────────────────────────────────
 */
class StatsLoadController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // CONSTANTES
    // ──────────────────────────────────────────────────────────────

    private const MONTH_NAMES = [
        1  => 'Enero',      2  => 'Febrero',   3  => 'Marzo',
        4  => 'Abril',      5  => 'Mayo',       6  => 'Junio',
        7  => 'Julio',      8  => 'Agosto',     9  => 'Septiembre',
        10 => 'Octubre',    11 => 'Noviembre',  12 => 'Diciembre',
    ];

    private const MONTH_PREFIXES = [
        1  => 'ene',  2  => 'feb',  3  => 'mar',  4  => 'abr',
        5  => 'may',  6  => 'jun',  7  => 'jul',  8  => 'ago',
        9  => 'sep',  10 => 'oct',  11 => 'nov',  12 => 'dic',
    ];

    // ──────────────────────────────────────────────────────────────
    // VISTA
    // ──────────────────────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        return view('modulos.recursoshumanos.loadchart.stats');
    }

    // ──────────────────────────────────────────────────────────────
    // ENDPOINT AJAX
    // GET /recursoshumanos/loadchart/stats/data?year=2026
    //
    // Respuesta:
    // {
    //   success:       bool,
    //   year:          int,
    //   empleadosData: [],   // bonos de campo por quincena por empleado
    //   quincenasData: [],   // resumen ejecutivo por quincena global
    //   mesData:       [],   // totales mensuales MXN
    //   pozosData:     [],   // costo acumulado de bonos de campo por pozo
    //   serviciosData: [],   // servicios registrados (services_list)
    // }
    // ──────────────────────────────────────────────────────────────

    public function getData(Request $request): \Illuminate\Http\JsonResponse
    {
        $year = (int) $request->input('year', date('Y'));

        try {
            // 1. Configuraciones quincenales del año indexadas por mes (1-12)
            $configs = FortnightlyConfig::where('year', $year)
                ->get()
                ->keyBy('month');

            // 2. Logs del año con empleado
            $logs = EmployeeMonthlyWorkLog::with('employee')
                ->where('month_and_year', 'like', $year . '-%')
                ->get();

            // 3. Estructuras de acumulación
            $empleadosMap  = [];   // [employee_id => array]
            $quincenaMap   = [];   // [globalQ     => array]
            $pozosMap      = [];   // [pozo_mes_q  => array]
            $serviciosMap  = [];   // [service+fecha => array]

            // 4. Recorrer logs
            foreach ($logs as $log) {
                $employee = $log->employee;
                if (! $employee) {
                    continue;
                }

                $monthNum = (int) substr($log->month_and_year, 5, 2);
                $prefix   = self::MONTH_PREFIXES[$monthNum] ?? null;
                if (! $prefix) {
                    continue;
                }

                $config = $configs->get($monthNum);
                $empId  = $employee->id;

                if (! isset($empleadosMap[$empId])) {
                    $empleadosMap[$empId] = $this->buildEmpleadoBase($employee);
                }

                // 5. Actividades diarias
                foreach (($log->daily_activities ?? []) as $activity) {
                    $date = $activity['date'] ?? null;
                    if (! $date) {
                        continue;
                    }

                    $qNum = $this->resolveQuincenaNumber($date, $config);
                    if (! $qNum) {
                        continue;
                    }

                    // ── Bonos de campo ────────────────────────────────────
                    $fieldBonusDay = $this->sumFieldBonuses($activity['field_bonuses'] ?? []);

                    if ($fieldBonusDay > 0) {
                        $key = $prefix . $qNum;
                        $empleadosMap[$empId][$key] = round(
                            ($empleadosMap[$empId][$key] ?? 0) + $fieldBonusDay, 2
                        );
                    }

                    // ── Acumular quincena global ──────────────────────────
                    $globalQ = ($monthNum - 1) * 2 + $qNum;
                    $this->accumulateQuincena(
                        $quincenaMap, $globalQ,
                        $monthNum, $qNum,
                        $fieldBonusDay, $activity, $employee
                    );

                    // ── Pozos (actividades tipo P con well_name) ──────────
                    if (
                        ($activity['activity_type'] ?? '') === 'P' &&
                        ! empty($activity['well_name'])
                    ) {
                        $this->accumulatePozo(
                            $pozosMap,
                            $activity['well_name'],
                            $monthNum,
                            $qNum,
                            $fieldBonusDay
                        );
                    }

                    // ── Servicios (services_list) ─────────────────────────
                    foreach (($activity['services_list'] ?? []) as $svc) {
                        $this->accumulateServicio(
                            $serviciosMap, $svc,
                            $monthNum, $qNum,
                            $date,
                            $employee
                        );
                    }
                }
            }

            // 6. Ordenar y calcular % variación entre quincenas
            ksort($quincenaMap);
            $quincenaArray = $this->attachPctDif(array_values($quincenaMap));

            // 7. Totales mensuales
            $mesData = $this->buildMesData($quincenaArray);

            return response()->json([
                'success'       => true,
                'year'          => $year,
                'empleadosData' => array_values($empleadosMap),
                'quincenasData' => $quincenaArray,
                'mesData'       => $mesData,
                'pozosData'     => array_values($pozosMap),
                'serviciosData' => array_values($serviciosMap),
            ]);

        } catch (\Throwable $e) {
            Log::error('StatsController@getData: ' . $e->getMessage(), [
                'year'  => $year,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las estadísticas: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────────────────────

    /**
     * Array base de un empleado con todas las quincenas del año en 0.
     *
     * Campos del modelo Employee (según app/Models/Employee.php):
     *   first_name, second_name, first_surname, second_surname
     *   full_name, employee_number, department
     */
    private function buildEmpleadoBase(Employee $employee): array
    {
        // Nombre completo: usa full_name si existe, sino concatena las partes
        $fullName = strtoupper(trim((string) $employee->full_name));

        if ($fullName === '') {
            $fullName = strtoupper(trim(implode(' ', array_filter([
                $employee->first_name    ?? null,
                $employee->second_name   ?? null,
                $employee->first_surname ?? null,
                $employee->second_surname ?? null,
            ]))));
        }

        if ($fullName === '') {
            $fullName = 'SIN NOMBRE';
        }

        // Clave del empleado: employee_number es el campo correcto según el modelo
        $clave = $employee->employee_number
              ?? 'V' . str_pad($employee->id, 5, '0', STR_PAD_LEFT);

        $base = [
            'id'     => $employee->id,
            'clave'  => strtoupper((string) $clave),
            'nombre' => $fullName,
            'area'   => strtoupper($employee->department ?? 'SIN ÁREA'),
        ];

        // Inicializar todas las quincenas en 0
        foreach (self::MONTH_PREFIXES as $p) {
            $base[$p . '1'] = 0.0;
            $base[$p . '2'] = 0.0;
        }

        return $base;
    }

    /**
     * Suma los daily_amount de todos los field_bonuses de un día.
     */
    private function sumFieldBonuses(array $bonuses): float
    {
        return (float) array_reduce(
            $bonuses,
            static fn (float $c, array $b): float => $c + (float) ($b['daily_amount'] ?? 0),
            0.0
        );
    }

    /**
     * Determina si la fecha pertenece a la 1ra (1) o 2da (2) quincena
     * usando FortnightlyConfig. Fallback: días 1-15 → Q1, 16-fin → Q2.
     */
    private function resolveQuincenaNumber(string $date, ?FortnightlyConfig $config): ?int
    {
        if (! $config) {
            return (int) Carbon::parse($date)->format('d') <= 15 ? 1 : 2;
        }

        $d = Carbon::parse($date);

        if ($d->between(
            Carbon::parse($config->q1_start)->startOfDay(),
            Carbon::parse($config->q1_end)->endOfDay()
        )) {
            return 1;
        }

        if ($d->between(
            Carbon::parse($config->q2_start)->startOfDay(),
            Carbon::parse($config->q2_end)->endOfDay()
        )) {
            return 2;
        }

        // Fecha fuera de ambas quincenas configuradas (p.ej. día festivo suelto)
        // Usamos fallback en lugar de devolver null para no perder el dato
        return (int) Carbon::parse($date)->format('d') <= 15 ? 1 : 2;
    }

    /**
     * Acumula bonos/servicios/suministros en $quincenaMap.
     */
    private function accumulateQuincena(
        array    &$map,
        int       $globalQ,
        int       $monthNum,
        int       $qNum,
        float     $fieldBonusDay,
        array     $activity,
        Employee  $employee
    ): void {
        if (! isset($map[$globalQ])) {
            $map[$globalQ] = [
                'num'              => $globalQ,
                'periodo'          => ($qNum === 1 ? '1era ' : '2da ') . self::MONTH_NAMES[$monthNum],
                'mes'              => self::MONTH_NAMES[$monthNum],
                'quincena'         => $qNum === 1 ? '1RA' : '2DA',
                'bonosMxn'         => 0.0,
                'serviciosCount'   => 0,
                'suministrosCount' => 0,
                '_suministrosEmp'  => [],  // helper interno; se elimina antes de devolver
            ];
        }

        $map[$globalQ]['bonosMxn'] = round(
            $map[$globalQ]['bonosMxn'] + $fieldBonusDay, 2
        );

        // Contar entradas en services_list
        $map[$globalQ]['serviciosCount'] += count($activity['services_list'] ?? []);

        // Empleados únicos de Suministros con bono ese periodo
        if (
            $fieldBonusDay > 0 &&
            stripos($employee->department ?? '', 'suministro') !== false
        ) {
            $map[$globalQ]['_suministrosEmp'][$employee->id] = true;
            $map[$globalQ]['suministrosCount'] =
                count($map[$globalQ]['_suministrosEmp']);
        }
    }

    /**
     * Acumula el importe de bonos de campo por pozo/mes/quincena.
     * "costo" aquí es la suma de field_bonuses pagados ese día en ese pozo.
     */
    private function accumulatePozo(
        array  &$map,
        string  $wellName,
        int     $monthNum,
        int     $qNum,
        float   $cost
    ): void {
        $quinLabel = $qNum === 1 ? '1RA QUINCENA' : '2DA QUINCENA';
        // Normalizar nombre del pozo (los JSONes a veces tienen espacios/guiones extra)
        $wellNameClean = trim($wellName);
        $pozoKey       = $wellNameClean . '_' . $monthNum . '_' . $qNum;

        if (! isset($map[$pozoKey])) {
            $map[$pozoKey] = [
                'mes'      => self::MONTH_NAMES[$monthNum],
                'quincena' => $quinLabel,
                'pozo'     => $wellNameClean,
                'costo'    => 0.0,
            ];
        }

        $map[$pozoKey]['costo'] = round($map[$pozoKey]['costo'] + $cost, 2);
    }

    /**
     * Acumula los servicios registrados en services_list.
     * Cada servicio es único por fecha real (service_real_date) + empleado.
     */
    private function accumulateServicio(
        array    &$map,
        array     $svc,
        int       $monthNum,
        int       $qNum,
        string    $activityDate,
        Employee  $employee
    ): void {
        $identifier  = $svc['service_identifier'] ?? 'SIN_ID';
        $realDate    = $svc['service_real_date']   ?? $activityDate;
        $key         = $identifier . '_' . $realDate . '_' . $employee->id;

        if (isset($map[$key])) {
            return; // Ya contabilizado (no debe duplicarse, pero por si acaso)
        }

        $map[$key] = [
            'mes'                => self::MONTH_NAMES[$monthNum],
            'quincena'           => $qNum === 1 ? '1RA QUINCENA' : '2DA QUINCENA',
            'service_identifier' => $identifier,
            'service_name'       => $svc['service_name']      ?? $svc['service_description'] ?? '',
            'service_performed'  => $svc['service_performed']  ?? '',
            'amount'             => (float) ($svc['amount']    ?? 0),
            'currency'           => $svc['currency']           ?? 'MXN',
            'status'             => $svc['status']             ?? 'under_review',
            'service_real_date'  => $realDate,
            'empleado_id'        => $employee->id,
            'empleado_nombre'    => strtoupper((string) ($employee->full_name ?? '')),
        ];
    }

    /**
     * Agrega el campo pctDif (% variación vs quincena anterior) y
     * elimina el campo helper interno _suministrosEmp.
     */
    private function attachPctDif(array $qs): array
    {
        foreach ($qs as $i => &$q) {
            unset($q['_suministrosEmp']);

            if ($i === 0 || $qs[$i - 1]['bonosMxn'] <= 0) {
                $q['pctDif'] = null;
                continue;
            }

            $prev        = $qs[$i - 1]['bonosMxn'];
            $curr        = $q['bonosMxn'];
            $q['pctDif'] = (int) round((($curr - $prev) / $prev) * 100);
        }
        unset($q);

        return $qs;
    }

    /**
     * Agrupa las quincenas por mes y suma bonosMxn.
     */
    private function buildMesData(array $quincenas): array
    {
        $map = [];

        foreach ($quincenas as $q) {
            $mes = $q['mes'];
            if (! isset($map[$mes])) {
                $map[$mes] = [
                    'mes'      => $mes,
                    'bonosMxn' => 0.0,
                    'bonosUsd' => 0,   // Extender si se registran bonos en USD
                    'hc'       => 0,   // Extender con conteo real de HC activo
                ];
            }
            $map[$mes]['bonosMxn'] = round($map[$mes]['bonosMxn'] + $q['bonosMxn'], 2);
        }

        return array_values($map);
    }
}
