@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Mis formas de pago</h1>
        <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">Nueva forma de pago</a>
    </div>

    <table class="table bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>Tipo</th>
                <th>Número</th>
                <th>Titular</th>
                <th>Válida</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($formasPago as $forma)
                <tr>
                    <td>{{ $forma->getTipoLegible() }}</td>
                    <td>{{ $forma->getNumeroEnmascarado() }}</td>
                    <td>{{ $forma->getTitular() ?? '-' }}</td>
                    <td>
                        @if($forma->validarMetodoPago())
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <form action="{{ route('payment-methods.destroy', $forma) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta forma de pago?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Todavía no registraste formas de pago.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
