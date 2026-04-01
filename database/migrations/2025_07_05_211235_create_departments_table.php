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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Relación estricta con el Área Padre
            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')
                  ->references('id')
                  ->on('areas')
                  ->onDelete('restrict');

            // Relación con el Jefe de Departamento
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->foreign('responsible_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('set null');

            // Estado para saber si el depto sigue vigente (Boolean: 1 = Activo, 0 = Inactivo)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes(); // Borrado lógico para proteger la historia
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
