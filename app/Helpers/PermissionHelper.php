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
     * Verifica si el usuario tiene al menos uno de los permisos especificados
     * @param array
     * @return bool
     */
    public static function hasAnyPermission(array $permissionNames): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()
            ->directPermissions()
            ->whereIn('name', $permissionNames)
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
