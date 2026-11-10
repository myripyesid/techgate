<?php
// Aplica el idioma guardado en la sesion en cada peticion. Si no hay ninguno,
// usa el locale por defecto de config/app.php. Idiomas soportados: es, en.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SOPORTADOS = ['es', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (in_array($locale, self::SOPORTADOS, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
