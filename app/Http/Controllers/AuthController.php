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
        $validated = $request->validate([
                'nombre' => 'required|string|max:255', // Validamos 'nombre'
                'email' => 'required|string|email|max:255|unique:users',
                'telefono' => 'nullable|string',
                'contraseña' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $validated['nombre'], // Asignamos 'nombre' al campo 'name' de la BD
                'email' => $validated['email'],
                'telefono' => $request->telefono,
                'password' => Hash::make($validated['contraseña']),
                'rol' => 'usuario',
            ]);
            
        $user->save();

        Auth::login($user);
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
