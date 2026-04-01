<?php

namespace App\Http\Controllers\RH\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Auth\User;
use App\Models\RH\LoadChart\LoadChartAssignment;
use App\Models\RH\OrgManagement\Area; // ✅ IMPORTANTE: Agregar el modelo Area
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        // ✅ Obtenemos las áreas activas en lugar de la antigua columna de texto
        $areas = Area::where('is_active', 1)->orderBy('name')->pluck('name');

        $reviewers = User::whereHas('directPermissions', function ($query) {
            $query->where('name', 'revisar_loadchart');
        })->get(['id', 'name']);

        $approvers = User::whereHas('directPermissions', function ($query) {
            $query->where('name', 'aprobar_loadchart');
        })->get(['id', 'name']);

        return view(
            "modules.rh.loadchart.review_assignments",
            [
                "areas" => $areas, // ✅ Pasamos las áreas a la vista
                "reviewers" => $reviewers,
                "approvers" => $approvers,
            ]
        );
    }

    public function getEmployees(Request $request)
    {
        $area = $request->input("area", "all"); // ✅ Filtramos por área
        $perPage = $request->input("per_page", 10);
        $page = $request->input("page", 1);
        $searchQuery = $request->input("search", "");
        $sortBy = $request->input("sort_by", "employee_number");
        $sortDirection = $request->input("sort_direction", "asc");

        // ✅ Agregamos with(['area', 'department']) y las claves foráneas al select
        $query = Employee::with(['area', 'department'])->select([
            "employees.id",
            "employees.employee_number",
            "employees.full_name",
            "employees.position",
            "employees.job_title",
            "employees.area_id",
            "employees.department_id"
        ]);

        if ($area !== "all") {
            $query->whereHas('area', function ($q) use ($area) {
                $q->where('name', $area);
            });
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where("employees.full_name", "like", "%" . $searchQuery . "%")
                    ->orWhere("employees.employee_number", "like", "%" . $searchQuery . "%")
                    ->orWhere("employees.position", "like", "%" . $searchQuery . "%")
                    ->orWhere("employees.job_title", "like", "%" . $searchQuery . "%")
                    ->orWhereHas('area', function ($qa) use ($searchQuery) {
                        $qa->where("name", "like", "%" . $searchQuery . "%");
                    })
                    ->orWhereHas('department', function ($qd) use ($searchQuery) {
                        $qd->where("name", "like", "%" . $searchQuery . "%");
                    });
            });
        }

        // ✅ Lógica de ordenación con joins para poder ordenar por las relaciones
        if ($sortBy === 'area') {
            $query->leftJoin('areas', 'employees.area_id', '=', 'areas.id')
                  ->orderBy('areas.name', $sortDirection);
        } elseif ($sortBy === 'department') {
            $query->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                  ->orderBy('departments.name', $sortDirection);
        } else {
            $query->orderBy("employees." . $sortBy, $sortDirection);
        }

        if ($perPage === "all") {
            $employees = $query->get();
            return response()->json([
                "employees" => $employees,
                "total" => $employees->count(),
                "per_page" => $employees->count(),
                "current_page" => 1,
                "last_page" => 1,
            ]);
        }

        $employees = $query->paginate($perPage, ["*"], "page", $page);

        return response()->json([
            "employees" => $employees->items(),
            "total" => $employees->total(),
            "per_page" => $employees->perPage(),
            "current_page" => $employees->currentPage(),
            "last_page" => $employees->lastPage(),
        ]);
    }

    public function getExistingAssignments(Request $request)
    {
        $employeeIds = $request->input('employee_ids', []);

        $assignments = LoadChartAssignment::whereIn('employee_id', $employeeIds)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item['employee_id'] => $item];
            });

        return response()->json($assignments);
    }

    public function saveAssignment(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'reviewer_id' => 'nullable|exists:users,id',
            'approver_id' => 'nullable|exists:users,id',
        ]);

        try {
            $assignment = LoadChartAssignment::updateOrCreate(
                ['employee_id' => $validated['employee_id']],
                [
                    'reviewer_id' => $validated['reviewer_id'],
                    'approver_id' => $validated['approver_id']
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Asignación guardada correctamente',
                'data' => $assignment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la asignación: ' . $e->getMessage()
            ], 500);
        }
    }
}
