@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Carrito de compras</h1>
        <small class="text-muted">
            Creado el {{ $carrito->getFechaCreacion()?->format('d/m/Y H:i') }} · Estado: {{ $carrito->getEstado() }}
        </small>
    </div>

    @if($carrito->estaVacio())
        <div class="alert alert-info">
            Tu carrito está vacío. <a href="{{ route('products.index') }}">Ver el catálogo</a>.
        </div>
    @else
        <table class="table align-middle bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio unitario</th>
                    <th style="width: 160px">Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($carrito->getItems() as $item)
                    <tr>
                        <td>{{ $item->getNombreProducto() }}</td>
                        <td>{{ $item->getProducto()?->getNombreCategoria() ?? 'N/A' }}</td>
                        <td>${{ number_format($item->getPrecioUnitario(), 2) }}</td>
                        <td>
                            <form action="{{ route('cart.update', $item->getProductId()) }}" method="POST"
                                  class="d-flex gap-1">
                                @csrf
                                @method('PUT')
                                <input type="number" name="cantidad" min="1" value="{{ $item->getCantidad() }}"
                                       class="form-control form-control-sm">
                                <button class="btn btn-sm btn-outline-secondary">OK</button>
                            </form>
                        </td>
                        <td>${{ number_format($item->getSubtotal(), 2) }}</td>
                        <td>
                            <form action="{{ route('cart.remove', $item->getProductId()) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Quitar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th colspan="4" class="text-end">Total</th>
                    <th colspan="2">${{ number_format($subtotal, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <form action="{{ route('cart.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">Vaciar carrito</button>
            </form>

            <div class="card" style="min-width: 320px">
                <div class="card-body">
                    <h2 class="h5">Finalizar compra</h2>

                    @if($formasPago->isEmpty())
                        <p class="mb-2 text-muted">Necesitas registrar una forma de pago.</p>
                        <a href="{{ route('payment-methods.create') }}" class="btn btn-primary w-100">
                            Agregar forma de pago
                        </a>
                    @else
                        <form action="{{ route('orders.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Forma de pago</label>
                                <select name="payment_method_id" class="form-select" required>
                                    @foreach($formasPago as $forma)
                                        <option value="{{ $forma->getId() }}">{{ $forma->getEtiqueta() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-success w-100">Generar pedido</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
