<?php

namespace App\Models\Supply\Procurement;

use Illuminate\Database\Eloquent\Model;

class SupplyContract extends Model
{
    protected $table = 'supply_contracts';

    protected $fillable = [
        'number',
        'description',
        'short_name',
        'status',
    ];
}
