<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;

class SysCfgS extends Model
{
    // Apuntamos a la tabla ofuscada
    protected $table = 's_cfg';

    protected $fillable = [
        'c1',
        'c2',
        'f1_p',
        'f2_p',
        's_k',
        'd_s',
        'd_e',
        'st',
    ];

    protected $casts = [
        'd_s' => 'date',
        'd_e' => 'date',
        'st' => 'boolean',
    ];
}
