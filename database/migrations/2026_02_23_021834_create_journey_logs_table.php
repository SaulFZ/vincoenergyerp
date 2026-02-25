<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('journey_logs', function (Blueprint $table) {
            $table->id();

            // Relación con el viaje (Tabla Maestra)
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();

            // Usuario que realiza la acción (Conductor o Admin)
            $table->foreignId('user_id')->constrained('users');

            /**
             * event_type: Control interno para lógica (if/else)
             * 'solicitud_enviada', 'aprobado', 'rechazado', 'inicio_viaje',
             * 'detencion', 'reanudacion', 'relevo', 'fin_viaje'
             */
            $table->string('event_type');

            // title: Lo que sale en negritas en el Timeline (Ej: "Unidad Detenida")
            $table->string('title');

            // description: El detalle (Motivo del select + Nota adicional)
            $table->text('description')->nullable();

            // event_time: La hora real del suceso para comparar con lo planeado
            $table->timestamp('event_time');

            // delay_minutes: Solo se calcula al 'reanudar' marcha
            $table->integer('delay_minutes')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('journey_logs');
    }
};
