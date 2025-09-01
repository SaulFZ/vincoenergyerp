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
            $table->integer('vacation_days_available')->default(0)->comment('Días de vacaciones disponibles');

            // Saldo de días de descanso disponibles
            $table->integer('rest_days_available')->default(0)->comment('Días de descanso disponibles');

            $table->timestamps();

            // Asegura que cada empleado tenga solo un registro de balance
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
