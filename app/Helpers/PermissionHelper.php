<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Verifica si el usuario tiene un permiso directo.
     * @param string $permissionName (ej: 'aprobar_loadchart')
     * @return bool
     */
    public static function hasDirectPermission(string $permissionName): bool
    {
        return Auth::check() &&
               Auth::user()
                   ->directPermissions()
                   ->where('name', $permissionName)
                   ->exists();
    }

    /**
     * Obtiene todos los permisos directos del usuario (nombres).
     * @return array
     */
    public static function getDirectPermissions(): array
    {
        return Auth::check()
            ? Auth::user()->directPermissions()->pluck('name')->toArray()
            : [];
    }
}
