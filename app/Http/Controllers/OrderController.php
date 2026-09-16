<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

/**
 * Pedidos del usuario autenticado.
 *
 * El flujo es: carrito -> checkout -> Order::crearDesdeCarrito()
 * -> procesarPedido() (cobra y descuenta stock) -> factura.
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $pedidos = $request->user()->orders()
            ->with('items', 'paymentMethod')
            ->latest('fecha')
            ->get();

        return view('orders.index', compact('pedidos'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->getUserId() === $request->user()->getId(), 403);

        return view('orders.show', [
            'pedido' => $order,
            'factura' => $order->generarFactura(),
        ]);
    }

    /**
     * Crea el pedido a partir del carrito activo y lo procesa.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);

        $usuario = $request->user();
        $carrito = $usuario->carritoActivo();

        if ($carrito->estaVacio()) {
            return back()->with('error', 'Tu carrito está vacío.');
        }

        $formaPago = PaymentMethod::where('id', $datos['payment_method_id'])
            ->where('user_id', $usuario->getId())
            ->first();

        if (! $formaPago) {
            return back()->with('error', 'La forma de pago seleccionada no te pertenece.');
        }

        try {
            $pedido = Order::crearDesdeCarrito($usuario, $carrito, $formaPago);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $pedido->procesarPedido()) {
            $pedido->cancelarPedido();

            return back()->with('error', 'No se pudo procesar el pedido: revisa el stock o la forma de pago.');
        }

        return redirect()->route('orders.show', $pedido)
            ->with('success', 'Pedido procesado correctamente.');
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->getUserId() === $request->user()->getId(), 403);

        if (! $order->cancelarPedido()) {
            return back()->with('error', 'Este pedido ya no se puede cancelar.');
        }

        return back()->with('success', 'Pedido cancelado.');
    }

    /**
     * Cambio de estado del pedido: solo el administrador.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $datos = $request->validate([
            'estado' => ['required', 'in:'.implode(',', Order::ESTADOS)],
        ]);

        if (! $order->actualizarEstado($datos['estado'])) {
            return back()->with('error', 'Estado no válido.');
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    /**
     * Listado de todos los pedidos (administrador).
     */
    public function adminIndex()
    {
        $pedidos = Order::with('user', 'items', 'paymentMethod')->latest('fecha')->get();

        return view('orders.admin', compact('pedidos'));
    }
}
