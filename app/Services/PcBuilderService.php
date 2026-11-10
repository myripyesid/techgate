<?php
// Armador de PC virtual ("Arma tu PC").
//
// Define los slots de un equipo (procesador, placa/chipset, RAM, grafica,
// almacenamiento, fuente, refrigeracion, gabinete), evalua la compatibilidad
// entre los componentes elegidos reutilizando CompatibilityService y calcula
// el consumo estimado y el total del armado.

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class PcBuilderService
{
    // Slots del armado: slug de categoria => etiqueta. El orden define el
    // orden en que se muestran en la interfaz.
    public const SLOTS = [
        'cpu' => 'Procesador',
        'chipset' => 'Placa madre (chipset)',
        'ram' => 'Memoria RAM',
        'gpu' => 'Tarjeta gráfica',
        'almacenamiento' => 'Almacenamiento',
        'fuente' => 'Fuente de poder',
        'refrigeracion' => 'Refrigeración',
        'gabinete' => 'Gabinete',
    ];

    // Slots imprescindibles para considerar el armado "completo".
    public const SLOTS_REQUERIDOS = ['cpu', 'chipset', 'ram', 'fuente', 'gabinete'];

    public function __construct(
        private readonly CompatibilityService $compatibilidad,
    ) {}

    // Opciones de producto para cada slot (solo categorias con slug conocido).
    //
    // @return array<string, array{etiqueta:string, requerido:bool, productos:\Illuminate\Support\Collection}>
    public function opciones(): array
    {
        $productos = Product::with('category')
            ->whereHas('category', fn ($q) => $q->whereIn('slug', array_keys(self::SLOTS)))
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn (Product $p) => $p->getCategorySlug());

        $salida = [];
        foreach (self::SLOTS as $slug => $etiqueta) {
            $salida[$slug] = [
                'etiqueta' => $etiqueta,
                'requerido' => in_array($slug, self::SLOTS_REQUERIDOS, true),
                'productos' => $productos[$slug] ?? collect(),
            ];
        }

        return $salida;
    }

    // Evalua un conjunto de productos seleccionados (indexados por slug).
    // Devuelve compatibilidad par a par, consumo, total y un resumen global.
    //
    // @param  array<string, Product>  $seleccion
    public function evaluar(array $seleccion): array
    {
        $seleccion = array_filter($seleccion);
        $pares = $this->pares($seleccion);
        $consumo = $this->consumo($seleccion);

        $hayIncompatible = collect($pares)->contains(fn ($p) => $p['estado'] === 'bad');
        $hayAdvertencia = collect($pares)->contains(fn ($p) => $p['estado'] === 'warn')
            || ($consumo && $consumo['estado'] === 'warn');

        if ($consumo && $consumo['estado'] === 'bad') {
            $hayIncompatible = true;
        }

        $total = collect($seleccion)->sum(fn (Product $p) => $p->esVendible() ? $p->getPrecio() : 0);
        $vendibles = collect($seleccion)->filter(fn (Product $p) => $p->estaDisponible());

        return [
            'pares' => $pares,
            'consumo' => $consumo,
            'total' => (float) $total,
            'estado_global' => $hayIncompatible ? 'bad' : ($hayAdvertencia ? 'warn' : 'ok'),
            'faltantes' => $this->faltantes($seleccion),
            'cantidad_vendible' => $vendibles->count(),
            'cantidad_total' => count($seleccion),
        ];
    }

    // Compatibilidad entre cada par de componentes seleccionados.
    private function pares(array $seleccion): array
    {
        $items = array_values($seleccion);
        $slugs = array_keys($seleccion);
        $resultado = [];

        for ($i = 0; $i < count($items); $i++) {
            for ($j = $i + 1; $j < count($items); $j++) {
                $detalle = $this->compatibilidad->evaluar($items[$i], $items[$j]);

                // Solo mostramos pares con una relacion tecnica real
                // (ocultamos "sin relacion" para no llenar de ruido la tabla).
                if ($detalle['estado'] === 'ninguna') {
                    continue;
                }

                $resultado[] = [
                    'slug_a' => $slugs[$i],
                    'slug_b' => $slugs[$j],
                    'producto_a' => $items[$i]->getNombre(),
                    'producto_b' => $items[$j]->getNombre(),
                    'estado' => $detalle['estado'],
                    'mensaje' => $detalle['mensaje'],
                    'compatible' => $detalle['compatible'],
                ];
            }
        }

        return $resultado;
    }

    // Consumo estimado (cpu + gpu + fuente) reutilizando CompatibilityService.
    private function consumo(array $seleccion): ?array
    {
        return $this->compatibilidad->estimarConsumo(
            ($seleccion['cpu'] ?? null)?->getEspecificaciones(),
            ($seleccion['gpu'] ?? null)?->getEspecificaciones(),
            ($seleccion['fuente'] ?? null)?->getEspecificaciones(),
        );
    }

    // Slots requeridos que aun no se han elegido.
    private function faltantes(array $seleccion): array
    {
        $faltan = [];
        foreach (self::SLOTS_REQUERIDOS as $slug) {
            if (empty($seleccion[$slug])) {
                $faltan[] = self::SLOTS[$slug];
            }
        }

        return $faltan;
    }

    // Carga los productos seleccionados desde un arreglo de ids por slug,
    // validando que cada producto pertenezca a la categoria del slot.
    //
    // @param  array<string, int|string|null>  $ids
    // @return array<string, Product>
    public function cargarSeleccion(array $ids): array
    {
        $seleccion = [];

        foreach (self::SLOTS as $slug => $etiqueta) {
            $id = $ids[$slug] ?? null;

            if (! $id) {
                continue;
            }

            $producto = Product::with('category')->find($id);

            if ($producto && $producto->getCategorySlug() === $slug) {
                $seleccion[$slug] = $producto;
            }
        }

        return $seleccion;
    }
}
