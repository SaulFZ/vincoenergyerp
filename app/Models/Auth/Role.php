<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role'; // <- Opcional, si el nombre fuera diferente al modelo

    protected $fillable = [
        'role',
    ];
}
