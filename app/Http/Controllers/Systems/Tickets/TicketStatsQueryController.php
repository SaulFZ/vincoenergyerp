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
            // 1. KPIs Generales
            $totalTickets = Ticket::count();
            $ticketsResueltos = Ticket::whereIn('status', ['realizado', 'cancelado'])->count();
            $ticketsPendientes = Ticket::whereNotIn('status', ['realizado', 'cancelado'])->count();

            // 2. Conteo por Estados
            $statusCounts = Ticket::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            // 3. Conteo por Prioridades
            $priorityCounts = Ticket::select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->pluck('total', 'priority')
                ->toArray();

            // 4. Conteo por Departamento (Área)
            $deptCounts = Ticket::select('department_code', DB::raw('count(*) as total'))
                ->groupBy('department_code')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->pluck('total', 'department_code')
                ->toArray();

            // 5. Tendencia de los últimos 7 días (CORREGIDO)
            $startDate = Carbon::now()->subDays(6)->startOfDay();

            // Usamos DB::raw en el groupBy para evitar errores de Strict Mode en MySQL
            $tendencia = Ticket::select(DB::raw('DATE(created_at) as date_val'), DB::raw('count(*) as total'))
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date_val', 'ASC')
                ->pluck('total', 'date_val')
                ->toArray();

            // Rellenar días vacíos en la tendencia con ceros (CORREGIDO EL MANEJO DE STRINGS)
            $trendData = [];
            $trendLabels = [];

            for ($i = 0; $i < 7; $i++) {
                // Clonamos la fecha inicial y agregamos los días
                $currentDate = (clone $startDate)->addDays($i);

                // Formateamos EXPLÍCITAMENTE a texto
                $keyDate = $currentDate->format('Y-m-d'); // Para buscar en la BD (ej: 2026-04-30)
                $displayDate = $currentDate->format('d/m'); // Para la gráfica (ej: 30/04)

                $trendLabels[] = $displayDate;
                // Buscamos usando el texto, no el objeto Carbon
                $trendData[] = $tendencia[$keyDate] ?? 0;
            }

            // Estructuramos la respuesta JSON
            return response()->json([
                'kpis' => [
                    'total'      => $totalTickets,
                    'resueltos'  => $ticketsResueltos,
                    'pendientes' => $ticketsPendientes,
                ],
                'status' => [
                    'nuevo'        => $statusCounts['nuevo'] ?? 0,
                    'abierto'      => $statusCounts['abierto'] ?? 0,
                    'en-espera'    => $statusCounts['en-espera'] ?? 0,
                    'por-concluir' => $statusCounts['por-concluir'] ?? 0,
                    'realizado'    => $statusCounts['realizado'] ?? 0,
                    'cancelado'    => $statusCounts['cancelado'] ?? 0,
                ],
                'priority' => [
                    'alta'  => $priorityCounts['alta'] ?? 0,
                    'media' => $priorityCounts['media'] ?? 0,
                    'baja'  => $priorityCounts['baja'] ?? 0,
                ],
                'departments' => [
                    'labels' => array_keys($deptCounts),
                    'data'   => array_values($deptCounts)
                ],
                'trend' => [
                    'labels' => $trendLabels,
                    'data'   => $trendData
                ]
            ]);

        } catch (\Exception $e) {
            // Si algo más falla, esto evitará el 500 silencioso y nos dirá exactamente qué pasó en la consola (Network)
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }
}
