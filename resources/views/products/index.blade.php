@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Catálogo de Productos</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">Nuevo Producto</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->nombre }}</td>
                    <td>{{ $product->category->nombre }}</td>
                    <td>${{ number_format($product->precio, 2) }}</td>
                    <td>{{ $product->stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection