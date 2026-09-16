@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Crear cuenta</h1>

                    <form action="{{ route('register') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                   value="{{ old('nombre') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control"
                                   value="{{ old('telefono') }}">
                        </div>

                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" name="contraseña" id="contrasena" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" name="contraseña_confirmation" id="contrasena_confirmation"
                                   class="form-control" required>
                        </div>

                        <p class="text-muted small">
                            El registro público crea cuentas de tipo <strong>usuario</strong>.
                            Las cuentas de administrador se crean desde el seeder.
                        </p>

                        <button type="submit" class="btn btn-success w-100">Registrarme</button>
                    </form>

                    <hr>
                    <p class="mb-0 text-center">
                        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
