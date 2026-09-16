@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Mi perfil</h1>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Datos personales</h2>

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="{{ old('nombre', $usuario->getNombre()) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $usuario->getEmail()) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                   value="{{ old('telefono', $usuario->getTelefono()) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <input type="text" class="form-control" value="{{ ucfirst($usuario->getRol()) }}" disabled>
                        </div>

                        <button class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Cambiar contraseña</h2>

                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" name="contraseña_actual" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="contraseña" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="contraseña_confirmation" class="form-control" required>
                        </div>

                        <button class="btn btn-warning">Cambiar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
