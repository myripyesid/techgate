@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Pedidos de la tienda</h1>

    <table class="table bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Forma de pago</th>
                <th style="width: 260px">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->getNumeroFactura() }}</td>
                    <td>{{ $pedido->getUsuario()?->getNombre() ?? '-' }}</td>
                    <td>{{ $pedido->getFecha()?->format('d/m/Y H:i') }}</td>
                    <td>${{ number_format($pedido->getTotal(), 2) }}</td>
                    <td>{{ $pedido->getFormaPago()?->getEtiqueta() ?? '-' }}</td>
                    <td>
                        <form action="{{ route('orders.status', $pedido) }}" method="POST" class="d-flex gap-1">
                            @csrf
                            @method('PUT')
                            <select name="estado" class="form-select form-select-sm">
                                @foreach(\App\Models\Order::ESTADOS as $estado)
                                    <option value="{{ $estado }}" @selected($pedido->getEstado() === $estado)>
                                        {{ ucfirst($estado) }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-primary">Actualizar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Todavía no hay pedidos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
