@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Comparador de productos</h1>

    <h2 class="h4">Agregar productos</h2>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
                <tr>
                    <td>{{ $producto->getNombre() }}</td>
                    <td>{{ $producto->getNombreCategoria() }}</td>
                    <td>${{ number_format($producto->getPrecio(), 2) }}</td>
                    <td>{{ $producto->getStock() }}</td>
                    <td>
                        <form action="{{ route('comparator.add', $producto) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">Agregar al comparador</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="h4 mt-5">Productos en el comparador</h2>

    @if($cuadro->isEmpty())
        <p>Todavía no agregaste productos al comparador.</p>
    @else
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Disponible</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($cuadro as $fila)
                    <tr>
                        <td>{{ $fila['nombre'] }}</td>
                        <td>{{ $fila['categoria'] }}</td>
                        <td>${{ number_format($fila['precio'], 2) }}</td>
                        <td>{{ $fila['stock'] }}</td>
                        <td>
                            @if($fila['disponible'])
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('comparator.remove', $fila['id']) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Quitar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form action="{{ route('comparator.clear') }}" method="POST" class="mb-4">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">Vaciar comparador</button>
        </form>

        <h2 class="h4 mt-4">Verificación de compatibilidad (armado)</h2>

        @if(count($compatibilidad) === 0)
            <p>Agrega al menos dos productos para verificar si son compatibles entre sí.</p>
        @else
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Producto A</th>
                        <th>Producto B</th>
                        <th>Compatible</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compatibilidad as $par)
                        <tr>
                            <td>{{ $par['producto_a'] }}</td>
                            <td>{{ $par['producto_b'] }}</td>
                            <td>
                                @if($par['compatible'])
                                    <span class="badge bg-success">Sí</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                            <td>{{ $par['razon'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</div>
@endsection
