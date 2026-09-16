@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Iniciar sesión</h1>

                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" name="contraseña" id="contrasena" class="form-control" required>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="recordarme" id="recordarme" class="form-check-input" value="1">
                            <label for="recordarme" class="form-check-label">Recordarme</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>

                    <hr>
                    <p class="mb-0 text-center">
                        ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body small text-muted">
                    <strong>Cuentas de prueba (seeder):</strong><br>
                    Administrador: admin@techgate.com / admin1234<br>
                    Usuario: usuario@techgate.com / usuario1234
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
