<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();

            // Identificadores y Datos del Formulario
            $table->string('folio')->unique();
            $table->date('request_date');
            $table->string('creator_name');
            $table->string('area');

            // Estados
            $table->string('approval_status')->default('pending');
            $table->string('journey_status')->default('not_started');

            // Ruta
            $table->string('destination_region');
            $table->string('specific_destination');
            $table->string('origin_address');
            $table->string('destination_address');

            // Tiempos
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('estimated_duration'); // <--- NUEVO: Ejemplo "05:30" (Horas:Minutos)

            // Configuración de Viaje
            $table->boolean('has_stops')->default(false);
            $table->json('planned_stops')->nullable();
            $table->integer('total_units')->default(1);
            $table->string('fleet_type'); // Guardará "Unidad Única" o "Convoy de Unidades"

            // Riesgo (Cálculo del Modal)
            $table->integer('risk_score')->nullable();
            $table->string('risk_level')->nullable();

            // Trazabilidad de Usuarios
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approver_id')->constrained('users');

            $table->timestamps();
            $table->softDeletes(); // Recomendado para ERPs
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};
