<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'description'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Formatea el nombre del permiso para mostrarlo de manera legible
     */
    public function getDisplayNameAttribute()
    {
        // Convierte aprovar_loadchart en Aprovar LoadChart
        return ucwords(str_replace('_', ' ', $this->name));
    }

    /**
     * Relación con usuarios que tienen este permiso directamente
     */
    public function users()
{
    return $this->belongsToMany(User::class, 'user_direct_permissions')
                ->withTimestamps();
}
}
