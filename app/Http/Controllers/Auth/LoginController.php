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

        // 3. Re-verificar el código para checar expiración (ya que podría haber pasado tiempo desde verifyCode)
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$resetRecord) {
            // Limpiar sesión y redirigir
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
            return redirect()->route('login')->with('error', 'El proceso de restablecimiento es inválido. Por favor, reinicia el proceso.');
        }

        // 4. Verificar que no haya expirado (5 minutos)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
            return redirect()->route('login')->with('error', 'El código ha expirado. Por favor, solicita uno nuevo.');
        }

        // Si todo es válido, se muestra el formulario con los datos
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
        // Lógica sin cambios
        $request->validate(['username' => 'required|string']);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No se encontró un usuario con ese nombre de usuario.'], 404);
        }

        if (empty($user->email)) {
            return response()->json(['success' => false, 'message' => 'Este usuario no tiene un correo electrónico asociado para la recuperación.'], 422);
        }

        // Ocultar parte del correo para privacidad (ej: vi***@vincoenergy.com)
        $emailParts = explode('@', $user->email);
        $localPart = $emailParts[0];
        // Enmascarar la parte local, dejando los primeros dos y el dominio visible.
        $maskedLocalPart = strlen($localPart) > 2
            ? substr($localPart, 0, 2) . str_repeat('*', strlen($localPart) - 2)
            : str_repeat('*', strlen($localPart));

        $maskedEmail = $maskedLocalPart . '@' . $emailParts[1];

        // Se incluye el nombre completo del usuario para el mail
        return response()->json(['success' => true, 'email' => $user->email, 'maskedEmail' => $maskedEmail, 'userName' => $user->name ?? $user->username]);
    }

    /**
     * Envía el código de 6 dígitos para restablecer la contraseña (5 minutos).
     */
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('Intento de enviar código de reseteo a correo no registrado: ' . $request->email);
            return response()->json(['success' => true, 'message' => 'Si el correo existe, se ha enviado un código de 6 dígitos.']);
        }

        // Obtener el nombre de usuario (asumiendo que 'name' es el campo a mostrar)
        $userName = $user->name ?? $user->username;

        // Generar código de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar código en la base de datos con expiración de 5 minutos
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $code,
                'created_at' => Carbon::now()
            ]
        );

        try {
            // Se pasa el nombre de usuario a la clase de correo
            Mail::to($user->email)->send(new PasswordResetMail($code, self::CODE_EXPIRATION_MINUTES, $userName));
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

        // Verificar que no haya expirado (5 minutos)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'El código ha expirado. Solicita uno nuevo.'], 422);
        }

        // --- CAMBIO CLAVE POR SEGURIDAD ---
        // Almacenar el email y el código validado en la sesión
        $request->session()->put(self::SESSION_EMAIL_KEY, $request->email);
        $request->session()->put(self::SESSION_TOKEN_KEY, $request->code);
        // El token en la BD sigue activo por 5 minutos
        // ---------------------------------

        return response()->json(['success' => true, 'message' => 'Código verificado correctamente.', 'redirect' => route('password.resetForm')]);
    }

    /**
     * Actualiza la contraseña del usuario en la base de datos.
     */
    public function resetPassword(Request $request)
    {
        // 1. Validar inputs de nueva contraseña
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

        // 2. Re-verificación final del token antes de cambiar
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        // 3. Checar token y expiración
        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            // Limpiar la sesión al fallar
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);

            return response()->json(['success' => false, 'message' => 'El código es inválido o ha expirado. Por favor, reinicia el proceso.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // 4. Actualizar contraseña y limpiar
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            // Limpiar la sesión al finalizar con éxito
            $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);

            return response()->json([
                'success' => true,
                'message' => '¡Tu contraseña ha sido actualizada con éxito!',
                'redirect' => route('login')
            ]);
        }

        // Limpiar la sesión en caso de error inesperado (aunque el email ya se validó antes)
        $request->session()->forget([self::SESSION_EMAIL_KEY, self::SESSION_TOKEN_KEY]);
        return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado.'], 500);
    }
}
