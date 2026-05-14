<?php
namespace App\Http\Controllers\Qhse\Management;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Qhse\Management\Journey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JourneyQueryController extends Controller
{

/**
 * Obtener lista de viajes con filtros y paginación
 */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            // Obtener el employee_id del usuario autenticado
            $employeeId = $user->employee_id;

            $canSeeAll = PermissionHelper::hasDirectPermission('ver_gv_todos') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_bajo') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_medio') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_alto') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_muy_alto');

            $perPage        = $request->get('per_page', 10);
            $search         = $request->get('search', '');
            $statusGv       = $request->get('status_gv', 'all');
            $statusViaje    = $request->get('status_viaje', 'all');
            $riskLevel      = $request->get('risk_level', 'all');
            $destination    = $request->get('destination', 'all');
            $fechaSolicitud = $request->get('fecha_solicitud', '');

            $query = Journey::with(['creator', 'approver', 'units'])
                ->orderBy('created_at', 'desc');

            if (! $canSeeAll) {
                $query->where(function ($q) use ($user, $employeeId) {
                    // Es el creador del viaje
                    $q->where('created_by', $user->id)
                    // O es el aprobador asignado
                        ->orWhere('approver_id', $user->id);

                    // O es conductor principal (driver_id guarda employee_id)
                    if ($employeeId) {
                        $q->orWhereHas('units', function ($uq) use ($employeeId) {
                            $uq->where('driver_id', $employeeId)
                            // O es pasajero/segundo conductor en el JSON
                                ->orWhereRaw(
                                    "JSON_SEARCH(passengers, 'one', ?) IS NOT NULL",
                                    [(string) $employeeId]
                                );
                        });
                    }
                });
            }

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('folio', 'like', "%{$search}%")
                        ->orWhere('creator_name', 'like', "%{$search}%")
                        ->orWhere('area', 'like', "%{$search}%")
                        ->orWhere('destination_region', 'like', "%{$search}%")
                        ->orWhere('specific_destination', 'like', "%{$search}%");
                });
            }

            if ($statusGv !== 'all') {
                if (in_array($statusGv, ['pending', 'approved', 'rejected', 'cancelled'])) {
                    $query->where('approval_status', $statusGv);
                }
            }

            if ($statusViaje !== 'all') {
                if (in_array($statusViaje, ['not_started', 'in_progress', 'completed', 'cancelled', 'no_procede'])) {
                    $query->where('journey_status', $statusViaje);
                }
            }

            if ($request->filled('fecha_solicitud')) {
                $query->whereDate('request_date', $request->input('fecha_solicitud'));
            }

            if ($riskLevel !== 'all') {
                $query->where('risk_level', $riskLevel);
            }

            if ($destination !== 'all' && $destination !== '') {
                if ($destination === 'OTRO') {
                    $query->whereNotIn('destination_region', ['Coatzacoalcos, Ver.', 'Paraíso, Tab.', 'Ciudad del Carmen, Camp.']);
                } elseif ($destination === 'COA') {
                    $query->where('destination_region', 'like', '%Coatzacoalcos%');
                } elseif ($destination === 'TAB') {
                    $query->where('destination_region', 'like', '%Paraíso%');
                } elseif ($destination === 'CAR') {
                    $query->where('destination_region', 'like', '%Ciudad del Carmen%');
                } else {
                    $query->where('destination_region', 'like', "%{$destination}%");
                }
            }

            if (! empty($fechaSolicitud)) {
                try {
                    $fechaParts = explode('/', $fechaSolicitud);
                    if (count($fechaParts) === 3) {
                        $fechaMySQL = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
                        $query->whereDate('request_date', $fechaMySQL);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing fecha solicitud: ' . $e->getMessage());
                }
            }

            $journeys = $query->paginate($perPage);

            $formattedJourneys = collect($journeys->items())->map(
                function ($journey) use ($user, $employeeId, $canSeeAll) {

                    // ✅ DETECTAR PARTICIPACIÓN COMPARANDO CONTRA employee_id
                    $isParticipant = false;

                    if ($employeeId) {
                        $isParticipant = $journey->units->contains(
                            function ($unit) use ($employeeId) {
                                // Es conductor principal
                                if ($unit->driver_id == $employeeId) {
                                    return true;
                                }

                                // Es pasajero o segundo conductor en el JSON
                                $passengers = [];
                                if (is_string($unit->passengers)) {
                                    $passengers = json_decode($unit->passengers, true) ?? [];
                                } elseif (is_array($unit->passengers)) {
                                    $passengers = $unit->passengers;
                                }

                                foreach ($passengers as $p) {
                                    if (isset($p['id']) && (string) $p['id'] === (string) $employeeId) {
                                        return true;
                                    }
                                }

                                return false;
                            }
                        );
                    }

                    return [
                        'id'                 => $journey->id,
                        'folio'              => $journey->folio,
                        'solicitante'        => $journey->creator_name,
                        'area'               => $journey->area,
                        'destino'            => $journey->destination_region,
                        'destino_especifico' => $journey->specific_destination,
                        'fechas'             => $this->formatDates($journey),
                        'fecha_solicitud'    => $journey->request_date
                            ? \Carbon\Carbon::parse($journey->request_date)->format('d/m/Y')
                            : '',
                        'tipo_viaje'         => $journey->fleet_type ?? 'Unidad Única',
                        'riesgo'             => [
                            'nivel' => $journey->risk_level ?? 'bajo',
                            'texto' => $this->getRiskText($journey->risk_level),
                            'clase' => $this->getRiskClass($journey->risk_level),
                        ],
                        'estado_gv'          => [
                            'texto' => $this->getApprovalStatusText($journey->approval_status),
                            'clase' => $this->getStatusClass($journey->approval_status),
                        ],
                        'estado_viaje'       => [
                            'texto' => $this->getJourneyStatusText($journey->journey_status),
                            'clase' => $this->getStatusClass($journey->journey_status),
                        ],
                        'is_creator'         => $journey->created_by === $user->id,
                        'can_approve'        => $journey->approver_id === $user->id,
                        'can_see_history'    => $canSeeAll,
                        'is_participant'     => $isParticipant, // ✅ Basado en employee_id
                    ];
                }
            );

            return response()->json([
                'success'    => true,
                'data'       => $formattedJourneys,
                'pagination' => [
                    'current_page' => $journeys->currentPage(),
                    'last_page'    => $journeys->lastPage(),
                    'per_page'     => $journeys->perPage(),
                    'total'        => $journeys->total(),
                    'from'         => $journeys->firstItem(),
                    'to'           => $journeys->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error cargando viajes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los viajes: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getNextFolio()
    {
        try {
            $lastJourney = Journey::orderBy('id', 'desc')->first();
            if (! $lastJourney) {
                $nextFolio = 'GV-00001';
            } else {
                $lastNumber = intval(substr($lastJourney->folio, 3));
                $nextNumber = $lastNumber + 1;
                $nextFolio  = 'GV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
            return response()->json(['success' => true, 'next_folio' => $nextFolio]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al generar folio'], 500);
        }
    }

/**
 * Obtener la fecha de la última inspección de un vehículo específico
 */
    public function getLastInspectionDate(Request $request, $economic_number)
    {
        try {
            // Obtenemos la fecha exacta del contexto.
            // Si viene del frontend (modo lectura), usamos esa.
            // Si no viene nada (viaje nuevo), usamos el momento actual.
            $contextDate = $request->get('context_date')
                ? \Carbon\Carbon::parse($request->get('context_date'))
                : now();

            $lastUnit = \App\Models\Qhse\Management\JourneyUnit::with(['lightInspection', 'heavyInspection'])
                ->where('economic_number', $economic_number)
                ->whereHas('journey', function ($q) use ($contextDate) {
                    // REGLA DE ORO: La fecha del viaje debe ser estrictamente MENOR (<) a la fecha de contexto.
                    // Así evitamos que la inspección actual se encuentre a sí misma.
                    $q->where('request_date', '<', $contextDate->format('Y-m-d'))
                        ->orWhere(function ($subQ) use ($contextDate) {
                            // Si es el mismo día, nos aseguramos que el ID del viaje sea menor
                            // para garantizar que fue un viaje anterior.
                            $subQ->where('request_date', '=', $contextDate->format('Y-m-d'))
                                ->where('created_at', '<', $contextDate);
                        });
                })
                ->where(function ($query) {
                    $query->has('lightInspection')->orHas('heavyInspection');
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastUnit) {
                $fechaInspeccion = $lastUnit->lightInspection
                    ? $lastUnit->lightInspection->created_at
                    : $lastUnit->heavyInspection->created_at;

                if ($fechaInspeccion) {
                    return response()->json([
                        'success' => true,
                        'date'    => $fechaInspeccion->format('d/m/Y'),
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Sin registros previos']);
        } catch (\Exception $e) {
            Log::error('Error en getLastInspectionDate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al buscar']);
        }
    }

    public function show($id)
    {
        try {
            $journey = Journey::with([
                'units',
                'units.lightInspection',
                'units.heavyInspection',
                'riskAssessment',
                'preConvoyMeeting',
                'creator',
                'approver',
                'logs',
            ])->findOrFail($id);

            // Formatear fotos de inspecciones
            $journey->units->transform(function ($unit) {
                if ($unit->lightInspection && ! empty($unit->lightInspection->photo_evidence)) {
                    $photos = is_string($unit->lightInspection->photo_evidence)
                        ? json_decode($unit->lightInspection->photo_evidence, true)
                        : $unit->lightInspection->photo_evidence;

                    $unit->lightInspection->formatted_photos = collect($photos)
                        ->map(function ($path) {
                            return [
                                'id'   => uniqid('photo_'),
                                'name' => basename($path),
                                'url'  => asset('storage/' . $path),
                                'type' => str_ends_with(strtolower($path), '.pdf')
                                    ? 'application/pdf'
                                    : 'image/jpeg',
                            ];
                        })->toArray();
                }

                if ($unit->heavyInspection && ! empty($unit->heavyInspection->photo_evidence)) {
                    $photos = is_string($unit->heavyInspection->photo_evidence)
                        ? json_decode($unit->heavyInspection->photo_evidence, true)
                        : $unit->heavyInspection->photo_evidence;

                    $unit->heavyInspection->formatted_photos = collect($photos)
                        ->map(function ($path) {
                            return [
                                'id'   => uniqid('photo_'),
                                'name' => basename($path),
                                'url'  => asset('storage/' . $path),
                                'type' => str_ends_with(strtolower($path), '.pdf')
                                    ? 'application/pdf'
                                    : 'image/jpeg',
                            ];
                        })->toArray();
                }

                return $unit;
            });

            $user       = auth()->user();
            $employeeId = $user->employee_id; // ✅ Usamos employee_id
            $isCreator  = $journey->created_by === $user->id;

            // ✅ DETECTAR PARTICIPACIÓN COMPARANDO CONTRA employee_id
            $isParticipant = false;

            if ($employeeId) {
                $isParticipant = $journey->units->contains(
                    function ($unit) use ($employeeId) {
                        // Es conductor principal
                        if ($unit->driver_id == $employeeId) {
                            return true;
                        }

                        // Es pasajero o segundo conductor en el JSON
                        $passengers = [];
                        if (is_string($unit->passengers)) {
                            $passengers = json_decode($unit->passengers, true) ?? [];
                        } elseif (is_array($unit->passengers)) {
                            $passengers = $unit->passengers;
                        }

                        foreach ($passengers as $p) {
                            if (isset($p['id']) && (string) $p['id'] === (string) $employeeId) {
                                return true;
                            }
                        }

                        return false;
                    }
                );
            }

            // Evaluar permiso de aprobación
            $canApprove = false;
            if ($journey->approver_id === $user->id) {
                $riskLevel = $journey->risk_level ?? 'bajo';
                if ($riskLevel === 'bajo' && PermissionHelper::hasDirectPermission('aprobar_gv_bajo')) {
                    $canApprove = true;
                } elseif ($riskLevel === 'medio' && PermissionHelper::hasDirectPermission('aprobar_gv_medio')) {
                    $canApprove = true;
                } elseif ($riskLevel === 'alto' && PermissionHelper::hasDirectPermission('aprobar_gv_alto')) {
                    $canApprove = true;
                } elseif ($riskLevel === 'muy_alto' && PermissionHelper::hasDirectPermission('aprobar_gv_muy_alto')) {
                    $canApprove = true;
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $journey,
                'auth'    => [
                    'is_creator'     => $isCreator,
                    'can_approve'    => $canApprove,
                    'is_participant' => $isParticipant, // ✅ Basado en employee_id
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Viaje no encontrado: ' . $e->getMessage(),
            ], 404);
        }
    }
    public function getStats()
    {
        try {
            $user = auth()->user();

            // Replicamos la regla de visibilidad para que los contadores coincidan con lo que ve el usuario
            $canSeeAll = PermissionHelper::hasDirectPermission('ver_gv_todos') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_bajo') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_medio') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_alto') ||
            PermissionHelper::hasDirectPermission('aprobar_gv_muy_alto');

            $query = Journey::query();

            // Si no puede ver todo, filtramos las estadísticas solo para sus viajes
            if (! $canSeeAll) {
                $query->where('created_by', $user->id);
            }

            $stats = [
                'activos'         => (clone $query)->where('journey_status', 'in_progress')->count(),
                'pendientes'      => (clone $query)->where('approval_status', 'pending')->count(),
                'completados'     => (clone $query)->where('journey_status', 'completed')->count(),
                'total'           => (clone $query)->count(),
                'riesgo_bajo'     => (clone $query)->where('risk_level', 'bajo')->count(),
                'riesgo_medio'    => (clone $query)->where('risk_level', 'medio')->count(),
                'riesgo_alto'     => (clone $query)->where('risk_level', 'alto')->count(),
                'riesgo_muy_alto' => (clone $query)->where('risk_level', 'muy_alto')->count(),
            ];
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
        }
    }

    public function getDestinations()
    {
        try {
            $destinations = Journey::select('destination_region')
                ->whereNotNull('destination_region')
                ->distinct()
                ->orderBy('destination_region')
                ->pluck('destination_region');
            return response()->json(['success' => true, 'data' => $destinations]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener destinos'], 500);
        }
    }

    // Funciones auxiliares
    private function formatDates($journey)
    {
        if (! $journey->start_date || ! $journey->end_date) {
            return 'Fechas no disponibles';
        }

        $start = \Carbon\Carbon::parse($journey->start_date);
        $end   = \Carbon\Carbon::parse($journey->end_date);
        return $start->format('d M') . ' al ' . $end->format('d M Y');
    }

    private function getRiskText($level)
    {
        $levels = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'muy_alto' => 'Muy Alto'];
        return $levels[$level] ?? 'No definido';
    }

    private function getRiskClass($level)
    {
        $classes = ['bajo' => 'status-riesgo-bajo', 'medio' => 'status-riesgo-medio', 'alto' => 'status-riesgo-alto', 'muy_alto' => 'status-riesgo-muy-alto'];
        return $classes[$level] ?? 'status-riesgo-bajo';
    }

    private function getApprovalStatusText($status)
    {
        $statuses = ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado', 'cancelled' => 'Cancelado'];
        return $statuses[$status] ?? 'Pendiente';
    }

    private function getJourneyStatusText($status)
    {
        $statuses = [
            'not_started' => 'Por Iniciar',
            'in_progress' => 'En Curso',
            'stopped'     => 'Detenido', // 👈 NUEVO
            'completed'   => 'Finalizado',
            'cancelled'   => 'Cancelado',
            'no_procede'  => 'No Procede',
        ];
        return $statuses[$status] ?? 'Por Iniciar';
    }

    private function getStatusClass($status)
    {
        $classes = [
            'pending'     => 'status-pendiente',
            'approved'    => 'status-aprobado',
            'rejected'    => 'status-rechazado',
            'cancelled'   => 'status-cancelado',
            'not_started' => 'status-poriniciar',
            'in_progress' => 'status-encurso',
            'stopped'     => 'status-detenido', // 👈 NUEVO
            'completed'   => 'status-finalizado',
            'no_procede'  => 'status-noprocede',
        ];
        return $classes[$status] ?? 'status-pendiente';
    }
}
