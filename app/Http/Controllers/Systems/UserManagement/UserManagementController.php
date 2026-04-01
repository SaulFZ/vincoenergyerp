<?php

namespace App\Http\Controllers\Systems\UserManagement; // ✅ Cambiado de Sistemas a Systems

use App\Http\Controllers\Controller;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Employee;
use App\Models\Systems\UserManagement\UserPermission; // ✅ RUTA NUEVAuse Illuminate\Http\Request;
use Illuminate\Http\Request; // 🚨 ESTA ES LA LÍNEA QUE FALTABA Y CAUSABA EL ERROR
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// ✅ El nombre perfecto para este controlador
class UserManagementController extends Controller
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

        // ✅ Apuntando a la nueva estructura de carpetas en inglés
        return view(
            'modules.systems.user-management.index',
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

        // ✅ Apuntando a la nueva estructura
        return view(
            'modules.systems.user-management.index',
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
            'photo'                => 'nullable|string',
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
            if ($user->employee_id && isset($validated['photo'])) {
                $employee = Employee::find($user->employee_id);
                if ($employee) {
                    $photoPath = $this->processPhoto($validated['photo'], $employee->employee_number, $employee->photo);
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

        // ✅ Apuntando a la nueva estructura
        return view(
            'modules.systems.user-management.index',
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
            'photo'                => 'nullable|string',
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

            $employee = null;
            if ($user->employee_id) {
                $employee = Employee::find($user->employee_id);
                if ($employee && isset($validatedData['photo'])) {
                    $photoPath = $this->processPhoto($validatedData['photo'], $employee->employee_number, $employee->photo);

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
            UserPermission::where('user_id', $id)->delete();
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
     * Método para procesar la foto y guardarla en storage
     */
    private function processPhoto($base64Photo, $employeeNumber, $currentPhoto = null)
    {
        if (empty($base64Photo)) {
            $this->deleteOldPhoto($currentPhoto);
            return null;
        }

        if (strpos($base64Photo, 'base64') !== false) {
            $this->deleteOldPhoto($currentPhoto);

            try {
                preg_match('/^data:image\/(\w+);base64,/', $base64Photo, $matches);
                $extension = strtolower($matches[1] ?? 'png');

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $extension = 'png';
                }

                $imageContent = substr($base64Photo, strpos($base64Photo, ',') + 1);
                $imageData = base64_decode($imageContent);

                $safeEmployeeNumber = Str::slug($employeeNumber);
                $fileName  = $safeEmployeeNumber . '_' . time() . '.' . $extension;
                $path      = 'rh/employees/photos/' . $fileName;

                Storage::disk('public')->put($path, $imageData);

                return $path;
            } catch (\Exception $e) {
                \Log::error("Error al procesar foto en UserManagementController: " . $e->getMessage());
                return $currentPhoto;
            }
        }

        return $currentPhoto;
    }

    /**
     * Método auxiliar para eliminar la foto
     */
    private function deleteOldPhoto($photoPath)
    {
        if (!$photoPath) return;

        if (str_starts_with($photoPath, 'assets/')) {
            $oldPath = public_path($photoPath);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        } else {
            if (Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
        }
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
