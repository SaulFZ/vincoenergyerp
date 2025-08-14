<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('squads', function (Blueprint $table) {
            $table->id();
            $table->integer('squad_number'); // El valor numérico (1, 2, 3...)
            $table->string('squad_name'); // El valor completo ("Cuadrilla-01")
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();

            // Esto asegura que cada empleado solo se pueda asignar una vez a cada cuadrilla
            $table->unique(['squad_number', 'employee_id']);
            $table->index('squad_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('squads');
    }
};
