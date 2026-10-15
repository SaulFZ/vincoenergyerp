<?php

namespace App\Http\Controllers\Administration\ExpenseClaims;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Administration\ExpenseClaims\CostCenter;
use App\Models\Administration\ExpenseClaims\Project;
use App\Models\Administration\ExpenseClaims\FslNode;

class ExpenseSettingsController extends Controller
{
    /**
     * Muestra la vista unificada de configuración (Pestañas)
     */
    public function index()
    {
        // 1. Datos para la pestaña de Centros de Costo
        $costCenters = CostCenter::with('projects')->orderBy('code', 'asc')->get();

        // 2. Datos para la pestaña de Nodos SAT (Solo lectura para pintar la tabla)
        $nodes = FslNode::orderBy('id', 'desc')->get();

        return view('modules.administration.expense_claims.settings', compact('costCenters', 'nodes'));
    }

    // =========================================================================
    // ── CENTROS DE COSTO ──
    // =========================================================================

    public function storeCostCenter(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:cost_centers,code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        CostCenter::create($request->all());
        return response()->json(['success' => true, 'message' => 'Centro de Costos creado exitosamente.']);
    }

    public function updateCostCenter(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:cost_centers,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $cc = CostCenter::findOrFail($id);
        $cc->update($request->all());
        return response()->json(['success' => true, 'message' => 'Centro de Costos actualizado.']);
    }

    // =========================================================================
    // ── PROYECTOS / SUBCENTROS ──
    // =========================================================================

    public function storeProject(Request $request)
    {
        $request->validate([
            'cost_center_id' => 'required|exists:cost_centers,id',
            'code' => 'required|string|unique:projects,code|max:50',
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:30',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        Project::create($request->all());
        return response()->json(['success' => true, 'message' => 'Proyecto registrado exitosamente.']);
    }

    public function updateProject(Request $request, $id)
    {
        $request->validate([
            'cost_center_id' => 'required|exists:cost_centers,id',
            'code' => 'required|string|max:50|unique:projects,code,' . $id,
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:30',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean'
        ]);

        $prj = Project::findOrFail($id);
        $prj->update($request->all());
        return response()->json(['success' => true, 'message' => 'Proyecto actualizado.']);
    }
}
