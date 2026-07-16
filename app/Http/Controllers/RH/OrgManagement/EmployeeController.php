<?php
namespace App\Http\Controllers\RH\OrgManagement;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RH\OrgManagement\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Devuelve los empleados para poblar la tabla (con relaciones).
     */
    public function getData(Request $request)
    {
        $employees = Employee::with(['area', 'department', 'manager'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($employees);
    }

    /**
     * Devuelve los catálogos necesarios para el formulario de alta.
     */
    public function getCreateData()
    {
        $areas = Area::with('departments')
            ->where('is_active', true)
            ->get();

        $managers = Employee::select('id', 'full_name', 'position', 'employee_number')
            ->where('employment_status', 'active')
            ->get();

        return response()->json([
            'areas'    => $areas,
            'managers' => $managers,
        ]);
    }

    /**
     * Guarda un nuevo empleado y procesa su fotografía.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_number'    => 'required|unique:employees,employee_number',
            'first_name'         => 'required|string|max:255',
            'first_surname'      => 'required|string|max:255',
            'gender'             => 'required|in:M,F',
            'birth_date'         => 'required|date_format:d/m/Y',
            'nationality'        => 'required|string',
            'second_nationality' => 'nullable|string', // NUEVO CAMPO
            'position'           => 'required|string|max:255',
            'hire_date'          => 'required|date_format:d/m/Y',
            'employment_status'  => 'required|in:active,inactive',
            'area_id'            => 'required|exists:areas,id',
            'department_id'      => 'nullable|exists:departments,id',
            'manager_id'         => 'nullable|exists:employees,id',
            'phone'              => 'nullable|string|max:20',
            'personal_email'     => 'nullable|email|max:255',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['photo', 'birth_date', 'hire_date', 'second_nationality']);

            // Unir nacionalidades
            $nats                = array_filter([$request->nationality, $request->second_nationality]);
            $data['nationality'] = implode(', ', $nats);

            // Construir nombre completo
            $parts = array_filter([
                $request->first_name, $request->second_name,
                $request->first_surname, $request->second_surname,
            ]);
            $data['full_name'] = implode(' ', $parts);

            $data['birth_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->birth_date)->format('Y-m-d');
            $data['hire_date']  = \Carbon\Carbon::createFromFormat('d/m/Y', $request->hire_date)->format('Y-m-d');

            // Procesar foto
            if ($request->hasFile('photo')) {
                $file          = $request->file('photo');
                $extension     = $file->getClientOriginalExtension();
                $cleanNumber   = strtolower(str_replace('-', '', $request->employee_number));
                $filename      = $cleanNumber . '_' . time() . '.' . $extension;
                $data['photo'] = $file->storeAs('rh/employees/photos', $filename, 'public');
            }

            $employee = Employee::create($data);
            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Empleado registrado correctamente.',
                'employee' => $employee->load(['area', 'department', 'manager']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene un empleado específico.
     */
    public function show($id)
    {
        try {
            $employee = Employee::with(['area', 'department', 'manager'])->findOrFail($id);
            return response()->json($employee);
        } catch (\ModelNotFoundException $e) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
    }

    /**
     * Actualiza los datos de un empleado existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);

            $request->validate([
                'first_name'         => 'sometimes|required|string|max:255',
                'first_surname'      => 'sometimes|required|string|max:255',
                'gender'             => 'sometimes|required|in:M,F',
                'birth_date'         => 'sometimes|required|date_format:d/m/Y',
                'nationality'        => 'sometimes|required|string',
                'second_nationality' => 'nullable|string', // NUEVO CAMPO
                'position'           => 'sometimes|required|string|max:255',
                'hire_date'          => 'sometimes|required|date_format:d/m/Y',
                'employment_status'  => 'sometimes|required|in:active,inactive',
                'area_id'            => 'sometimes|required|exists:areas,id',
                'photo'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            DB::beginTransaction();

            $data = $request->except(['photo', '_method', 'second_nationality']);

            // Unir nacionalidades si se enviaron
            if ($request->has('nationality')) {
                $nats                = array_filter([$request->nationality, $request->second_nationality]);
                $data['nationality'] = implode(', ', $nats);
            }

            if ($request->has('birth_date') && $request->birth_date) {
                $data['birth_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->birth_date)->format('Y-m-d');
            }

            if ($request->has('hire_date') && $request->hire_date) {
                $data['hire_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->hire_date)->format('Y-m-d');
            }

            if ($request->has('first_name') || $request->has('second_name') || $request->has('first_surname') || $request->has('second_surname')) {
                $parts = array_filter([
                    $request->first_name ?? $employee->first_name,
                    $request->second_name ?? $employee->second_name,
                    $request->first_surname ?? $employee->first_surname,
                    $request->second_surname ?? $employee->second_surname,
                ]);
                $data['full_name'] = implode(' ', $parts);
            }

            // Procesar foto (elimina la física anterior si subes una nueva)
            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                    Storage::disk('public')->delete($employee->photo);
                }

                $file          = $request->file('photo');
                $extension     = $file->getClientOriginalExtension();
                $cleanNumber   = strtolower(str_replace('-', '', $employee->employee_number));
                $filename      = $cleanNumber . '_' . time() . '.' . $extension;
                $data['photo'] = $file->storeAs('rh/employees/photos', $filename, 'public');
            }

            $employee->update($data);
            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Empleado actualizado correctamente.',
                'employee' => $employee->load(['area', 'department', 'manager']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un empleado (soft delete si es aplicable).
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);

            // Cambiar estado a inactivo en lugar de eliminar
            $employee->update(['employment_status' => 'inactive']);

            return response()->json([
                'success' => true,
                'message' => 'Empleado desactivado correctamente.',
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error al desactivar empleado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
