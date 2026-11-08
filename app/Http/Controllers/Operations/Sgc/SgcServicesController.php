<?php

namespace App\Http\Controllers\Operations\Sgc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Operations\Well;
use App\Models\Employee;
use App\Models\Core\VehicleUnit;

class SgcServicesController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos qué pestaña quieren ver (por defecto 'list')
        $view = $request->query('view', 'list');
        abort_unless(in_array($view, ['list', 'staff', 'new_service'], true), 404);
        abort_unless(in_array($request->query('mode', 'create'), ['create', 'edit', 'view'], true), 422);

        // para crear, editar o visualizar un servcio se abre un modal fullscreen
        if ($view === 'new_service' && !$request->ajax()) {
            return redirect()->route('operations.sgc.services', ['view' => 'list']);
        }

        if ($view === 'new_service' && $request->query('catalog') === 'employee-training') {
            $request->validate(['employee_id' => 'required|integer|exists:employees,id']);
            $employee = Employee::findOrFail($request->integer('employee_id'));
            $records = DB::table('employee_training_records as records')
                ->join('training_courses as courses', 'courses.id', '=', 'records.training_course_id')
                ->where('records.employee_id', $employee->id)
                ->orderBy('courses.name')
                ->get(['courses.name', 'courses.category', 'records.issue_date', 'records.expiration_date', 'records.status']);

            return response()->json(['employee' => $employee->full_name, 'records' => $records]);
        }

        if ($view === 'new_service') {
            return view('modules.operations.sgc.tabs.serv.new_service', [
                'clients' => DB::table('clients')->where('status', 'active')->orderBy('name')->get(['id', 'name']),
                'serviceTypes' => DB::table('service_types')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'equipment' => DB::table('equipment')->orderBy('code')->get(['id', 'code', 'name', 'category', 'status', 'serial_number'])->groupBy('category'),
                'toolFamilies' => DB::table('tool_families')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'tools' => DB::table('tools')->orderBy('name')->get(['id', 'tool_family_id', 'code', 'name', 'serial_number', 'status']),
                'cables' => DB::table('cables')->orderBy('code')->get(['id', 'code', 'drum_code', 'current_unit_id', 'status']),
                'wells' => Well::where('status', 'active')->orderBy('name')->get(['id', 'name']),
                'employees' => Employee::where('employment_status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'position']),
                'units' => VehicleUnit::orderBy('economic_number')->get(['id', 'economic_number', 'vehicle_type', 'status', 'serial_number']),
            ]);
        }

        // 2. LA MAGIA AQUÍ: Si la petición viene por Javascript (AJAX)
        if ($request->ajax()) {
            // Devolvemos SOLAMENTE el blade del tab específico
            return view("modules.operations.sgc.tabs.serv.{$view}");
        }

        // 3. Si es carga normal (F5 o entrar por primera vez), devolvemos toda la página
        return view('modules.operations.sgc.services', compact('view'));
    }
}
