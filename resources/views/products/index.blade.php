@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Catálogo de productos</h1>

        @if(auth()->user()->esAdministrador())
            <a href="{{ route('products.create') }}" class="btn btn-primary">Nuevo producto</a>
        @endif
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar producto..."
                   value="{{ request('buscar') }}">
        </div>
        <div class="col-md-4">
            <select name="category_id" class="form-select">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->getId() }}" @selected(request('category_id') == $category->getId())>
                        {{ $category->getNombre() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <table class="table table-striped align-middle bg-white">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Disponible</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->getNombre() }}</td>
                    <td>{{ $product->getNombreCategoria() }}</td>
                    <td>${{ number_format($product->getPrecio(), 2) }}</td>
                    <td>{{ $product->getStock() }}</td>
                    <td>
                        @if($product->estaDisponible())
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if(auth()->user()->esUsuario())
                            <div class="d-inline-flex gap-1 align-items-center">
                                <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline-flex gap-1">
                                    @csrf
                                    <input type="number" name="cantidad" value="1" min="1"
                                           class="form-control form-control-sm" style="width: 70px">
                                    <button type="submit" class="btn btn-sm btn-success"
                                            @disabled(! $product->estaDisponible())>Al carrito</button>
                                </form>

                                <form action="{{ route('comparator.add', $product) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Comparar</button>
                                </form>
                            </div>
                        @endif

                        @if(auth()->user()->esAdministrador())
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No hay productos que coincidan con la búsqueda.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
