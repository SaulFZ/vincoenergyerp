<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('heavy_inspections', function (Blueprint $table) {
            $table->id();

            // Relación con la unidad específica
            $table->foreignId('journey_unit_id')->constrained('journey_units')->cascadeOnDelete();

            // --- CABECERA (DATOS DINÁMICOS) ---
            $table->string('fuel_level');      // nivel_diesel
            $table->integer('mileage');       // kilometraje

            // --- I. DOCUMENTACIÓN (Pesados) ---
            $table->boolean('doc_registration_card')->default(false); // doc_tarjeta
            $table->boolean('doc_insurance_policy')->default(false);  // doc_poliza
            $table->boolean('doc_cargo_permit')->default(false);      // doc_permiso_carga
            $table->boolean('doc_emissions_cert')->default(false);    // doc_bajos_contam
            $table->boolean('doc_mechanical_cert')->default(false);   // doc_fisico_mec
            $table->boolean('doc_waybill')->default(false);           // doc_carta_porte
            $table->boolean('doc_emergency_phones')->default(false);  // doc_tel_emergencia
            $table->boolean('doc_driving_license')->default(false);   // doc_licencia

            // --- II. INSPECCIÓN VISUAL (Pesados) ---
            $table->boolean('vis_first_aid_kit')->default(false);     // vis_botiquin
            $table->boolean('vis_safety_cones')->default(false);      // vis_conos
            $table->boolean('vis_fire_extinguisher')->default(false); // vis_extintor
            $table->boolean('vis_jack')->default(false);              // vis_gato
            $table->boolean('vis_jumper_cables')->default(false);     // vis_cables
            $table->boolean('vis_flashlight')->default(false);        // vis_linterna
            $table->boolean('vis_mirrors')->default(false);           // vis_espejos
            $table->boolean('vis_spare_tire')->default(false);        // vis_refaccion
            $table->boolean('vis_tires_condition')->default(false);   // vis_llantas_estado
            $table->boolean('vis_tires_calibrated')->default(false);  // vis_llantas_calib
            $table->boolean('vis_doors_windows')->default(false);     // vis_puertas
            $table->boolean('vis_body_dents')->default(false);        // vis_golpes
            $table->boolean('vis_windshield_wipers')->default(false); // vis_limpiaparabrisas
            $table->boolean('vis_air_conditioning')->default(false);  // vis_aire_acond
            $table->boolean('vis_springs_suspension')->default(false); // vis_resortes
            $table->boolean('vis_air_bags_suspension')->default(false); // vis_bolsas_aire
            $table->boolean('vis_general_lights')->default(false);    // vis_luces_gral
            $table->boolean('vis_horn')->default(false);              // vis_claxon
            $table->boolean('vis_reverse_alarm')->default(false);     // vis_alarma_reversa
            $table->boolean('vis_logos')->default(false);             // vis_logos
            $table->boolean('vis_seats')->default(false);             // vis_asientos
            $table->boolean('vis_seatbelts')->default(false);         // vis_cinturones
            $table->boolean('vis_beacon_light')->default(false);      // vis_torreta

            // --- III. MANTENIMIENTO (Pesados) ---
            $table->boolean('maint_date_km_check')->default(false);   // mant_fecha_km
            $table->boolean('maint_engine_start')->default(false);    // mant_encendido
            $table->boolean('maint_oil_pressure')->default(false);    // mant_presion_aceite
            $table->boolean('maint_engine_temp')->default(false);     // mant_temp_motor
            $table->boolean('maint_air_pressure')->default(false);    // mant_presion_aire
            $table->boolean('maint_fan_clutch')->default(false);      // mant_fan_clutch
            $table->boolean('maint_batteries')->default(false);       // mant_baterias
            $table->boolean('maint_speedometer')->default(false);     // mant_velocimetro
            $table->boolean('maint_rpm_indicator')->default(false);   // mant_rpm
            $table->boolean('maint_oil_level')->default(false);       // mant_nivel_aceite
            $table->boolean('maint_coolant_level')->default(false);   // mant_nivel_anticongelante
            $table->boolean('maint_hydraulic_level')->default(false); // mant_nivel_hidraulico
            $table->boolean('maint_diesel_level')->default(false);    // mant_nivel_diesel
            $table->boolean('maint_engine_brake')->default(false);    // mant_freno_motor
            $table->boolean('maint_parking_brake')->default(false);   // mant_freno_parqueo
            $table->boolean('maint_belts')->default(false);           // mant_bandas
            $table->boolean('maint_air_tank_purge')->default(false);  // mant_purgado

            // --- IV. ANOMALÍAS Y EVIDENCIA ---
            $table->boolean('has_anomalies')->default(false);         // anomalias_pesada
            $table->text('anomaly_comments')->nullable();             // comentarios

            // Rutas de fotos en JSON (Estructura: ["qhse/gerenciamiento/anomaliasP/foto1.jpg", ...])
            $table->json('photo_evidence')->nullable();

            $table->timestamps(); // created_at será tu fecha de inspección
        });
    }

    public function down(): void {
        Schema::dropIfExists('heavy_inspections');
    }
};
