<?php
namespace App\Models;

use App\Models\RecursosHumanos\LoadChart\EmployeeMonthlyWorkLog;
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

    // Agregamos un Accessor (campo virtual) para obtener el email del destinatario.
    // Esto es crucial si el campo 'email' en la tabla 'employees' es nulo o si prefieres el email del usuario.
    protected $appends = ['recipient_email'];

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
        return $this->hasOne(\App\Models\RecursosHumanos\LoadChart\EmployeeVacationBalance::class);
    }

    public function squads()
    {
        return $this->hasMany(\App\Models\RecursosHumanos\LoadChart\Squad::class, 'employee_id');
    }
    public function license()
{
    // Laravel busca automáticamente 'employee_id' en la tabla 'employee_licenses'
    return $this->hasOne(EmployeeLicense::class);
}
    /**
     * Accessor para obtener el email para el envío del correo.
     * Retorna el email del empleado o el email del usuario asociado.
     * Esto asegura que siempre intentemos enviar a una dirección válida.
     */
    public function getRecipientEmailAttribute()
    {
        // 1. Intentar con el email directo del empleado
        if ($this->email) {
            return $this->email;
        }

        // 2. Si no hay email directo, intentar con el email de la cuenta de usuario asociada
        if ($this->user && $this->user->email) {
            return $this->user->email;
        }

        return null;
    }
}
