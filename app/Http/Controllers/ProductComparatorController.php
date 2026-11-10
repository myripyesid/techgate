<?php
// Autor: Marbin

namespace App\Http\Controllers;

use App\Models\ComponentCompatibility;
use App\Models\Product;
use App\Models\ProductComparator;
use App\Services\PerformanceScoreService;
use App\Services\ProductComparisonService;
use InvalidArgumentException;
use Illuminate\Http\Request;

// Comparador de productos. Cada usuario autenticado tiene el suyo.
class ProductComparatorController extends Controller
{
    // Muestra la pagina del comparador: productos agregados, el cuadro
    // comparativo y la verificacion de compatibilidad entre todos los
    // pares de productos agregados.
    public function index(Request $request)
    {
        $comparator = ProductComparator::paraUsuario($request->user());
        $productos = Product::with('category')->orderBy('nombre')->get();
        $cuadro = $comparator->generarCuadroComparativo();
        $compatibilidad = $comparator->verificarArmado();
        $consumo = $comparator->estimarConsumo();

        // Comparacion directa de dos productos de la misma categoria
        // (?product_a_id=..&product_b_id=..).
        $comparacion = null;
        $errorComparacion = null;
        $rendimiento = null;
        $productoA = $request->filled('product_a_id') ? Product::with('category')->find($request->integer('product_a_id')) : null;
        $productoB = $request->filled('product_b_id') ? Product::with('category')->find($request->integer('product_b_id')) : null;

        if ($productoA && $productoB) {
            if ($productoA->getId() === $productoB->getId()) {
                $errorComparacion = 'Selecciona dos productos diferentes.';
            } else {
                try {
                    $comparacion = app(ProductComparisonService::class)->comparar($productoA, $productoB);
                    // Indice de rendimiento estimado (ponderado) para los graficos.
                    $rendimiento = app(PerformanceScoreService::class)->comparar($productoA, $productoB);
                } catch (InvalidArgumentException $e) {
                    $errorComparacion = $e->getMessage();
                }
            }
        }

        return view('comparator.index', compact(
            'productos', 'comparator', 'cuadro', 'compatibilidad', 'consumo',
            'comparacion', 'errorComparacion', 'productoA', 'productoB', 'rendimiento'
        ));
    }

    public function addToComparator(Request $request, Product $product)
    {
        ProductComparator::paraUsuario($request->user())->agregarAlComparador($product);

        return back()->with('success', "\"{$product->nombre}\" agregado al comparador.");
    }

    public function removeFromComparator(Request $request, Product $product)
    {
        ProductComparator::paraUsuario($request->user())->removerDelComparador($product);

        return back()->with('success', "\"{$product->nombre}\" eliminado del comparador.");
    }

    public function clear(Request $request)
    {
        ProductComparator::paraUsuario($request->user())->vaciar();

        return back()->with('success', 'Comparador vaciado.');
    }

    // Version JSON de la verificacion de compatibilidad entre dos productos.
    public function checkCompatibility(Request $request)
    {
        $validated = $request->validate([
            'product_a_id' => 'required|exists:products,id',
            'product_b_id' => 'required|exists:products,id|different:product_a_id',
        ]);

        $productA = Product::findOrFail($validated['product_a_id']);
        $productB = Product::findOrFail($validated['product_b_id']);

        $compatibility = new ComponentCompatibility();
        $esCompatible = $compatibility->evaluarCompatibilidad($productA, $productB);

        $detalle = $compatibility->evaluar($productA, $productB);

        return response()->json([
            'es_compatible' => $esCompatible,
            'estado' => $detalle['estado'],
            'razon' => $esCompatible
                ? ($detalle['estado'] === 'ok' ? 'Son compatibles.' : $detalle['mensaje'])
                : $detalle['mensaje'],
        ]);
    }
}
