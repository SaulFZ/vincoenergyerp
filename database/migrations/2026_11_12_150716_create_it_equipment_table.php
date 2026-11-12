<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('it_equipment', function (Blueprint $table) {
            $table->id();

            // Folio único automático (Ej. VES-LAP-001, VES-RED-002)
            $table->string('codigo', 30)->unique();

            // Categoría y Tipo de equipo
            $table->string('tipo', 40)->index();
            $table->string('subtipo', 120);

            // Datos de identificación del activo
            $table->string('marca', 100);
            $table->string('modelo', 150);

            // Números de serie (individual o por piezas cuando es combo o kit)
            $table->string('serie', 190)->nullable()->index();
            $table->string('serie_teclado', 100)->nullable()->index();
            $table->string('serie_mouse', 100)->nullable()->index();
            $table->string('serie_receptor', 100)->nullable()->index();

            // Los 3 indicadores de control usando string
            $table->string('estado', 40)->default('nuevo')->index();
            $table->string('condicion', 40)->default('excelente')->index();
            $table->string('estatus', 40)->default('disponible')->index();

            // Relación con el catálogo de Áreas de RH (App\Models\RH\OrgManagement\Area)
            $table->foreignId('area_id')
                ->nullable()
                ->constrained('areas')
                ->nullOnDelete();

            // Fechas de control
            $table->date('fecha_alta');
            $table->date('garantia_hasta')->nullable()->index();

            // Características técnicas dinámicas según la categoría
            $table->json('specs')->nullable();

            // Notas e historial breve de TI
            $table->text('observaciones')->nullable();

            // Auditoría de quién dio de alta el equipo
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('it_equipment_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('it_equipment_id')
                ->constrained('it_equipment')
                ->cascadeOnDelete();

            $table->string('tipo', 120);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 150)->nullable();

            // Series del accesorio o componente incluido
            $table->string('serie', 190)->nullable()->index();
            $table->string('serie_teclado', 100)->nullable()->index();
            $table->string('serie_mouse', 100)->nullable()->index();
            $table->string('serie_receptor', 100)->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('it_equipment_components');
        Schema::dropIfExists('it_equipment');
    }
};
