<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pre_convoy_meetings', function (Blueprint $table) {
            $table->id();

            // Relación directa con la tabla raíz (Un viaje = Una reunión de seguridad)
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();

            // ID del conductor designado como líder (Relacionado a la tabla de usuarios o personal)
            $table->unsignedBigInteger('convoy_leader_id')->nullable();

            // --- CHECKLIST DE SEGURIDAD (Mapeo de tus Radio Buttons) ---
            // Guardamos booleanos: true para 'si', false para 'no'

            // 1. ¿Comprenden puntos de parada?
            $table->boolean('understand_stopping_points')->default(false);

            // 2. ¿Saben qué hacer en caso de ruptura del convoy?
            $table->boolean('know_convoy_break_protocol')->default(false);

            // 3. ¿Verificaron documentación vigente?
            $table->boolean('documents_verified')->default(false);

            // 4. ¿Conscientes de medidas de prevención?
            $table->boolean('accident_prevention_aware')->default(false);

            // 5. ¿Llevan contactos de emergencia / PRE?
            $table->boolean('has_emergency_contacts')->default(false);

            // 6. ¿Compromiso y liderazgo del líder confirmado?
            $table->boolean('leader_commitment_confirmed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_convoy_meetings');
    }
};

