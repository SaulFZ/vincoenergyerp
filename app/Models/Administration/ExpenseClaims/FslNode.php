<?php

namespace App\Models\Administration\ExpenseClaims;

use Illuminate\Database\Eloquent\Model;

class FslNode extends Model
{
    protected $table = 'fsl_nodes';

    protected $fillable = [
        'g_id',
        'e_name',
        'c_bin',
        'k_bin',
        'sec_token',
        'start_date',
        'end_date',
        'is_live',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_live' => 'boolean',
    ];
}
