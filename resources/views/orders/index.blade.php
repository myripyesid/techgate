@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Mis pedidos</h1>

    <table class="table bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Forma de pago</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->getNumeroFactura() }}</td>
                    <td>{{ $pedido->getFecha()?->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($pedido->getEstado()) }}</span></td>
                    <td>{{ $pedido->getCantidadTotal() }}</td>
                    <td>${{ number_format($pedido->getTotal(), 2) }}</td>
                    <td>{{ $pedido->getFormaPago()?->getEtiqueta() ?? '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('orders.show', $pedido) }}" class="btn btn-sm btn-outline-primary">Ver factura</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Todavía no tienes pedidos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
