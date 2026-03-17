<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla simplificada de pozos para registro rápido.
     */
    public function up(): void
    {
        Schema::create('wells', function (Blueprint $table) {
            $table->id(); // Primary Key (id_well)

            // Nombre del pozo (ej: 'OGARRIO 1452')
            $table->string('name')->unique();

            // Estatus del pozo en minúsculas
            $table->string('status')->default('active');

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wells');
    }
};
