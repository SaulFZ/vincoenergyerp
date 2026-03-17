<?php

namespace App\Models\Operaciones;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Well extends Model
{
    use HasFactory;

    protected $table = 'wells';

    protected $fillable = [
        'name',
        'status',
    ];
}
