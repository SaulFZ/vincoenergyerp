<?php

namespace App\Http\Controllers\Systems\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Employee;
use App\Models\Systems\UserManagement\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
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
                        'direct_permissions' => $user->directPermissions->map(function ($perm) {
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
            'modules.systems.user-management.index',
            compact('users', 'roles')
        );
    }

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

    public function create()
    {
        $roles = Role::getRolesForSelect();
        return view('modules.systems.user-management.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = validator($request->all(), [
            'name'                 => 'required|string|max:255',
            'username'             => 'required|string|max:255|unique:users',
            'email'                => 'required|email|unique:users',
            'password'             => 'required|string|min:8',
            'status'               => 'required|in:active,inactive',
            'employee_id'          => 'nullable|exists:employees,id',
            'role_id'              => 'required|exists:roles,id',
            'permissions'          => 'nullable|array',
            'direct_permissions'   => 'sometimes|array',
            'direct_permissions.*' => 'exists:permissions,id',
            'photo'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
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
            if ($user->employee_id) {
                $employee = Employee::find($user->employee_id);

                // Guardado de la foto como archivo real (No Base64)
                if ($employee && $request->hasFile('photo')) {
                    $file = $request->file('photo');
                    $this->deleteOldPhoto($employee->photo);

                    $extension = $file->getClientOriginalExtension();
                    $fileName  = Str::slug($employee->employee_number) . '_' . time() . '.' . $extension;
                    $photoPath = $file->storeAs('rh/employees/photos', $fileName, 'public');

                    $employee->photo = $photoPath;
                    $employee->save();
                }
            }

            $formattedPermissions = $this->formatPermissionsForStorage($validated['permissions'] ?? []);
            UserPermission::updatePermissions($user->id, $formattedPermissions);

            if (! empty($validated['direct_permissions'])) {
                $user->directPermissions()->sync($validated['direct_permissions']);
            } else {
                $user->directPermissions()->detach();
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Usuario y permisos guardados correctamente',
                'user'           => $user,
                'employee_photo' => $employee->photo ?? null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $user        = User::with(['employee', 'role'])->findOrFail($id);
        $permissions = UserPermission::getUserPermissions($id);
        $roles       = Role::getRolesForSelect();

        return view('modules.systems.user-management.index', compact('user', 'permissions', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = validator($request->all(), [
            'name'                 => 'required|string|max:255',
            'username'             => 'required|string|max:255|unique:users,username,' . $id,
            'email'                => 'required|string|email|max:255|unique:users,email,' . $id,
            'password'             => 'nullable|string|min:5',
            'status'               => 'required|in:active,inactive',
            'employee_id'          => 'nullable|exists:employees,id',
            'role_id'              => 'required|exists:roles,id',
            'permissions'          => 'nullable|array',
            'direct_permissions'   => 'sometimes|array',
            'direct_permissions.*' => 'exists:permissions,id',
            'photo'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
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

                if ($employee) {
                    if ($request->hasFile('photo')) {
                        $file = $request->file('photo');
                        $this->deleteOldPhoto($employee->photo);

                        $extension = $file->getClientOriginalExtension();
                        $fileName  = Str::slug($employee->employee_number) . '_' . time() . '.' . $extension;
                        $photoPath = $file->storeAs('rh/employees/photos', $fileName, 'public');

                        $employee->photo = $photoPath;
                        $employee->save();
                    } elseif ($request->input('remove_photo') === '1') {
                        $this->deleteOldPhoto($employee->photo);
                        $employee->photo = null;
                        $employee->save();
                    }
                }
            }

            $formattedPermissions = $this->formatPermissionsForStorage($validatedData['permissions'] ?? []);
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
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage(),
            ], 500);
        }
    }

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

    private function deleteOldPhoto($photoPath)
    {
        if (! $photoPath) {
            return;
        }

        if (Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }
    }

    public function getRoles()
    {
        $roles = Role::getRolesForSelect();
        return response()->json([
            'success' => true,
            'roles'   => $roles,
        ]);
    }

public function getEmployeePhoto($path)
    {
        // 🚨 ESTA LÍNEA ES LA QUE QUITA EL "STALLED" 🚨
        // Le dice a Laravel que libere la sesión para que el botón "Guardar" funcione en paralelo
        if (request()->hasSession()) {
            request()->session()->save();
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Imagen no encontrada.');
        }

        $fullPath = Storage::disk('public')->path($path);

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }
}
