<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLicense extends Model
{
    // Opcional: Si el nombre de la tabla sigue la convención (plural del modelo), no es necesario,
    // pero es bueno ser explícito.
    protected $table = 'employee_licenses';

    // 1. FILLABLE: Campos que permites llenar masivamente (create/update)
    protected $fillable = [
        'employee_id',
        'light_defensive_course_expires_at',
        'driver_license_expires_at',
        'driver_license_is_permanent',
        'heavy_defensive_course_expires_at',
        'federal_license_expires_at',
    ];

    // 2. CASTS: Conversión automática de tipos de datos
    // Esto es muy útil para las fechas. Al ponerlas como 'date' o 'datetime',
    // Laravel te devuelve objetos Carbon, permitiéndote usar ->format('d/m/Y') en la vista.
    protected $casts = [
        'light_defensive_course_expires_at' => 'date',
        'driver_license_expires_at'         => 'date',
        'heavy_defensive_course_expires_at' => 'date',
        'federal_license_expires_at'        => 'date',
        'driver_license_is_permanent'       => 'boolean',
    ];

    /**
     * Relación Inversa: Una licencia pertenece a un Empleado.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
