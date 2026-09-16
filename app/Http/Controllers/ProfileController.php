<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Perfil del usuario: actualizarPerfil() y cambiarContrasena().
 */
class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['usuario' => $request->user()]);
    }

    public function update(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $usuario->actualizarPerfil($datos);

        return back()->with('success', 'Perfil actualizado.');
    }

    public function updatePassword(Request $request)
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'contraseña_actual' => ['required', 'string'],
            'contraseña' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'contraseña_actual' => 'contraseña actual',
            'contraseña' => 'contraseña',
        ]);

        if (! $usuario->verificarContrasena($datos['contraseña_actual'])) {
            throw ValidationException::withMessages([
                'contraseña_actual' => 'La contraseña actual no es correcta.',
            ]);
        }

        $usuario->cambiarContrasena($datos['contraseña']);

        return back()->with('success', 'Contraseña actualizada.');
    }
}
