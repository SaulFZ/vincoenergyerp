<?php
namespace App\Http\Controllers\Qhse\Management;

use App\Http\Controllers\Controller;
use App\Mail\Qhse\Management\AnomalyNotificationMail;
use App\Mail\Qhse\Management\JourneyApprovalMail;
use App\Models\Auth\User;
use App\Models\Qhse\Management\HeavyInspection;
use App\Models\Qhse\Management\Journey;
use App\Models\Qhse\Management\JourneyLog;
use App\Models\Qhse\Management\JourneyUnit;
use App\Models\Qhse\Management\LightInspection;
use App\Models\Qhse\Management\PreConvoyMeeting;
use App\Models\Qhse\Management\RiskAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class JourneyStoreController extends Controller
{
    /**
     * Guardar un viaje completo con todas sus relaciones
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->all();

            // 1. CREAR EL VIAJE PRINCIPAL
            $journey = $this->createJourney($data);

            // 2. CREAR LAS UNIDADES Y SUS INSPECCIONES
            $this->createUnits($journey, $data['unidades'] ?? []);

            // 3. CREAR LA EVALUACIÓN DE RIESGO
            if (isset($data['evaluacion_riesgo'])) {
                $this->createRiskAssessment($journey, $data['evaluacion_riesgo']);
            }

            // 4. CREAR LA REUNIÓN PRE-CONVOY (si existe)
            if (isset($data['reunion_pre_convoy'])) {
                $this->createPreConvoyMeeting($journey, $data['reunion_pre_convoy']);
            }

            // 5. REGISTRAR EL LOG EN LA BITÁCORA
            JourneyLog::create([
                'journey_id'  => $journey->id,
                'user_id'     => Auth::id(),
                'event_type'  => 'created',
                'title'       => 'Solicitud Creada',
                'description' => 'El viaje ha sido registrado exitosamente y está a la espera de autorización.',
                'event_time'  => now(),
            ]);

            DB::commit();

            // =========================================================
            // 6. ENVIAR CORREO AL AUTORIZADOR
            // =========================================================
            try {
                if ($journey->approver_id) {
                    $approver = User::find($journey->approver_id);
                    if ($approver && $approver->email) {
                        Mail::to($approver->email)->send(new JourneyApprovalMail($journey));
                    }
                }
            } catch (\Exception $mailEx) {
                Log::error('Error enviando correo de autorización (GV): ' . $mailEx->getMessage());
            }

            // =========================================================
            // 7. ENVIAR CORREO DE ANOMALÍAS A LOGÍSTICA / MANTENIMIENTO
            // =========================================================
            try {
                $journey->load(['units.lightInspection', 'units.heavyInspection']);
                $anomaliesList = [];

                foreach ($journey->units as $unit) {
                    $hasAnomaly  = false;
                    $comments    = '';
                    $failedItems = []; // Array para guardar lo que falló

                    $inspection = $unit->lightInspection ?: $unit->heavyInspection;

                    if ($inspection && $inspection->has_anomalies) {
                        $hasAnomaly = true;
                        $comments   = $inspection->anomaly_comments;

                        // Obtenemos todos los datos de la inspección y buscamos los "no"
                        foreach ($inspection->getAttributes() as $key => $value) {
                            if ($value === 'no' && ! in_array($key, ['has_anomalies'])) {
                                $failedItems[] = $this->translateInspectionField($key);
                            }
                        }
                    }

                    if ($hasAnomaly) {
                        $anomaliesList[] = [
                            'unidad'          => $unit->economic_number,
                            'tipo'            => $unit->unit_type,
                            'comentarios'     => $comments ?: 'Sin comentarios detallados.',
                            'puntos_fallidos' => $failedItems, // Guardamos la lista de fallos
                        ];
                    }
                }

                if (count($anomaliesList) > 0) {
                    $usersWithPermission = User::active()
                        ->whereHas('directPermissions', function ($query) {
                            $query->where('name', 'notificacion_anomalias');
                        })
                        ->pluck('email')
                        ->filter()
                        ->toArray();

                    if (! empty($usersWithPermission)) {
                        Mail::to($usersWithPermission)->send(new AnomalyNotificationMail($journey, $anomaliesList));
                    }
                }
            } catch (\Exception $anomalyMailEx) {
                Log::error('🚨 Error enviando alerta de anomalías: ' . $anomalyMailEx->getMessage());
            }
            // =========================================================
            // 8. RESPONDER AL NAVEGADOR
            // =========================================================
            return response()->json([
                'success'    => true,
                'message'    => 'Viaje guardado exitosamente',
                'journey_id' => $journey->id,
                'folio'      => $journey->folio,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando viaje: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el viaje: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Crear el viaje principal
     */
    private function createJourney($data)
    {
        // Validar que tenemos el folio
        $folio = $this->generateFolio();
        // Determinar tipo de flota
        $fleetType = isset($data['unidades']) && count($data['unidades']) > 1
            ? 'Convoy de Unidades'
            : 'Unidad Única';

        // Parsear duración estimada
        $estimatedDuration = $this->calculateEstimatedDuration(
            $data['hora_inicio'] ?? '',
            $data['hora_fin'] ?? ''
        );

        $journey = Journey::create([
            'folio'                => $folio,
            'request_date'         => $data['fecha_solicitud'] ?? now()->format('Y-m-d'),
            'creator_name'         => $data['solicitante'] ?? Auth::user()->name,
            'area'                 => $data['area'] ?? '',
            'approval_status'      => 'pending',
            'journey_status'       => 'not_started',
            'destination_region'   => $data['destino_predefinido'] ?? '',
            'specific_destination' => $data['destino_especifico'] ?? '',
            'origin_address'       => $data['origen'] ?? '',
            'destination_address'  => $data['llegada'] ?? '',
            'start_date'           => $this->parseDate($data['fecha_inicio'] ?? ''),
            'end_date'             => $this->parseDate($data['fecha_fin'] ?? ''),
            'start_time'           => $data['hora_inicio'] ?? '',
            'end_time'             => $data['hora_fin'] ?? '',
            'estimated_duration'   => $estimatedDuration,
            'has_stops'            => ($data['tiene_paradas'] ?? 'no') === 'si',
            'planned_stops'        => $data['paradas'] ?? null,
            'total_units'          => count($data['unidades'] ?? []),
            'fleet_type'           => $fleetType,
            'risk_score'           => $data['riesgo_puntaje'] ?? null,
            'risk_level'           => $data['riesgo_nivel'] ?? null,
            'created_by'           => Auth::id(),
            'approver_id'          => $data['autorizador_id'] ?? null,
        ]);

        return $journey;
    }

    /**
     * Crear las unidades y sus inspecciones
     */
    private function createUnits($journey, $unidades)
    {
        foreach ($unidades as $index => $unidadData) {
            // Extraer datos del conductor
            $conductorData = $this->extractDriverData($unidadData);

            // Crear la unidad
            $journeyUnit = JourneyUnit::create([
                'journey_id'                       => $journey->id,
                'unit_type'                        => $unidadData['tipo_vehiculo'] ?? $this->determineUnitType($unidadData['vehiculo'] ?? ''),
                'economic_number'                  => $unidadData['vehiculo'] ?? '',
                'driver_id'                        => $conductorData['conductor_id'],
                'driver_name'                      => $conductorData['nombre'],
                'alcohol_pct'                      => $conductorData['alcohol_pct'],
                'blood_pressure'                   => $conductorData['presion_valor'],
                'takes_medication'                 => $conductorData['toma_medicamento'],
                'medication_name'                  => $conductorData['medicamento_nombre'],
                'state_license_validity'           => $conductorData['vigencia_lic'],
                'light_defensive_driving_validity' => $conductorData['vigencia_man'],
                'federal_license_validity'         => $conductorData['licencia_federal'] ?? null,
                'heavy_defensive_driving_validity' => $conductorData['curso_pesado'] ?? null,
                'sleep_at'                         => $conductorData['hora_dormir'],
                'wake_up_at'                       => $conductorData['hora_levantar'],
                'total_sleep_hours'                => $conductorData['total_dormidas'],
                'awake_hours_before'               => $conductorData['horas_despierto'],
                'journey_duration'                 => $conductorData['horas_viaje'],
                'total_active_hours'               => $conductorData['total_finalizar'],
                'passengers'                       => $this->extractPassengersData($unidadData),
            ]);

            // Si hay inspección ligera, guardarla
            if (isset($unidadData['inspeccion_ligera'])) {
                $this->createLightInspection($journeyUnit, $unidadData['inspeccion_ligera']);
            }

            // Si hay inspección pesada, guardarla
            if (isset($unidadData['inspeccion_pesada'])) {
                $this->createHeavyInspection($journeyUnit, $unidadData['inspeccion_pesada']);
            }
        }
    }

    /**
     * Extraer datos del conductor principal
     */
    private function extractDriverData($unidadData)
    {
        return [
            'conductor_id'       => $unidadData['conductor_id'] ?? null,
            'nombre'             => $unidadData['conductor'] ?? '',
            'alcohol_pct'        => $unidadData['alcohol_pct'] ?? 0.0,
            'presion_valor'      => $unidadData['presion_valor'] ?? '',
            'toma_medicamento'   => $unidadData['toma_medicamento'] ?? 'no',
            'medicamento_nombre' => $unidadData['medicamento_nombre'] ?? null,
            'vigencia_lic'       => $unidadData['vigencia_lic'] ?? '',
            'vigencia_man'       => $unidadData['vigencia_man'] ?? '',
            'licencia_federal'   => $unidadData['licencia_federal'] ?? null,
            'curso_pesado'       => $unidadData['curso_pesado'] ?? null,
            'hora_dormir'        => $unidadData['hora_dormir'] ?? null,
            'hora_levantar'      => $unidadData['hora_levantar'] ?? null,
            'total_dormidas'     => $unidadData['total_dormidas'] ?? '0:00',
            'horas_despierto'    => $unidadData['horas_despierto'] ?? '0:00',
            'horas_viaje'        => $unidadData['horas_viaje'] ?? '0:00',
            'total_finalizar'    => $unidadData['total_finalizar'] ?? '0:00',
        ];
    }

    /**
     * Extraer datos de pasajeros
     */
    private function extractPassengersData($unidadData)
    {
        $passengers = [];

        if (isset($unidadData['pasajeros']) && is_array($unidadData['pasajeros'])) {
            foreach ($unidadData['pasajeros'] as $pasajeroData) {
                $isRelay = isset($pasajeroData['es_relevo']) && $pasajeroData['es_relevo'];

                $passenger = [
                    'Passenger' => count($passengers) + 1,
                    'id'        => $pasajeroData['id'] ?? null,
                    'name'      => $pasajeroData['nombre'] ?? '',
                    'is_relay'  => $isRelay,
                    'role'      => $isRelay ? 'second_driver' : 'passenger',
                ];

                // Si es relevo, incluir datos adicionales
                if ($isRelay) {
                    $passenger = array_merge($passenger, [
                        'alcohol_pct'         => $pasajeroData['alcohol_pct'] ?? 0.0,
                        'blood_pressure'      => $pasajeroData['presion_valor'] ?? '',
                        'takes_medication'    => $pasajeroData['medicamento'] ?? 'no',
                        'medication_name'     => $pasajeroData['medicamento_nombre'] ?? '',
                        'sleep_at'            => $pasajeroData['dormir'] ?? '',
                        'wake_up_at'          => $pasajeroData['levantar'] ?? '',
                        'total_sleep_hours'   => $pasajeroData['hrs_dormidas'] ?? '',
                        'awake_hours_before'  => $pasajeroData['hr_despierto'] ?? '',
                        'journey_duration'    => $pasajeroData['duracion_viaje'] ?? '',
                        'total_active_hours'  => $pasajeroData['total_hrs'] ?? '',
                        'state_license_val'   => $pasajeroData['vigencia_lic'] ?? '',
                        'federal_license_val' => $pasajeroData['licencia_federal'] ?? '',
                        'light_course_val'    => $pasajeroData['vigencia_man_ligera'] ?? '',
                        'heavy_course_val'    => $pasajeroData['curso_pesado'] ?? '',
                    ]);
                }

                $passengers[] = $passenger;
            }
        }

        return $passengers;
    }
/**
 * Crear inspección ligera
 */
    private function createLightInspection($journeyUnit, $inspectionData)
    {
        // Procesar fotos si existen
        $photoPaths = $this->processPhotos($inspectionData['fotos'] ?? [], 'L', $journeyUnit->journey->folio);

        LightInspection::create([
            'journey_unit_id'         => $journeyUnit->id,
            'fuel_level'              => $inspectionData['nivel_gasolina'] ?? '',
            'mileage'                 => isset($inspectionData['kilometraje']) ? (int) str_replace(',', '', $inspectionData['kilometraje']) : 0,

            // --- PASAMOS EL STRING DIRECTO, POR DEFECTO 'na' ---
            'doc_registration_card'   => $inspectionData['doc_tarjeta'] ?? 'na',
            'doc_insurance_policy'    => $inspectionData['doc_poliza'] ?? 'na',
            'doc_emergency_phones'    => $inspectionData['doc_tel_emergencia'] ?? 'na',
            'vis_first_aid_kit'       => $inspectionData['vis_botiquin'] ?? 'na',
            'vis_safety_triangles'    => $inspectionData['vis_triangulo'] ?? 'na',
            'vis_fire_extinguisher'   => $inspectionData['vis_extintor'] ?? 'na',
            'vis_jack_wrench'         => $inspectionData['vis_gato'] ?? 'na',
            'vis_jumper_cables'       => $inspectionData['vis_cables'] ?? 'na',
            'vis_basic_tools'         => $inspectionData['vis_herramientas'] ?? 'na',
            'vis_flashlight'          => $inspectionData['vis_linterna'] ?? 'na',
            'vis_mirrors'             => $inspectionData['vis_espejos'] ?? 'na',
            'vis_spare_tire'          => $inspectionData['vis_refaccion'] ?? 'na',
            'vis_tires_condition'     => $inspectionData['vis_neumaticos'] ?? 'na',
            'vis_paint_condition'     => $inspectionData['vis_pintura'] ?? 'na',
            'vis_windshield_wipers'   => $inspectionData['vis_parabrisas'] ?? 'na',
            'vis_bumpers'             => $inspectionData['vis_defensas'] ?? 'na',
            'vis_main_lights'         => $inspectionData['vis_luces_gral'] ?? 'na',
            'vis_stop_reverse_lights' => $inspectionData['vis_luces_stop'] ?? 'na',
            'vis_horn'                => $inspectionData['vis_claxon'] ?? 'na',
            'vis_company_logos'       => $inspectionData['vis_logos'] ?? 'na',
            'vis_seats_condition'     => $inspectionData['vis_asientos'] ?? 'na',
            'vis_dashboard_panel'     => $inspectionData['vis_panel'] ?? 'na',
            'vis_seatbelts'           => $inspectionData['vis_cinturones'] ?? 'na',
            'maint_leaks_check'       => $inspectionData['mant_fugas'] ?? 'na',
            'maint_fluid_levels'      => $inspectionData['mant_niveles'] ?? 'na',
            'maint_belts_condition'   => $inspectionData['mant_bandas'] ?? 'na',

            // --- ESTA SE QUEDA IGUAL (es la única booleana en la BD) ---
            'has_anomalies'           => ($inspectionData['anomalias_detectadas'] ?? 'no') === 'si',
            'anomaly_comments'        => $inspectionData['comentarios'] ?? null,
            'photo_evidence'          => $photoPaths,
        ]);
    }

/**
 * Crear inspección pesada
 */
    private function createHeavyInspection($journeyUnit, $inspectionData)
    {
        // Procesar fotos si existen
        $photoPaths = $this->processPhotos($inspectionData['fotos'] ?? [], 'P', $journeyUnit->journey->folio);

        HeavyInspection::create([
            'journey_unit_id'         => $journeyUnit->id,
            'fuel_level'              => $inspectionData['nivel_diesel'] ?? '',
            'mileage'                 => isset($inspectionData['kilometraje']) ? (int) str_replace(',', '', $inspectionData['kilometraje']) : 0,

            // --- PASAMOS EL STRING DIRECTO, POR DEFECTO 'na' ---
            'doc_registration_card'   => $inspectionData['doc_tarjeta'] ?? 'na',
            'doc_insurance_policy'    => $inspectionData['doc_poliza'] ?? 'na',
            'doc_cargo_permit'        => $inspectionData['doc_permiso_carga'] ?? 'na',
            'doc_emissions_cert'      => $inspectionData['doc_bajos_contam'] ?? 'na',
            'doc_mechanical_cert'     => $inspectionData['doc_fisico_mec'] ?? 'na',
            'doc_waybill'             => $inspectionData['doc_carta_porte'] ?? 'na',
            'doc_emergency_phones'    => $inspectionData['doc_tel_emergencia'] ?? 'na',
            'vis_first_aid_kit'       => $inspectionData['vis_botiquin'] ?? 'na',
            'vis_safety_cones'        => $inspectionData['vis_conos'] ?? 'na',
            'vis_fire_extinguisher'   => $inspectionData['vis_extintor'] ?? 'na',
            'vis_jack'                => $inspectionData['vis_gato'] ?? 'na',
            'vis_jumper_cables'       => $inspectionData['vis_cables'] ?? 'na',
            'vis_flashlight'          => $inspectionData['vis_linterna'] ?? 'na',
            'vis_mirrors'             => $inspectionData['vis_espejos'] ?? 'na',
            'vis_spare_tire'          => $inspectionData['vis_refaccion'] ?? 'na',
            'vis_tires_condition'     => $inspectionData['vis_llantas_estado'] ?? 'na',
            'vis_tires_calibrated'    => $inspectionData['vis_llantas_calib'] ?? 'na',
            'vis_doors_windows'       => $inspectionData['vis_puertas'] ?? 'na',
            'vis_body_dents'          => $inspectionData['vis_golpes'] ?? 'na',
            'vis_windshield_wipers'   => $inspectionData['vis_limpiaparabrisas'] ?? 'na',
            'vis_air_conditioning'    => $inspectionData['vis_aire_acond'] ?? 'na',
            'vis_springs_suspension'  => $inspectionData['vis_resortes'] ?? 'na',
            'vis_air_bags_suspension' => $inspectionData['vis_bolsas_aire'] ?? 'na',
            'vis_general_lights'      => $inspectionData['vis_luces_gral'] ?? 'na',
            'vis_horn'                => $inspectionData['vis_claxon'] ?? 'na',
            'vis_reverse_alarm'       => $inspectionData['vis_alarma_reversa'] ?? 'na',
            'vis_logos'               => $inspectionData['vis_logos'] ?? 'na',
            'vis_seats'               => $inspectionData['vis_asientos'] ?? 'na',
            'vis_seatbelts'           => $inspectionData['vis_cinturones'] ?? 'na',
            'vis_beacon_light'        => $inspectionData['vis_torreta'] ?? 'na',
            'maint_engine_start'      => $inspectionData['mant_encendido'] ?? 'na',
            'maint_oil_pressure'      => $inspectionData['mant_presion_aceite'] ?? 'na',
            'maint_engine_temp'       => $inspectionData['mant_temp_motor'] ?? 'na',
            'maint_air_pressure'      => $inspectionData['mant_presion_aire'] ?? 'na',
            'maint_fan_clutch'        => $inspectionData['mant_fan_clutch'] ?? 'na',
            'maint_batteries'         => $inspectionData['mant_baterias'] ?? 'na',
            'maint_speedometer'       => $inspectionData['mant_velocimetro'] ?? 'na',
            'maint_rpm_indicator'     => $inspectionData['mant_rpm'] ?? 'na',
            'maint_oil_level'         => $inspectionData['mant_nivel_aceite'] ?? 'na',
            'maint_coolant_level'     => $inspectionData['mant_nivel_anticongelante'] ?? 'na',
            'maint_hydraulic_level'   => $inspectionData['mant_nivel_hidraulico'] ?? 'na',
            'maint_diesel_level'      => $inspectionData['mant_nivel_diesel'] ?? 'na',
            'maint_engine_brake'      => $inspectionData['mant_freno_motor'] ?? 'na',
            'maint_parking_brake'     => $inspectionData['mant_freno_parqueo'] ?? 'na',
            'maint_belts'             => $inspectionData['mant_bandas'] ?? 'na',
            'maint_air_tank_purge'    => $inspectionData['mant_purgado'] ?? 'na',

            // --- ESTA SE QUEDA IGUAL ---
            'has_anomalies'           => ($inspectionData['anomalias_detectadas'] ?? 'no') === 'si',
            'anomaly_comments'        => $inspectionData['comentarios'] ?? null,
            'photo_evidence'          => $photoPaths,
        ]);
    }

    /**
     * Crear evaluación de riesgo
     */
    private function createRiskAssessment($journey, $riskData)
    {
        RiskAssessment::create([
            'journey_id'                => $journey->id,
            'defensive_driving_option'  => $riskData['defensive_driving_option'] ?? '',
            'defensive_driving_score'   => $riskData['defensive_driving_score'] ?? 0,
            'awake_hours_option'        => $riskData['awake_hours_option'] ?? '',
            'awake_hours_score'         => $riskData['awake_hours_score'] ?? 0,
            'fleet_composition_option'  => $riskData['fleet_composition_option'] ?? '',
            'fleet_composition_score'   => $riskData['fleet_composition_score'] ?? 0,
            'communication_option'      => $riskData['communication_option'] ?? '',
            'communication_score'       => $riskData['communication_score'] ?? 0,
            'weather_option'            => $riskData['weather_option'] ?? '',
            'weather_score'             => $riskData['weather_score'] ?? 0,
            'lighting_option'           => $riskData['lighting_option'] ?? '',
            'lighting_score'            => $riskData['lighting_score'] ?? 0,
            'road_condition_option'     => $riskData['road_condition_option'] ?? '',
            'road_condition_score'      => $riskData['road_condition_score'] ?? 0,
            'extra_road_hazards_option' => $riskData['extra_road_hazards_option'] ?? '',
            'extra_road_hazards_score'  => $riskData['extra_road_hazards_score'] ?? 0,
            'wildlife_activity_option'  => $riskData['wildlife_activity_option'] ?? '',
            'wildlife_activity_score'   => $riskData['wildlife_activity_score'] ?? 0,
            'route_security_option'     => $riskData['route_security_option'] ?? '',
            'route_security_score'      => $riskData['route_security_score'] ?? 0,
            'hazardous_material_option' => $riskData['hazardous_material_option'] ?? '',
            'hazardous_material_score'  => $riskData['hazardous_material_score'] ?? 0,
            'is_night_shift'            => $riskData['is_night_shift'] ?? false,
            'has_low_sleep'             => $riskData['has_low_sleep'] ?? false,
            'exceeds_midnight'          => $riskData['exceeds_midnight'] ?? false,
            'extreme_fatigue'           => $riskData['extreme_fatigue'] ?? false,
            'total_score'               => $riskData['total_score'] ?? 0,
            'risk_level'                => $riskData['risk_level'] ?? 'bajo',
        ]);
    }

    /**
     * Crear reunión pre-convoy
     */
    private function createPreConvoyMeeting($journey, $meetingData)
    {

        PreConvoyMeeting::create([
            'journey_id'                  => $journey->id,
            // AHORA USAMOS EL ID QUE MANDÓ EL JAVASCRIPT DIRECTAMENTE
            'convoy_leader_id'            => $meetingData['lider_convoy_id'] ?? null,

            'understand_stopping_points'  => ($meetingData['puntos_parada'] ?? 'no') === 'si',
            'know_convoy_break_protocol'  => ($meetingData['ruptura_convoy'] ?? 'no') === 'si',
            'documents_verified'          => ($meetingData['doc_vigente'] ?? 'no') === 'si',
            'accident_prevention_aware'   => ($meetingData['prevencion_acc'] ?? 'no') === 'si',
            'has_emergency_contacts'      => ($meetingData['contactos_emerg'] ?? 'no') === 'si',
            'leader_commitment_confirmed' => ($meetingData['compromiso_lider'] ?? 'no') === 'si',
        ]);
    }

    /**
     * Procesar fotos y guardarlas en storage
     */
/**
 * Procesar fotos y guardarlas en carpetas organizadas por Folio
 * @param array $fotos Arreglo de fotos en base64 o archivos
 * @param string $typeSuffix 'L' para Ligera, 'P' para Pesada
 * @param string $folio Folio del viaje (ej. GV-00001)
 */
    private function processPhotos($fotos, $typeSuffix, $folio)
    {
        $paths = [];

        if (isset($fotos) && is_array($fotos)) {
            foreach ($fotos as $index => $fotoData) {
                // Caso 1: Si es base64 (Cámara)
                if (isset($fotoData['base64'])) {
                    $path = $this->saveBase64Image($fotoData['base64'], $typeSuffix, $folio);
                    if ($path) {
                        $paths[] = $path;
                    }

                }
                // Caso 2: Si es archivo subido (Input file)
                elseif (isset($fotoData['file']) && $fotoData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $folder   = "qhse/management/anomalias{$typeSuffix}/{$folio}";
                    $fileName = 'anomalia_' . time() . '_' . ($index + 1) . '.' . $fotoData['file']->getClientOriginalExtension();
                    $path     = $fotoData['file']->storeAs($folder, $fileName, 'public');
                    $paths[]  = $path;
                }
            }
        }

        return $paths;
    }

/**
 * Guardar imagen en base64 con nombre corto y carpeta de Folio
 */
    private function saveBase64Image($base64String, $typeSuffix, $folio)
    {
        try {
            $base64String = preg_replace('/^data:image\/(\w+);base64,/', '', $base64String);
            $extension    = 'jpg'; // O dinámica si extraes el mime

            $imageData = base64_decode($base64String);

            $fileName   = 'anomalia_' . str_replace(['.', ' '], '_', microtime(true)) . '.' . $extension;
            $folderPath = "qhse/management/anomalias{$typeSuffix}/{$folio}";
            $fullPath   = "{$folderPath}/{$fileName}";

            // --- ESTO EVITA EL ERROR 500 POR CARPETA NO EXISTENTE ---
            if (! Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->makeDirectory($folderPath, 0755, true);
            }

            Storage::disk('public')->put($fullPath, $imageData);

            return $fullPath;
        } catch (\Exception $e) {
            Log::error('Error guardando imagen: ' . $e->getMessage());
            return null;
        }
    }

/**
 * Generar folio único seguro
 */
    private function generateFolio()
    {
        // Buscamos el último viaje registrado
        $lastJourney = Journey::orderBy('id', 'desc')->first();

        // Extraemos el número (ej: de "GV-00005" saca el "5")
        $lastNumber = $lastJourney ? intval(substr($lastJourney->folio, 3)) : 0;

        // Bucle de seguridad: Garantiza que el folio no exista en la BD
        do {
            $lastNumber++;
            $folio = 'GV-' . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);
        } while (Journey::where('folio', $folio)->exists());

        return $folio;
    }

    /**
     * Calcular duración estimada
     */
    private function calculateEstimatedDuration($startTime, $endTime)
    {
        if (empty($startTime) || empty($endTime)) {
            return '0:00';
        }

        try {
            $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
            $end   = \Carbon\Carbon::createFromFormat('H:i', $endTime);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $diff = $start->diff($end);
            return $diff->format('%H:%I');
        } catch (\Exception $e) {
            return '0:00';
        }
    }

    /**
     * Parsear fecha de formato d/m/Y a Y-m-d para MySQL
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Paso 1: Limpiar el string (eliminar escapes y espacios)
            $cleanDate = stripslashes(trim($dateString));

            // Paso 2: Registrar para depuración
            Log::info('Parseando fecha - Original: ' . $dateString . ' | Limpia: ' . $cleanDate);

            // Paso 3: Si ya está en formato YYYY-MM-DD, devolverlo directamente
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $cleanDate)) {
                return $cleanDate;
            }

            // Paso 4: Convertir de DD/MM/YYYY a YYYY-MM-DD
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $cleanDate, $matches)) {
                $dia  = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $mes  = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $año = $matches[3];

                $fechaMySQL = $año . '-' . $mes . '-' . $dia;
                Log::info('Fecha convertida a MySQL: ' . $fechaMySQL);
                return $fechaMySQL;
            }

            // Paso 5: Intentar con Carbon como último recurso
            $date = \Carbon\Carbon::createFromFormat('d/m/Y', $cleanDate);
            if ($date) {
                return $date->format('Y-m-d');
            }

            Log::warning('No se pudo parsear la fecha: ' . $dateString);
            return null;

        } catch (\Exception $e) {
            Log::error('Error parseando fecha: ' . $e->getMessage() . ' - Fecha: ' . $dateString);
            return null;
        }
    }

    /**
     * Determinar tipo de unidad por número económico
     */
    private function determineUnitType($economicNumber)
    {
                         // Aquí puedes implementar lógica para determinar si es ligera o pesada
                         // basado en el número económico
        return 'Ligera'; // Valor por defecto
    }

   /**
     * Traduce las columnas de la BD al texto exacto que el usuario vio en la pantalla
     */
    private function translateInspectionField($field)
    {
        $labels = [
            // ================= I. DOCUMENTACIÓN =================
            'doc_registration_card' => 'Tarjeta de circulación',
            'doc_insurance_policy'  => 'Póliza de seguro vigente',
            'doc_emergency_phones'  => 'Teléfonos Emergencia / Aseguradora',
            // Exclusivos Pesadas
            'doc_cargo_permit'      => 'Permiso de transporte de carga',
            'doc_emissions_cert'    => 'Certificado de bajos contaminantes',
            'doc_mechanical_cert'   => 'Certificado físico mecánico',
            'doc_waybill'           => 'Carta Porte',

            // ================= II. INSPECCIÓN VISUAL =================
            'vis_first_aid_kit'       => 'Kit de primeros auxilios',
            'vis_fire_extinguisher'   => 'Extintor (Cargado/Vigente)',
            'vis_jumper_cables'       => 'Cables pasa corrientes',
            'vis_flashlight'          => 'Linterna de mano',
            'vis_spare_tire'          => 'Llanta de refacción',
            'vis_tires_condition'     => 'Neumáticos en buen estado',
            'vis_horn'                => 'Claxon',
            'vis_seatbelts'           => 'Cinturones de seguridad',

            // Exclusivos Ligeras
            'vis_safety_triangles'    => 'Triángulos de seguridad',
            'vis_jack_wrench'         => 'Gato y Cruceta',
            'vis_basic_tools'         => 'Kit de herramientas básicas',
            'vis_mirrors'             => 'Espejos (Laterales/Retrovisor)',
            'vis_paint_condition'     => 'Laminación y pintura',
            'vis_windshield_wipers'   => 'Parabrisas y limpiadores',
            'vis_bumpers'             => 'Defensas',
            'vis_main_lights'         => 'Luces (Altas, Bajas, Direccionales)',
            'vis_stop_reverse_lights' => 'Luces de Stop y Reversa',
            'vis_company_logos'       => 'Logotipos (Compañía y No. Eco)',
            'vis_seats_condition'     => 'Estado de Asientos',
            'vis_dashboard_panel'     => 'Panel de control (Indicadores)',

            // Exclusivos Pesadas
            'vis_safety_cones'        => 'Conos reflejantes',
            'vis_jack'                => 'Gato hidráulico',
            'vis_tires_calibrated'    => 'Llantas calibradas',
            'vis_doors_windows'       => 'Puertas, vidrios y ventanas',
            'vis_body_dents'          => 'Golpes',
            'vis_air_conditioning'    => 'Funcionamiento Aire Acondicionado',
            'vis_springs_suspension'  => 'Resortes y muelles',
            'vis_air_bags_suspension' => 'Bolsas de aire de suspensión',
            'vis_general_lights'      => 'Luces en general',
            'vis_reverse_alarm'       => 'Alarma de reversa',
            'vis_logos'               => 'Logotipos (Compañía y Num. Económico)',
            'vis_seats'               => 'Asientos',
            'vis_beacon_light'        => 'Torreta',

            // ================= III. MANTENIMIENTO =================
            // Exclusivos Ligeras
            'maint_leaks_check'         => 'Revisión de fugas',
            'maint_fluid_levels'        => 'Niveles óptimos (Aceite, Líquido de Frenos, Agua)',
            'maint_belts_condition'     => 'Revisión de estado de bandas',

            // Exclusivos Pesadas
            'maint_engine_start'        => 'Encendido de motor',
            'maint_oil_pressure'        => 'Presión de aceite de motor',
            'maint_engine_temp'         => 'Temperatura del motor',
            'maint_air_pressure'        => 'Presión de Aire',
            'maint_fan_clutch'          => 'Fan clutch',
            'maint_batteries'           => 'Condiciones de baterías',
            'maint_speedometer'         => 'Velocímetro',
            'maint_rpm_indicator'       => 'Indicador de RPM',
            'maint_oil_level'           => 'Nivel Aceite de motor',
            'maint_coolant_level'       => 'Nivel Anticongelante',
            'maint_hydraulic_level'     => 'Nivel de aceite hidráulico',
            'maint_diesel_level'        => 'Nivel de diésel',
            'maint_engine_brake'        => 'Freno de motor',
            'maint_parking_brake'       => 'Freno de parqueo',
            'maint_belts'               => 'Bandas',
            'maint_air_tank_purge'      => 'Purgado de tanque de aire',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
