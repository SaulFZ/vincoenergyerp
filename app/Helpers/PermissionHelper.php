<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User; // ← ajusta el namespace si es diferente

class PermissionHelper
{
    /**
     * Verifica si el usuario tiene un permiso directo.
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
     * Verifica si un usuario ESPECÍFICO (por ID o instancia) tiene un permiso directo.
     * Útil para verificar permisos de otro usuario sin cambiar el Auth.
     */
    public static function hasDirectPermissionForUser(int|User $user, string $permissionName): bool
    {
        if (is_int($user)) {
            $user = User::find($user);
        }

        if (! $user) {
            return false;
        }

        return $user->directPermissions()
            ->where('name', $permissionName)
            ->exists();
    }

    /**
     * Verifica si el usuario tiene al menos uno de los permisos especificados
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
     */
    public static function getDirectPermissions(): array
    {
        return Auth::check()
            ? Auth::user()->directPermissions()->pluck('name')->toArray()
            : [];
    }
}
