<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {

        Schema::create('ves_units', function (Blueprint $table) {

            $table->id();
            // NO. ECONOMICO (Mayúsculas)
            $table->string('economic_number')->unique();
            // "Activa", "En Taller", "Baja Temporal", etc.
            $table->string('status')->default('Activa');
            // ASIGNACION DE UNIDAD
            $table->string('assignment')->nullable();
            // LOCACION
            $table->string('location')->nullable();
            // UNIDAD (Pesada / Ligera)
            $table->string('unit_type')->nullable();
            // TIPO DE VEHICULO
            $table->string('vehicle_type')->nullable();
            // MARCA
            $table->string('brand')->nullable();
            // MODELO (Año)
            $table->string('model_year')->nullable();
            // NUMERO DE SERIE (Mayúsculas)
            $table->string('serial_number')->nullable();
            // NUMERO DE PLACA (Mayúsculas)
            $table->string('plate_number')->nullable();
            // TARJETA DE CIRCULACION A NOMBRE
            $table->string('owner_name')->nullable();
            $table->timestamps();

        });

    }

    public function down()
    {

        Schema::dropIfExists('ves_units');

    }

};
