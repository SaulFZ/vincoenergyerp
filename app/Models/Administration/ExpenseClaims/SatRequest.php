<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;

class SatRequest extends Model
{
    // Vinculación con la tabla exacta en la base de datos
    protected $table = 'sat_requests';

    // Campos permitidos para inserción masiva (Mass Assignment)
    protected $fillable = [
        'ticket_id',
        'request_date',
        'status'
    ];

    // Casteo de tipos de datos automáticos
    protected function casts(): array
    {
        return [
            'request_date' => 'date',
        ];
    }
}
