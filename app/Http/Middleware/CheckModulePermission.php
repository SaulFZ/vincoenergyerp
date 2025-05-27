<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Sistemas\UserPermission;

class CheckModulePermission
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string  $module
   * @param  string|null  $permission
   * @return mixed
   */
  public function handle(
    Request $request,
    Closure $next,
    $module,
    $permission = null
  ) {
    // Verificar si el usuario está autenticado
    if (!session()->has("auth_user")) {
      return redirect()->route("login");
    }

    $userId = session("auth_user.id");

    // Si no se especificó un permiso específico, verificamos solo el acceso al módulo
    if ($permission === null || $permission === "general") {
      // Verificar si el usuario tiene acceso al módulo
      if (!UserPermission::hasModuleAccess($userId, $module)) {
        if ($request->ajax()) {
          return response()->json(
            [
              "success" => false,
              "message" => "No tienes permisos para acceder a este módulo.",
            ],
            403
          );
        }

        // Para peticiones no AJAX, redirigimos con parámetros para mostrar SweetAlert2
        return redirect()->route("home", [
          "permission_error" => "true",
          "error_type" => "module",
        ]);
      }
    } else {
      // Verificar si el usuario tiene el permiso específico requerido
      if (!UserPermission::hasPermission($userId, $module, $permission)) {
        if ($request->ajax()) {
          return response()->json(
            [
              "success" => false,
              "message" => "No tienes permisos para realizar esta acción.",
            ],
            403
          );
        }

        // Para peticiones no AJAX, redirigimos con parámetros para mostrar SweetAlert2
        return redirect()->to(
          url()->previous() . "?permission_error=true&error_type=system"
        );
      }
    }

    return $next($request);
  }
}
