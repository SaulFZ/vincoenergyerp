<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Metadatos de Identificación
            $table->string('folio')->unique()->comment('Folio completo Ej: SIS-001');
            $table->string('department_code', 10)->comment('Código del área para filtros y conteo Ej: SIS');

            // Relaciones de Usuarios
            $table->foreignId('user_id')->constrained()->comment('Usuario que solicita el ticket');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->comment('Técnico TI dueño actual/final del ticket');

            // Datos del Reporte
            $table->string('subject')->comment('Asunto breve');
            $table->text('description')->comment('Descripción detallada de la falla');

            // Clasificación (Usamos string en lugar de ENUM para mayor compatibilidad)
            $table->string('priority', 20)->default('media')->comment('alta, media, baja');
            $table->string('status', 30)->default('nuevo')->comment('nuevo, abierto, en-espera, por-concluir, realizado, cancelado');

            // CRÍTICO: Hilo de Correo
            $table->string('email_message_id')->nullable()->comment('Guarda el Message-ID original para que las respuestas sigan el mismo hilo');

            $table->timestamps(); // Genera automatically created_at y updated_at
            $table->softDeletes(); // Papelera de reciclaje lógica
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
