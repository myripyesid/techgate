<?php
// Servicio web (API JSON) que provee informacion relevante de la aplicacion:
// la lista de productos EN STOCK y a la venta, con el enlace directo a la
// ficha de cada producto. Pensado para ser consumido por otro equipo.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductApiController extends Controller
{
    // GET /api/productos  -> lista de productos en stock en venta.
    public function index()
    {
        $productos = Product::with('category')
            ->vendibles()
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'marca' => $p->getMarca(),
                'categoria' => $p->getNombreCategoria(),
                'precio' => $p->getPrecio(),
                'moneda' => 'USD',
                'stock' => $p->getStock(),
                // Enlace directo a la visualizacion (ficha) del producto.
                'url' => route('products.show', $p->getId()),
            ]);

        return response()->json([
            'tienda' => 'TechGate',
            'generado' => now()->toIso8601String(),
            'total' => $productos->count(),
            'productos' => $productos,
        ]);
    }
}
