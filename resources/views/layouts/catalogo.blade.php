<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogo DIZANY')</title>

    @vite(['resources/css/app.css'])

    @stack('styles') {{-- 👈 ESTA LÍNEA FALTABA --}}

    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

<div class="container py-4">
    @yield('content')
</div>

<footer class="text-center py-4 text-muted small">
    © {{ date('Y') }} DIZANY
</footer>

@stack('scripts')

</body>
</html>
