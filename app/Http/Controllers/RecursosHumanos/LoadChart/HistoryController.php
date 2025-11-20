<?php
namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\FortnightlyConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Muestra la vista principal del Historial de Actividades.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Obtener el employee_id desde la tabla users
        $user = Auth::user();
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            return view('modulos.recursoshumanos.sistemas.loadchart.history', [
                'employee' => null,
                'historyData' => collect([]),
                'error_message' => 'No se encontró un empleado asociado a tu cuenta de usuario.',
            ]);
        }

        // Obtener los datos del empleado
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return view('modulos.recursoshumanos.sistemas.loadchart.history', [
                'employee' => null,
                'historyData' => collect([]),
                'error_message' => 'Empleado no encontrado en el sistema.',
            ]);
        }

        // Obtener parámetros de filtrado
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusFilter = $request->input('status', 'all');
        $activityTypeFilter = $request->input('activity_type', 'all');

        // Obtener logs con filtros
        $historyLogs = $this->getFilteredHistory($employeeId, $startDate, $endDate, $statusFilter, $activityTypeFilter);

        // Preparar datos para la vista
        $historyData = $this->formatHistoryData($historyLogs);

        return view('modulos.recursoshumanos.sistemas.loadchart.history', [
            'employee' => $employee,
            'historyData' => $historyData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $statusFilter,
                'activity_type' => $activityTypeFilter,
            ],
            'error_message' => null,
        ]);
    }

    /**
     * Obtiene el historial filtrado
     */
    private function getFilteredHistory($employeeId, $startDate, $endDate, $statusFilter, $activityTypeFilter)
    {
        $query = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
            ->orderBy('month_and_year', 'desc');

        // Aplicar filtros de fecha
        if ($startDate) {
            $query->where('month_and_year', '>=', Carbon::parse($startDate)->format('Y-m'));
        }
        if ($endDate) {
            $query->where('month_and_year', '<=', Carbon::parse($endDate)->format('Y-m'));
        }

        $logs = $query->get();

        // Aplicar filtros adicionales a nivel de aplicación
        return $logs->map(function ($log) use ($statusFilter, $activityTypeFilter) {
            if ($log->daily_activities && is_array($log->daily_activities)) {
                $filteredActivities = collect($log->daily_activities)->filter(function ($activity) use ($statusFilter, $activityTypeFilter) {
                    $matchesStatus = $statusFilter === 'all' ||
                                    ($activity['day_status'] ?? 'under_review') === $statusFilter;

                    $matchesActivityType = $activityTypeFilter === 'all' ||
                                        (($activity['activity_type'] ?? 'N') === $activityTypeFilter && $statusFilter === 'all') ||
                                        ($activityTypeFilter !== 'all' && ($activity['activity_type'] ?? 'N') === $activityTypeFilter);


                    // Si ambos filtros son 'all', muestra la actividad
                    if ($statusFilter === 'all' && $activityTypeFilter === 'all') {
                        return true;
                    }

                    // Si solo se filtra por estado, muestra si coincide el estado
                    if ($statusFilter !== 'all' && $activityTypeFilter === 'all') {
                        return $matchesStatus;
                    }

                    // Si solo se filtra por tipo, muestra si coincide el tipo
                    if ($statusFilter === 'all' && $activityTypeFilter !== 'all') {
                        return $matchesActivityType;
                    }

                    // Si se filtran ambos, deben coincidir
                    return $matchesStatus && $matchesActivityType;

                })->toArray();

                $log->daily_activities = array_values($filteredActivities);
            }
            return $log;
        })->filter(function ($log) {
            return !empty($log->daily_activities);
        });
    }


    /**
     * Formatea los datos del historial
     */
    private function formatHistoryData($logs)
    {
        $history = collect();

        foreach ($logs as $log) {
            $monthYear = Carbon::parse($log->month_and_year);
            $monthName = $monthYear->locale('es')->monthName;
            $year = $monthYear->year;

            if (is_array($log->daily_activities)) {
                foreach ($log->daily_activities as $activity) {
                    $activityDate = Carbon::parse($activity['date']);

                    $history->push([
                        'date' => $activityDate->format('d/m/Y'),
                        'day_name' => $activityDate->locale('es')->dayName,
                        'month_name' => $monthName,
                        'year' => $year,
                        'activity_type' => $activity['activity_type'] ?? 'N',
                        'activity_description' => $this->getActivityDescription($activity['activity_type'] ?? 'N'),
                        'overall_status' => $activity['day_status'] ?? 'under_review',
                        'well_name' => $activity['well_name'] ?? null,
                        'travel_destination' => $activity['travel_destination'] ?? null,
                        'travel_reason' => $activity['travel_reason'] ?? null,
                        'commissioned_to' => $activity['commissioned_to'] ?? null,
                        'daily_items' => $this->extractDailyItems($activity),
                        'has_rejections' => $this->hasRejections($activity),
                    ]);
                }
            }
        }

        return $history->sortByDesc(function ($item) {
            return Carbon::createFromFormat('d/m/Y', $item['date'])->timestamp;
        })->values();
    }

    /**
     * Extrae items diarios - MEJORADO: Ahora incluye detalles de la actividad principal con <br>
     */
    private function extractDailyItems($activity)
    {
        $items = [];
        $activityType = $activity['activity_type'] ?? 'N';

        // Actividad principal - MEJORADO: Incluir detalles en este item
        if ($activityType !== 'N') {
            $details = $this->getActivityDescription($activityType); // Primera línea: Nombre de la actividad

            // Agregar detalles específicos de la actividad en líneas separadas usando <br>
            if ($activityType === 'P') { // Trabajo en Pozo
                // Se añade un <br> antes de cada detalle adicional
                $details .= $activity['well_name'] ? "<br>Pozo: {$activity['well_name']}" : '';
            } elseif ($activityType === 'V') { // Viaje
                $details .= $activity['travel_destination'] ? "<br>Destino: {$activity['travel_destination']}" : '';
                $details .= $activity['travel_reason'] ? "<br>Motivo: {$activity['travel_reason']}" : '';
            } elseif ($activityType === 'C') { // Comisionado
                $details .= $activity['commissioned_to'] ? "<br>Area: {$activity['commissioned_to']}" : '';
            }

            $items[] = $this->createItem(
                'Actividad',
                null, // No mostrar tipo redundante
                $activity['activity_status'] ?? 'under_review',
                $details, // Contiene la descripción y los detalles adicionales separados por <br>
                $activity['rejection_reason'] ?? null,
                null, // ID
                null  // Monto
            );
        }

        // Bonos de comida - MEJORADO
        if (!empty($activity['food_bonuses'])) {
            foreach ($activity['food_bonuses'] as $item) {
                $items[] = $this->createItem(
                    'Bono de Comida',
                    $item['bonus_type'] ?? 'Comida',
                    $item['status'] ?? 'under_review',
                    "Cant: {$item['num_daily']}",
                    $item['rejection_reason'] ?? null,
                    $item['bonus_identifier'] ?? null,
                    $item['daily_amount'] ?? null
                );
            }
        }

        // Bonos de campo - MEJORADO
        if (!empty($activity['field_bonuses'])) {
            foreach ($activity['field_bonuses'] as $item) {
                $items[] = $this->createItem(
                    'Bono',
                    $item['bonus_type'] ?? 'Campo',
                    $item['status'] ?? 'under_review',
                    $item['bonus_description'] ??null,
                    $item['rejection_reason'] ?? null,
                    $item['bonus_identifier'] ?? null,
                    $item['daily_amount'] ?? null
                );
            }
        }

        // Servicios - MEJORADO
        if (!empty($activity['services_list'])) {
            foreach ($activity['services_list'] as $item) {
                $items[] = $this->createItem(
                    'Servicio',
                    $item['service_name'] ?? null,
                    $item['status'] ?? 'under_review',
                    $item['service_description'] ??null,
                    $item['rejection_reason'] ?? null,
                    $item['service_identifier'] ?? null,
                    $item['amount'] ?? null
                );
            }
        }

        return $items;
    }

    /**
     * Crea un item del historial - MEJORADO
     */
    private function createItem($concept, $type, $status, $details = null, $rejectionReason = null, $id = null, $amount = null)
    {
        return [
            'concept' => $concept,
            'type' => $type,
            'status' => ucfirst(strtolower($status)),
            'details' => $details,
            'rejection_reason' => $rejectionReason,
            'id' => $id,
            'amount' => $amount,
            'status_color' => $this->getStatusColor($status),
        ];
    }

    /**
     * Obtiene el color del estado
     */
    private function getStatusColor($status)
    {
        $status = strtolower($status);
        $colors = [
            'approved' => '#28a745',
            'reviewed' => '#ffc107',
            'rejected' => '#dc3545',
            'under_review' => '#17a2b8',
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Verifica si hay rechazos
     */
    private function hasRejections($activity)
    {
        if (($activity['activity_status'] ?? 'under_review') === 'rejected') {
            return true;
        }

        $itemTypes = ['food_bonuses', 'field_bonuses', 'services_list'];
        foreach ($itemTypes as $type) {
            if (!empty($activity[$type])) {
                foreach ($activity[$type] as $item) {
                    if (($item['status'] ?? 'under_review') === 'rejected') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Obtiene la descripción de la actividad
     */
    private function getActivityDescription($activityType)
    {
        $descriptions = [
            'B' => 'Trabajo en Base',
            'P' => 'Trabajo en Pozo',
            'C' => 'Comisionado',
            'TC' => 'Trabajo en Casa',
            'V' => 'Viaje',
            'D' => 'Descanso',
            'VAC' => 'Vacaciones',
            'E' => 'Entrenamiento',
            'M' => 'Médico',
            'A' => 'Ausencia',
            'PE' => 'Permiso',
            'N' => 'Ninguna',
        ];

        return $descriptions[$activityType] ?? 'Actividad desconocida';
    }

    /**
     * Endpoint para datos AJAX
     */
    public function getHistoryData(Request $request)
    {
        try {
            $user = Auth::user();
            $employeeId = $user->employee_id;

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un empleado asociado a tu cuenta.'
                ], 404);
            }

            // Obtener los datos del empleado
            $employee = Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado en el sistema.'
                ], 404);
            }

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $statusFilter = $request->input('status', 'all');
            $activityTypeFilter = $request->input('activity_type', 'all');

            $historyLogs = $this->getFilteredHistory(
                $employeeId,
                $startDate,
                $endDate,
                $statusFilter,
                $activityTypeFilter
            );

            $historyData = $this->formatHistoryData($historyLogs);

            return response()->json([
                'success' => true,
                'history' => $historyData,
                'total_records' => $historyData->count(),
                'employee_name' => $employee->full_name,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en HistoryController: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el historial: ' . $e->getMessage()
            ], 500);
        }
    }
}
