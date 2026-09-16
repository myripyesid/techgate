@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Editar producto</h1>

    <form action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Nombre del producto</label>
            <input type="text" name="nombre" class="form-control"
                   value="{{ old('nombre', $product->getNombre()) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $category)
                    <option value="{{ $category->getId() }}" @selected(old('category_id', $product->getCategoryId()) == $category->getId())>
                        {{ $category->getNombre() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio ($)</label>
            <input type="number" step="0.01" name="precio" class="form-control"
                   value="{{ old('precio', $product->getPrecio()) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control"
                   value="{{ old('stock', $product->getStock()) }}" required>
        </div>

        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
