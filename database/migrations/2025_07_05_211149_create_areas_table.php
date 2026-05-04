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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();

            // LA NUEVA COLUMNA: Siglas del área para folios en todo el ERP
            $table->string('code', 10)->unique()->comment('Siglas del área, Ej: SIS, GEO, OPR');

            $table->string('description')->nullable();

            // Relación con el Director/Gerente de Área
            // Nota: Se asume que la tabla 'employees' ya existe
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->foreign('responsible_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');

            // Permite que un área (Geociencias) pertenezca a otra (Operaciones)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('areas') // Referencia a esta misma tabla
                ->nullOnDelete();

            // Estado para saber si el área sigue vigente (Boolean: 1 = Activo, 0 = Inactivo)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes(); // Borrado lógico para proteger la historia
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
