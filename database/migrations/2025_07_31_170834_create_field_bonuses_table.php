<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('field_bonuses', function (Blueprint $table) {
            $table->id();

            $table->string('employee_category', 80)->nullable(false);
            $table->string('bonus_type', 35)->nullable(false);

            // Campo de monto y moneda
            $table->decimal('amount', 12, 2)->nullable(false)
                ->comment('Monto del bono');
            $table->char('currency', 3)->default('MXN')
                ->comment('Moneda (MXN, USD, EUR)');

            // Identificador flexible
            $table->string('bonus_identifier', 15)->nullable(false)
                ->comment('ID del bono (ej: "0.5", "1", "1.5")');

            // --- AÑADIR ESTA LÍNEA ---
            $table->boolean('is_active')->default(true)
                ->comment('Indica si el bono está activo o no');

            $table->timestamps();

            $table->unique(['employee_category', 'bonus_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('field_bonuses');
    }
};
