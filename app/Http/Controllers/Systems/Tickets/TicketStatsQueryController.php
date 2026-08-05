<?php

namespace App\Http\Controllers\Systems\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Systems\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketStatsQueryController extends Controller
{
    public function getGlobalStats(Request $request)
    {
        try {
            $tickets = DB::table('tickets')->get();

            // 1. KPIs
            $kpis = [
                'nuevo' => $tickets->where('status', 'nuevo')->count(),
                'abierto' => $tickets->where('status', 'abierto')->count(),
                'espera' => $tickets->where('status', 'en-espera')->count(),
                'concluir' => $tickets->where('status', 'por-concluir')->count(),
                'realizado' => $tickets->where('status', 'realizado')->count(),
                'cancelado' => $tickets->where('status', 'cancelado')->count(),
            ];

            // 2. Detalle Operativo por Departamento
            $deptMap = [
                'ADM' => 'Administración',
                'OPR' => 'Operaciones',
                'VEN' => 'Ventas',
                'QHS' => 'QHSE',
                'GEO' => 'Geociencias'
            ];

            $departmentsDetail = [];
            $groupedDepts = $tickets->groupBy('department_code');

            foreach ($groupedDepts as $code => $deptTickets) {
                $departmentsDetail[] = [
                    'name'      => $deptMap[$code] ?? $code,
                    'total'     => $deptTickets->count(),
                    'nuevo'     => $deptTickets->where('status', 'nuevo')->count(),
                    'abierto'   => $deptTickets->where('status', 'abierto')->count(),
                    'espera'    => $deptTickets->where('status', 'en-espera')->count(),
                    'concluir'  => $deptTickets->where('status', 'por-concluir')->count(),
                    'realizado' => $deptTickets->where('status', 'realizado')->count(),
                    'cancelado' => $deptTickets->where('status', 'cancelado')->count(),
                    'alta'      => $deptTickets->where('priority', 'alta')->count(),
                    'media'     => $deptTickets->where('priority', 'media')->count(),
                    'baja'      => $deptTickets->where('priority', 'baja')->count(),
                ];
            }

            // Ordenar por mayor volumen de tickets
            usort($departmentsDetail, function($a, $b) { return $b['total'] <=> $a['total']; });

            // 3. Agentes (Tickets Asignados vs Resueltos)
            $agentesMap = [];
            foreach ($tickets->whereNotNull('assigned_to') as $t) {
                $ag = $t->assigned_to;
                if (!isset($agentesMap[$ag])) {
                    $agentesMap[$ag] = ['nombre' => 'Soporte ID '.$ag, 'asignados' => 0, 'resueltos' => 0];
                }
                $agentesMap[$ag]['asignados']++;
                if ($t->status === 'realizado') {
                    $agentesMap[$ag]['resueltos']++;
                }
            }
            $agentes = array_values($agentesMap);
            usort($agentes, function($a, $b) { return $b['asignados'] <=> $a['asignados']; });

            // 4. Tipos de Incidencia (Distribución por departamento como proxy)
            $tiposIncidencia = [
                'labels' => [],
                'values' => []
            ];
            foreach ($departmentsDetail as $d) {
                $tiposIncidencia['labels'][] = $d['name'];
                $tiposIncidencia['values'][] = $d['total'];
            }

            // 5. Evolución (Últimas 4 Semanas para mostrar la tendencia real)
            $semanas = [];
            $volumen = [];
            $tiempoPromedio = [];

            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

                $tWeek = $tickets->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                $semanas[] = 'Sem ' . $startOfWeek->format('W');
                $volumen[] = $tWeek->count();

                // Calcular tiempo promedio en horas de los tickets realizados en esa semana
                $tResueltos = $tWeek->where('status', 'realizado');
                $horasSuma = 0;
                foreach ($tResueltos as $tr) {
                    $horasSuma += Carbon::parse($tr->created_at)->diffInHours(Carbon::parse($tr->updated_at));
                }
                $promedio = $tResueltos->count() > 0 ? round($horasSuma / $tResueltos->count(), 1) : 0;
                $tiempoPromedio[] = $promedio;
            }

            $tendencia = [
                'semanas' => $semanas,
                'volumen' => $volumen,
                'tiempo_promedio' => $tiempoPromedio
            ];

            // 6. Histograma de Tiempos de Resolución
            $histogramaData = ['< 4 h' => 0, '4–12 h' => 0, '12–24 h' => 0, '1–2 días' => 0, '2–3 días' => 0, '3–5 días' => 0, '> 5 días' => 0];
            foreach ($tickets->where('status', 'realizado') as $t) {
                $horas = Carbon::parse($t->created_at)->diffInHours(Carbon::parse($t->updated_at));

                if ($horas < 4) $histogramaData['< 4 h']++;
                elseif ($horas <= 12) $histogramaData['4–12 h']++;
                elseif ($horas <= 24) $histogramaData['12–24 h']++;
                elseif ($horas <= 48) $histogramaData['1–2 días']++;
                elseif ($horas <= 72) $histogramaData['2–3 días']++;
                elseif ($horas <= 120) $histogramaData['3–5 días']++;
                else $histogramaData['> 5 días']++;
            }

            $histograma = [
                'rangos' => array_keys($histogramaData),
                'conteo' => array_values($histogramaData)
            ];

            // 7. Burndown (Últimos 15 días)
            $sprintDays = 15;
            $burndownDias = ['Inicio'];
            $burndownIdeal = [];
            $burndownReal = [];

            $startDate = Carbon::now()->subDays($sprintDays)->startOfDay();
            $startBacklog = DB::table('tickets')->where('created_at', '<', $startDate)->whereNotIn('status', ['realizado', 'cancelado'])->count();

            for ($i = 0; $i <= $sprintDays; $i++) {
                if ($i > 0) $burndownDias[] = 'Día ' . $i;

                // Línea ideal constante hacia cero
                $burndownIdeal[] = max(0, round($startBacklog * (1 - ($i / $sprintDays))));

                // Backlog real histórico
                $currentDayEnd = (clone $startDate)->addDays($i)->endOfDay();
                $realBacklog = DB::table('tickets')
                    ->where('created_at', '<=', $currentDayEnd)
                    ->where(function($q) use ($currentDayEnd) {
                        $q->whereNotIn('status', ['realizado', 'cancelado'])
                          ->orWhere('updated_at', '>', $currentDayEnd);
                    })->count();

                $burndownReal[] = $realBacklog;
            }

            $burndown = [
                'dias'  => $burndownDias,
                'ideal' => $burndownIdeal,
                'real'  => $burndownReal
            ];

            // Formato de salida exacto que espera JavaScript
            return response()->json([
                'kpis'               => $kpis,
                'departments_detail' => $departmentsDetail,
                'agentes'            => $agentes,
                'tiposIncidencia'    => $tiposIncidencia,
                'tendencia'          => $tendencia,
                'histograma'         => $histograma,
                'burndown'           => $burndown,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }
}
