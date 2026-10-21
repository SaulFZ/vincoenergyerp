<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_advances', function (Blueprint $table) {
            $table->id();

            $table->string('folio_system')->unique()->comment('Folio del anticipo de gasto, ej: ANT-2607-01');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // NUEVO CAMPO AGREGADO
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete()->comment('Capturista en sesión');

            // Recuerda que en pasos anteriores agregamos approver_id, lo incluyo aquí para que tu migración esté completa
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete()->comment('ID del usuario que aprobó o rechazó');

            $table->date('advance_date')->comment('Fecha de requerimiento del dinero');
            $table->string('advance_reason')->comment('Motivo del anticipo, ej: Operativos, Viaticos, Caja Chica.');
            $table->string('advance_type')->comment('Transferencia, Tarjeta Toka, etc');
            $table->text('description')->comment('Destino o justificacion del dinero');

            $table->decimal('amount', 20, 2)->comment('Monto entregado al empleado');
            $table->decimal('balance', 20, 2)->default(0)->comment('Saldo remanente por comprobar');

            $table->string('status')->default('pendiente')->comment('pendiente, aprobado, rechazado');
            $table->string('settlement_status')->default('no_comprobado')->comment('no_comprobado, comprobando, comprobado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_advances');
    }
};
