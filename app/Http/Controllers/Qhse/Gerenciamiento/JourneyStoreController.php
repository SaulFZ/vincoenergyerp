<?php
namespace App\Http\Controllers\Qhse\Gerenciamiento;

use App\Http\Controllers\Controller;
use App\Mail\Qhse\Gerenciamiento\JourneyApprovalMail;
use App\Models\Auth\User;
use App\Models\Qhse\Gerenciamiento\HeavyInspection;
use App\Models\Qhse\Gerenciamiento\Journey;
use App\Models\Qhse\Gerenciamiento\JourneyLog;
use App\Models\Qhse\Gerenciamiento\JourneyUnit;
use App\Models\Qhse\Gerenciamiento\LightInspection;
use App\Models\Qhse\Gerenciamiento\PreConvoyMeeting;
use App\Models\Qhse\Gerenciamiento\RiskAssessment;
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

            // =========================================================
            // 🚨 NUEVO: REGISTRAR EL LOG DE CREACIÓN EN LA BITÁCORA
            // =========================================================
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
            // ENVIAR CORREO AL AUTORIZADOR DESPUÉS DE GUARDAR
            // =========================================================
            try {
                if ($journey->approver_id) {
                    $approver = User::find($journey->approver_id);
                    if ($approver && $approver->email) {
                        Mail::to($approver->email)->send(new JourneyApprovalMail($journey));
                    }
                }
            } catch (\Exception $mailEx) {
                // Registramos el error de correo pero NO cancelamos el viaje creado
                Log::error('Error enviando correo de autorización (GV): ' . $mailEx->getMessage());
            }

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
            'department'           => $data['departamento'] ?? '',
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
            'journey_unit_id'           => $journeyUnit->id,
            'fuel_level'                => $inspectionData['nivel_gasolina'] ?? '',
            'mileage'                   => isset($inspectionData['kilometraje']) ? (int) str_replace(',', '', $inspectionData['kilometraje']) : 0,

            // --- PASAMOS EL STRING DIRECTO, POR DEFECTO 'na' ---
            'doc_registration_card'     => $inspectionData['doc_tarjeta'] ?? 'na',
            'doc_insurance_policy'      => $inspectionData['doc_poliza'] ?? 'na',
            'doc_emergency_phones'      => $inspectionData['doc_tel_emergencia'] ?? 'na',
            'doc_driving_license'       => $inspectionData['doc_licencia'] ?? 'na',
            'vis_first_aid_kit'         => $inspectionData['vis_botiquin'] ?? 'na',
            'vis_safety_triangles'      => $inspectionData['vis_triangulo'] ?? 'na',
            'vis_fire_extinguisher'     => $inspectionData['vis_extintor'] ?? 'na',
            'vis_jack_wrench'           => $inspectionData['vis_gato'] ?? 'na',
            'vis_jumper_cables'         => $inspectionData['vis_cables'] ?? 'na',
            'vis_basic_tools'           => $inspectionData['vis_herramientas'] ?? 'na',
            'vis_flashlight'            => $inspectionData['vis_linterna'] ?? 'na',
            'vis_mirrors'               => $inspectionData['vis_espejos'] ?? 'na',
            'vis_spare_tire'            => $inspectionData['vis_refaccion'] ?? 'na',
            'vis_tires_condition'       => $inspectionData['vis_neumaticos'] ?? 'na',
            'vis_paint_condition'       => $inspectionData['vis_pintura'] ?? 'na',
            'vis_windshield_wipers'     => $inspectionData['vis_parabrisas'] ?? 'na',
            'vis_bumpers'               => $inspectionData['vis_defensas'] ?? 'na',
            'vis_main_lights'           => $inspectionData['vis_luces_gral'] ?? 'na',
            'vis_stop_reverse_lights'   => $inspectionData['vis_luces_stop'] ?? 'na',
            'vis_horn'                  => $inspectionData['vis_claxon'] ?? 'na',
            'vis_company_logos'         => $inspectionData['vis_logos'] ?? 'na',
            'vis_seats_condition'       => $inspectionData['vis_asientos'] ?? 'na',
            'vis_dashboard_panel'       => $inspectionData['vis_panel'] ?? 'na',
            'vis_seatbelts'             => $inspectionData['vis_cinturones'] ?? 'na',
            'maint_last_check_verified' => $inspectionData['mant_fecha_km'] ?? 'na',
            'maint_leaks_check'         => $inspectionData['mant_fugas'] ?? 'na',
            'maint_fluid_levels'        => $inspectionData['mant_niveles'] ?? 'na',
            'maint_belts_condition'     => $inspectionData['mant_bandas'] ?? 'na',

            // --- ESTA SE QUEDA IGUAL (es la única booleana en la BD) ---
            'has_anomalies'             => ($inspectionData['anomalias_detectadas'] ?? 'no') === 'si',
            'anomaly_comments'          => $inspectionData['comentarios'] ?? null,
            'photo_evidence'            => $photoPaths,
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
            'doc_driving_license'     => $inspectionData['doc_licencia'] ?? 'na',
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
            'maint_date_km_check'     => $inspectionData['mant_fecha_km'] ?? 'na',
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
                    $folder   = "qhse/gerenciamiento/anomalias{$typeSuffix}/{$folio}";
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
            // 1. Limpiar el prefijo data:image/... si existe
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
                $base64String = substr($base64String, strpos($base64String, ',') + 1);
                $extension    = strtolower($matches[1]);
            } else {
                $extension = 'jpg'; // default
            }

            $imageData = base64_decode(str_replace(' ', '+', $base64String));

            if ($imageData === false) {
                return null;
            }

            // 2. Construir la ruta: qhse/gerenciamiento/anomaliasL/GV-00001/anomalia_17000000.jpg
            $fileName   = 'anomalia_' . microtime(true) * 100 . '.' . $extension;
            $folderPath = "qhse/gerenciamiento/anomalias{$typeSuffix}/{$folio}";
            $fullPath   = "{$folderPath}/{$fileName}";

            // 3. Guardar en disco public
            Storage::disk('public')->put($fullPath, $imageData);

            return $fullPath;
        } catch (\Exception $e) {
            Log::error('Error guardando imagen base64: ' . $e->getMessage());
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
}
