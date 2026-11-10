{{-- Ficha tecnica del producto (catalogo fusionado) --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index', ['category_id' => $product->getCategoryId()]) }}">{{ $product->getNombreCategoria() }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->getNombre() }}</li>
        </ol>
    </nav>

    <div class="tg-detail">
        <div class="pic">{!! $catIcon($product->getCategorySlug(), 150) !!}</div>
        <div>
            <span class="text-muted text-uppercase small fw-bold">{{ $product->getMarca() ?? 'TechGate' }}</span>
            <h1 class="h3 mb-2">{{ $product->getNombre() }}</h1>
            <div class="text-muted mb-3">
                {{ $product->getNombreCategoria() }}
                @if($product->getLanzamiento()) · Lanzamiento: {{ $product->getLanzamiento() }} @endif
                @if(! $product->esVendible()) · <span class="badge bg-secondary">Producto de referencia</span> @endif
            </div>

            @if($product->esVendible())
                <div class="price">${{ number_format($product->getPrecio(), 2) }}</div>
                <small class="text-muted d-block mb-3">Stock disponible: {{ $product->getStock() }}</small>
            @else
                <div class="h5 text-muted mb-1">Precio por confirmar</div>
                <small class="text-muted d-block mb-3">No disponible para compra</small>
            @endif

            <div class="d-flex flex-wrap gap-2">
                @if(auth()->user()->esUsuario())
                    @if($product->estaDisponible())
                        <form action="{{ route('cart.add', $product) }}" method="POST">
                            @csrf
                            <button class="btn btn-primary px-4">Agregar al carrito</button>
                        </form>
                    @endif
                    <form action="{{ route('comparator.add', $product) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-primary">Comparar</button>
                    </form>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>
    </div>

    @if($product->getPrecioLanzamiento() !== null)
        <p class="text-muted">Precio de lanzamiento de referencia: US$ {{ number_format($product->getPrecioLanzamiento(), 2) }}</p>
    @endif

    @if($product->getConfianza() && $product->getConfianza() !== 'alta')
        <div class="alert alert-warning">Algunos datos de esta ficha tienen confianza <strong>{{ $product->getConfianza() }}</strong>: verifícalos con la ficha del fabricante.</div>
    @endif

    @php($specs = $product->getEspecificaciones())
    <h2 class="tg-title">Especificaciones técnicas</h2>
    <table class="table table-striped bg-white">
        <tbody>
            @foreach($definiciones as [$campo, $etiqueta, $unidad, $direccion, $peso, $tipo])
                @continue(! array_key_exists($campo, $specs))
                @php($v = $specs[$campo])
                <tr>
                    <th class="w-50">{{ $etiqueta }}</th>
                    <td>
                        @if($v === null || $v === '')
                            <span class="text-muted">sin dato</span>
                        @elseif($tipo === 'b')
                            {{ $v ? 'Sí' : 'No' }}
                        @else
                            {{ $v }}{{ $unidad !== '' ? ' '.$unidad : '' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
