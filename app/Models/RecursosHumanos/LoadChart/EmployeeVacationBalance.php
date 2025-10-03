<?php
namespace App\Models\RecursosHumanos\LoadChart;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmployeeVacationBalance extends Model
{
    use HasFactory;

    protected $table = 'employee_vacation_balance';

    protected $fillable = [
        'employee_id',
        'vacation_days_available',
        'rest_days_available',
        'years_of_service',
        'rest_mode',
        'work_rest_cycle_counter', // Mantenemos el campo en fillable para inserción inicial
        'last_activity_date',      // Mantenemos el campo en fillable para inserción inicial
    ];

    protected $casts = [
        'last_activity_date'      => 'date',
        'vacation_days_available' => 'integer',
        'rest_days_available'     => 'integer',
        'years_of_service'        => 'integer',
        'work_rest_cycle_counter' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calcula los días de vacaciones mínimos obligatorios según los años de servicio.
     */
    public static function calculateMandatoryVacationDays(int $years): int
    {
        if ($years < 1) { return 0; }
        if ($years === 1) { return 12; }
        if ($years === 2) { return 14; }
        if ($years === 3) { return 16; }
        if ($years === 4) { return 18; }
        if ($years === 5) { return 20; }
        if ($years >= 6 && $years <= 10) { return 22; }
        if ($years >= 11 && $years <= 15) { return 24; }
        if ($years >= 16 && $years <= 20) { return 26; }
        if ($years >= 21 && $years <= 25) { return 28; }
        if ($years >= 26 && $years <= 30) { return 30; }
        return 30;
    }

    /**
     * Verifica si hoy es el aniversario de ingreso
     */
    public function isAnniversaryToday(): bool
    {
        if (! $this->employee || ! $this->employee->hire_date) {
            return false;
        }

        $hireDate = Carbon::parse($this->employee->hire_date);
        $today    = Carbon::now();

        return $hireDate->format('m-d') === $today->format('m-d');
    }

    /**
     * Obtiene los años de servicio actualizados
     */
    public function getCurrentYearsOfService(): int
    {
        if (! $this->employee || ! $this->employee->hire_date) {
            return $this->years_of_service;
        }

        $hireDate = Carbon::parse($this->employee->hire_date);
        $today    = Carbon::now();

        return $hireDate->diffInYears($today);
    }

}
