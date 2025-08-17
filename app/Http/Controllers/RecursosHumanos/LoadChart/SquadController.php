<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\RecursosHumanos\LoadChart\Squad;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SquadController extends Controller
{
    public function index()
    {
        // Cache operadores por 1 hora para mejor performance
        $operadores = Cache::remember('operadores_list', 3600, function() {
            return Employee::where('department', 'Operaciones')
                ->whereNotNull('employee_number')
                ->whereNotNull('full_name')
                ->select('employee_number', 'full_name')
                ->orderBy('full_name')
                ->get();
        });

        $squads = $this->getSquadsWithEmployees();

        return view('modulos.recursoshumanos.sistemas.loadchart.approval', [
            'operadores' => $operadores,
            'squads' => $squads
        ]);
    }

    public function getOperadores()
    {
        // Versión optimizada con caché
        $operadores = Cache::remember('operadores_api_list', 3600, function() {
            return Employee::where('department', 'Operaciones')
                ->whereNotNull('employee_number')
                ->whereNotNull('full_name')
                ->select('employee_number', 'full_name')
                ->orderBy('full_name')
                ->get();
        });

        return response()->json($operadores);
    }

    public function getSquads()
    {
        $squads = Cache::remember('squads_list', 300, function() {
            return $this->getSquadsWithEmployees();
        });

        return response()->json($squads);
    }

    private function getSquadsWithEmployees()
    {
        return Squad::with('employee')
            ->orderBy('squad_number')
            ->get()
            ->groupBy('squad_number')
            ->map(function($squadMembers, $squadNumber) {
                return [
                    'squad_number' => $squadNumber,
                    'squad_name' => $squadMembers->first()->squad_name,
                    'employees' => $squadMembers->map(function($member) {
                        return [
                            'employee_number' => $member->employee->employee_number,
                            'full_name' => $member->employee->full_name
                        ];
                    })->toArray()
                ];
            })
            ->values()
            ->toArray();
    }

    public function store(Request $request)
    {
        $request->validate([
            'squad_number' => 'required|integer|between:1,20',
            'employee_ids' => 'required|array|min:1|max:4',
            'employee_ids.*' => 'required|string|exists:employees,employee_number',
            'is_edit' => 'sometimes|boolean'
        ]);

        $squadNumber = $request->squad_number;
        $squadName = 'Cuadrilla-' . str_pad($squadNumber, 2, '0', STR_PAD_LEFT);
        $employeeNumbers = $request->employee_ids;
        $isEdit = $request->is_edit ?? false;

        try {
            DB::beginTransaction();

            // Verificar si la cuadrilla ya existe (solo en modo creación)
            if (!$isEdit) {
                $existingSquad = Squad::where('squad_number', $squadNumber)->exists();
                if ($existingSquad) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "La cuadrilla {$squadName} ya existe. Por favor edítala en lugar de crear una nueva."
                    ], 422);
                }
            }

            // Verificar empleados asignados a otras cuadrillas
            $assignedEmployees = Squad::whereIn('employee_id', function($query) use ($employeeNumbers) {
                $query->select('id')
                    ->from('employees')
                    ->whereIn('employee_number', $employeeNumbers);
            })
            ->where('squad_number', '!=', $squadNumber)
            ->with('employee')
            ->get();

            if ($assignedEmployees->count() > 0) {
                $assignedNames = $assignedEmployees->pluck('employee.full_name')->implode(', ');
                DB::rollBack();
                return response()->json([
                    'message' => "Los siguientes empleados ya están asignados a otras cuadrillas: {$assignedNames}"
                ], 422);
            }

            // Eliminar miembros actuales de la cuadrilla (si estamos editando)
            Squad::where('squad_number', $squadNumber)->delete();

            // Insertar nuevos miembros
            foreach ($employeeNumbers as $employeeNumber) {
                $employee = Employee::where('employee_number', $employeeNumber)->first();
                if ($employee) {
                    Squad::create([
                        'squad_number' => $squadNumber,
                        'squad_name' => $squadName,
                        'employee_id' => $employee->id,
                    ]);
                }
            }

            DB::commit();

            // Limpiar caché
            Cache::forget('squads_list');

            return response()->json([
                'message' => 'Cuadrilla guardada correctamente.',
                'squad' => [
                    'squad_number' => $squadNumber,
                    'squad_name' => $squadName,
                    'employees' => Employee::whereIn('employee_number', $employeeNumbers)
                        ->select('employee_number', 'full_name')
                        ->get()
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al guardar la cuadrilla.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($squadNumber)
    {
        try {
            $deleted = Squad::where('squad_number', $squadNumber)->delete();

            if ($deleted > 0) {
                // Limpiar caché
                Cache::forget('squads_list');

                return response()->json([
                    'message' => 'Cuadrilla eliminada correctamente.'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No se encontró la cuadrilla especificada.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la cuadrilla.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($squadNumber)
    {
        $squad = Squad::with('employee')
            ->where('squad_number', $squadNumber)
            ->get();

        if ($squad->isEmpty()) {
            return response()->json([
                'message' => 'Cuadrilla no encontrada.'
            ], 404);
        }

        $employees = $squad->map(function($member) {
            return [
                'employee_number' => $member->employee->employee_number,
                'full_name' => $member->employee->full_name
            ];
        });

        return response()->json([
            'squad_number' => $squadNumber,
            'squad_name' => $squad->first()->squad_name,
            'employees' => $employees
        ]);
    }
}
