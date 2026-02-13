<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogo DIZANY')</title>

    @vite(['resources/css/app.css'])

    <style>
        body { background:#f5f6f8; }
        .product-card:hover { transform: translateY(-5px); transition: .2s ease; }
        .navbar-catalogo {
            background:#111827;
            color:white;
        }
        .cart-badge {
            position:absolute;
            top:-5px;
            right:-8px;
            font-size:12px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-catalogo px-4 py-3 d-flex justify-content-between align-items-center">
    <h5 class="m-0">DIZANY</h5>

    <div class="position-relative">
        <a href="#" class="text-white text-decoration-none fs-5">
            🛒
            <span id="contador-carrito"
                  class="badge bg-danger cart-badge">0</span>
        </a>
    </div>
</nav>

<div class="container py-4">
    @yield('content')
</div>

<footer class="text-center py-4 text-muted small">
    © {{ date('Y') }} DIZANY
</footer>

@stack('scripts')

</body>
</html>
