<?php
namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
use App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeVacationBalanceController extends Controller
{
    /**
     * Muestra la lista de balances.
     */
    public function index()
    {
        $vacationBalances = EmployeeVacationBalance::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'department');
        }])
            ->orderBy('employee_id')
            ->get();

        $employees = Employee::select('id', 'full_name', 'hire_date')->orderBy('full_name')->get();

        // 🥇 NUEVO: Lógica para obtener y consolidar todos los días de vacaciones tomadas (VAC)
        $vacationDaysTaken = $this->getConsolidatedVacationDaysTaken();
        // -----------------------------------------------------------------------------------

        return view('modulos.recursoshumanos.sistemas.loadchart.employee_vacation_balance', [
            'vacationBalances'  => $vacationBalances,
            'employees'         => $employees,
            'vacationDaysTaken' => $vacationDaysTaken, // Pasamos el nuevo dataset
        ]);
    }

    /**
     * 🥇 NUEVO: Consolida todos los días de vacaciones (VAC) tomados por empleado.
     */
    private function getConsolidatedVacationDaysTaken(): array
    {
        $logsWithVacations = EmployeeMonthlyWorkLog::with(['employee' => function ($query) {
            $query->select('id', 'full_name', 'hire_date', 'employee_number', 'department');
        }])
            ->whereNotNull('daily_activities')
            ->get();

        $consolidatedData = [];

        foreach ($logsWithVacations as $log) {
            $employee = $log->employee;
            if (! $employee) {
                continue;
            }

            $vacationActivities = $log->getVacationActivities();

            if (empty($vacationActivities)) {
                continue;
            }

            $employeeId = $employee->id;

            // Inicializar el registro si no existe
            if (! isset($consolidatedData[$employeeId])) {
                $consolidatedData[$employeeId] = [
                    'employee_number'           => $employee->employee_number ?? 'N/A',
                    'full_name'                 => $employee->full_name,
                    'hire_date'                 => $employee->hire_date,
                    'area'                      => $employee->department, // Usamos 'department' como 'Área'
                    'total_vacation_days_count' => 0,
                    'vacation_days_details'     => [],
                ];
            }

            // Consolidar los datos de vacaciones
            $consolidatedData[$employeeId]['total_vacation_days_count'] += count($vacationActivities);
            $consolidatedData[$employeeId]['vacation_days_details'] = array_merge(
                $consolidatedData[$employeeId]['vacation_days_details'],
                $vacationActivities
            );
        }

        // Ordenar los detalles de vacaciones por fecha (importante para la vista)
        foreach ($consolidatedData as &$data) {
            usort($data['vacation_days_details'], function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
        }
        unset($data);

        return array_values($consolidatedData);
    }

    /**
     * Almacena un nuevo balance.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'           => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id',
            'rest_mode'             => 'required|string|max:15',
            'rest_days_available'   => 'required|integer',
        ], [
            'employee_id.unique'    => 'Este empleado ya tiene un balance de vacaciones registrado.',
            'employee_id.required'  => 'Debe seleccionar un empleado.',
            'employee_id.exists'    => 'El empleado seleccionado no existe.',
            'rest_mode.required'    => 'Debe seleccionar una modalidad de descanso.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        try {
            $employee       = Employee::findOrFail($request->employee_id);
            $calculatedData = $this->calculateInitialVacationData($employee);

            $dataToStore = [
                'employee_id'             => $request->employee_id,
                'vacation_days_available' => $calculatedData['vacation_days_available'],
                'rest_days_available'     => $request->rest_days_available,
                'years_of_service'        => $calculatedData['years_of_service'],
                'rest_mode'               => $request->rest_mode,
                'work_rest_cycle_counter' => $calculatedData['work_rest_cycle_counter'],
                'last_activity_date'      => $calculatedData['last_activity_date'],
            ];

            EmployeeVacationBalance::create($dataToStore);

            return response()->json(['success' => true, 'message' => '¡Balance de vacaciones creado exitosamente!']);
        } catch (\Exception $e) {
            Log::error("Error al crear balance: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado al crear el balance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los datos para edición y recalcula años de servicio.
     */
    public function edit($id)
    {
        // Usamos with('employee') para cargar la relación y permitir la verificación en updateYearsOfService
        $balance = EmployeeVacationBalance::with('employee')->findOrFail($id);

        if ($balance->employee) {
            $this->updateYearsOfService($balance);
            $balance->refresh();
        }

        return response()->json($balance);
    }

    /**
     * 🥇 CORRECCIÓN CLAVE: Actualiza el balance de vacaciones.
     */
    public function update(Request $request, $id)
    {
        $balance = EmployeeVacationBalance::findOrFail($id);

        // 1. Validar primero para asegurar que 'employee_id' es requerido y existe.
        $validator = Validator::make($request->all(), [
            'employee_id'             => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id,' . $id,
            'vacation_days_available' => 'required|integer|min:0',
            'rest_days_available'     => 'required|integer',
            'rest_mode'               => 'required|string|max:15',
        ], [
            'employee_id.required'    => 'El ID del empleado es obligatorio.', // Mensaje que se disparaba
            'employee_id.exists'      => 'El empleado seleccionado no existe.', // Si el ID no existe
            'employee_id.unique'      => 'Este empleado ya tiene un balance de vacaciones registrado en otro registro.',
            'rest_mode.required'      => 'La modalidad de descanso es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        try {
            // 2. Cargar Employee SOLO después de que la validación garantice que existe
            $employee = Employee::findOrFail($request->employee_id);

            // 3. Recalcular Años de Servicio
            $currentYearsOfService = Carbon::parse($employee->hire_date)->diffInYears(Carbon::now());

            // 4. Actualizar
            $balance->update([
                'employee_id'             => $request->employee_id,
                'vacation_days_available' => $request->vacation_days_available,
                'rest_days_available'     => $request->rest_days_available,
                'years_of_service'        => $currentYearsOfService, // Usamos el valor recalculado
                'rest_mode'               => $request->rest_mode,
            ]);

            return response()->json(['success' => true, 'message' => '¡Balance de vacaciones actualizado exitosamente!']);
        } catch (\Exception $e) {
            Log::error("Error al actualizar balance: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado al actualizar el balance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un balance de vacaciones.
     */
    public function destroy($id)
    {
        try {
            $balance = EmployeeVacationBalance::findOrFail($id);
            $balance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Balance de vacaciones eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el balance: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Métodos Auxiliares

    private function updateYearsOfService(EmployeeVacationBalance $balance)
    {
        if (! $balance->employee || ! $balance->employee->hire_date) {
            return;
        }

        $hireDate = Carbon::parse($balance->employee->hire_date);
        $today    = Carbon::now();

        $currentYearsOfService = $hireDate->diffInYears($today);

        $dbYearsOfService = (int) $balance->years_of_service;

        $isAnniversary = $hireDate->format('m-d') === $today->format('m-d');

        $updates = [];

        if ($dbYearsOfService != $currentYearsOfService) {
            $updates['years_of_service'] = $currentYearsOfService;
        }

        if ($isAnniversary) {

            $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($currentYearsOfService);

            if ($balance->vacation_days_available != $mandatoryVacationDays) {
                $updates['vacation_days_available'] = $mandatoryVacationDays;
            }
        }

        if (! empty($updates)) {
            $balance->update($updates);
        }
    }

    private function calculateInitialVacationData(Employee $employee): array
    {
        $hireDate = Carbon::parse($employee->hire_date);
        $today    = Carbon::now();

        $yearsOfService = $hireDate->diffInYears($today);

        $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($yearsOfService);

        return [
            'years_of_service'        => $yearsOfService,
            'vacation_days_available' => $mandatoryVacationDays,
            'rest_days_available'     => 6, // Valor inicial (manual)
            'work_rest_cycle_counter' => 0,
            'last_activity_date'      => null,
        ];
    }
}
