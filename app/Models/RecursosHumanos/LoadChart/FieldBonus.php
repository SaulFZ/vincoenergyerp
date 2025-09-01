<?php

namespace App\Models\RecursosHumanos\LoadChart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_category',
        'bonus_type',
        'amount',
        'currency',
        'bonus_identifier',
    ];
}
