<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TechGate - Tienda de Tecnología</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('products.index') }}">TechGate</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                @auth
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Productos</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categorías</a></li>

                        @if(auth()->user()->esUsuario())
                            <li class="nav-item"><a class="nav-link" href="{{ route('cart.index') }}">Carrito</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Mis pedidos</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('payment-methods.index') }}">Formas de pago</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('comparator.index') }}">Comparador</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('compatibility.index') }}">Compatibilidad</a></li>
                        @endif

                        @if(auth()->user()->esAdministrador())
                            <li class="nav-item"><a class="nav-link" href="{{ route('products.create') }}">Nuevo producto</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('categories.create') }}">Nueva categoría</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('orders.admin') }}">Pedidos de la tienda</a></li>
                        @endif
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item">
                            <span class="navbar-text me-2">
                                {{ auth()->user()->getNombre() }}
                                <span class="badge {{ auth()->user()->esAdministrador() ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                    {{ ucfirst(auth()->user()->getRol()) }}
                                </span>
                            </span>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">Perfil</a></li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-light">Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                @else
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Registrarse</a></li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
