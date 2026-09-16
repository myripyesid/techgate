<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Login / registro / logout.
 *
 * El rol (usuario o administrador) se guarda en la columna "rol" de la
 * tabla users y determina que secciones puede ver cada quien.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

  /*  public function login(Request $request)
{
    $email = $request->input('email');
    $password = $request->input('password');

    // 1. Buscar usuario manualmente en BD
    $user = \App\Models\User::where('email', $email)->first();

    // 2. Probar Hash::check directo
    $hashCheck = $user ? \Illuminate\Support\Facades\Hash::check($password, $user->password) : false;

    // 3. Probar Auth::attempt directo (sin pasar por User::iniciarSesion)
    $authAttempt = \Illuminate\Support\Facades\Auth::attempt([
        'email' => $email,
        'password' => $password,
    ]);

    dd([
        'email_recibido'      => $email,
        'password_recibida'   => $password,
        'longitud_password'   => strlen($password),
        'usuario_encontrado'  => $user ? $user->only(['id', 'email', 'password']) : null,
        'Hash::check_directo' => $hashCheck,
        'Auth::attempt'       => $authAttempt,
    ]);
}
*/

public function login(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $autenticado = User::iniciarSesion(
        $request->input('email'),
        $request->input('password'),
        $request->boolean('recordarme')
    );

    if (! $autenticado) {
        throw ValidationException::withMessages([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    $request->session()->regenerate();

    return redirect()->intended(route('products.index'))
        ->with('success', 'Bienvenido a TechGate.');
}

    public function showRegister()
    {
        return view('auth.register');
    }

 public function register(Request $request)
{
    $validated = $request->validate([
        'nombre'     => 'required|string|max:255',
        'email'      => 'required|string|email|max:255|unique:users',
        'telefono'   => 'nullable|string',
        'contraseña' => 'required|string|min:8',
    ]);

    $user = User::create([
        'name'     => $validated['nombre'],
        'email'    => $validated['email'],
        'telefono' => $validated['telefono'],
        'password' => Hash::make($validated['contraseña']),
        'rol'      => User::ROL_USUARIO,
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('products.index')
        ->with('success', 'Cuenta creada correctamente. ¡Bienvenido a TechGate!');
}

public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->cerrarSesion();
        } else {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada.');
    }
}
