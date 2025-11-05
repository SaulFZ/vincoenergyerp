<?php
namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Muestra la vista principal del Historial de Actividades.
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 1. Obtener el ID del usuario autenticado
        $userId = auth()->id();
        $employee = Employee::where('user_id', $userId)->first();
        $employeeId = $employee ? $employee->id : null;

        if (!$employeeId) {
            // Manejar caso donde el usuario autenticado no es un 'Employee'
            return view('modulos.recursoshumanos.sistemas.loadchart.history', [
                'employee' => null,
                'historyLogs' => collect([]),
                'error_message' => 'No se encontró un registro de empleado asociado a tu cuenta.',
            ]);
        }

        // 2. Obtener los registros de trabajo del empleado, ordenados cronológicamente inverso
        $historyLogs = EmployeeMonthlyWorkLog::where('employee_id', $employeeId)
            ->orderBy('month_and_year', 'desc')
            ->get();

        // 3. Formatear y preparar los datos para la vista
        $historyData = $this->formatHistoryData($historyLogs);

        return view('modulos.recursoshumanos.sistemas.loadchart.history', [
            'employee' => $employee,
            'historyLogs' => $historyData,
            'error_message' => null,
        ]);
    }

    /**
     * Formatea los datos del log mensual en una estructura de historial diario.
     *
     * @param \Illuminate\Database\Eloquent\Collection $logs
     * @return \Illuminate\Support\Collection
     */
    private function formatHistoryData($logs)
    {
        $history = collect();

        foreach ($logs as $log) {
            $monthYear = Carbon::parse($log->month_and_year);
            $monthName = $monthYear->locale('es')->monthName;

            if (is_array($log->daily_activities)) {
                foreach ($log->daily_activities as $activity) {
                    $activityDate = Carbon::parse($activity['date']);

                    // Solo incluir días con actividad o un estado de aprobación/rechazo
                    $isRelevant = ($activity['activity_type'] ?? 'N') !== 'N' || ($activity['day_status'] ?? 'under_review') !== 'under_review';

                    if ($isRelevant) {
                        $dailyItems = [];

                        // Agregar actividad principal
                        if (($activity['activity_type'] ?? 'N') !== 'N') {
                            $dailyItems[] = $this->extractItemDetails(
                                'Actividad',
                                $activity['activity_type'],
                                $activity['activity_status'] ?? 'under_review',
                                $activity['activity_description'] ?? null,
                                $activity['rejection_reason'] ?? null
                            );
                        }

                        // Agregar bonos de comida
                        if (!empty($activity['food_bonuses'])) {
                            foreach ($activity['food_bonuses'] as $item) {
                                $dailyItems[] = $this->extractItemDetails(
                                    'Bono Comida',
                                    $item['bonus_type'],
                                    $item['status'] ?? 'under_review',
                                    "Cant: {$item['num_daily']}",
                                    $item['rejection_reason'] ?? null
                                );
                            }
                        }

                        // Agregar bonos de campo
                        if (!empty($activity['field_bonuses'])) {
                            foreach ($activity['field_bonuses'] as $item) {
                                $amount = number_format($item['daily_amount'] ?? 0, 2);
                                $dailyItems[] = $this->extractItemDetails(
                                    'Bono Campo',
                                    $item['bonus_type'],
                                    $item['status'] ?? 'under_review',
                                    "ID: {$item['bonus_identifier']}, Monto: \${$amount} {$item['currency']}",
                                    $item['rejection_reason'] ?? null
                                );
                            }
                        }

                        // Agregar servicios
                        if (!empty($activity['services_list'])) {
                            foreach ($activity['services_list'] as $item) {
                                $amount = number_format($item['amount'] ?? 0, 2);
                                $dailyItems[] = $this->extractItemDetails(
                                    'Servicio',
                                    $item['service_name'],
                                    $item['status'] ?? 'under_review',
                                    "ID: {$item['service_identifier']}, Monto: \${$amount}",
                                    $item['rejection_reason'] ?? null
                                );
                            }
                        }

                        // Agregar el día a la colección de historial
                        $history->push([
                            'date' => $activityDate->format('d/m/Y'),
                            'day_name' => $activityDate->locale('es')->dayName,
                            'month_name' => $monthName,
                            'overall_status' => $activity['day_status'] ?? 'under_review', // Estado consolidado
                            'daily_items' => $dailyItems,
                        ]);
                    }
                }
            }
        }

        return $history;
    }

    /**
     * Función auxiliar para extraer detalles de un ítem.
     */
    private function extractItemDetails($concept, $type, $status, $details = null, $rejectionReason = null)
    {
        return [
            'concept' => $concept,
            'type' => $type,
            'status' => ucfirst(strtolower($status)), // Capitalizar estado para mostrar
            'details' => $details,
            'rejection_reason' => $rejectionReason,
        ];
    }
}
