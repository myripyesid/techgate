<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Formas de pago del usuario autenticado.
 */
class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        return view('payment_methods.index', [
            'formasPago' => $request->user()->paymentMethods()->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('payment_methods.create', ['tipos' => PaymentMethod::TIPOS]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(PaymentMethod::TIPOS))],
            'numeroTarjeta' => ['nullable', 'string', 'max:25'],
            'titular' => ['nullable', 'string', 'max:255'],
        ]);

        $formaPago = (new PaymentMethod())
            ->setTipo($datos['tipo'])
            ->setNumeroTarjeta($datos['numeroTarjeta'] ?? null)
            ->setTitular($datos['titular'] ?? null)
            ->setUsuario($request->user());

        // validarMetodoPago() de la clase Formas de pago.
        if (! $formaPago->validarMetodoPago()) {
            return back()
                ->withInput()
                ->with('error', 'La forma de pago no es válida. Revisa el número de tarjeta y el titular.');
        }

        $formaPago->save();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Forma de pago registrada.');
    }

    public function destroy(Request $request, PaymentMethod $payment_method)
    {
        abort_unless($payment_method->getUserId() === $request->user()->getId(), 403);

        $payment_method->delete();

        return back()->with('success', 'Forma de pago eliminada.');
    }
}
