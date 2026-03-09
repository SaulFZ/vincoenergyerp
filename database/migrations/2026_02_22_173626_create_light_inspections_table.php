<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('light_inspections', function (Blueprint $table) {
            $table->id();

            // Relación con la unidad (Llave foránea)
            $table->foreignId('journey_unit_id')->constrained('journey_units')->cascadeOnDelete();

            // --- CABECERA (DATOS GENERALES) ---
            $table->string('fuel_level', 50);      // nivel_gasolina
            $table->integer('mileage');            // kilometraje

            // --- I. DOCUMENTACIÓN (String de 15 caracteres, SIN default) ---
            $table->string('doc_registration_card', 15); // doc_tarjeta
            $table->string('doc_insurance_policy', 15);  // doc_poliza
            $table->string('doc_emergency_phones', 15);  // doc_tel_emergencia
            $table->string('doc_driving_license', 15);   // doc_licencia

            // --- II. INSPECCIÓN VISUAL ---
            $table->string('vis_first_aid_kit', 15);     // vis_botiquin
            $table->string('vis_safety_triangles', 15);  // vis_triangulo
            $table->string('vis_fire_extinguisher', 15); // vis_extintor
            $table->string('vis_jack_wrench', 15);       // vis_gato
            $table->string('vis_jumper_cables', 15);     // vis_cables
            $table->string('vis_basic_tools', 15);       // vis_herramientas
            $table->string('vis_flashlight', 15);        // vis_linterna
            $table->string('vis_mirrors', 15);           // vis_espejos
            $table->string('vis_spare_tire', 15);        // vis_refaccion
            $table->string('vis_tires_condition', 15);   // vis_neumaticos
            $table->string('vis_paint_condition', 15);   // vis_pintura
            $table->string('vis_windshield_wipers', 15); // vis_parabrisas
            $table->string('vis_bumpers', 15);           // vis_defensas
            $table->string('vis_main_lights', 15);       // vis_luces_gral
            $table->string('vis_stop_reverse_lights', 15); // vis_luces_stop
            $table->string('vis_horn', 15);              // vis_claxon
            $table->string('vis_company_logos', 15);     // vis_logos
            $table->string('vis_seats_condition', 15);   // vis_asientos
            $table->string('vis_dashboard_panel', 15);   // vis_panel
            $table->string('vis_seatbelts', 15);         // vis_cinturones

            // --- III. MANTENIMIENTO ---
            $table->string('maint_last_check_verified', 15); // mant_fecha_km
            $table->string('maint_leaks_check', 15);      // mant_fugas
            $table->string('maint_fluid_levels', 15);     // mant_niveles
            $table->string('maint_belts_condition', 15);  // mant_bandas

            // --- IV. ANOMALÍAS Y EVIDENCIA ---
            $table->boolean('has_anomalies');             // anomalias_ligera (1 = si, 0 = no)
            $table->text('anomaly_comments')->nullable(); // comentarios

            // Rutas de fotos en JSON
            $table->json('photo_evidence')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('light_inspections');
    }
};
