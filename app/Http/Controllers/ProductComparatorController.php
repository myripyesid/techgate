<?php

namespace App\Http\Controllers;

use App\Models\ProductComparator;
use App\Models\Product;
use App\Models\ComponentCompatibility;
use Illuminate\Http\Request;

class ProductComparatorController extends Controller
{
    public function addProduct(Request $request, ProductComparator $comparator)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $comparator->agregarAlComparador($product);

        return response()->json(['message' => 'Producto agregado al comparador.']);
    }

    public function checkCompatibility(Request $request, ComponentCompatibility $compatibility)
    {
        $validated = $request->validate([
            'product_a_id' => 'required|exists:products,id',
            'product_b_id' => 'required|exists:products,id',
        ]);

        $productA = Product::findOrFail($validated['product_a_id']);
        $productB = Product::findOrFail($validated['product_b_id']);

        $esCompatible = $compatibility->evaluarCompatibilidad($productA, $productB);
        $razon = $esCompatible ? 'Son compatibles.' : $compatibility->obtenerRazonIncompatibilidad($productA, $productB);

        return response()->json([
            'es_compatible' => $esCompatible,
            'razon' => $razon
        ]);
    }
}