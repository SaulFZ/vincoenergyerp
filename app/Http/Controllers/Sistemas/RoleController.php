<?php
namespace App\Http\Controllers\Sistemas;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Sistemas\UserPermission;
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
            $users = User::with('permissions')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'status' => $user->status ?? 'inactive',
                    'permissions' => $user->permissions,
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
        $users = User::with('permissions')->get();
        return view('modulos.sistemas.sistemas.gestionderoles.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario con permisos
     */
    public function create()
    {
        return view('modulos.sistemas.sistemas.gestionderoles.index');
    }

    /**
     * Almacena un nuevo usuario con sus permisos
     */
    public function store(Request $request)
    {
        $data = $request->json()->all();

        $validated = validator($data, [
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:255|unique:users',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|string|min:8',
            'status'      => 'required|in:active,inactive',
            'permissions' => 'required|array',
        ])->validate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status'   => $validated['status'],
            ]);

            $formattedPermissions = $this->formatPermissionsForStorage($validated['permissions']);
            UserPermission::updatePermissions($user->id, $formattedPermissions);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario y permisos guardados correctamente',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el formulario para editar los permisos de un usuario
     */
    public function edit($id)
    {
        $user        = User::findOrFail($id);
        $permissions = UserPermission::getUserPermissions($id);

        return view('modulos.sistemas.sistemas.gestionderoles.index', compact('user', 'permissions'));
    }

    /**
     * Actualiza los permisos de un usuario
     */
    public function update(Request $request, $id)
    {
        // Determinar si es una petición JSON o form data
        $data = $request->isJson() ? $request->json()->all() : $request->all();

        $validatedData = validator($data, [
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:255|unique:users,username,' . $id,
            'email'       => 'required|string|email|max:255|unique:users,email,' . $id,
            'password'    => 'nullable|string|min:5',
            'status'      => 'required|in:active,inactive',
            'permissions' => 'required|array',
        ])->validate();

        try {
            DB::beginTransaction();

            $user           = User::findOrFail($id);
            $user->name     = $validatedData['name'];
            $user->username = $validatedData['username'];
            $user->email    = $validatedData['email'];
            $user->status   = $validatedData['status'];

            if (!empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            $formattedPermissions = $this->formatPermissionsForStorage($validatedData['permissions']);
            UserPermission::updatePermissions($id, $formattedPermissions);

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
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage(),
            ], 500);
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
}
