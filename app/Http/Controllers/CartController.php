<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Carrito de compras. Exclusivo de usuarios autenticados: cada quien
 * opera sobre su propio carrito activo.
 */
class CartController extends Controller
{
    public function index(Request $request)
    {
        $carrito = $request->user()->carritoActivo();
        $carrito->load('items.product.category');

        return view('cart.index', [
            'carrito' => $carrito,
            'subtotal' => $carrito->calcularSubtotal(),
            'formasPago' => $request->user()->paymentMethods()->get(),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $datos = $request->validate([
            'cantidad' => ['nullable', 'integer', 'min:1'],
        ]);

        $cantidad = $datos['cantidad'] ?? 1;
        $carrito = $request->user()->carritoActivo();

        if (! $carrito->agregarProducto($product, $cantidad)) {
            return back()->with('error', "No hay stock suficiente de \"{$product->nombre}\".");
        }

        return back()->with('success', "\"{$product->nombre}\" agregado al carrito.");
    }

    public function update(Request $request, Product $product)
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0'],
        ]);

        $carrito = $request->user()->carritoActivo();

        if (! $carrito->actualizarCantidad($product, $datos['cantidad'])) {
            return back()->with('error', 'No fue posible actualizar la cantidad (revisa el stock).');
        }

        return back()->with('success', 'Carrito actualizado.');
    }

    public function remove(Request $request, Product $product)
    {
        $request->user()->carritoActivo()->removerProducto($product);

        return back()->with('success', "\"{$product->nombre}\" eliminado del carrito.");
    }

    public function clear(Request $request)
    {
        $request->user()->carritoActivo()->vaciarCarrito();

        return back()->with('success', 'Carrito vaciado.');
    }
}
