<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('expense_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Empleado afectado');
            $table->unsignedBigInteger('created_by_id')->comment('Usuario administrador/finanzas que autorizó el movimiento');

            // Relaciones polimórficas explícitas para rastrear el origen exacto del sobrante o faltante
            $table->unsignedBigInteger('expense_advance_id')->nullable()->comment('ID del Anticipo origen del sobrante');
            $table->unsignedBigInteger('expense_claim_id')->nullable()->comment('ID del Reembolso o Comprobación origen del excedente');

            $table->string('folio_system', 30)->unique()->comment('Folio único del movimiento de balance (Ej: BAL-2607-001)');
            $table->string('movement_type')->comment('Abono_Retencion = Retiene sobrante de anticipo | Cargo_Excedente = Reconoce gasto extra a favor del empleado | Ajuste_Manual | Liquidacion_Caja');

            $table->decimal('amount', 12, 2)->comment('Monto absoluto del movimiento');
            $table->decimal('previous_balance', 12, 2)->comment('Saldo en cuenta antes de la transacción');
            $table->decimal('new_balance', 12, 2)->comment('Saldo resultante tras la transacción');

            $table->string('description', 255)->comment('Motivo contable de la retención o abono');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('expense_advance_id')->references('id')->on('expense_advances')->onDelete('set null');
            $table->foreign('expense_claim_id')->references('id')->on('expense_claims')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expense_account_transactions');
    }
};
