@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Categorías</h1>

        @if(auth()->user()->esAdministrador())
            <a href="{{ route('categories.create') }}" class="btn btn-primary">Nueva categoría</a>
        @endif
    </div>

    <table class="table table-striped bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                @php($detalle = $category->mostrarDetallesCategoria())
                <tr>
                    <td>{{ $detalle['id'] }}</td>
                    <td>{{ $detalle['nombre'] }}</td>
                    <td>{{ $detalle['descripcion'] ?? '-' }}</td>
                    <td>{{ $detalle['total_productos'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No hay categorías registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
