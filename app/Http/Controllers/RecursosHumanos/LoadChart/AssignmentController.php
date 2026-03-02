<?php

namespace App\Http\Controllers\RecursosHumanos\LoadChart;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Auth\User;
use App\Models\RecursosHumanos\LoadChart\LoadChartAssignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $departments = Employee::select("department")
            ->whereNotNull("department")
            ->distinct()
            ->orderBy("department")
            ->pluck("department");

        $reviewers = User::whereHas('directPermissions', function ($query) {
            $query->where('name', 'revisar_loadchart');
        })->get(['id', 'name']);

        $approvers = User::whereHas('directPermissions', function ($query) {
            $query->where('name', 'aprobar_loadchart');
        })->get(['id', 'name']);

        return view(
            "modulos.recursoshumanos.loadchart.review_assignments",
            [
                "departments" => $departments,
                "reviewers" => $reviewers,
                "approvers" => $approvers,
            ]
        );
    }

    public function getEmployees(Request $request)
    {
        $department = $request->input("department", "all");
        $perPage = $request->input("per_page", 10);
        $page = $request->input("page", 1);
        $searchQuery = $request->input("search", "");
        $sortBy = $request->input("sort_by", "employee_number");
        $sortDirection = $request->input("sort_direction", "asc");

        $query = Employee::query()->select([
            "id",
            "employee_number",
            "full_name",
            "department",
            "position",
            "job_title",
        ]);

        if ($department !== "all") {
            $query->where("department", $department);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where("full_name", "like", "%" . $searchQuery . "%")
                    ->orWhere("employee_number", "like", "%" . $searchQuery . "%")
                    ->orWhere("department", "like", "%" . $searchQuery . "%")
                    ->orWhere("position", "like", "%" . $searchQuery . "%")
                    ->orWhere("job_title", "like", "%" . $searchQuery . "%");
            });
        }

        $query->orderBy($sortBy, $sortDirection);

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
