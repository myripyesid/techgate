<?php
// Indice de rendimiento ESTIMADO (0-100) derivado de las especificaciones
// ponderadas de config/catalogo.php.
//
// IMPORTANTE: no es un benchmark real (no mide FPS, puntajes sinteticos ni
// pruebas de laboratorio). Es un indice relativo que normaliza cada
// especificacion comparable de una categoria contra el resto de productos de
// esa misma categoria y los combina segun el peso y la direccion definidos en
// el catalogo. Sirve para comparar de forma objetiva dos productos del mismo
// tipo y alimentar los graficos del comparador.

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class PerformanceScoreService
{
    // Cache en memoria de los rangos (min/max) por categoria, para no
    // recalcularlos en cada llamada dentro de una misma peticion.
    private array $rangos = [];

    // Devuelve el indice 0-100 de un producto dentro de su categoria.
    public function indice(Product $producto): ?float
    {
        $slug = $producto->getCategorySlug();

        if ($slug === null) {
            return null;
        }

        $rangos = $this->rangosCategoria($slug);
        $desglose = $this->desglosePorSpec($slug, $producto->getEspecificaciones(), $rangos);

        return $this->combinar($desglose);
    }

    // Desglose por caracteristica: cada spec ponderada con su puntaje 0-100.
    // Util para el grafico de barras y el radar.
    //
    // @return array<int, array{campo:string, etiqueta:string, peso:float, puntaje:?float}>
    public function desglose(Product $producto): array
    {
        $slug = $producto->getCategorySlug();

        if ($slug === null) {
            return [];
        }

        return $this->desglosePorSpec(
            $slug,
            $producto->getEspecificaciones(),
            $this->rangosCategoria($slug)
        );
    }

    // Compara el indice de dos productos de la misma categoria y devuelve un
    // resumen listo para la vista (indices, ganador y desglose alineado para
    // el radar: mismas etiquetas en el mismo orden).
    public function comparar(Product $a, Product $b): array
    {
        $slug = $a->getCategorySlug();
        $rangos = $this->rangosCategoria($slug);

        $desgloseA = $this->desglosePorSpec($slug, $a->getEspecificaciones(), $rangos);
        $desgloseB = $this->desglosePorSpec($slug, $b->getEspecificaciones(), $rangos);

        $indiceA = $this->combinar($desgloseA);
        $indiceB = $this->combinar($desgloseB);

        // Ejes del radar: solo specs que puntuan (peso > 0) y que tienen dato
        // en al menos uno de los dos productos.
        $mapaA = collect($desgloseA)->keyBy('campo');
        $mapaB = collect($desgloseB)->keyBy('campo');

        $ejes = [];
        foreach ($desgloseA as $item) {
            if ($item['peso'] <= 0) {
                continue;
            }

            $pa = $mapaA[$item['campo']]['puntaje'] ?? null;
            $pb = $mapaB[$item['campo']]['puntaje'] ?? null;

            if ($pa === null && $pb === null) {
                continue;
            }

            $ejes[] = [
                'etiqueta' => $item['etiqueta'],
                'a' => $pa !== null ? round($pa, 1) : 0,
                'b' => $pb !== null ? round($pb, 1) : 0,
            ];
        }

        return [
            'indice_a' => $indiceA !== null ? round($indiceA, 1) : null,
            'indice_b' => $indiceB !== null ? round($indiceB, 1) : null,
            'ganador' => $this->ganador($indiceA, $indiceB),
            'ejes' => $ejes,
        ];
    }

    private function ganador(?float $a, ?float $b): ?string
    {
        if ($a === null || $b === null || abs($a - $b) < 0.05) {
            return null;
        }

        return $a > $b ? 'a' : 'b';
    }

    // Combina el desglose en un unico indice 0-100 (media ponderada de los
    // puntajes disponibles). Devuelve null si no hay ninguna spec con dato.
    private function combinar(array $desglose): ?float
    {
        $suma = 0.0;
        $pesos = 0.0;

        foreach ($desglose as $item) {
            if ($item['peso'] <= 0 || $item['puntaje'] === null) {
                continue;
            }

            $suma += $item['puntaje'] * $item['peso'];
            $pesos += $item['peso'];
        }

        return $pesos > 0 ? $suma / $pesos : null;
    }

    // Calcula el puntaje 0-100 de cada spec comparable de un producto segun
    // los rangos (min/max) de su categoria y la direccion (mas/menos mejor).
    private function desglosePorSpec(string $slug, array $specs, array $rangos): array
    {
        $definiciones = config("catalogo.categorias.$slug.specs", []);
        $salida = [];

        foreach ($definiciones as $def) {
            [$campo, $etiqueta, , $direccion, $peso, $tipo] = $def;
            $extra = $def[6] ?? [];

            if ($peso <= 0 || $direccion === 0) {
                continue;
            }

            $valor = $this->numero($specs[$campo] ?? null, $tipo, $extra);
            $rango = $rangos[$campo] ?? null;
            $puntaje = null;

            if ($valor !== null && $rango !== null) {
                $puntaje = $this->normalizar($valor, $rango['min'], $rango['max'], $direccion);
            }

            $salida[] = [
                'campo' => $campo,
                'etiqueta' => $etiqueta,
                'peso' => (float) $peso,
                'puntaje' => $puntaje,
            ];
        }

        return $salida;
    }

    // Normaliza un valor a 0-100 dentro del rango de su categoria.
    // direccion > 0: mas es mejor; direccion < 0: menos es mejor.
    // Si todos los productos tienen el mismo valor, el puntaje es 100.
    private function normalizar(float $valor, float $min, float $max, int $direccion): float
    {
        if ($max <= $min) {
            return 100.0;
        }

        $proporcion = ($valor - $min) / ($max - $min);
        $proporcion = $direccion > 0 ? $proporcion : 1 - $proporcion;

        return max(0.0, min(100.0, $proporcion * 100));
    }

    // min/max de cada spec comparable dentro de una categoria, usando TODOS
    // los productos de esa categoria como referencia.
    private function rangosCategoria(string $slug): array
    {
        if (isset($this->rangos[$slug])) {
            return $this->rangos[$slug];
        }

        $definiciones = config("catalogo.categorias.$slug.specs", []);

        $productos = Product::whereHas('category', fn ($q) => $q->where('slug', $slug))->get();

        $rangos = [];

        foreach ($definiciones as $def) {
            [$campo, , , $direccion, $peso, $tipo] = $def;
            $extra = $def[6] ?? [];

            if ($peso <= 0 || $direccion === 0) {
                continue;
            }

            $valores = $productos
                ->map(fn (Product $p) => $this->numero($p->getEspecificaciones()[$campo] ?? null, $tipo, $extra))
                ->filter(fn ($v) => $v !== null)
                ->values();

            if ($valores->isNotEmpty()) {
                $rangos[$campo] = ['min' => (float) $valores->min(), 'max' => (float) $valores->max()];
            }
        }

        return $this->rangos[$slug] = $rangos;
    }

    // Convierte un valor de spec a numero comparable, con la misma logica que
    // ProductComparisonService (numerico, booleano y ranking).
    private function numero(mixed $v, string $tipo, array $extra): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }

        if ($tipo === 'r') {
            $orden = null;
            foreach (($extra['mapa'] ?? []) as $clave => $puntaje) {
                if (str_contains((string) $v, (string) $clave)) {
                    $orden = max($orden ?? 0, $puntaje);
                }
            }

            return $orden === null ? null : (float) $orden;
        }

        if ($tipo === 'b') {
            return (float) (int) (bool) $v;
        }

        return is_numeric($v) ? (float) $v : null;
    }
}
