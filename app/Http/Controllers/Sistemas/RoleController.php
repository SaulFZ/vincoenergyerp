<?php
namespace App\Http\Controllers\Sistemas;

use App\Http\Controllers\Controller;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Employee;
use App\Models\Sistemas\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File; // Necesario para manipular archivos

class RoleController extends Controller
{
    /**
     * Muestra la vista principal de gestión de roles o devuelve datos JSON
     */
    public function index(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            $users = User::with([
                'permissions',
                'employee',
                'role',
                'directPermissions',
            ])
                ->get()
                ->map(function ($user) {
                    return [
                        'id'                 => $user->id,
                        'name'               => $user->name,
                        'username'           => $user->username,
                        'email'              => $user->email,
                        'status'             => $user->status ?? 'inactive',
                        'employee_id'        => $user->employee_id,
                        'employee_name'      => $user->employee
                            ? $user->employee->full_name
                            : null,
                        'employee_photo'     => $user->employee ? $user->employee->photo : null,
                        'role_id'            => $user->role_id,
                        'role_name'          => $user->role ? $user->role->name : null,
                        'permissions'        => $user->permissions,
                        'direct_permissions' => $user->directPermissions->map(function (
                            $perm
                        ) {
                            return [
                                'id'           => $perm->id,
                                'name'         => $perm->name,
                                'display_name' => $perm->display_name,
                            ];
                        }),
                        'created_at'         => $user->created_at,
                        'updated_at'         => $user->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'users'   => $users,
            ]);
        }

        $users = User::with([
            'permissions',
            'employee',
            'role',
            'directPermissions',
        ])->get();
        $roles = Role::getRolesForSelect();

        return view(
            'modulos.sistemas.gestionderoles.index',
            compact('users', 'roles')
        );
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
            ->get(['id', 'full_name', 'photo']);

        return response()->json($employees);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario con permisos
     */
    public function create()
    {
        $roles = Role::getRolesForSelect();
        return view(
            'modulos.sistemas.gestionderoles.index',
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
            'name'                 => 'required|string|max:255',
            'username'             => 'required|string|max:255|unique:users',
            'email'                => 'required|email|unique:users',
            'password'             => 'required|string|min:8',
            'status'               => 'required|in:active,inactive',
            'employee_id'          => 'nullable|exists:employees,id',
            'role_id'              => 'required|exists:roles,id',
            'permissions'          => 'required|array',
            'direct_permissions'   => 'sometimes|array',
            'direct_permissions.*' => 'exists:permissions,id',
            'photo'                => 'nullable|string', // Para la foto en base64
        ])->validate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'        => $validated['name'],
                'username'    => $validated['username'],
                'email'       => $validated['email'],
                'password'    => Hash::make($validated['password']),
                'status'      => $validated['status'],
                'employee_id' => $validated['employee_id'] ?? null,
                'role_id'     => $validated['role_id'],
            ]);

            $employee = null;
            // Manejar la foto al CREAR usuario
            if ($user->employee_id && isset($validated['photo'])) {
                $employee = Employee::find($user->employee_id);
                if ($employee) {
                    $photoPath = $this->processPhoto($validated['photo'], $employee->photo);
                    if ($photoPath) {
                        $employee->photo = $photoPath;
                        $employee->save();
                    }
                }
            }

            $formattedPermissions = $this->formatPermissionsForStorage(
                $validated['permissions']
            );
            UserPermission::updatePermissions($user->id, $formattedPermissions);

            // Sincronizar permisos directos
            if (! empty($validated['direct_permissions'])) {
                $user->directPermissions()->sync($validated['direct_permissions']);
            } else {
                $user->directPermissions()->detach();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario y permisos guardados correctamente',
                'user'    => $user,
                'employee_photo' => $employee->photo ?? null,
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
        $user        = User::with(['employee', 'role'])->findOrFail($id);
        $permissions = UserPermission::getUserPermissions($id);
        $roles       = Role::getRolesForSelect();

        return view(
            'modulos.sistemas.gestionderoles.index',
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
            'name'                 => 'required|string|max:255',
            'username'             => 'required|string|max:255|unique:users,username,' . $id,
            'email'                => 'required|string|email|max:255|unique:users,email,' . $id,
            'password'             => 'nullable|string|min:5',
            'status'               => 'required|in:active,inactive',
            'employee_id'          => 'nullable|exists:employees,id',
            'role_id'              => 'required|exists:roles,id',
            'permissions'          => 'required|array',
            'direct_permissions'   => 'sometimes|array',
            'direct_permissions.*' => 'exists:permissions,id',
            'photo'                => 'nullable|string', // Para la foto en base64
        ])->validate();

        try {
            DB::beginTransaction();

            $user              = User::findOrFail($id);
            $user->name        = $validatedData['name'];
            $user->username    = $validatedData['username'];
            $user->email       = $validatedData['email'];
            $user->status      = $validatedData['status'];
            $user->employee_id = $validatedData['employee_id'] ?? null;
            $user->role_id     = $validatedData['role_id'];

            if (! empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            // Actualizar foto del empleado si existe
            $employee = null;
            if ($user->employee_id) {
                $employee = Employee::find($user->employee_id);
                if ($employee && isset($validatedData['photo'])) {
                    $photoPath = $this->processPhoto($validatedData['photo'], $employee->photo);

                    if (is_null($photoPath) && $validatedData['photo'] === '') {
                         $employee->photo = null;
                    } elseif ($photoPath) {
                        $employee->photo = $photoPath;
                    }
                    $employee->save();
                }
            }

            $formattedPermissions = $this->formatPermissionsForStorage($validatedData['permissions']);
            UserPermission::updatePermissions($id, $formattedPermissions);

            if (isset($validatedData['direct_permissions'])) {
                $user->directPermissions()->sync($validatedData['direct_permissions']);
            } else {
                // Si no se envían permisos directos, desvincular TODOS.
                $user->directPermissions()->detach();
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Usuario y permisos actualizados correctamente',
                'user'           => $user,
                'employee_photo' => $employee->photo ?? null,
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
            if (! empty($modulePermissions) && is_array($modulePermissions)) {
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

    /**
     * Obtiene los permisos disponibles
     */
    public function getPermissions()
    {
        $permissions = Permission::all()->map(function ($permission) {
            return [
                'id'           => $permission->id,
                'name'         => $permission->name,
                'display_name' => $permission->display_name,
                'description'  => $permission->description,
            ];
        });

        return response()->json([
            'success'     => true,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Método para procesar la foto - Incluye la lógica para eliminar la foto
     */
    private function processPhoto($base64Photo, $currentPhoto = null)
    {
        // Caso 1: La foto se ha borrado (el cliente envía una cadena vacía)
        if (empty($base64Photo)) {
            if ($currentPhoto) {
                 // Eliminar físicamente la foto anterior
                $oldPhotoPath = public_path($currentPhoto);
                if (File::exists($oldPhotoPath)) {
                    File::delete($oldPhotoPath);
                }
            }
            return null; // Devolver null para guardar en DB
        }

        // Caso 2: Se sube una nueva foto (base64)
        if (strpos($base64Photo, 'base64') !== false) {

            // Eliminar la foto anterior antes de subir la nueva
            if ($currentPhoto) {
                $oldPhotoPath = public_path($currentPhoto);
                if (File::exists($oldPhotoPath)) {
                    File::delete($oldPhotoPath);
                }
            }

            // Procesar y guardar nueva foto
            try {
                $image     = explode(',', $base64Photo)[1];
                $imageData = base64_decode($image);

                // Asegurar que la carpeta exista
                $dirPath = public_path('assets/img/employees');
                if (!File::exists($dirPath)) {
                    File::makeDirectory($dirPath, 0777, true, true);
                }

                $fileName  = 'employee_' . time() . '.png';
                $path      = 'assets/img/employees/' . $fileName;

                File::put(public_path($path), $imageData);

                return $path;
            } catch (\Exception $e) {
                \Log::error("Error al procesar foto: " . $e->getMessage());
                return $currentPhoto; // Si falla, mantener la foto antigua
            }
        }

        // Caso 3: Es la URL de la foto existente y se mantiene (no hacer nada)
        return $currentPhoto;
    }

    /**
     * Obtiene los roles para el select
     */
    public function getRoles()
    {
        $roles = Role::getRolesForSelect();
        return response()->json([
            'success' => true,
            'roles'   => $roles,
        ]);
    }
}
