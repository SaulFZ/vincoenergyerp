<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     public function permissions()
{
    return $this->hasOne(\App\Models\Sistemas\UserPermission::class);
}
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'status', // Campo agregado
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Verifica si el usuario está activo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Scope para obtener solo usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para obtener solo usuarios inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
