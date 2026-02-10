<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_licenses', function (Blueprint $table) {
            $table->id();

            // 1. Relación con la tabla employees (Clave Foránea)
            // Esto conecta con la ID que mostraste en tus INSERTS
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade') // Si se borra el empleado, se borran sus licencias
                  ->unique(); // Una fila de licencias por empleado

            // 2. Curso Manejo Defensivo Unidades Ligeras
            $table->date('light_defensive_course_expires_at')->nullable();

            // 3. Licencia Conductor
            $table->date('driver_license_expires_at')->nullable();
            // Agregamos esto para manejar el texto "Permanente" de la imagen
            $table->boolean('driver_license_is_permanent')->default(false);

            // 4. Curso Manejo Defensivo Unidades Pesadas
            $table->date('heavy_defensive_course_expires_at')->nullable();

            // 5. Licencia Federal
            $table->date('federal_license_expires_at')->nullable();

            // Auditoría
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_licenses');
    }
};
