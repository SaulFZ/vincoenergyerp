<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Auth\User;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Show the login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // Buscar usuario en la base de datos
        $user = User::where('username', $request->username)->first();

        // Verificar si existe el usuario
        if ($user) {
            $passwordMatches = false;

            // Intentar primero con una comparación de texto plano
            if ($request->password === $user->password) {
                $passwordMatches = true;
            }
            // Intentar después con Hash::check por si la contraseña está hasheada
            else {
                try {
                    if (Hash::check($request->password, $user->password)) {
                        $passwordMatches = true;
                    }
                } catch (\Exception $e) {
                    // Ignorar errores de formato de hash incompatible
                }
            }

            // Si la contraseña coincide de cualquier manera
            if ($passwordMatches) {
                // Crear una sesión para el usuario de la base de datos
                session(['auth_user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email
                ]]);

                return redirect()->intended('home');
            }
        }

        // Si no se encuentra el usuario o la contraseña es incorrecta
        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas no son correctas.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Limpiar sesión manual
        session()->forget('auth_user');

        // Por si acaso hay una sesión de Laravel Auth
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
