<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            // ---------------------------------------------------------
            // 1. IDENTIFICACIÓN Y FOTO
            // ---------------------------------------------------------
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('photo')->nullable();

            // ---------------------------------------------------------
            // 2. DATOS PERSONALES
            // ---------------------------------------------------------
            $table->string('first_name');
            $table->string('second_name')->nullable();
            $table->string('first_surname');
            $table->string('second_surname')->nullable();
            $table->string('full_name');
            $table->string('gender');
            $table->date('birth_date');
            $table->string('nationality');

            // ---------------------------------------------------------
            // 3. DATOS LABORALES
            // ---------------------------------------------------------
            $table->string('employment_status')->default('active');
            $table->date('hire_date');
            $table->string('position');
            $table->string('job_title')->nullable();

            // ---------------------------------------------------------
            // 4. DATOS DE CONTACTO
            // ---------------------------------------------------------
            $table->string('phone')->nullable();
            $table->string('personal_email')->nullable();

            // ---------------------------------------------------------
            // 5. DOCUMENTACIÓN LEGAL Y SALUD
            // ---------------------------------------------------------
            $table->string('rfc')->nullable();
            $table->string('unique_population_code')->nullable(); // CURP
            $table->string('social_security_number')->nullable(); // NSS
            $table->string('blood_type')->nullable();
            $table->text('medical_history')->nullable();

            // ---------------------------------------------------------
            // 6. ESTRUCTURA ORGANIZACIONAL (LAS NUEVAS RELACIONES)
            // ---------------------------------------------------------
            // Área a la que pertenece (Ej: Operativa, Administrativa)
            // Se define como nullable() temporalmente por si hay registros huérfanos durante la migración de datos.
            $table->foreignId('area_id')
                  ->nullable()
                  ->constrained('areas')
                  ->onDelete('restrict');

            // Departamento al que pertenece (Ej: Geociencias, Recursos Humanos)
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->onDelete('restrict');

            // Jefe Directo (Autorreferencia: un empleado supervisa a otro empleado)
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            // ---------------------------------------------------------
            // 7. AUDITORÍA Y CONTROL (TIMESTAMPS)
            // ---------------------------------------------------------
            $table->timestamps();
            $table->softDeletes(); // Permite "eliminar" registros sin borrarlos físicamente de la BD

            // ---------------------------------------------------------
            // 8. ÍNDICES DE OPTIMIZACIÓN (Para búsquedas rápidas)
            // ---------------------------------------------------------
            $table->index('full_name');
            $table->index('employment_status');
            $table->index('employee_number');
            $table->index('personal_email');

            // Índice compuesto muy útil para dashboards que filtran por área, departamento y estado a la vez
            $table->index(['area_id', 'department_id', 'employment_status'], 'idx_emp_structure_status');
        });
    }

};
