<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Employee; // Add this import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view("auth.login");
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::whereRaw("BINARY username = ?", [$request->username])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Invalid credentials'])
                : back()->with('error', 'Invalid credentials');
        }

        Auth::login($user); // Use Laravel's auth system

        return $request->expectsJson()
            ? response()->json(['success' => true, 'redirect' => route("splash")])
            : redirect()->intended("home");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/login");
    }
}
