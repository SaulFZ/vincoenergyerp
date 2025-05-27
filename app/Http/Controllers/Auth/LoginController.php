<?php

namespace App\Http\Controllers\Auth;

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
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Si la solicitud no espera JSON, pero es AJAX, configúrela para esperar JSON
        if ($request->ajax() && !$request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }

        // Verificar si los campos están vacíos
        if (empty($request->username) || empty($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, completa todos los campos'
            ]);
        }

        // Buscar usuario en la base de datos usando comparación exacta
        $user = User::whereRaw('BINARY username = ?', [$request->username])->first();

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

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('splash')
                    ]);
                }

                return redirect()->intended('home');
            }
        }

        // Si no se encuentra el usuario o la contraseña es incorrecta
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales proporcionadas no son correctas.'
            ]);
        }

        return back()->with('error', 'Las credenciales proporcionadas no son correctas.');
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
