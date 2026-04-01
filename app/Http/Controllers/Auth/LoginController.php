<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\PasswordResetMail;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Tiempo de expiración del token en minutos
    private const CODE_EXPIRATION_MINUTES = 5;
    // Claves de sesión
    private const SESSION_EMAIL_KEY = 'reset_email';
    private const SESSION_TOKEN_KEY = 'reset_code';

    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm()
    {
        return view("auth.login");
    }

    /**
     * Maneja la autenticación del usuario.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::whereRaw("BINARY username = ?", [$request->username])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Credenciales inválidas'], 401)
                : back()->with('error', 'Credenciales inválidas');
        }

        Auth::login($user);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'redirect' => route("splash")])
            : redirect()->intended("home");
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/login");
    }

    // --- MÉTODOS PARA RESTABLECER CONTRASEÑA ---

    /**
     * Muestra el formulario de restablecimiento de contraseña.
     * Lee el email y token de la sesión.
     */
    public function showResetForm(Request $request)
    {
        // 1. Obtener los datos de la sesión
        $email = $request->session()->get(self::SESSION_EMAIL_KEY);
        $token = $request->session()->get(self::SESSION_TOKEN_KEY);

        // 2. Verificar que ambos existan
        if (empty($email) || empty($token)) {
            return redirect()->route('login')->with('error', 'El proceso de restablecimiento no fue iniciado o el enlace es inválido.');
        }

        // 3. Re-verificar el código para checar expiración
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
            return redirect()->route('login')->with('error', 'El proceso de restablecimiento es inválido. Por favor, reinicia el proceso.');
        }

        // 4. Verificar que no haya expirado (5 minutos)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
            return redirect()->route('login')->with('error', 'El código ha expirado. Por favor, solicita uno nuevo.');
        }

        return view('auth.passwords.reset', [
            'email' => $email,
            'token' => $token
        ]);
    }

    /**
     * Obtiene el correo asociado al nombre de usuario.
     */
    public function getUserEmail(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        // Traemos al usuario junto con su relación 'employee' para poder checar el correo personal
        $user = User::with('employee')->where('username', $request->username)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No se encontró un usuario con ese nombre de usuario.'], 404);
        }

        // ✅ LÓGICA DE DETECCIÓN DE CORREO (Corporativo primero, luego personal)
        $targetEmail = !empty($user->email) ? $user->email : ($user->employee->personal_email ?? null);

        if (empty($targetEmail)) {
            return response()->json(['success' => false, 'message' => 'Este usuario no tiene un correo corporativo ni personal asociado para la recuperación.'], 422);
        }

        // Ocultar parte del correo para privacidad (ej: vi***@vincoenergy.com)
        $emailParts = explode('@', $targetEmail);
        $localPart = $emailParts[0];
        $maskedLocalPart = strlen($localPart) > 2
            ? substr($localPart, 0, 2) . str_repeat('*', strlen($localPart) - 2)
            : str_repeat('*', strlen($localPart));

        $maskedEmail = $maskedLocalPart . '@' . $emailParts[1];

        // Devolvemos el correo que se usará (targetEmail)
        return response()->json([
            'success' => true,
            'email' => $targetEmail,
            'maskedEmail' => $maskedEmail,
            'userName' => $user->name ?? $user->username
        ]);
    }

    /**
     * Envía el código de 6 dígitos para restablecer la contraseña (5 minutos).
     */
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // ✅ BUSCAR USUARIO POR CORREO CORPORATIVO O PERSONAL
        $user = User::where('email', $request->email)
                    ->orWhereHas('employee', function ($query) use ($request) {
                        $query->where('personal_email', $request->email);
                    })->first();

        if (!$user) {
            Log::warning('Intento de enviar código de reseteo a correo no registrado: ' . $request->email);
            return response()->json(['success' => true, 'message' => 'Si el correo existe, se ha enviado un código de 6 dígitos.']);
        }

        $userName = $user->name ?? $user->username;
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar código en la BD (usamos el correo validado que nos mandó el front)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => Carbon::now()
            ]
        );

        try {
            Mail::to($request->email)->send(new PasswordResetMail($code, self::CODE_EXPIRATION_MINUTES, $userName));
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de recuperación: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo enviar el correo de recuperación. Inténtalo más tarde.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Se ha enviado un código de 6 dígitos a tu correo.']);
    }

    /**
     * Verifica el código de 6 dígitos y lo guarda en sesión (5 minutos).
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$resetRecord) {
            return response()->json(['success' => false, 'message' => 'El código es inválido.'], 422);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'El código ha expirado. Solicita uno nuevo.'], 422);
        }

        $request->session()->put(self::SESSION_EMAIL_KEY, $request->email);
        $request->session()->put(self::SESSION_TOKEN_KEY, $request->code);

        return response()->json(['success' => true, 'message' => 'Código verificado correctamente.', 'redirect' => route('password.resetForm')]);
    }

    /**
     * Actualiza la contraseña del usuario en la base de datos.
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'code' => 'required|string|size:6',
                'password' => 'required|confirmed|min:8',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
            return response()->json(['success' => false, 'message' => 'El código es inválido o ha expirado. Por favor, reinicia el proceso.'], 422);
        }

        // ✅ BUSCAR USUARIO POR CORREO CORPORATIVO O PERSONAL PARA ACTUALIZAR PASSWORD
        $user = User::where('email', $request->email)
                    ->orWhereHas('employee', function ($query) use ($request) {
                        $query->where('personal_email', $request->email);
                    })->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);

            return response()->json([
                'success' => true,
                'message' => '¡Tu contraseña ha sido actualizada con éxito!',
                'redirect' => route('login')
            ]);
        }

        $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
        return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado.'], 500);
    }
}
