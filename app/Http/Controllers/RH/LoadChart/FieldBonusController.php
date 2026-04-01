<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RH\LoadChart\FieldBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FieldBonusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bonuses = FieldBonus::orderBy('employee_category')
            ->orderBy('bonus_type')
            ->get();

        return view('modules.rh.loadchart.field_bonuses', [
            'bonuses' => $bonuses,
            'jobTitles' => $this->getJobTitles(),
            'currencies' => $this->getCurrencies(),
        ]);
    }

    /**
     * Get bonuses data for AJAX requests
     */
    public function getBonuses(Request $request)
    {
        $query = FieldBonus::orderBy('employee_category')->orderBy('bonus_type');

        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('employee_category', 'like', "%{$searchTerm}%")
                    ->orWhere('bonus_type', 'like', "%{$searchTerm}%")
                    ->orWhere('bonus_identifier', 'like', "%{$searchTerm}%");
            });
        }

        // Handle pagination
        if ($request->has('per_page') && $request->per_page === 'all') {
            $bonuses = $query->get();
            $paginationData = [
                'data' => $bonuses,
                'total' => $bonuses->count(),
                'last_page' => 1,
                'current_page' => 1
            ];
        } else {
            $perPage = $request->input('per_page', 10);
            $bonuses = $query->paginate($perPage);
            $paginationData = $bonuses->toArray();
        }

        return response()->json($paginationData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $bonus = FieldBonus::findOrFail($id);
            return response()->json($bonus);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bono no encontrado'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_category' => 'required|string|max:80',
            'bonus_type' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3|in:MXN,USD',
            'bonus_identifier' => 'required|string|max:15',
        ], [
            'employee_category.required' => 'El puesto de empleado es requerido',
            'bonus_type.required' => 'El tipo de bono es requerido',
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número válido',
            'amount.min' => 'El monto debe ser mayor o igual a 0',
            'currency.required' => 'La moneda es requerida',
            'currency.in' => 'La moneda debe ser MXN o USD',
            'bonus_identifier.required' => 'El identificador es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $bonus = FieldBonus::create([
                'employee_category' => $request->employee_category,
                'bonus_type' => $request->bonus_type,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'bonus_identifier' => $request->bonus_identifier,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bono de campo creado exitosamente',
                'data' => $bonus,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el bono: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $bonus = FieldBonus::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bono no encontrado',
            ], 404);
        }

        // Valida la unicidad de la combinación de 'employee_category' y 'bonus_type'
        // ignorando el registro actual
        $validator = Validator::make($request->all(), [
            'employee_category' => [
                'required',
                'string',
                'max:80',
                Rule::unique('field_bonuses')->where(function ($query) use ($request) {
                    return $query->where('bonus_type', $request->bonus_type);
                })->ignore($id)
            ],
            'bonus_type' => [
                'required',
                'string',
                'max:35',
                Rule::unique('field_bonuses')->where(function ($query) use ($request) {
                    return $query->where('employee_category', $request->employee_category);
                })->ignore($id)
            ],
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3|in:MXN,USD',
            'bonus_identifier' => 'required|string|max:15', // <-- La regla de unicidad es eliminada de aquí
        ], [
            'employee_category.required' => 'El puesto de empleado es requerido',
            'bonus_type.required' => 'El tipo de bono es requerido',
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número válido',
            'amount.min' => 'El monto debe ser mayor o igual a 0',
            'currency.required' => 'La moneda es requerida',
            'currency.in' => 'La moneda debe ser MXN o USD',
            'bonus_identifier.required' => 'El identificador es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $bonus->update([
                'employee_category' => $request->employee_category,
                'bonus_type' => $request->bonus_type,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'bonus_identifier' => $request->bonus_identifier,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bono de campo actualizado exitosamente',
                'data' => $bonus->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el bono: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $bonus = FieldBonus::findOrFail($id);
            $bonus->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bono de campo eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el bono: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        try {
            $bonus = FieldBonus::findOrFail($id);
            $bonus->update(['is_active' => !$bonus->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Estado del bono actualizado',
                'is_active' => $bonus->is_active,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unique job titles from the Employee model.
     */
    private function getJobTitles()
    {
        return Employee::select('job_title')
            ->distinct()
            ->whereNotNull('job_title')
            ->pluck('job_title')
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Get currencies
     */
    private function getCurrencies()
    {
        return [
            'MXN' => 'MXN - Peso Mexicano',
            'USD' => 'USD - Dólar Americano',
        ];
    }
}
