<?php
// Comparacion de dos productos de la MISMA categoria: diferencias de
// precio, de especificaciones y un veredicto de cual es mejor.
//
// Las especificaciones comparables, su direccion (mas es mejor / menos es
// mejor) y su peso se definen por categoria en config/catalogo.php.
// El nucleo (compararDatos) trabaja con arreglos para poder probarse
// sin base de datos; comparar() lo adapta a modelos Product.

namespace App\Services;

use App\Models\Product;
use InvalidArgumentException;

class ProductComparisonService
{
    public function comparar(Product $a, Product $b): array
    {
        $slugA = $a->getCategorySlug();
        $slugB = $b->getCategorySlug();

        if ($slugA === null || $slugA !== $slugB) {
            throw new InvalidArgumentException('Solo se pueden comparar productos de la misma categoría.');
        }

        return $this->compararDatos($slugA, $this->datos($a), $this->datos($b));
    }

    private function datos(Product $p): array
    {
        return [
            'nombre' => $p->getNombre(),
            'marca' => $p->getMarca(),
            'precio' => $p->getPrecioLanzamiento(),
            'lanzamiento' => $p->getLanzamiento(),
            'confianza' => $p->getConfianza(),
            'specs' => $p->getEspecificaciones(),
        ];
    }

    // $a y $b: ['nombre','marca','precio','lanzamiento','confianza','specs'].
    public function compararDatos(string $slug, array $a, array $b): array
    {
        $definiciones = config("catalogo.categorias.$slug.specs", []);

        $filas = [];
        $puntosA = $puntosB = 0.0;
        $ganadasA = $ganadasB = $comparables = 0;

        // Fila de precio (no puntua: se analiza aparte) y de lanzamiento.
        $filas[] = $this->fila('Precio de lanzamiento (USD, no precio actual)', $a['precio'], $b['precio'], 'US$ ', -1, 'n', [], false, '');
        $filas[] = $this->fila('Lanzamiento', $a['lanzamiento'], $b['lanzamiento'], '', 0, 't', [], false, '');

        foreach ($definiciones as $def) {
            [$campo, $etiqueta, $unidad, $direccion, $peso, $tipo] = $def;
            $extra = $def[6] ?? [];
            $puntua = $peso > 0 && $direccion !== 0
                && (empty($extra['misma_marca']) || ($a['marca'] ?? null) === ($b['marca'] ?? null));

            $fila = $this->fila(
                $etiqueta,
                $a['specs'][$campo] ?? null,
                $b['specs'][$campo] ?? null,
                $unidad, $direccion, $tipo, $extra, true, $unidad
            );

            if ($puntua && $fila['comparado']) {
                $comparables++;
                if ($fila['ganador'] === 'a') {
                    $puntosA += $peso;
                    $ganadasA++;
                } elseif ($fila['ganador'] === 'b') {
                    $puntosB += $peso;
                    $ganadasB++;
                }
            }

            $filas[] = $fila;
        }

        return [
            'filas' => $filas,
            'veredicto' => $this->veredicto($a, $b, $puntosA, $puntosB, $ganadasA, $ganadasB, $comparables),
            'avisos' => $this->avisos($a, $b),
            'puntos' => ['a' => $puntosA, 'b' => $puntosB],
        ];
    }

    private function fila(string $etiqueta, mixed $va, mixed $vb, string $prefijoUnidad, int $direccion, string $tipo, array $extra, bool $esSpec, string $unidad): array
    {
        $na = $this->valor($va, $tipo, $extra);
        $nb = $this->valor($vb, $tipo, $extra);
        $comparado = $tipo !== 't' && $direccion !== 0 && $na !== null && $nb !== null;
        $ganador = null;
        $ventaja = null;

        if ($comparado && $na !== $nb) {
            $aGana = $direccion > 0 ? $na > $nb : $na < $nb;
            $ganador = $aGana ? 'a' : 'b';

            if ($tipo === 'n') {
                $mayor = max($na, $nb);
                $menor = min($na, $nb);
                $ventaja = $menor > 0 ? '+'.round(($mayor - $menor) / $menor * 100).' %' : null;
            }
        }

        return [
            'etiqueta' => $etiqueta,
            'a' => $this->mostrar($va, $tipo, $esSpec ? $unidad : $prefijoUnidad, ! $esSpec),
            'b' => $this->mostrar($vb, $tipo, $esSpec ? $unidad : $prefijoUnidad, ! $esSpec),
            'ganador' => $ganador,
            'ventaja' => $ventaja,
            'comparado' => $comparado,
            'igual' => (string) $va === (string) $vb,
        ];
    }

