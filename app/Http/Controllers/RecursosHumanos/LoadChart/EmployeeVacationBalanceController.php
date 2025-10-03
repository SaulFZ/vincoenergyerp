<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmployeeVacationBalanceController extends Controller
{
    /**
     * Muestra la lista de balances.
     */
    public function index()
    {
        $vacationBalances = EmployeeVacationBalance::with(['employee' => function($query) {
                $query->select('id', 'full_name', 'hire_date');
            }])
            ->orderBy('employee_id')
            ->get();

        $employees = Employee::select('id', 'full_name', 'hire_date')->orderBy('full_name')->get();

        return view('modulos.recursoshumanos.sistemas.loadchart.employee_vacation_balance', [
            'vacationBalances' => $vacationBalances,
            'employees' => $employees,
        ]);
    }

    /**
     * Sincroniza SÓLO los años de servicio si la antigüedad ha cambiado,
     * y SÓLO reinicia las vacaciones si HOY es aniversario (MM-DD).
     *
     * @param EmployeeVacationBalance $balance El modelo de balance a actualizar.
     */
    private function updateYearsOfService(EmployeeVacationBalance $balance)
    {
        if (!$balance->employee || !$balance->employee->hire_date) return;

        $hireDate = Carbon::parse($balance->employee->hire_date);
        $today = Carbon::now();

        // 1. Calcular años completos de servicio HOY
        $currentYearsOfService = $hireDate->diffInYears($today);

        // Años de servicio registrados en la DB
        $dbYearsOfService = (int) $balance->years_of_service;

        // Verificar si es el aniversario de ingreso (mismo día y mes)
        $isAnniversary = $hireDate->format('m-d') === $today->format('m-d');

        $updates = [];

        // 2. 🥇 Actualizar años de servicio si la antigüedad ha cambiado
        if ($dbYearsOfService != $currentYearsOfService) {
            $updates['years_of_service'] = $currentYearsOfService;
        }

        // 3. 🏖️ Reiniciar vacaciones: SÓLO POR ANIVERSARIO ESTRICTO
        if ($isAnniversary) {

            // Lógica de Días de Vacaciones (Mandatorio por antigüedad)
            $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($currentYearsOfService);

            // Solo reiniciar si los días disponibles son diferentes a los mandatorios
            if ($balance->vacation_days_available != $mandatoryVacationDays) {
                $updates['vacation_days_available'] = $mandatoryVacationDays;
            }
        }

        if (!empty($updates)) {
            $balance->update($updates);
        }
    }

    /**
     * Lógica para calcular vacaciones al crear nuevo registro
     * Se mantienen los campos de ciclo con valor inicial 0/null.
     */
    private function calculateInitialVacationData(Employee $employee): array
    {
        $hireDate = Carbon::parse($employee->hire_date);
        $today = Carbon::now();

        // Calcular años completos de servicio
        $yearsOfService = $hireDate->diffInYears($today);

        // Determinar días mandatorios según antigüedad
        $mandatoryVacationDays = EmployeeVacationBalance::calculateMandatoryVacationDays($yearsOfService);

        return [
            'years_of_service' => $yearsOfService,
            'vacation_days_available' => $mandatoryVacationDays,
            'rest_days_available' => 6, // Valor inicial (manual)
            'work_rest_cycle_counter' => 0,
            'last_activity_date' => null,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id',
            'rest_mode' => 'required|string|max:15',
            'rest_days_available' => 'required|integer', // Se permite negativo
        ], [
            'employee_id.unique' => 'Este empleado ya tiene un balance de vacaciones registrado.',
            'employee_id.required' => 'Debe seleccionar un empleado.',
            'rest_mode.required' => 'Debe seleccionar una modalidad de descanso.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        try {
            $employee = Employee::findOrFail($request->employee_id);
            $calculatedData = $this->calculateInitialVacationData($employee);

            $dataToStore = [
                'employee_id' => $request->employee_id,
                'vacation_days_available' => $calculatedData['vacation_days_available'],
                'rest_days_available' => $request->rest_days_available,
                'years_of_service' => $calculatedData['years_of_service'],
                'rest_mode' => $request->rest_mode,
                'work_rest_cycle_counter' => $calculatedData['work_rest_cycle_counter'],
                'last_activity_date' => $calculatedData['last_activity_date'],
            ];

            EmployeeVacationBalance::create($dataToStore);

            return response()->json(['success' => true, 'message' => '¡Balance de vacaciones creado exitosamente!']);
        } catch (\Exception $e) {
            Log::error("Error al crear balance: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado al crear el balance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene los datos de balance para edición y dispara la actualización si es necesaria.
     */
    public function edit($id)
    {
        $balance = EmployeeVacationBalance::with('employee')->findOrFail($id);

        if ($balance->employee) {
            $this->updateYearsOfService($balance);
            $balance->refresh();
        }

        return response()->json($balance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $balance = EmployeeVacationBalance::findOrFail($id);
        $employee = Employee::findOrFail($request->employee_id);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id|unique:employee_vacation_balance,employee_id,' . $id,
            'vacation_days_available' => 'required|integer|min:0',
            'rest_days_available' => 'required|integer',
            'rest_mode' => 'required|string|max:15',
        ], [
            'employee_id.unique' => 'Este empleado ya tiene un balance de vacaciones registrado en otro registro.',
            'rest_mode.required' => 'La modalidad de descanso es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
        }

        $currentYearsOfService = Carbon::parse($employee->hire_date)->diffInYears(Carbon::now());

        try {
            // Se mantienen los campos de ciclo en la BD con su valor anterior (no se tocan)
            $balance->update([
                'employee_id' => $request->employee_id,
                'vacation_days_available' => $request->vacation_days_available,
                'rest_days_available' => $request->rest_days_available,
                'years_of_service' => $currentYearsOfService,
                'rest_mode' => $request->rest_mode,
            ]);

            return response()->json(['success' => true, 'message' => '¡Balance de vacaciones actualizado exitosamente!']);
        } catch (\Exception $e) {
            Log::error("Error al actualizar balance: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error inesperado al actualizar el balance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $balance = EmployeeVacationBalance::findOrFail($id);
            $balance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Balance de vacaciones eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el balance: ' . $e->getMessage()
            ], 500);
        }
    }
}
