<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type', 50)->nullable();
            $table->string('service_type', 100)->nullable();
            $table->string('service_performed', 200)->nullable();
            $table->string('identifier', 50)->nullable();
            $table->string('service_description', 250)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('MX');
            $table->timestamps(); // Esto agrega las columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
