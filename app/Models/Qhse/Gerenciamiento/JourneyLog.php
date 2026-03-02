<?php

namespace App\Models\Qhse\Gerenciamiento;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourneyLog extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigues la convención, pero es buena práctica)
    protected $table = 'journey_logs';

    // Campos que se pueden llenar masivamente (Mass Assignment)
    protected $fillable = [
        'journey_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'event_time',
        'delay_minutes'
    ];

    // Para que Laravel trate estas columnas como fechas (Carbon) automáticamente
    protected $casts = [
        'event_time' => 'datetime',
    ];

    /**
     * Relación: Un log pertenece a un viaje.
     */
    public function journey()
    {
        return $this->belongsTo(Journey::class, 'journey_id');
    }

    /**
     * Relación: Un log fue registrado por un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
