<?php
// Autor: Marbin

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // Register any application services.
public function register(): void
    {
        // Tu enlace anterior (si lo sigues usando en otra parte)
        $this->app->bind(
            \App\Services\Reports\ReportGenerator::class,
            \App\Services\Reports\CsvReportGenerator::class,
        );

        // La nueva línea de Inversión de Dependencias para los reportes
        $this->app->bind(
            \App\Contracts\ReportInterface::class, 
            \App\Services\PdfReportService::class
        );
    }

    // Bootstrap any application services.
    public function boot(): void
    {
        // Icono (imagen SVG de linea) representativo por categoria (slug).
        // Reemplaza los emojis: son dibujos propios, escalables y sin
        // dependencias externas. Compartido con todas las vistas.
        View::share('catIcon', function (?string $slug, int $tam = 48): string {
            $paths = [
                // Procesador
                'cpu' => '<rect x="7" y="7" width="10" height="10" rx="1.5"/><path d="M9 3v2M12 3v2M15 3v2M9 19v2M12 19v2M15 19v2M3 9h2M3 12h2M3 15h2M19 9h2M19 12h2M19 15h2"/>',
                // Tarjeta grafica
                'gpu' => '<rect x="2" y="7" width="20" height="11" rx="2"/><circle cx="8" cy="12.5" r="2.5"/><circle cx="15" cy="12.5" r="2.5"/><path d="M6 18v3"/>',
                // Placa / chipset
                'chipset' => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6" rx="1"/><path d="M12 4v2M12 18v2M4 12h2M18 12h2"/>',
                // Memoria RAM
                'ram' => '<rect x="2" y="8" width="20" height="9" rx="1"/><path d="M6 8v-2M10 8v-2M14 8v-2M18 8v-2M5 17v2M19 17v2"/><path d="M7 11v3M12 11v3M17 11v3"/>',
                // Almacenamiento (SSD/disco)
                'almacenamiento' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M12 5v2"/>',
                // Fuente de poder
                'fuente' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="9" cy="12" r="2.5"/><path d="M15 10h3M15 13h3"/>',
                // Refrigeracion (ventilador/cooler)
                'refrigeracion' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2"/><path d="M12 10c0-4 -1-6 -3-6s-1 4 3 6M14 12c4 0 6-1 6-3s-4-1-6 3M12 14c0 4 1 6 3 6s1-4-3-6M10 12c-4 0-6 1-6 3s4 1 6-3"/>',
                // Gabinete
                'gabinete' => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="7" r="1"/><path d="M9 11h6M9 14h6M9 17h3"/>',
                // Celular
                'celular' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
                // Televisor
                'televisor' => '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8M12 17v4"/>',
                // Consola (mando)
                'consola' => '<rect x="2" y="7" width="20" height="10" rx="5"/><path d="M7 11v2M6 12h2"/><circle cx="16" cy="11" r="1"/><circle cx="18" cy="13" r="1"/>',
                // Ventilador de hogar
                'ventilador' => '<circle cx="12" cy="10" r="7"/><circle cx="12" cy="10" r="1.5"/><path d="M12 21v-4M9 21h6"/>',
                // Nevera
                'nevera' => '<rect x="6" y="2" width="12" height="20" rx="2"/><path d="M6 10h12M9 5v2M9 13v3"/>',
                // Microondas
                'microondas' => '<rect x="2" y="5" width="20" height="14" rx="2"/><rect x="5" y="8" width="9" height="8" rx="1"/><path d="M17 9v2M17 14v1"/>',
            ];
            $d = $paths[$slug] ?? '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18"/>';

            return '<svg class="tg-ico" width="'.$tam.'" height="'.$tam.'" viewBox="0 0 24 24" '
                .'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" '
                .'stroke-linejoin="round" aria-hidden="true">'.$d.'</svg>';
        });

        // Datos compartidos por el layout: categorias para la barra de
        // navegacion, resumen del carrito activo y el clima de Medellin
        // (servicio de tercero) que se muestra en la cabecera.
        View::composer('layouts.app', function ($view) {
            $usuario = auth()->user();
            $carrito = null;

            if ($usuario && $usuario->esUsuario()) {
                $carrito = $usuario->carts()->where('estado', 'activo')->with('items')->first();
            }

            $view->with([
                'navCategories' => Category::orderBy('nombre')->get(),
                'navCartCount' => $carrito ? $carrito->items->sum('cantidad') : 0,
                'navCartTotal' => $carrito && method_exists($carrito, 'calcularSubtotal')
                    ? call_user_func([$carrito, 'calcularSubtotal'])
                    : 0,
                'navClima' => app(\App\Services\WeatherService::class)->medellin(),
            ]);
        });
    }
}
