<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 80)->nullable();
            $table->string('status', 30)->default('active')->index();

            // Procedencia de la carga inicial; los registros manuales no requieren legacy_id.
            $table->unsignedBigInteger('legacy_id')->nullable()->unique();
            $table->string('source', 30)->default('manual')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
