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
     * Muestra la vista principal de gestión de roles
     */
    public function index()
    {
        $users = User::all();
        return view('modulos.sistemas.sistemas.gestionderoles.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario con permisos
     */
    public function create()
    {
        return view('modulos.sistemas.sistemas.gestionderoles.create');
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
            'permissions' => 'required|array',
        ])->validate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Ya tienes este método corregido:
            $formattedPermissions = $this->formatPermissionsForStorage($validated['permissions']);
            UserPermission::updatePermissions($user->id, $formattedPermissions);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Permisos guardados correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al guardar permisos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario para editar los permisos de un usuario
     */
    public function edit($id)
    {
        $user        = User::findOrFail($id);
        $permissions = UserPermission::getUserPermissions($id);

        return view('modulos.sistemas.sistemas.gestionderoles.edit', compact('user', 'permissions'));
    }

    /**
     * Actualiza los permisos de un usuario
     */
    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validatedData = $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:255|unique:users,username,' . $id,
            'email'       => 'required|string|email|max:255|unique:users,email,' . $id,
            'password'    => 'nullable|string|min:8',
            'permissions' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar el usuario
            $user           = User::findOrFail($id);
            $user->name     = $validatedData['name'];
            $user->username = $validatedData['username'];
            $user->email    = $validatedData['email'];

            if (! empty($validatedData['password'])) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            // Procesar los permisos para el formato JSON
            $formattedPermissions = $this->formatPermissionsForStorage($validatedData['permissions']);

            // Actualizar los permisos del usuario
            UserPermission::updatePermissions($id, $formattedPermissions);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario y permisos actualizados correctamente',
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

            // Eliminar permisos del usuario (el modelo se encarga de esto gracias a las restricciones de clave foránea)
            UserPermission::where('user_id', $id)->delete();

            // Eliminar usuario
            User::destroy($id);

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
     * Convierte de formato original:
     * [
     *   'modulo1' => ['permiso1' => true, 'permiso2' => true],
     *   'modulo2' => ['permiso1' => true]
     * ]
     *
     * A formato JSON:
     * [
     *   'modulo1' => ['permiso1', 'permiso2'],
     *   'modulo2' => ['permiso1']
     * ]
     */
    private function formatPermissionsForStorage(array $permissions)
    {
        $formattedPermissions = [];

        foreach ($permissions as $module => $modulePermissions) {
            // Si hay permisos específicos para el módulo, los procesamos
            if (! empty($modulePermissions) && is_array($modulePermissions)) {
                $formattedPermissions[$module] = [];

                foreach ($modulePermissions as $permission => $value) {
                    if ($value) {
                        $formattedPermissions[$module][] = $permission;
                    }
                }
            } else {
                // Si no hay permisos específicos pero el módulo está activado,
                // lo incluimos con un array vacío para indicar acceso general al módulo
                $formattedPermissions[$module] = [];
            }
        }

        return $formattedPermissions;
    }
}
