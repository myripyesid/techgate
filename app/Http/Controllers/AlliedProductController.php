<?php
// Consume el SERVICIO WEB del equipo precedente y muestra sus productos.
//
// La URL del servicio aliado se configura en services.allied_products_url
// (variable de entorno ALLIED_PRODUCTS_URL). Mientras no haya un equipo
// aliado configurado, se consume el propio servicio /api/productos como
// demostracion del flujo de consumo (misma estructura JSON).

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class AlliedProductController extends Controller
{
    public function index()
    {
        $url = config('services.allied_products_url');

        // Caso real (entregable #2): hay un equipo aliado configurado -> se
        // consume su servicio web por HTTP.
        if ($url) {
            return $this->desdeServicio($url);
        }

        // Demostracion sin aliado configurado: se consume el propio servicio
        // /api/productos reutilizando su controlador (misma estructura JSON),
        // sin depender de una llamada de red a si mismo.
        $json = app(\App\Http\Controllers\Api\ProductApiController::class)->index()->getData(true);

        return view('allied.index', [
            'productos' => $json['productos'] ?? [],
            'disponible' => true,
            'fuente' => route('api.products').' (demostración: servicio propio)',
        ]);
    }

    // Consume por HTTP el servicio JSON de otro equipo (equipo anterior:
    // grupo 3, "Tienda de Joyas"). Normaliza la respuesta para tolerar
    // distintas estructuras de JSON (nombres de campo en es/en).
    private function desdeServicio(string $url)
    {
        $productos = [];
        $disponible = true;

        try {
            $resp = Http::timeout(5)->acceptJson()->get($url);

            if ($resp->ok()) {
                $datos = $resp->json();
                // La lista puede venir bajo distintas claves o ser un arreglo plano.
                $lista = $datos['productos']
                    ?? $datos['products']
                    ?? $datos['data']
                    ?? $datos['items']
                    ?? (is_array($datos) ? $datos : []);

                $productos = array_map([$this, 'normalizar'], array_values((array) $lista));
            } else {
                $disponible = false;
            }
        } catch (\Throwable $e) {
            $disponible = false;
        }

        return view('allied.index', [
            'productos' => $productos,
            'disponible' => $disponible,
            'fuente' => $url,
        ]);
    }

    // Mapea un item del servicio aliado a la estructura que espera la vista,
    // aceptando variantes comunes de nombres de campo (es / en).
    private function normalizar(mixed $item): array
    {
        if (! is_array($item)) {
            return ['nombre' => (string) $item, 'marca' => null, 'categoria' => null, 'precio' => null, 'stock' => null, 'url' => null];
        }

        $buscar = fn (array $claves) => collect($claves)
            ->map(fn ($k) => $item[$k] ?? null)
            ->first(fn ($v) => $v !== null && $v !== '');

        return [
            'nombre' => $buscar(['nombre', 'name', 'title', 'titulo', 'producto']) ?? '—',
            'marca' => $buscar(['marca', 'brand', 'fabricante']),
            'categoria' => $buscar(['categoria', 'category', 'tipo', 'type']),
            'precio' => $buscar(['precio', 'price', 'valor', 'cost', 'costo']),
            'stock' => $buscar(['stock', 'cantidad', 'quantity', 'existencias']),
            'url' => $buscar(['url', 'link', 'enlace', 'href']),
        ];
    }
}
