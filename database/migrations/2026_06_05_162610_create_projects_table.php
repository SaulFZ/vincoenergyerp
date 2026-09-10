<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // ── RELACIÓN FINANCIERA PADRE ──
            $table->foreignId('cost_center_id')
                  ->constrained('cost_centers')
                  ->restrictOnDelete() // Evita borrar un Centro si tiene Proyectos vivos
                  ->comment('El departamento que absorbe el gasto de este proyecto');

            // ── IDENTIFICACIÓN ──
            $table->string('code', 50)->unique()->comment('Código del proyecto (Ej. PRJ-WKSH)');
            $table->string('name')->comment('Nombre del Proyecto o Subcentro');
            $table->text('description')->nullable()->comment('Alcance o justificación del proyecto');

            // ── CRONOGRAMA (Planificación) ──
            $table->date('start_date')->nullable()->comment('Fecha estimada de inicio');
            $table->date('end_date')->nullable()->comment('Fecha estimada de fin (Informativo)');

            // ── CONTROL DURO (El que manda) ──
            $table->string('status', 30)->default('Activo')->comment('Estados: Activo,Cancelado, Pausado, Cerrado');
            $table->boolean('is_active')->default(true)->comment('Si es true, acepta gastos, sin importar si ya pasó el end_date');

            // ── AUDITORÍA ──
            $table->timestamps();
            $table->softDeletes();

            // ── ÍNDICES DE ALTO RENDIMIENTO ──
            $table->index(['cost_center_id', 'is_active']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