    // Convierte el valor a un numero comparable (o null si no hay dato).
    private function valor(mixed $v, string $tipo, array $extra): ?float
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
            return (float) (int) $v;
        }

        return is_numeric($v) ? (float) $v : null;
    }

    private function mostrar(mixed $v, string $tipo, string $unidad, bool $prefijo): string
    {
        if ($v === null || $v === '') {
            return 'sin dato';
        }

        if ($tipo === 'b') {
            return $v ? 'Sí' : 'No';
        }

        if ($tipo === 'n' && is_numeric($v)) {
            $n = (float) $v;
            $txt = number_format($n, floor($n) == $n ? 0 : 2, ',', '.');
            $txt = str_contains($txt, ',') ? rtrim(rtrim($txt, '0'), ',') : $txt;

            if ($unidad === '') {
                return $txt;
            }

            return $prefijo ? $unidad.$txt : $txt.(in_array($unidad, ['″', '°'], true) ? $unidad : ' '.$unidad);
        }

        return (string) $v;
    }

    private function veredicto(array $a, array $b, float $pa, float $pb, int $ga, int $gb, int $n): array
    {
        $frases = [];
        $ganador = null;

        if ($n === 0) {
            $titulo = 'No hay datos numéricos suficientes para decidir';
        } elseif (abs($pa - $pb) < 1e-9) {
            $titulo = 'Empate técnico';
        } else {
            $ganador = $pa > $pb ? 'a' : 'b';
            $w = $ganador === 'a' ? $a : $b;
            $l = $ganador === 'a' ? $b : $a;
            $titulo = 'Mejor en especificaciones: '.$w['nombre'];
            $frases[] = 'Gana en '.max($ga, $gb)." de {$n} características comparables frente a ".$l['nombre'].'.';
        }

        $precioA = $a['precio'];
        $precioB = $b['precio'];

        if ($precioA && $precioB) {
            if ((float) $precioA === (float) $precioB) {
                $frases[] = 'Mismo precio de lanzamiento.';
            } else {
                $baratoEsA = $precioA < $precioB;
                $barato = $baratoEsA ? $a : $b;
                $caro = $baratoEsA ? $b : $a;
                $bajo = min($precioA, $precioB);
                $alto = max($precioA, $precioB);
                $frases[] = $barato['nombre'].' salió US$ '.round($alto - $bajo).' más barato ('.round(($alto - $bajo) / $bajo * 100).' % menos que '.$caro['nombre'].' en precio de lanzamiento).';

                if ($ganador !== null) {
                    $frases[] = (($ganador === 'a') === $baratoEsA)
                        ? 'Además de ganar en especificaciones, es la opción más económica.'
                        : 'Rinde mejor, pero a mayor precio: depende de si la diferencia compensa.';
                }
            }
        } else {
            $frases[] = 'Falta el precio de lanzamiento de uno o ambos productos: no se puede comparar el costo.';
        }

        return ['titulo' => $titulo, 'ganador' => $ganador, 'ganadas' => max($ga, $gb), 'comparables' => $n, 'frases' => $frases];
    }

    private function avisos(array $a, array $b): array
    {
        $avisos = [];
        $ya = (int) substr((string) ($a['lanzamiento'] ?? ''), 0, 4);
        $yb = (int) substr((string) ($b['lanzamiento'] ?? ''), 0, 4);

        if ($ya && $yb && abs($ya - $yb) >= 2) {
            $avisos[] = "Son de generaciones distintas ({$ya} y {$yb}); la diferencia puede deberse a la antigüedad.";
        }

        if (($a['confianza'] ?? 'alta') !== 'alta' || ($b['confianza'] ?? 'alta') !== 'alta') {
            $avisos[] = 'Algunos datos están marcados con confianza media: verifícalos antes de decidir.';
        }

        $avisos[] = 'La comparación solo usa las especificaciones del catálogo; no mide calidad de cámara, pantalla real, software ni reseñas.';

        return $avisos;
    }
}
