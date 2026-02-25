<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();

            // Relación con el viaje raíz (Cascada: si se borra el viaje, se borra el riesgo)
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();

            // --- 11 PUNTOS DE EVALUACIÓN (OPCIÓN + PUNTAJE) ---
            // 1. Curso manejo defensivo
            $table->string('defensive_driving_option')->nullable();
            $table->integer('defensive_driving_score')->nullable();

            // 2. Horas despierto
            $table->string('awake_hours_option')->nullable();
            $table->integer('awake_hours_score')->nullable();

            // 3. Núm. de vehículos y pasajeros
            $table->string('fleet_composition_option')->nullable();
            $table->integer('fleet_composition_score')->nullable();

            // 4. Comunicación
            $table->string('communication_option')->nullable();
            $table->integer('communication_score')->nullable();

            // 5. Condiciones clima
            $table->string('weather_option')->nullable();
            $table->integer('weather_score')->nullable();

            // 6. Condiciones de iluminación
            $table->string('lighting_option')->nullable();
            $table->integer('lighting_score')->nullable();

            // 7. Condiciones de la carretera
            $table->string('road_condition_option')->nullable();
            $table->integer('road_condition_score')->nullable();

            // 8. Otras Cond. de la carretera
            $table->string('extra_road_hazards_option')->nullable();
            $table->integer('extra_road_hazards_score')->nullable();

            // 9. Animales de la zona
            $table->string('wildlife_activity_option')->nullable();
            $table->integer('wildlife_activity_score')->nullable();

            // 10. Seguridad de la ruta
            $table->string('route_security_option')->nullable();
            $table->integer('route_security_score')->nullable();

            // 11. Material radiactivo
            $table->string('hazardous_material_option')->nullable();
            $table->integer('hazardous_material_score')->nullable();

            // --- FACTORES ADICIONALES (CHECKBOXES) ---
            $table->boolean('is_night_shift')->default(false);    // Después de las 21:00
            $table->boolean('has_low_sleep')->default(false);     // <= 6 hrs dormidas
            $table->boolean('exceeds_midnight')->default(false);  // Rebase media noche
            $table->boolean('extreme_fatigue')->default(false);   // > 16 hrs despierto

            // --- RESULTADO FINAL ---
            $table->integer('total_score')->nullable();
            $table->string('risk_level')->nullable(); // Low, Medium, High, Very High

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('risk_assessments');
    }
};
