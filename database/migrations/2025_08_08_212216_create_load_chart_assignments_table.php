<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('load_chart_assignments', function (Blueprint $table) {
            $table->id();

            // ID del empleado que será revisado/aprobado
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade');

            // ID del usuario asignado como revisor
            $table->foreignId('reviewer_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // ID del usuario asignado como aprobador
            $table->foreignId('approver_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_chart_assignments');
    }
};
