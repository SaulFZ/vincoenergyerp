<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;

class SatRequest extends Model
{
    protected $table = 'sat_requests';

    protected $fillable = [
        'ticket_id',
        'request_date',
        'status',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];
}
