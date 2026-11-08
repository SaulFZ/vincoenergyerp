<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            // Beta contiene nombres equivalentes con diferencias de mayúsculas; se conservan por legacy_id.
            $table->string('name')->index();
            $table->string('category')->nullable()->index();
            $table->string('code')->nullable()->index();
            $table->unsignedInteger('validity_months')->nullable();
            $table->boolean('required_for_driving')->default(false);
            $table->boolean('required_for_operation')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();

            // Procedencia de la carga inicial; los registros manuales no requieren legacy_id.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('source', 30)->default('manual')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_courses');
    }
};
