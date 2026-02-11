<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleUnit extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     */
    protected $table = 'ves_units';

    /**
     * Los atributos que son asignables masivamente.
     */
    protected $fillable = [
        'economic_number',
        'status',          // <--- ¡Disponible/Activa/En Taller/En Viaje!
        'assignment',
        'location',
        'unit_type',
        'vehicle_type',
        'brand',
        'model_year',
        'serial_number',
        'plate_number',
        'owner_name',
        'ownership',       // <--- Nuevo campo agregado
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
