@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-4">Nueva forma de pago</h1>

            <form action="{{ route('payment-methods.store') }}" method="POST" class="card">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            @foreach($tipos as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de tarjeta</label>
                        <input type="text" name="numeroTarjeta" class="form-control"
                               value="{{ old('numeroTarjeta') }}" placeholder="4111111111111111">
                        <div class="form-text">Solo para tarjeta de crédito o débito. Se valida con el algoritmo de Luhn.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Titular</label>
                        <input type="text" name="titular" class="form-control" value="{{ old('titular') }}">
                    </div>

                    <button class="btn btn-success">Guardar</button>
                    <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
