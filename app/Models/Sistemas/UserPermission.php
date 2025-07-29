<?php

namespace App\Models\Sistemas;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'user_permissions';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'permissions'];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'json',  // Convierte automáticamente JSON a/desde array
    ];

    /**
     * Obtiene el usuario al que pertenece este permiso.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica si un usuario tiene un permiso específico.
     *
     * @param int $userId
     * @param string $system
     * @param string $permission
     * @return bool
     */
    public static function hasPermission($userId, $system, $permission)
    {
        $userPermission = self::where('user_id', $userId)->first();

        if (!$userPermission) {
            return false;
        }

        $permissions = $userPermission->permissions;

        // Verifica si el módulo existe y si contiene el permiso específico
        return isset($permissions[$system]) &&
            (in_array($permission, $permissions[$system]) ||
                empty($permissions[$system]));
    }

    /**
     * Verifica si un usuario tiene acceso a un módulo (si el módulo está presente en sus permisos)
     *
     * @param int $userId
     * @param string $module
     * @return bool
     */
    public static function hasModuleAccess($userId, $system)
    {
        $userPermission = self::where('user_id', $userId)->first();

        if (!$userPermission) {
            return false;
        }

        $permissions = $userPermission->permissions;

        // Si el módulo existe en los permisos, entonces el usuario tiene acceso
        return isset($permissions[$system]);
    }

    /**
     * Obtiene todos los permisos de un usuario.
     *
     * @param int $userId
     * @return array
     */
    public static function getUserPermissions($userId)
    {
        $userPermission = self::where('user_id', $userId)->first();

        if (!$userPermission) {
            return [];
        }

        return $userPermission->permissions;
    }

    /**
     * Actualiza los permisos de un usuario.
     *
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public static function updatePermissions($userId, $permissions)
    {
        return self::updateOrCreate(
            ['user_id' => $userId],
            ['permissions' => $permissions]
        );
    }
}
