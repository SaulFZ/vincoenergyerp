<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();

            // ── IDENTIFICACIÓN ──
            $table->string('code', 50)->unique()->comment('Código contable (Ej. ADM, SUM)');
            $table->string('name')->comment('Nombre del departamento o área');
            $table->text('description')->nullable()->comment('Descripción de la función del CeCo');

            // ── CONTROL DE VIGENCIA ──
            $table->boolean('is_active')->default(true)->comment('Interruptor maestro para habilitar/deshabilitar imputaciones');

            // ── AUDITORÍA ──
            $table->timestamps();
            $table->softDeletes(); // Nunca borramos, solo ocultamos si hubo un error

            // ── ÍNDICES ──
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
