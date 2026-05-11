<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RH\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RH\LoadChart\FortnightlyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class StatsLoadController extends Controller
{
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

    public function index(): \Illuminate\View\View
    {
        return view('modules.rh.loadchart.stats');
    }

    public function getData(Request $request): \Illuminate\Http\JsonResponse
    {
        $year = (int) $request->input('year', date('Y'));

        try {
            $configs = FortnightlyConfig::where('year', $year)
                ->get()
                ->keyBy('month');

            $logs = EmployeeMonthlyWorkLog::with(['employee.area'])
                ->where('month_and_year', 'like', $year . '-%')
                ->get();

            $tcRates = $this->fetchMonthlyExchangeRates($year);

            $empleadosMap            = [];
            $areasMap                = [];
            $quincenaMap             = [];
            $pozosMap                = [];
            $serviciosMap            = [];
            $suministrosMap          = [];
            $actividadesPorEmpleado  = [];

            $actividadesMap = [];
            for ($i = 1; $i <= 12; $i++) {
                $actividadesMap[$i] = [
                    'mes' => self::MONTH_NAMES[$i],
                    'B' => 0, 'P' => 0, 'C' => 0, 'TC' => 0, 'V' => 0,
                    'D' => 0, 'VAC' => 0, 'M' => 0, 'E' => 0, 'A' => 0, 'PE' => 0
                ];
            }

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

                $config      = $configs->get($monthNum);
                $empId       = $employee->id;
                $empAreaName = $employee->area ? $employee->area->name : 'SIN ÁREA';
                $empArea     = strtoupper(trim($empAreaName));
                $mesNombre   = self::MONTH_NAMES[$monthNum];

                if (! isset($empleadosMap[$empId])) {
                    $empleadosMap[$empId] = $this->buildEmpleadoBase($employee);
                }

                if (! isset($actividadesPorEmpleado[$mesNombre][$empId])) {
                    $fullName = $empleadosMap[$empId]['nombre'];
                    $actividadesPorEmpleado[$mesNombre][$empId] = [
                        'nombre' => $fullName,
                        'area'   => $empArea,
                        'B'  => 0, 'P'  => 0, 'C'  => 0, 'TC' => 0, 'V'  => 0,
                        'D'  => 0, 'VAC'=> 0, 'M'  => 0, 'E'  => 0, 'A'  => 0, 'PE' => 0,
                        'clave'=> $empleadosMap[$empId]['clave']
                    ];
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

                    $qKey = $prefix . $qNum;

                    $actType1 = $activity['activity_type'] ?? null;
                    $actType2 = $activity['activity_type_vespertina'] ?? null;

                    if ($actType1 && $actType2 && $actType1 !== $actType2) {
                        if (isset($actividadesMap[$monthNum][$actType1])) {
                            $actividadesMap[$monthNum][$actType1] += 0.5;
                        }
                        if (isset($actividadesMap[$monthNum][$actType2])) {
                            $actividadesMap[$monthNum][$actType2] += 0.5;
                        }
                        if (isset($actividadesPorEmpleado[$mesNombre][$empId][$actType1])) {
                            $actividadesPorEmpleado[$mesNombre][$empId][$actType1] += 0.5;
                        }
                        if (isset($actividadesPorEmpleado[$mesNombre][$empId][$actType2])) {
                            $actividadesPorEmpleado[$mesNombre][$empId][$actType2] += 0.5;
                        }
                    } else {
                        $type = $actType1 ?? $actType2;
                        if ($type) {
                            if (isset($actividadesMap[$monthNum][$type])) {
                                $actividadesMap[$monthNum][$type] += 1;
                            }
                            if (isset($actividadesPorEmpleado[$mesNombre][$empId][$type])) {
                                $actividadesPorEmpleado[$mesNombre][$empId][$type] += 1;
                            }
                        }
                    }

                    $bonusInfo     = $this->extractDailyBonuses($activity, $date);
                    $totalBonusDay = $bonusInfo['total'];
                    $dailyDetails  = $bonusInfo['details'];

                    if ($totalBonusDay > 0) {
                        $empName = $empleadosMap[$empId]['nombre'];

                        $detailsWithEmp = array_map(function ($d) use ($empName, $empArea) {
                            $d['empleado']    = $empName;
                            $d['area_origen'] = $empArea;
                            return $d;
                        }, $dailyDetails);

                        $empleadosMap[$empId][$qKey] = round(
                            ($empleadosMap[$empId][$qKey] ?? 0) + $totalBonusDay, 2
                        );
                        $empleadosMap[$empId]['detalles'][$qKey] = array_merge(
                            $empleadosMap[$empId]['detalles'][$qKey] ?? [],
                            $detailsWithEmp
                        );

                        $isCommissioned = (($actType1 === 'C' || $actType2 === 'C') && ! empty($activity['commissioned_to']));
                        $targetArea     = $isCommissioned ? strtoupper(trim($activity['commissioned_to'])) : $empArea;

                        if (! isset($areasMap[$targetArea])) {
                            $areasMap[$targetArea] = ['area' => $targetArea];
                        }
                        if (! isset($areasMap[$targetArea][$qKey])) {
                            $areasMap[$targetArea][$qKey] = [
                                'normal'                => 0.0,
                                'comisionado'           => 0.0,
                                'normal_detalles'       => [],
                                'comisionados_detalles' => [],
                            ];
                        }

                        if ($isCommissioned) {
                            $areasMap[$targetArea][$qKey]['comisionado']           += $totalBonusDay;
                            $areasMap[$targetArea][$qKey]['comisionados_detalles']  = array_merge(
                                $areasMap[$targetArea][$qKey]['comisionados_detalles'],
                                $detailsWithEmp
                            );
                        } else {
                            $areasMap[$targetArea][$qKey]['normal']           += $totalBonusDay;
                            $areasMap[$targetArea][$qKey]['normal_detalles']   = array_merge(
                                $areasMap[$targetArea][$qKey]['normal_detalles'],
                                $detailsWithEmp
                            );
                        }
                    }

                    $isSuministrosArea = stripos($empAreaName, 'suministro') !== false;
                    $isViaje = ($actType1 === 'V' || $actType2 === 'V');
                    $isContinuation = filter_var($activity['is_continuation'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if ($isSuministrosArea && $isViaje) {
                        if (!isset($suministrosMap[$qKey])) {
                            $suministrosMap[$qKey] = [];
                        }
                        $suministrosMap[$qKey][] = [
                            'fecha'               => $date,
                            'empleado_nombre'     => $empleadosMap[$empId]['nombre'],
                            'empleado'            => $empleadosMap[$empId]['nombre'],
                            'area_origen'         => $empArea,
                            'tipo'                => 'Viaje',
                            'descripcion'         => $activity['travel_reason'] ?? 'Viaje de Suministros',
                            'monto'               => $totalBonusDay,
                            'contrato'            => $activity['contract_number'] ?? '-',
                            'tipo_servicio_viaje' => $activity['travel_service_type'] ?? $activity['travel_reason'] ?? '-',
                            'qKey'                => $qKey,
                            'is_continuation'     => $isContinuation
                        ];
                    }

                    $isNewSuministrosTrip = ($isSuministrosArea && $isViaje && !$isContinuation);

                    $globalQ = ($monthNum - 1) * 2 + $qNum;
                    $this->accumulateQuincena(
                        $quincenaMap, $globalQ,
                        $monthNum, $qNum,
                        $totalBonusDay, $activity, $isNewSuministrosTrip
                    );

                    if (($activity['activity_type'] ?? '') === 'P' && ! empty($activity['well_name'])) {
                        $this->accumulatePozo(
                            $pozosMap,
                            $activity['well_name'],
                            $monthNum,
                            $qNum,
                            $totalBonusDay,
                            $detailsWithEmp ?? []
                        );
                    }

                    foreach (($activity['services_list'] ?? []) as $svc) {
                        $this->accumulateServicio(
                            $serviciosMap, $svc,
                            $monthNum, $qNum,
                            $date,
                            $employee,
                            $qKey
                        );
                    }
                }
            }

            ksort($quincenaMap);
            $quincenaArray = $this->attachPctDif(array_values($quincenaMap));

            $mesData = $this->buildMesData($quincenaArray, $tcRates);

            $actividadesPorEmpleadoOut = [];
            foreach ($actividadesPorEmpleado as $mes => $empleados) {
                $actividadesPorEmpleadoOut[$mes] = array_values($empleados);
            }

            return response()->json([
                'success'                  => true,
                'year'                     => $year,
                'empleadosData'            => array_values($empleadosMap),
                'areasData'                => array_values($areasMap),
                'quincenasData'            => $quincenaArray,
                'mesData'                  => $mesData,
                'pozosData'                => array_values($pozosMap),
                'serviciosData'            => array_values($serviciosMap),
                'suministrosData'          => $suministrosMap,
                'actividadesData'          => array_values($actividadesMap),
                'actividadesPorEmpleado'   => $actividadesPorEmpleadoOut,
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

private function fetchMonthlyExchangeRates(int $year): array
    {
        // API gratuita, confiable y sin necesidad de Token
        $urlRange = "https://api.frankfurter.app/{$year}-01-01..{$year}-12-31?from=USD&to=MXN";
        $urlLatest = "https://api.frankfurter.app/latest?from=USD&to=MXN";

        $rates = [];
        $lastKnownRate = null;

        try {
            // 1. Obtener los datos históricos del año solicitado
            $response = Cache::remember("frankfurter_tc_{$year}", 86400, function() use ($urlRange) {
                $resp = Http::timeout(10)->get($urlRange);
                return $resp->successful() ? $resp->json() : null;
            });

            if ($response && isset($response['rates'])) {
                $datos = $response['rates'];

                // Nos aseguramos de que las fechas vengan en orden cronológico (ej. 2026-01-01, 2026-01-02...)
                ksort($datos);

                // Al iterar de enero a diciembre, el valor del mes se sobreescribirá
                // con el del día siguiente, dejando guardado el del ÚLTIMO DÍA del mes.
                foreach ($datos as $date => $rateData) {
                    if (isset($rateData['MXN'])) {
                        $val = (float) $rateData['MXN'];
                        $parts = explode('-', $date); // El formato es YYYY-MM-DD

                        if (count($parts) === 3) {
                            $m = (int) $parts[1];
                            if (isset(self::MONTH_NAMES[$m])) {
                                $mName = self::MONTH_NAMES[$m];
                                $rates[$mName] = $val;
                                $lastKnownRate = $val;
                            }
                        }
                    }
                }
            } else {
                // 2. Si falla el historial por alguna razón, consultamos el día actual de emergencia
                $latest = Cache::remember("frankfurter_tc_latest", 3600, function() use ($urlLatest) {
                    $resp = Http::timeout(5)->get($urlLatest);
                    return $resp->successful() ? $resp->json() : null;
                });

                if ($latest && isset($latest['rates']['MXN'])) {
                    $lastKnownRate = (float) $latest['rates']['MXN'];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Frankfurter API Error: " . $e->getMessage());
        }

        // 3. Fallback extremo por si se cae el internet del servidor
        if (!$lastKnownRate) {
            $lastKnownRate = 20.40;
        }

        $finalRates = [];
        $currentRateFallback = reset($rates) ?: $lastKnownRate;

        // 4. Construir el arreglo final de 12 meses
        for ($i = 1; $i <= 12; $i++) {
            $mName = self::MONTH_NAMES[$i];

            if (isset($rates[$mName])) {
                $currentRateFallback = $rates[$mName];
                $finalRates[$mName] = round($currentRateFallback, 4);
            } else {
                // Si el mes no tiene datos (ej. meses futuros), arrastramos el último conocido
                $finalRates[$mName] = round($currentRateFallback, 4);
            }
        }

        return $finalRates;
    }
    private function extractDailyBonuses(array $activity, string $date): array
    {
        $total   = 0.0;
        $details = [];

        foreach (($activity['food_bonuses'] ?? []) as $fb) {
            $amt = (float) ($fb['daily_amount'] ?? 0);
            if ($amt > 0) {
                $total     += $amt;
                $details[]  = [
                    'fecha'       => $date,
                    'tipo'        => 'Comida',
                    'descripcion' => $fb['bonus_type'] ?? 'Bono de Comida',
                    'monto'       => $amt,
                ];
            }
        }

        foreach (($activity['field_bonuses'] ?? []) as $fb) {
            $amt = (float) ($fb['daily_amount'] ?? 0);
            if ($amt > 0) {
                $total     += $amt;
                $details[]  = [
                    'fecha'       => $date,
                    'tipo'        => 'Campo',
                    'descripcion' => $fb['bonus_type'] ?? 'Bono de Campo',
                    'monto'       => $amt,
                ];
            }
        }

        foreach (($activity['services_list'] ?? []) as $sv) {
            $amt = (float) ($sv['amount'] ?? 0);
            if ($amt > 0) {
                $total     += $amt;
                $details[]  = [
                    'fecha'       => $date,
                    'tipo'        => 'Servicio',
                    'descripcion' => $sv['service_name'] ?? $sv['service_description'] ?? 'Servicio Realizado',
                    'monto'       => $amt,
                ];
            }
        }

        return ['total' => $total, 'details' => $details];
    }

    private function buildEmpleadoBase(Employee $employee): array
    {
        $fullName = strtoupper(trim((string) $employee->full_name));

        if ($fullName === '') {
            $fullName = strtoupper(trim(implode(' ', array_filter([
                $employee->first_name     ?? null,
                $employee->second_name    ?? null,
                $employee->first_surname  ?? null,
                $employee->second_surname ?? null,
            ]))));
        }

        if ($fullName === '') {
            $fullName = 'SIN NOMBRE';
        }

        $clave       = $employee->employee_number ?? 'V' . str_pad($employee->id, 5, '0', STR_PAD_LEFT);
        $empAreaName = $employee->area ? $employee->area->name : 'SIN ÁREA';

        $base = [
            'id'       => $employee->id,
            'clave'    => strtoupper((string) $clave),
            'nombre'   => $fullName,
            'area'     => strtoupper($empAreaName),
            'detalles' => [],
        ];

        foreach (self::MONTH_PREFIXES as $p) {
            $base[$p . '1']             = 0.0;
            $base[$p . '2']             = 0.0;
            $base['detalles'][$p . '1'] = [];
            $base['detalles'][$p . '2'] = [];
        }

        return $base;
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

    private function accumulateQuincena(
        array &$map, int $globalQ, int $monthNum, int $qNum, float $totalBonusDay, array $activity, bool $isNewSuministrosTrip
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
            ];
        }

        $map[$globalQ]['bonosMxn']       = round($map[$globalQ]['bonosMxn'] + $totalBonusDay, 2);
        $map[$globalQ]['serviciosCount'] += count($activity['services_list'] ?? []);

        // Solo suma +1 si es un nuevo viaje y no una continuación
        if ($isNewSuministrosTrip) {
            $map[$globalQ]['suministrosCount']++;
        }
    }

    private function accumulatePozo(
        array  &$map, string $wellName, int $monthNum, int $qNum, float $cost, array $detailsWithEmp
    ): void {
        $quinLabel     = $qNum === 1 ? '1RA QUINCENA' : '2DA QUINCENA';
        $wellNameClean = trim($wellName);
        $pozoKey       = $wellNameClean . '_' . $monthNum . '_' . $qNum;

        if (! isset($map[$pozoKey])) {
            $map[$pozoKey] = [
                'mes'      => self::MONTH_NAMES[$monthNum],
                'quincena' => $quinLabel,
                'pozo'     => $wellNameClean,
                'costo'    => 0.0,
                'detalles' => [],
            ];
        }

        $map[$pozoKey]['costo'] = round($map[$pozoKey]['costo'] + $cost, 2);

        if (! empty($detailsWithEmp)) {
            $map[$pozoKey]['detalles'] = array_merge($map[$pozoKey]['detalles'], $detailsWithEmp);
        }
    }

    private function accumulateServicio(
        array &$map, array $svc, int $monthNum, int $qNum, string $activityDate, Employee $employee, string $qKey
    ): void {
        $identifier = $svc['service_identifier'] ?? 'SIN_ID';
        $realDate   = $svc['service_real_date']  ?? $activityDate;
        $key        = $identifier . '_' . $realDate . '_' . $employee->id;

        if (isset($map[$key])) {
            return;
        }

        $map[$key] = [
            'qKey'               => $qKey,
            'mes'                => self::MONTH_NAMES[$monthNum],
            'quincena'           => $qNum === 1 ? '1RA QUINCENA' : '2DA QUINCENA',
            'service_identifier' => $identifier,
            'service_name'       => $svc['service_name']       ?? $svc['service_description'] ?? '',
            'service_performed'  => $svc['service_performed']  ?? '',
            'amount'             => (float) ($svc['amount']    ?? 0),
            'currency'           => $svc['currency']           ?? 'MXN',
            'status'             => $svc['status']             ?? 'under_review',
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

    private function buildMesData(array $quincenas, array $tcRates): array
    {
        $map = [];

        foreach (self::MONTH_NAMES as $monthName) {
            $map[$monthName] = [
                'mes'              => $monthName,
                'bonosMxn'         => 0.0,
                'bonosUsd'         => 0.0,
                'tc'               => $tcRates[$monthName] ?? 20.10,
                'serviciosCount'   => 0,
                'suministrosCount' => 0,
                'hc'               => 0
            ];
        }

        foreach ($quincenas as $q) {
            $mes = $q['mes'];
            $map[$mes]['bonosMxn']         = round($map[$mes]['bonosMxn'] + $q['bonosMxn'], 2);
            $map[$mes]['serviciosCount']   += ($q['serviciosCount'] ?? 0);
            $map[$mes]['suministrosCount'] += ($q['suministrosCount'] ?? 0);
        }

        foreach ($map as $mes => $data) {
             $map[$mes]['bonosUsd'] = $data['bonosMxn'] > 0 ? round($data['bonosMxn'] / $data['tc'], 2) : 0;
        }

        return array_values($map);
    }
}
