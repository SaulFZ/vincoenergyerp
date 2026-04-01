<?php

namespace App\Http\Middleware;

use App\Models\Systems\UserManagement\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class CheckModulePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $system
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(
        Request $request,
        Closure $next,
        $system,
        $permission = null
    ) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        if ($permission === null) {
            if (!UserPermission::hasModuleAccess($userId, $system)) {
                return $this->denyAccess($request, 'system');
            }
        } else {
            if (!UserPermission::hasPermission($userId, $system, $permission)) {
                return $this->denyAccess($request, 'permission');
            }
        }

        return $next($request);
    }

    private function denyAccess(Request $request, $type)
    {
        if ($request->expectsJson()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'No tienes permisos para acceder.',
                ],
                403
            );
        }

        // Agregar mensaje flash para SweetAlert
        $message =
            $type === 'system'
                ? 'No tienes acceso a este módulo del sistema.'
                : 'No tienes los permisos necesarios para realizar esta acción.';

        session()->flash('swal', [
            'icon' => 'error',
            'title' => 'Error de permisos',
            'text' => $message,
            'timer' => 3000,
        ]);

        return redirect()->route('home');
    }
}
