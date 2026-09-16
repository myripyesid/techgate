@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Compatibilidad entre componentes</h1>

    <p class="text-muted">Elige dos productos para verificar si son compatibles entre sí.</p>

    <form action="{{ route('compatibility.index') }}" method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-5">
            <label for="product_a_id" class="form-label">Producto A</label>
            <select name="product_a_id" id="product_a_id" class="form-select" required>
                <option value="">Seleccione un producto</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->getId() }}" @selected($productoA?->getId() == $producto->getId())>
                        {{ $producto->getNombre() }} ({{ $producto->getNombreCategoria() }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5">
            <label for="product_b_id" class="form-label">Producto B</label>
            <select name="product_b_id" id="product_b_id" class="form-select" required>
                <option value="">Seleccione un producto</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->getId() }}" @selected($productoB?->getId() == $producto->getId())>
                        {{ $producto->getNombre() }} ({{ $producto->getNombreCategoria() }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Verificar</button>
        </div>
    </form>

    @if($productoA && $productoB)
        @if($productoA->getId() === $productoB->getId())
            <div class="alert alert-warning">Selecciona dos productos diferentes.</div>
        @else
            <div class="alert {{ $esCompatible ? 'alert-success' : 'alert-danger' }}">
                <strong>{{ $productoA->getNombre() }}</strong> y <strong>{{ $productoB->getNombre() }}</strong>
                son
                @if($esCompatible)
                    <strong>compatibles</strong>.
                @else
                    <strong>incompatibles</strong>. Motivo: {{ $razon }}
                @endif
            </div>
        @endif
    @endif
</div>
@endsection
