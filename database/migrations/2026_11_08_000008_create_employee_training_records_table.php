<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('training_course_id')->constrained('training_courses')->restrictOnDelete();
            $table->date('issue_date')->nullable();
            $table->date('expiration_date')->nullable()->index();
            $table->string('status', 80)->default('Pendiente');
            $table->string('provider')->nullable();
            $table->string('certificate_number')->nullable();
            $table->text('evidence_path')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['employee_id', 'training_course_id']);

            // Procedencia de la carga inicial; los registros manuales no requieren legacy_id.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('source', 30)->default('manual')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_training_records');
    }
};
