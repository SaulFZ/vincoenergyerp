<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Validación temporal de admin/admin
        if ($request->username === 'admin' && $request->password === 'admin') {
            // Crear una sesión manual
            session(['auth_user' => [
                'name' => 'Administrador',
                'username' => 'admin',
                'role' => 'Administrador'
            ]]);

            return redirect()->intended('home');
        }

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
