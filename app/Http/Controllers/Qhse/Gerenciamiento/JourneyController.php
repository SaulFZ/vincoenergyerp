<?php
namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Core\VehicleUnit;
use App\Models\Qhse\Gerenciamiento\Destination;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
// Importar esto arriba
// Importar esto

class JourneyController extends Controller
{
    public function index()
    {
        // 1. Usuario autenticado con empleado
        $user = Auth::user()->load('employee');
        // 2. Empleados activos con licencias y usuario activo
        $employees = Employee::with(['license', 'user' => function ($q) {
            $q->where('status', 'active');
        }])
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($emp) {
                $license = $emp->license;
                $user = $emp->user;
                return [
                    'id' => $emp->id,
                    'nombre_completo' => $emp->full_name,
                    'departamento' => $emp->department,
                    // Licencias del empleado
                    'licencia_conductor_vigencia' => $license
                        ? $license->driver_license_expires_at
                        : null,
                    'licencia_conductor_permanente' => $license
                        ? (bool) $license->driver_license_is_permanent
                        : false,
                    'curso_ligero_vigencia' => $license
                        ? $license->light_defensive_course_expires_at
                        : null,
                    'curso_pesado_vigencia' => $license
                        ? $license->heavy_defensive_course_expires_at
                        : null,
                    'licencia_federal_vigencia' => $license
                        ? $license->federal_license_expires_at
                        : null,
                    // Datos del usuario (si existe)
                    'email' => $user ? $user->email : null,
                    'estado' => $user ? $user->status : null,
                ];
            });
        // 3. Pasar datos del usuario logueado a la vista
        $userData = [
            'nombre' => $user->employee ? $user->employee->full_name : $user->name,
            'departamento' => $user->employee ? $user->employee->department : 'N/A',
            'email' => $user->email,
        ];
        return view(
            'modulos.qhse.sistemas.gerenciamiento.journey_management',
            compact('userData', 'employees')
        );
    }

    public function getDestinations()
    {
        try {
            // 1. Buscamos el ID de México (Nivel País)
            // Según tus datos, México es ID 1, pero lo buscamos por nombre por seguridad o parent_id null
            $mexico = Destination::where('name', 'México')->first();

            if (!$mexico) {
                return response()->json(['success' => false, 'message' => 'País no encontrado']);
            }

            // 2. Obtenemos los Estados (Hijos de México) y sus Municipios (Hijos de los Estados)
            // Usamos 'children' recursivamente.
            $estados = Destination::where('parent_id', $mexico->id)
                ->where('level', 'state')
                ->with(['children' => function ($query) {
                    $query->orderBy('name', 'asc');  // Ordenar municipios alfabéticamente
                }])
                ->orderBy('name', 'asc')  // Ordenar estados alfabéticamente
                ->get();

            return response()->json([
                'success' => true,
                'data' => $estados,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtener todos los conductores (empleados con usuario activo)
     * para el autocomplete
     */
    public function getConductores()
    {
        try {
            $conductores = Employee::with(['license', 'user' => function ($q) {
                $q->where('status', 'active');
            }])
                ->whereHas('user', function ($q) {
                    $q->where('status', 'active');
                })
                ->whereNull('deleted_at')
                ->get()
                ->map(function ($emp) {
                    $license = $emp->license;
                    return [
                        'nombre_completo' => $emp->full_name,
                        'departamento' => $emp->department,
                        // Datos de licencias
                        'licencia_conductor' => $license ? [
                            'vigencia' => $license->driver_license_expires_at
                                ? $license->driver_license_expires_at->format('Y-m-d')
                                : null,
                            'permanente' => (bool) $license->driver_license_is_permanent,
                        ] : null,
                        'curso_manejo_defensivo' => $license ? [
                            'ligero' => $license->light_defensive_course_expires_at
                                ? $license->light_defensive_course_expires_at->format('Y-m-d')
                                : null,
                            'pesado' => $license->heavy_defensive_course_expires_at
                                ? $license->heavy_defensive_course_expires_at->format('Y-m-d')
                                : null,
                        ] : null,
                        'licencia_federal' => $license ? [
                            'vigencia' => $license->federal_license_expires_at
                                ? $license->federal_license_expires_at->format('Y-m-d')
                                : null,
                        ] : null,
                    ];
                });
            // Formatear para el autocomplete
            $nombresConductores = $conductores->pluck('nombre_completo')->toArray();
            // Crear objeto con datos de cada conductor
            $datosConductores = [];
            foreach ($conductores as $conductor) {
                $datosConductores[$conductor['nombre_completo']] = [
                    'vigencia' => $conductor['licencia_conductor']['vigencia'] ?? '',
                    'manDefVigencia' => $conductor['curso_manejo_defensivo']['ligero'] ?? '',
                    'cursoPesadoVigencia' => $conductor['curso_manejo_defensivo']['pesado'] ?? '',
                    'federalVigencia' => $conductor['licencia_federal']['vigencia'] ?? '',
                    'departamento' => $conductor['departamento'] ?? '',
                    'permanente' => $conductor['licencia_conductor']['permanente'] ?? false,
                ];
            }
            return response()->json([
                'success' => true,
                'conductores' => $nombresConductores,
                'datosConductores' => $datosConductores,
                'total' => count($nombresConductores),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar conductores: ' . $e->getMessage(),
                'conductores' => [],
                'datosConductores' => [],
            ], 500);
        }
    }

    /**
     * Obtener todos los vehículos desde la tabla ves_units
     */
    public function getVehicles()
    {
        try {
            // USAR CACHÉ: Recordar esta consulta por 3600 segundos (1 hora)
            // Si tienes muchos usuarios, esto reduce la carga a la BD drásticamente.
            $data = Cache::remember('api_vehicles_list', 3600, function () {
                // OPTIMIZACIÓN SQL:
                // 1. Usamos select() para traer SOLO lo necesario.
                // 2. Usamos get() simple.
                $vehicles = VehicleUnit::select('economic_number', 'unit_type')
                    ->orderBy('economic_number', 'asc')  // Ordenar desde la BD es más rápido
                    ->get();

                $ligeras = [];
                $pesadas = [];
                $clasificacion = [];

                foreach ($vehicles as $vehicle) {
                    $econ = $vehicle->economic_number;
                    $type = $vehicle->unit_type;

                    $clasificacion[$econ] = $type;

                    // stripos es más rápido que str_contains + strtolower
                    if (stripos($type, 'ligera') !== false) {
                        $ligeras[] = $econ;
                    } elseif (stripos($type, 'pesada') !== false) {
                        $pesadas[] = $econ;
                    }
                }

                return [
                    'success' => true,
                    'ligeras' => $ligeras,
                    'pesadas' => $pesadas,
                    'clasificacion' => $clasificacion,
                    'total' => count($ligeras) + count($pesadas),
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'ligeras' => [],
                'pesadas' => [],
                'clasificacion' => [],
            ], 500);
        }
    }
}
