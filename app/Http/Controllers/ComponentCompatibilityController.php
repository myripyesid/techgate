<?php
// Autor: Marbin

namespace App\Http\Controllers;

use App\Models\ComponentCompatibility;
use App\Models\Product;
use Illuminate\Http\Request;

class ComponentCompatibilityController extends Controller
{
    // Muestra un formulario para elegir dos productos y verificar si
    // son compatibles entre si. Si vienen product_a_id y product_b_id
    // en la query string, calcula y muestra el resultado.
    public function index(Request $request)
    {
        $productos = Product::with('category')->orderBy('nombre')->get();

        $productoA = null;
        $productoB = null;
        $esCompatible = null;
        $razon = null;
        $detalle = null;

        $productoAId = $request->query('product_a_id');
        $productoBId = $request->query('product_b_id');

        if ($productoAId && $productoBId) {
            $productoA = Product::find($productoAId);
            $productoB = Product::find($productoBId);

            if ($productoA && $productoB && $productoA->getId() !== $productoB->getId()) {
                $compatibilidad = new ComponentCompatibility();
                $detalle = $compatibilidad->evaluar($productoA, $productoB);
                $esCompatible = $detalle['compatible'];
                $razon = $esCompatible ? null : $detalle['mensaje'];
            }
        }

        return view('compatibility.index', compact(
            'productos', 'productoA', 'productoB', 'esCompatible', 'razon', 'detalle'
        ));
    }
}
