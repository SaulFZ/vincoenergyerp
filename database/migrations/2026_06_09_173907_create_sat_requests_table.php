<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->nullable()->unique()->comment('ID de solicitud devuelto por el SAT');
            $table->date('request_date')->index()->comment('Fecha de la consulta (Hoy)');
            $table->string('status')->comment('pending, completed, failed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_requests');
    }
};
