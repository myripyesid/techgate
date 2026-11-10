<?php
// Cambia el idioma de la aplicacion y lo guarda en la sesion.

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (in_array($locale, SetLocale::SOPORTADOS, true)) {
            $request->session()->put('locale', $locale);
        }

        return back();
    }
}
