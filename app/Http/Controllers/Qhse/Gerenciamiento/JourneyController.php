<?php

namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Core\VehicleUnit;
use App\Models\Employee;
use App\Models\Qhse\Gerenciamiento\Destination;
use Illuminate\Support\Facades\Auth;

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
            'modulos.qhse.gerenciamiento.journey_management',
            compact('userData', 'employees')
        );
    }

    /**
     * Obtener usuarios autorizadores dependiendo del nivel de riesgo
     */
    public function getAutorizadores($nivel)
    {
        try {
            // 1. Mapear el nivel de riesgo con el nombre exacto de tu tabla permissions
            $permisoRequerido = match ($nivel) {
                'bajo' => 'aprobar_gv_bajo',
                'medio' => 'aprobar_gv_medio',
                'alto' => 'aprobar_gv_alto',
                'muy_alto' => 'aprobar_gv_muy_alto',
                default => null,
            };

            if (! $permisoRequerido) {
                return response()->json(['success' => false, 'message' => 'Nivel no válido'], 400);
            }

            // 2. Buscar usuarios Activos que tengan ese permiso directo
            // Utilizamos el scopeActive() de tu modelo User
            $usuarios = User::active()
                ->whereHas('directPermissions', function ($query) use ($permisoRequerido) {
                    $query->where('name', $permisoRequerido);
                })
                ->with('employee') // Traemos la relación employee para sacar el nombre real y puesto
                ->get();

            // 3. Mapear los datos para el frontend
            $autorizadores = $usuarios->map(function ($user) {
                return [
                    'id' => $user->id,
                    // Si tiene empleado asociado, usamos full_name, sino el nombre de usuario
                    'nombre' => $user->employee ? $user->employee->full_name : $user->name,
                    // Usamos position o job_title del empleado
                    'puesto' => $user->employee
                                ? ($user->employee->position ?? $user->employee->job_title ?? 'Autorizador')
                                : 'Autorizador',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $autorizadores,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar autorizadores: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getDestinations()
    {
        try {
            // 1. Buscamos el ID de México (Nivel País)
            // Según tus datos, México es ID 1, pero lo buscamos por nombre por seguridad o parent_id null
            $mexico = Destination::where('name', 'México')->first();

            if (! $mexico) {
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
                        'id' => $emp->id, // <--- SE AGREGA EL ID DEL EMPLEADO
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

            // Formatear para el autocomplete (AHORA ENVIAMOS UN OBJETO CON ID Y NOMBRE)
            $listaConductoresAutocomplete = $conductores->map(function ($c) {
                return [
                    'id' => $c['id'],
                    'nombre' => $c['nombre_completo'],
                ];
            })->values()->toArray();

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
                'conductores' => $listaConductoresAutocomplete, // Enviamos el array de objetos
                'datosConductores' => $datosConductores,
                'total' => count($listaConductoresAutocomplete),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar conductores: '.$e->getMessage(),
                'conductores' => [],
                'datosConductores' => [],
            ], 500);
        }
    }

    /**
     * Obtener todos los vehículos desde la tabla ves_units incluyendo ownership (string)
     */
    public function getVehicles()
    {
        try {
            // 1. AGREGAMOS 'brand' AL SELECT
            $vehicles = VehicleUnit::select('economic_number', 'unit_type', 'ownership', 'brand')
                ->orderBy('economic_number', 'asc')
                ->get();

            $ligeras = [];
            $pesadas = [];
            $clasificacion = [];

            // 2. NUEVA VARIABLE PARA DETALLES
            $detallesVehiculo = [];

            foreach ($vehicles as $vehicle) {
                $econ = $vehicle->economic_number;
                $type = $vehicle->unit_type;
                $owner = (string) ($vehicle->ownership ?? '');
                $marca = (string) ($vehicle->brand ?? 'Sin Marca'); // Obtenemos la marca

                $clasificacion[$econ] = $type;

                // Guardamos marca y propiedad en un objeto detallado indexado por el número económico
                $detallesVehiculo[$econ] = [
                    'propiedad' => $owner,
                    'marca' => $marca,

                ];

                if (stripos($type, 'ligera') !== false) {
                    $ligeras[] = $econ;
                } elseif (stripos($type, 'pesada') !== false) {
                    $pesadas[] = $econ;
                }
            }

            return response()->json([
                'success' => true,
                'ligeras' => $ligeras,
                'pesadas' => $pesadas,
                'clasificacion' => $clasificacion,
                'detalles' => $detallesVehiculo, // 3. RETORNAMOS LOS DETALLES
                'total' => count($ligeras) + count($pesadas),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
                'ligeras' => [],
                'pesadas' => [],
                'clasificacion' => [],
                'ownership' => [],
            ], 500);
        }
    }
}
