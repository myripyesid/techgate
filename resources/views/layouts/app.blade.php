<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechGate - Tienda de Tecnología</title>
    <!-- Bootstrap 5 CSS CDN para estilos rápidos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('products.index') }}">TechGate</a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('products.index') }}">Productos</a>
                <a class="nav-link" href="{{ route('categories.index') }}">Categorías</a>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>