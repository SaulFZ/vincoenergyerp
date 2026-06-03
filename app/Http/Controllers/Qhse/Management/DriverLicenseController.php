<?php
namespace App\Http\Controllers\Qhse\Management;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DriverLicenseController extends Controller
{
public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search  = $request->input('search');

        $query = Employee::with(['license', 'area'])
            ->where('employment_status', 'active');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('first_surname', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', first_surname) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('area', function ($areaQuery) use ($search) {
                        $areaQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('first_name', 'asc')
            ->orderBy('first_surname', 'asc');

        $empleados = $query->paginate($perPage);

        if ($request->ajax()) {
            $items = $empleados->getCollection()->map(function ($emp) {

                // =======================================================
                // LÓGICA DE RUTAS PARA FOTOS PROTEGIDAS EN STORAGE
                // =======================================================
                $photoUrl = null;
                if ($emp->photo) {
                    $path = str_replace('public/', '', $emp->photo);
                    if (str_starts_with($path, 'http')) {
                        $photoUrl = $path; // Si ya es una URL web, la dejamos igual
                    } else {
                        // Encriptamos la ruta para mandarla por URL sin romper los slashes
                        $encodedPath = str_replace(['+', '/'], ['-', '_'], base64_encode($path));
                        $photoUrl = route('management.employee.photo', ['path' => $encodedPath]);
                    }
                }

                return [
                    'id'              => $emp->id,
                    'name'            => $emp->full_name ?? ($emp->first_name . ' ' . $emp->first_surname),
                    'area'            => optional($emp->area)->name ?? 'Sin área',
                    'photo'           => $photoUrl,
                    'driver_license'  => optional($emp->license)->driver_license_expires_at ? optional($emp->license)->driver_license_expires_at->format('Y-m-d') : '',
                    'light_course'    => optional($emp->license)->light_defensive_course_expires_at ? optional($emp->license)->light_defensive_course_expires_at->format('Y-m-d') : '',
                    'federal_license' => optional($emp->license)->federal_license_expires_at ? optional($emp->license)->federal_license_expires_at->format('Y-m-d') : '',
                    'heavy_course'    => optional($emp->license)->heavy_defensive_course_expires_at ? optional($emp->license)->heavy_defensive_course_expires_at->format('Y-m-d') : '',
                ];
            });

            return response()->json([
                'data'       => $items,
                'pagination' => [
                    'current_page' => $empleados->currentPage(),
                    'last_page'    => $empleados->lastPage(),
                    'total'        => $empleados->total(),
                ],
            ]);
        }

        return view('modules.qhse.management.driver_licenses', compact('empleados', 'perPage'));
    }

    /**
     * Función para servir las fotos de los empleados desde el Storage
     */
    public function showPhoto($path)
    {
        try {
            // 1. Decodificar la ruta
            $decodedPath = base64_decode(str_replace(['-', '_'], ['+', '/'], $path));
            $decodedPath = str_replace('public/', '', $decodedPath);

            // 2. Verificar si existe en el disco public
            if (!Storage::disk('public')->exists($decodedPath)) {
                abort(404, 'Foto no encontrada');
            }

            // 3. Servir el archivo directamente al navegador
            $file = Storage::disk('public')->path($decodedPath);
            $mimeType = Storage::disk('public')->mimeType($decodedPath);

            return response()->file($file, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sirviendo foto de empleado: ' . $e->getMessage());
            abort(404);
        }
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
                    'driver_license_expires_at'         => $request->driver_license_expires_at,
                    'light_defensive_course_expires_at' => $request->light_defensive_course_expires_at,
                    'federal_license_expires_at'        => $request->federal_license_expires_at,
                    'heavy_defensive_course_expires_at' => $request->heavy_defensive_course_expires_at,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Credenciales actualizadas correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar licencias del empleado ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar actualizar los datos.',
            ], 500);
        }
    }
}
