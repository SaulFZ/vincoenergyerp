<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create("employees", function (Blueprint $table) {
      // Identificación
      $table->id();
      $table->string("employee_number")->unique();

      // Foto
      $table->string("photo")->nullable();

      // Datos laborales
      $table->date("hire_date");
      $table->string("employment_status")->default("active");
      $table->string("position");
      $table->string("job_title")->nullable();
      $table->string("manager")->nullable();
      $table->string("department");

      // Datos personales
      $table->string("first_name");
      $table->string("second_name")->nullable();
      $table->string("first_surname");
      $table->string("second_surname")->nullable();
      $table->string("full_name");

      $table->string("gender");
      $table->date("birth_date");
      $table->string("nationality");

      // Documentación
      $table->string("rfc")->nullable();
      $table->string("unique_population_code")->nullable();
      $table->string("social_security_number")->nullable();
      $table->string("blood_type")->nullable();

      // Contacto
      $table->string("phone")->nullable();
      $table->string("email")->nullable();

      // Salud
      $table->text("medical_history")->nullable();

      // Timestamps
      $table->timestamps();
      $table->softDeletes();

      // Índices
      $table->index("full_name");
      $table->index("employment_status");
      $table->index("employee_number");
      $table->index("email");
      $table->index(["department", "employment_status"]);
    });
  }

  public function down()
  {
    Schema::dropIfExists("employees");
  }
};
