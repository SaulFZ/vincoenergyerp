<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fsl_nodes', function (Blueprint $table) {
            $table->id();

            // Datos identificadores ofuscados
            $table->string('g_id', 13)->unique(); // RFC
            $table->string('e_name'); // Razón social

            // Rutas de archivos ofuscadas
            $table->text('c_bin'); // Ruta del .cer
            $table->text('k_bin'); // Ruta del .key

            // Contraseña encriptada
            $table->text('sec_token'); // Passphrase

            // Vigencia y control
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_live')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fsl_nodes');
    }
};
