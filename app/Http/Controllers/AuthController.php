<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function login(Request $request)
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'contraseña' => ['required', 'string'],
        ], [], [
            'contraseña' => 'contraseña',
        ]);

        // Operacion iniciarSesion(email, contrasena) de la clase Usuario.
        $autenticado = User::iniciarSesion(
            $datos['email'],
            $datos['contraseña'],
            $request->boolean('recordarme')
        );

        if (! $autenticado) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('products.index'))
            ->with('success', 'Bienvenido, '.$request->user()->getNombre().'.');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'telefono' => ['nullable', 'string', 'max:30'],
            'contraseña' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'contraseña' => 'contraseña',
        ]);

        // El registro publico siempre crea usuarios normales.
        // Los administradores se crean por seeder o desde el panel de admin.
        // Se construye con los setters de la clase Usuario.
        $usuario = (new User())
            ->setNombre($datos['nombre'])
            ->setEmail($datos['email'])
            ->setTelefono($datos['telefono'] ?? null)
            ->setContrasena($datos['contraseña'])
            ->setRol(User::ROL_USUARIO);

        $usuario->save();

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('products.index')
            ->with('success', 'Cuenta creada correctamente. ¡Bienvenido a TechGate!');
    }

    public function logout(Request $request)
    {
        // Operacion cerrarSesion() de la clase Usuario.
        $request->user()->cerrarSesion();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada.');
    }
}
