<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Llave foránea para la lista de adyacencia (self-referencing)
            $table->unsignedBigInteger('parent_id')->nullable();

            // Para identificar si es País, Estado o Municipio
            $table->enum('level', ['country', 'state', 'city'])->default('city');

            $table->timestamps();

            // Definición de la relación
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('destinations')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
