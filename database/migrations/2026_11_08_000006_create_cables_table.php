<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cables', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('cable_type')->nullable();
            $table->string('drum_code')->nullable();
            $table->string('cem_number')->nullable();
            $table->string('line_code')->nullable();
            $table->string('diameter')->nullable();
            $table->string('head_type')->nullable();
            $table->string('collector')->nullable();
            $table->string('total_lines')->nullable();
            $table->string('reel_type')->nullable();
            $table->string('length_text')->nullable();
            $table->decimal('length_meters', 14, 3)->nullable();
            // La relación se asignará con un ID real de VesCore; nunca con el ID de beta.
            $table->foreignId('current_unit_id')->nullable()->constrained('ves_units')->nullOnDelete();
            $table->string('current_unit_label')->nullable();
            $table->string('status', 80)->default('Disponible')->index();
            $table->text('notes')->nullable();

            // Procedencia de la carga inicial; los registros manuales no requieren legacy_id.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('source', 30)->default('manual')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cables');
    }
};
