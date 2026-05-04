<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_trackings', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->comment('El técnico o usuario que escribe este comentario/actualización');

            // Datos del Seguimiento
            $table->text('message')->nullable()->comment('El texto de la justificación o seguimiento');
            $table->string('status_after', 30)->nullable()->comment('El estado en el que se dejó el ticket tras este comentario');

            $table->timestamps(); // Fecha y hora exacta de este movimiento
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_trackings');
    }
};
