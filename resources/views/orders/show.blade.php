@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Factura {{ $factura['numero_factura'] }}</h1>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">Volver a mis pedidos</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="h6 text-muted">Cliente</h2>
                    <p class="mb-0">
                        {{ $factura['cliente']['nombre'] }}<br>
                        {{ $factura['cliente']['email'] }}<br>
                        {{ $factura['cliente']['telefono'] }}
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h2 class="h6 text-muted">Pedido</h2>
                    <p class="mb-0">
                        Fecha: {{ $factura['fecha'] }}<br>
                        Estado: <span class="badge bg-secondary">{{ ucfirst($factura['estado']) }}</span><br>
                        Forma de pago: {{ $factura['forma_pago'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <table class="table bg-white">
        <thead class="table-dark">
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura['lineas'] as $linea)
                <tr>
                    <td>{{ $linea['producto'] }}</td>
                    <td>{{ $linea['cantidad'] }}</td>
                    <td>${{ number_format($linea['precio_unitario'], 2) }}</td>
                    <td>${{ number_format($linea['subtotal'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light">
                <th colspan="3" class="text-end">Total</th>
                <th>${{ number_format($factura['total'], 2) }}</th>
            </tr>
        </tfoot>
    </table>

    @if($pedido->sePuedeCancelar())
        <form action="{{ route('orders.cancel', $pedido) }}" method="POST"
              onsubmit="return confirm('¿Cancelar este pedido? Se devolverá el stock.')">
            @csrf
            <button class="btn btn-outline-danger">Cancelar pedido</button>
        </form>
    @endif
</div>
@endsection
