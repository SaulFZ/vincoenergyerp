<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supply_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->text('description');
            $table->string('short_name')->nullable();
            // Lo dejamos como string para que guardes lo que quieras (active, finished, cancelled, etc.)
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }
};
