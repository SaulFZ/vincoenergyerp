<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expense_account_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('ID del empleado titular de la cuenta corriente');

            // Si balance_amount > 0: La empresa le debe dinero al empleado (Saldo a favor del empleado), en otro caso es en contra de la empresa.
            // Si balance_amount < 0: El empleado le debe dinero a la empresa (Saldo en contra / Sobrante retenido), en otro caso es afavor de la empresa.
            $table->decimal('balance_amount', 12, 2)->default(0.00)->comment('Saldo consolidado (+ Favor Empleado | - Deuda a Empresa)');

            $table->timestamp('last_movement_at')->nullable()->comment('Fecha y hora de la última modificación en su ledger');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expense_account_balances');
    }
};
