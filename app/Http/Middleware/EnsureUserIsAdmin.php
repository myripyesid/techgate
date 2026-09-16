<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta a los usuarios con rol "administrador".
 * Se registra con el alias "admin" en bootstrap/app.php.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! $usuario->esAdministrador()) {
            abort(403, 'Esta seccion es exclusiva del administrador.');
        }

        return $next($request);
    }
}
