<?php
namespace App\Http\Controllers\Sistemas;

use App\Http\Controllers\Controller;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Sistemas\UserPermission;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleController extends Controller
{
    /**
     * Muestra la vista principal de gestión de roles o devuelve datos JSON
     */
    public function index(Request $request)
{
    // Si es una petición AJAX, devolver JSON
    if ($request->expectsJson() || $request->ajax()) {
        $users = User::with(['permissions', 'employee', 'role', 'directPermissions'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'status' => $user->status ?? 'inactive',
                    'employee_id' => $user->employee_id,
                    'employee_name' => $user->employee ? $user->employee->full_name : null,
                    'role_id' => $user->role_id,
                    'role_name' => $user->role ? $user->role->name : null,
                    'permissions' => $user->permissions,
                    'direct_permissions' => $user->directPermissions->map(function($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'display_name' => $perm->display_name
                        ];
                    }),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    // Si es una petición normal, devolver la vista
    $users = User::with(['permissions', 'employee', 'role', 'directPermissions'])->get();
    $roles = Role::getRolesForSelect();

    return view('modulos.sistemas.sistemas.gestionderoles.index', compact('users', 'roles'));
}

    /**
     * Busca empleados por nombre completo
     */
    public function searchEmployees(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->where('full_name', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['id', 'full_name']);

        return response()->json($employees);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario con permisos
     */
    public function create()
    {
        $roles = Role::getRolesForSelect();
        return view(
            'modulos.sistemas.sistemas.gestionderoles.index',
            compact('roles')
        );
    }

    /**
     * Almacena un nuevo usuario con sus permisos
     */
    public function store(Request $request)
    {
        $data = $request->json()->all();

        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'status' => 'required|in:active,inactive',
            'employee_id' => 'nullable|exists:employees,id',
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'direct_permissions' => 'sometimes|array',
            'direct_permissions.*' => 'exists:permissions,id',
        ])->validate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'employee_id' => $validated['employee_id'] ?? null,
                'role_id' => $validated['role_id'],
            ]);

            $formattedPermissions = $this->formatPermissionsForStorage(
                $validated['permissions']
            );
            UserPermission::updatePermissions($user->id, $formattedPermissions);

            // Sincronizar permisos directos - ESTA ES LA PARTE CLAVE
            if (!empty($validated['direct_permissions'])) {
                $user->directPermissions()->sync($validated['direct_permissions']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario y permisos guardados correctamente',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error al guardar permisos: ' . $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Muestra el formulario para editar los permisos de un usuario
     */
    public function edit($id)
    {
        $user = User::with(['employee', 'role'])->findOrFail($id);
        $permissions = UserPermission::getUserPermissions($id);
        $roles = Role::getRolesForSelect();

        return view(
            'modulos.sistemas.sistemas.gestionderoles.index',
            compact('user', 'permissions', 'roles')
        );
    }

    /**
     * Actualiza los permisos de un usuario
     */
    public function update(Request $request, $id)
{
    $data = $request->isJson() ? $request->json()->all() : $request->all();

    $validatedData = validator($data, [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username,' . $id,
        'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        'password' => 'nullable|string|min:5',
        'status' => 'required|in:active,inactive',
        'employee_id' => 'nullable|exists:employees,id',
        'role_id' => 'required|exists:roles,id',
        'permissions' => 'required|array',
        'direct_permissions' => 'sometimes|array',
        'direct_permissions.*' => 'exists:permissions,id'
    ])->validate();

    try {
        DB::beginTransaction();

        $user = User::findOrFail($id);
        $user->name = $validatedData['name'];
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->status = $validatedData['status'];
        $user->employee_id = $validatedData['employee_id'] ?? null;
        $user->role_id = $validatedData['role_id'];

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        $formattedPermissions = $this->formatPermissionsForStorage($validatedData['permissions']);
        UserPermission::updatePermissions($id, $formattedPermissions);

        // Sincronizar permisos directos - ESTA ES LA PARTE CLAVE
        if (isset($validatedData['direct_permissions'])) {
            $user->directPermissions()->sync($validatedData['direct_permissions']);
        } else {
            // Si no vienen permisos directos, eliminamos todos
            $user->directPermissions()->detach();
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Usuario y permisos actualizados correctamente',
            'user' => $user
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar el usuario y permisos: ' . $e->getMessage(),
        ], 500);
    }
}
    /**
     * Elimina un usuario y sus permisos
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Eliminar permisos del usuario
            UserPermission::where('user_id', $id)->delete();

            // Eliminar usuario
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error al eliminar el usuario: ' . $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Formatea los permisos para almacenamiento JSON
     */
    private function formatPermissionsForStorage(array $permissions)
    {
        $formattedPermissions = [];

        foreach ($permissions as $module => $modulePermissions) {
            if (!empty($modulePermissions) && is_array($modulePermissions)) {
                $formattedPermissions[$module] = [];

                foreach ($modulePermissions as $permission => $value) {
                    if ($value) {
                        $formattedPermissions[$module][] = $permission;
                    }
                }
            } else {
                $formattedPermissions[$module] = [];
            }
        }

        return $formattedPermissions;
    }

    // Agrega este método al RoleController
    public function getPermissions()
    {
        $permissions = Permission::all()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'description' => $permission->description,
            ];
        });

        return response()->json([
            'success' => true,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Obtiene los roles para el select
     */
    public function getRoles()
    {
        $roles = Role::getRolesForSelect();
        return response()->json([
            'success' => true,
            'roles' => $roles,
        ]);
    }
}
