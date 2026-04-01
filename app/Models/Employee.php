<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// Importamos los modelos de la nueva ubicación
use App\Models\RH\OrgManagement\Area;
use App\Models\RH\OrgManagement\Department;
use App\Models\RH\LoadChart\EmployeeMonthlyWorkLog;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "employee_number", "photo", "hire_date", "employment_status",
        "position", "job_title", "first_name", "second_name",
        "first_surname", "second_surname", "full_name", "gender",
        "birth_date", "nationality", "rfc", "unique_population_code",
        "social_security_number", "blood_type", "phone", "personal_email",
        "medical_history",
        "area_id", "department_id", "manager_id", // Nuevas relaciones
    ];

    protected $dates = ["deleted_at"];
    protected $appends = ['recipient_email'];

    // --- RELACIONES DE ESTRUCTURA ORGANIZACIONAL ---

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    // --- RELACIONES EXISTENTES ---

    public function user()
    {
        return $this->hasOne(\App\Models\Auth\User::class, "employee_id", "id");
    }

    public function employeeMonthlyWorkLogs()
    {
        return $this->hasMany(EmployeeMonthlyWorkLog::class);
    }

    public function vacationBalance()
    {
        return $this->hasOne(\App\Models\RH\LoadChart\EmployeeVacationBalance::class);
    }

    public function squads()
    {
        return $this->hasMany(\App\Models\RH\LoadChart\Squad::class, 'employee_id');
    }

    public function license()
    {
        return $this->hasOne(EmployeeLicense::class);
    }

    // --- ACCESSORS ---

    public function getRecipientEmailAttribute()
    {
        if ($this->personal_email) {
            return $this->personal_email;
        }

        if ($this->user && $this->user->email) {
            return $this->user->email;
        }

        return null;
    }
}
