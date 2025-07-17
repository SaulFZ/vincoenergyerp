<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         // Creamos la nueva tabla con la estructura modificada
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('permissions'); // Almacenará todos los permisos como JSON
            $table->timestamps();

            // Índice para la búsqueda rápida
            $table->index('user_id');
         });

        // Migración de datos (opcional, depende de si tienes datos para migrar)
        // Esto se debe hacer en un script o comando separado
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_permissions');
    }
}
