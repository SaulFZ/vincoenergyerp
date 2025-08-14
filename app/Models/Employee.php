<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
  use SoftDeletes;

  protected $fillable = [
    "employee_number",
    "photo",
    "hire_date",
    "employment_status",
    "position",
    "job_title",
    "manager",
    "department",
    "first_name",
    "second_name",
    "first_name",
    "first_surname",
    "second_surname",
    "full_name",
    "gender",
    "birth_date",
    "nationality",
    "rfc",
    "unique_population_code",
    "social_security_number",
    "blood_type",
    "phone",
    "email",
    "medical_history",
  ];

  protected $dates = ["deleted_at"];

  public function user()
  {
    return $this->hasOne(\App\Models\Auth\User::class, "employee_id", "id");
  }

}
