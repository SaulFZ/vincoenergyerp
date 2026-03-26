<?php
namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RH\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RH\LoadChart\FortnightlyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatsLoadController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // CONSTANTES
    // ──────────────────────────────────────────────────────────────

    private const MONTH_NAMES = [
        1  => 'Enero', 2    => 'Febrero', 3    => 'Marzo',
        4  => 'Abril', 5    => 'Mayo', 6       => 'Junio',
        7  => 'Julio', 8    => 'Agosto', 9     => 'Septiembre',
        10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    private const MONTH_PREFIXES = [
        1 => 'ene', 2  => 'feb', 3  => 'mar', 4  => 'abr',
        5 => 'may', 6  => 'jun', 7  => 'jul', 8  => 'ago',
        9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
    ];

    // ──────────────────────────────────────────────────────────────
    // VISTA
    // ──────────────────────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        return view('modulos.rh.loadchart.stats');
    }

    // ──────────────────────────────────────────────────────────────
    // ENDPOINT AJAX
    // ──────────────────────────────────────────────────────────────

    public function getData(Request $request): \Illuminate\Http\JsonResponse
    {
        $year = (int) $request->input('year', date('Y'));

        try {
            $configs = FortnightlyConfig::where('year', $year)
                ->get()
                ->keyBy('month');

            $logs = EmployeeMonthlyWorkLog::with('employee')
                ->where('month_and_year', 'like', $year . '-%')
                ->get();

            $empleadosMap = []; // [employee_id => array]
            $areasMap     = []; // [area_name => array] ⭐ NUEVO MAPA DE ÁREAS
            $quincenaMap  = []; // [globalQ     => array]
            $pozosMap     = []; // [pozo_mes_q  => array]
            $serviciosMap = []; // [service+fecha => array]

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

                        // 1. Acumular al Empleado (Siempre se le paga a él)
                        $empleadosMap[$empId][$key] = round(
                            ($empleadosMap[$empId][$key] ?? 0) + $fieldBonusDay, 2
                        );

                        // ⭐ 2. ACUMULAR AL ÁREA (Lógica de Comisionados)
                        $actType = $activity['activity_type'] ?? 'N';
                        $vType   = $activity['activity_type_vespertina'] ?? 'N';

                        $isCommissioned = ($actType === 'C' || $vType === 'C') && ! empty($activity['commissioned_to']);

                        $empArea    = strtoupper(trim($employee->department ?? 'SIN ÁREA'));
                        $targetArea = $isCommissioned ? strtoupper(trim($activity['commissioned_to'])) : $empArea;

                        if (! isset($areasMap[$targetArea])) {
                            $areasMap[$targetArea] = ['area' => $targetArea];
                        }
                        if (! isset($areasMap[$targetArea][$key])) {
                            $areasMap[$targetArea][$key] = ['normal' => 0.0, 'comisionado' => 0.0, 'comisionado_fuentes' => []];
                        }

                        if ($isCommissioned) {
                            $areasMap[$targetArea][$key]['comisionado'] += $fieldBonusDay;

                            // ⭐ NUEVO: Registrar el nombre del empleado Y su área de origen
                            $empName   = $empleadosMap[$empId]['nombre'];
                            $fuenteKey = $empName . ' (De: ' . $empArea . ')';

                            if (! isset($areasMap[$targetArea][$key]['comisionado_fuentes'][$fuenteKey])) {
                                $areasMap[$targetArea][$key]['comisionado_fuentes'][$fuenteKey] = 0.0;
                            }
                            $areasMap[$targetArea][$key]['comisionado_fuentes'][$fuenteKey] += $fieldBonusDay;

                        } else {
                            $areasMap[$targetArea][$key]['normal'] += $fieldBonusDay;
                        }
                    }

                    // ── Acumular quincena global ──────────────────────────
                    $globalQ = ($monthNum - 1) * 2 + $qNum;
                    $this->accumulateQuincena(
                        $quincenaMap, $globalQ,
                        $monthNum, $qNum,
                        $fieldBonusDay, $activity, $employee
                    );

                    // ── Pozos (actividades tipo P con well_name) ──────────
                    if (($activity['activity_type'] ?? '') === 'P' && ! empty($activity['well_name'])) {
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

            ksort($quincenaMap);
            $quincenaArray = $this->attachPctDif(array_values($quincenaMap));
            $mesData       = $this->buildMesData($quincenaArray);

            return response()->json([
                'success'       => true,
                'year'          => $year,
                'empleadosData' => array_values($empleadosMap),
                'areasData'     => array_values($areasMap), // ⭐ SE ENVÍA AL FRONTEND
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

    private function buildEmpleadoBase(Employee $employee): array
    {
        $fullName = strtoupper(trim((string) $employee->full_name));

        if ($fullName === '') {
            $fullName = strtoupper(trim(implode(' ', array_filter([
                $employee->first_name ?? null,
                $employee->second_name ?? null,
                $employee->first_surname ?? null,
                $employee->second_surname ?? null,
            ]))));
        }

        if ($fullName === '') {
            $fullName = 'SIN NOMBRE';
        }

        $clave = $employee->employee_number ?? 'V' . str_pad($employee->id, 5, '0', STR_PAD_LEFT);

        $base = [
            'id'     => $employee->id,
            'clave'  => strtoupper((string) $clave),
            'nombre' => $fullName,
            'area'   => strtoupper($employee->department ?? 'SIN ÁREA'),
        ];

        foreach (self::MONTH_PREFIXES as $p) {
            $base[$p . '1'] = 0.0;
            $base[$p . '2'] = 0.0;
        }

        return $base;
    }

    private function sumFieldBonuses(array $bonuses): float
    {
        return (float) array_reduce(
            $bonuses,
            static fn(float $c, array $b): float => $c + (float) ($b['daily_amount'] ?? 0),
            0.0
        );
    }

    private function resolveQuincenaNumber(string $date, ?FortnightlyConfig $config): ?int
    {
        if (! $config) {
            return (int) Carbon::parse($date)->format('d') <= 15 ? 1 : 2;
        }
        $d = Carbon::parse($date);
        if ($d->between(Carbon::parse($config->q1_start)->startOfDay(), Carbon::parse($config->q1_end)->endOfDay())) {
            return 1;
        }
        if ($d->between(Carbon::parse($config->q2_start)->startOfDay(), Carbon::parse($config->q2_end)->endOfDay())) {
            return 2;
        }
        return (int) Carbon::parse($date)->format('d') <= 15 ? 1 : 2;
    }

    private function accumulateQuincena(array &$map, int $globalQ, int $monthNum, int $qNum, float $fieldBonusDay, array $activity, Employee $employee): void
    {
        if (! isset($map[$globalQ])) {
            $map[$globalQ] = [
                'num'              => $globalQ,
                'periodo'          => ($qNum === 1 ? '1era ' : '2da ') . self::MONTH_NAMES[$monthNum],
                'mes'              => self::MONTH_NAMES[$monthNum],
                'quincena'         => $qNum === 1 ? '1RA' : '2DA',
                'bonosMxn'         => 0.0,
                'serviciosCount'   => 0,
                'suministrosCount' => 0,
                '_suministrosEmp'  => [],
            ];
        }

        $map[$globalQ]['bonosMxn']        = round($map[$globalQ]['bonosMxn'] + $fieldBonusDay, 2);
        $map[$globalQ]['serviciosCount'] += count($activity['services_list'] ?? []);

        if ($fieldBonusDay > 0 && stripos($employee->department ?? '', 'suministro') !== false) {
            $map[$globalQ]['_suministrosEmp'][$employee->id] = true;
            $map[$globalQ]['suministrosCount']               = count($map[$globalQ]['_suministrosEmp']);
        }
    }

    private function accumulatePozo(array &$map, string $wellName, int $monthNum, int $qNum, float $cost): void
    {
        $quinLabel     = $qNum === 1 ? '1RA QUINCENA' : '2DA QUINCENA';
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

    private function accumulateServicio(array &$map, array $svc, int $monthNum, int $qNum, string $activityDate, Employee $employee): void
    {
        $identifier = $svc['service_identifier'] ?? 'SIN_ID';
        $realDate   = $svc['service_real_date'] ?? $activityDate;
        $key        = $identifier . '_' . $realDate . '_' . $employee->id;

        if (isset($map[$key])) {
            return;
        }

        $map[$key] = [
            'mes'                => self::MONTH_NAMES[$monthNum],
            'quincena'           => $qNum === 1 ? '1RA QUINCENA' : '2DA QUINCENA',
            'service_identifier' => $identifier,
            'service_name'       => $svc['service_name'] ?? $svc['service_description'] ?? '',
            'service_performed'  => $svc['service_performed'] ?? '',
            'amount'             => (float) ($svc['amount'] ?? 0),
            'currency'           => $svc['currency'] ?? 'MXN',
            'status'             => $svc['status'] ?? 'under_review',
            'service_real_date'  => $realDate,
            'empleado_id'        => $employee->id,
            'empleado_nombre'    => strtoupper((string) ($employee->full_name ?? '')),
        ];
    }

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

    private function buildMesData(array $quincenas): array
    {
        $map = [];

        foreach ($quincenas as $q) {
            $mes = $q['mes'];
            if (! isset($map[$mes])) {
                $map[$mes] = [
                    'mes'      => $mes,
                    'bonosMxn' => 0.0,
                    'bonosUsd' => 0,
                    'hc'       => 0,
                ];
            }
            $map[$mes]['bonosMxn'] = round($map[$mes]['bonosMxn'] + $q['bonosMxn'], 2);
        }

        return array_values($map);
    }
}
