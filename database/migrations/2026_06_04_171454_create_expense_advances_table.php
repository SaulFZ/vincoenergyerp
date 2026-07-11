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

            $table->date('advance_date')->comment('Fecha de requerimiento del dinero');
            $table->string('advance_type')->comment('Operativos, Viaticos, Caja Chica');
            $table->text('description')->comment('Destino o justificacion del dinero');

            $table->decimal('amount', 20, 2)->comment('Monto entregado al empleado');
            $table->decimal('balance', 20, 2)->default(0)->comment('Saldo remanente por comprobar');

            $table->string('status')->default('Pendiente')->comment('Pendiente, Aprobado, Entregado, Comprobado, Rechazado');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_advances');
    }
};
