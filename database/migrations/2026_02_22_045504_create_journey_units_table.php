<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_units', function (Blueprint $table) {
            $table->id();

            // Relación con la tabla raíz
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();

                                                           // --- 1. CLASIFICACIÓN Y UNIDAD ---
            $table->string('unit_type')->nullable();       // 'Ligera' o 'Pesada'
            $table->string('economic_number')->nullable(); // Número Económico

            // --- 2. CONDUCTOR PRINCIPAL ---
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('driver_name')->nullable();
            $table->decimal('alcohol_pct', 4, 2)->nullable();
            $table->string('blood_pressure')->nullable();
            $table->string('takes_medication')->nullable();
            $table->string('medication_name')->nullable();

            // --- 3. VIGENCIAS SEPARADAS (Tabla Principal) ---
            $table->string('state_license_validity')->nullable();
            $table->string('light_defensive_driving_validity')->nullable();
            $table->string('federal_license_validity')->nullable();
            $table->string('heavy_defensive_driving_validity')->nullable();

                                                    // --- 4. GESTIÓN DE FATIGA PRINCIPAL ---
            $table->time('sleep_at')->nullable();   // Hr que Durmió
            $table->time('wake_up_at')->nullable(); // Hr que Despertó
            $table->string('total_sleep_hours');    // Hrs Dormidas
            $table->string('awake_hours_before');   // Hr Despierto (antes de iniciar)
            $table->string('journey_duration');     // Duración Viaje (la misma de la raíz)
            $table->string('total_active_hours');   // Total Hrs (Despierto + Viaje)S

            // --- 5. COLUMNA DE PASAJEROS Y RELEVOS ---
            /**
             * FORMATO DEL JSON ESPERADO (Con vigencias separadas):
             * [
             * {
             * "Passenger": "1",
             * "name": "Luis Sanchez",
             * "is_relay": false,
             * "role": "passenger"
             * },
             * {
             * "Passenger": "2",
             * "name": "Saul Falcon",
             * "is_relay": true,
             * "role": "second_driver",
             * "alcohol_pct": 0.0,
             * "blood_pressure": "120/80",
             * "takes_medication": "si",
             * "medication_name": "Enalapril",
             * "sleep_at": "22:30",
             * "wake_up_at": "06:00",
             * "total_sleep_hours": "07:30",
             * "awake_hours_before": "04:15",
             * "journey_duration": "05:30",
             * "total_active_hours": "09:45"
             *"state_license_val": "2027-10-15",
             * "federal_license_val": "N/A",
             *"light_course_val": "2026-05-20",
             *"heavy_course_val": "N/A"
             * }
             * ]
             */
            $table->json('passengers')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_units');
    }
};
