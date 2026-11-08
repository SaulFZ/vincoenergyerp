<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_family_id')->nullable()->constrained('tool_families')->restrictOnDelete();
            $table->string('code')->nullable()->unique();
            $table->string('name')->index();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('status', 80)->default('Disponible')->index();
            $table->date('calibration_due')->nullable();
            $table->text('notes')->nullable();

            // Procedencia de la carga inicial; los registros manuales no requieren legacy_id.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('source', 30)->default('manual')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
