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
            $table->string('fuel_level');      // nivel_gasolina
            $table->integer('mileage');       // kilometraje

            // --- I. DOCUMENTACIÓN (Sección 1 del modal) ---
            $table->boolean('doc_registration_card')->default(false); // doc_tarjeta
            $table->boolean('doc_insurance_policy')->default(false);  // doc_poliza
            $table->boolean('doc_emergency_phones')->default(false);  // doc_tel_emergencia
            $table->boolean('doc_driving_license')->default(false);   // doc_licencia

            // --- II. INSPECCIÓN VISUAL (Sección 2 del modal) ---
            $table->boolean('vis_first_aid_kit')->default(false);     // vis_botiquin
            $table->boolean('vis_safety_triangles')->default(false);  // vis_triangulo
            $table->boolean('vis_fire_extinguisher')->default(false); // vis_extintor
            $table->boolean('vis_jack_wrench')->default(false);       // vis_gato
            $table->boolean('vis_jumper_cables')->default(false);     // vis_cables
            $table->boolean('vis_basic_tools')->default(false);       // vis_herramientas
            $table->boolean('vis_flashlight')->default(false);        // vis_linterna
            $table->boolean('vis_mirrors')->default(false);           // vis_espejos
            $table->boolean('vis_spare_tire')->default(false);        // vis_refaccion
            $table->boolean('vis_tires_condition')->default(false);   // vis_neumaticos
            $table->boolean('vis_paint_condition')->default(false);   // vis_pintura
            $table->boolean('vis_windshield_wipers')->default(false); // vis_parabrisas
            $table->boolean('vis_bumpers')->default(false);           // vis_defensas
            $table->boolean('vis_main_lights')->default(false);       // vis_luces_gral
            $table->boolean('vis_stop_reverse_lights')->default(false); // vis_luces_stop
            $table->boolean('vis_horn')->default(false);              // vis_claxon
            $table->boolean('vis_company_logos')->default(false);     // vis_logos
            $table->boolean('vis_seats_condition')->default(false);   // vis_asientos
            $table->boolean('vis_dashboard_panel')->default(false);   // vis_panel
            $table->boolean('vis_seatbelts')->default(false);         // vis_cinturones

            // --- III. MANTENIMIENTO (Sección 3 del modal) ---
            $table->boolean('maint_last_check_verified')->default(false); // mant_fecha_km
            $table->boolean('maint_leaks_check')->default(false);      // mant_fugas
            $table->boolean('maint_fluid_levels')->default(false);     // mant_niveles
            $table->boolean('maint_belts_condition')->default(false);  // mant_bandas

            // --- IV. ANOMALÍAS Y EVIDENCIA (Sección 4 del modal) ---
            $table->boolean('has_anomalies')->default(false);          // anomalias_ligera
            $table->text('anomaly_comments')->nullable();              // comentarios

            // Rutas de fotos en JSON (Estructura: ["qhse/gerenciamiento/anomaliasL/foto1.jpg", ...])
            $table->json('photo_evidence')->nullable();

            /**
             * TIMESTAMPS:
             * created_at = "Fecha Actual" del modal.
             * updated_at = Fecha de última modificación.
             */
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('light_inspections');
    }
};
