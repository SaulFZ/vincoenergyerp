<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_families', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();

            // Procedencia de la carga inicial; los registros manuales no requieren legacy_id.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('source', 30)->default('manual')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_families');
    }
};
