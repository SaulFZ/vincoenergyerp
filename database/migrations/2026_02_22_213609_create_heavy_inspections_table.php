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
            $table->string('fuel_level', 50);      // nivel_diesel
            $table->integer('mileage');            // kilometraje

            // --- I. DOCUMENTACIÓN (Pesados) ---
            $table->string('doc_registration_card', 15); // doc_tarjeta
            $table->string('doc_insurance_policy', 15);  // doc_poliza
            $table->string('doc_cargo_permit', 15);      // doc_permiso_carga
            $table->string('doc_emissions_cert', 15);    // doc_bajos_contam
            $table->string('doc_mechanical_cert', 15);   // doc_fisico_mec
            $table->string('doc_waybill', 15);           // doc_carta_porte
            $table->string('doc_emergency_phones', 15);  // doc_tel_emergencia
            $table->string('doc_driving_license', 15);   // doc_licencia

            // --- II. INSPECCIÓN VISUAL (Pesados) ---
            $table->string('vis_first_aid_kit', 15);     // vis_botiquin
            $table->string('vis_safety_cones', 15);      // vis_conos
            $table->string('vis_fire_extinguisher', 15); // vis_extintor
            $table->string('vis_jack', 15);              // vis_gato
            $table->string('vis_jumper_cables', 15);     // vis_cables
            $table->string('vis_flashlight', 15);        // vis_linterna
            $table->string('vis_mirrors', 15);           // vis_espejos
            $table->string('vis_spare_tire', 15);        // vis_refaccion
            $table->string('vis_tires_condition', 15);   // vis_llantas_estado
            $table->string('vis_tires_calibrated', 15);  // vis_llantas_calib
            $table->string('vis_doors_windows', 15);     // vis_puertas
            $table->string('vis_body_dents', 15);        // vis_golpes
            $table->string('vis_windshield_wipers', 15); // vis_limpiaparabrisas
            $table->string('vis_air_conditioning', 15);  // vis_aire_acond
            $table->string('vis_springs_suspension', 15); // vis_resortes
            $table->string('vis_air_bags_suspension', 15); // vis_bolsas_aire
            $table->string('vis_general_lights', 15);    // vis_luces_gral
            $table->string('vis_horn', 15);              // vis_claxon
            $table->string('vis_reverse_alarm', 15);     // vis_alarma_reversa
            $table->string('vis_logos', 15);             // vis_logos
            $table->string('vis_seats', 15);             // vis_asientos
            $table->string('vis_seatbelts', 15);         // vis_cinturones
            $table->string('vis_beacon_light', 15);      // vis_torreta

            // --- III. MANTENIMIENTO (Pesados) ---
            $table->string('maint_date_km_check', 15);   // mant_fecha_km
            $table->string('maint_engine_start', 15);    // mant_encendido
            $table->string('maint_oil_pressure', 15);    // mant_presion_aceite
            $table->string('maint_engine_temp', 15);     // mant_temp_motor
            $table->string('maint_air_pressure', 15);    // mant_presion_aire
            $table->string('maint_fan_clutch', 15);      // mant_fan_clutch
            $table->string('maint_batteries', 15);       // mant_baterias
            $table->string('maint_speedometer', 15);     // mant_velocimetro
            $table->string('maint_rpm_indicator', 15);   // mant_rpm
            $table->string('maint_oil_level', 15);       // mant_nivel_aceite
            $table->string('maint_coolant_level', 15);   // mant_nivel_anticongelante
            $table->string('maint_hydraulic_level', 15); // mant_nivel_hidraulico
            $table->string('maint_diesel_level', 15);    // mant_nivel_diesel
            $table->string('maint_engine_brake', 15);    // mant_freno_motor
            $table->string('maint_parking_brake', 15);   // mant_freno_parqueo
            $table->string('maint_belts', 15);           // mant_bandas
            $table->string('maint_air_tank_purge', 15);  // mant_purgado

            // --- IV. ANOMALÍAS Y EVIDENCIA ---
            $table->boolean('has_anomalies');             // anomalias_pesada (1 = si, 0 = no)
            $table->text('anomaly_comments')->nullable(); // comentarios

            // Rutas de fotos en JSON
            $table->json('photo_evidence')->nullable();

            $table->timestamps(); // created_at será tu fecha de inspección
        });
    }

    public function down(): void {
        Schema::dropIfExists('heavy_inspections');
    }
};
