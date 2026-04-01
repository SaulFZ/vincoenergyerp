<?php

namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class DriverLicenseController extends Controller
{
    /**
     * Muestra la vista inicial o responde con JSON si es por AJAX (Paginación/Búsqueda)
     */
   public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search');

        // 1. Construir la consulta y filtrar SOLO LOS ACTIVOS
        // OJO: Verifica si en tu base de datos se guarda como 'Activo', 'ACTIVO' o 1.
        $query = Employee::with('license')
                         ->where('employment_status', 'active');

        // 2. Aplicar búsqueda del lado del servidor si hay texto
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('first_surname', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', first_surname) LIKE ?", ["%{$search}%"])
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // 3. Orden alfabético
        $query->orderBy('first_name', 'asc')
              ->orderBy('first_surname', 'asc');

        // 4. Paginación
        $empleados = $query->paginate($perPage);

        // Si la petición es AJAX (Búsqueda o Paginador)
        if ($request->ajax()) {
            $items = $empleados->getCollection()->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->full_name ?? ($emp->first_name . ' ' . $emp->first_surname),
                    'department' => $emp->department ?? 'Sin departamento',
                    'photo' => $emp->photo ? asset($emp->photo) : null,
                    'driver_license' => optional($emp->license)->driver_license_expires_at ? optional($emp->license)->driver_license_expires_at->format('Y-m-d') : '',
                    'light_course' => optional($emp->license)->light_defensive_course_expires_at ? optional($emp->license)->light_defensive_course_expires_at->format('Y-m-d') : '',
                    'federal_license' => optional($emp->license)->federal_license_expires_at ? optional($emp->license)->federal_license_expires_at->format('Y-m-d') : '',
                    'heavy_course' => optional($emp->license)->heavy_defensive_course_expires_at ? optional($emp->license)->heavy_defensive_course_expires_at->format('Y-m-d') : '',
                ];
            });

            return response()->json([
                'data' => $items,
                'pagination' => [
                    'current_page' => $empleados->currentPage(),
                    'last_page' => $empleados->lastPage(),
                    'total' => $empleados->total()
                ]
            ]);
        }

        // Carga inicial normal
        return view('modules.qhse.gerenciamiento.driver_licenses', compact('empleados', 'perPage'));
    }
    /**
     * Recibe la petición AJAX para actualizar las fechas de los cursos y licencias
     */
    public function updateLicenses(Request $request, $id)
    {
        try {
            $empleado = Employee::findOrFail($id);

            // Usamos updateOrCreate para actualizar o crear credenciales
            $empleado->license()->updateOrCreate(
                ['employee_id' => $id],
                [
                    'driver_license_expires_at' => $request->driver_license_expires_at,
                    'light_defensive_course_expires_at' => $request->light_defensive_course_expires_at,
                    'federal_license_expires_at' => $request->federal_license_expires_at,
                    'heavy_defensive_course_expires_at' => $request->heavy_defensive_course_expires_at,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Credenciales actualizadas correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar licencias del empleado ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar actualizar los datos.'
            ], 500);
        }
    }
}
