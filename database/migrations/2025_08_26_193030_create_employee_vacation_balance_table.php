<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_vacation_balance', function (Blueprint $table) {
            $table->id();

            // Relación con la tabla de empleados
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // Saldo de días de vacaciones disponibles
            $table->integer('vacation_days_available')->default(0)->comment('Available vacation days');

            // Saldo de días de descanso disponibles
            $table->integer('rest_days_available')->default(0)->comment('Available rest days');

            // --- Modalidad de días de descanso ---
            $table->string('rest_mode', 10)->default('5x2')->comment('Work/rest day pattern (e.g., 5x2, 6x1, 28x7)');

            // Campo agregado para el conteo de años de servicio
            $table->integer('years_of_service')->default(0)->comment('Current years of service for vacation calculation');

            // --- NUEVOS CAMPOS ---
            // Contador del ciclo trabajo/descanso
            $table->unsignedSmallInteger('work_rest_cycle_counter')->default(0)->after('rest_mode');

            // Última fecha de actividad aprobada
            $table->date('last_activity_date')->nullable()->after('work_rest_cycle_counter');

            $table->timestamps();

            // Asegura que cada empleado tenga solo un registro de balance.
            $table->unique('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_vacation_balance');
    }
};
