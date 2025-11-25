<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vista - Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
    'resources/css/estilos_vista.css',
    'resources/css/app.css',
    'resources/js/app.js'
])

</head>
<body class="{{ $tema == 'oscuro' ? 'theme-dark' : 'theme-light' }}">

    @include('components.header')

    @include('components.sidebar')

    <main id="content">
        @yield('content')
    </main>

    @include('components.footer')

    <script>
    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');

    // Alternar sidebar
    btnToggleSidebar.addEventListener('click', () => {
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            document.body.classList.toggle('sidebar-visible');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
    });

    // Ocultar en móvil al hacer clic en un enlace del sidebar
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                document.body.classList.remove('sidebar-visible');
            }
        });
    });

    // Mostrar/ocultar sidebar por defecto
    window.addEventListener('DOMContentLoaded', () => {
        if (window.innerWidth > 768) {
            document.body.classList.remove('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-visible');
        }
    });

    // Adaptar al redimensionar
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            document.body.classList.remove('sidebar-visible');
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.submenu-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const submenu = button.nextElementSibling;
            const icon = button.querySelector('.toggle-icon');

            submenu.classList.toggle('show');
            icon.classList.toggle('rotated');
        });
    });

    // Si ya está activo al cargar (por URL), gira el ícono
    document.querySelectorAll('.submenu-items.show').forEach(submenu => {
        const icon = submenu.previousElementSibling.querySelector('.toggle-icon');
        if (icon) {
            icon.classList.add('rotated');
        }
    });
});
</script>

<!-- Aquí se cargan los scripts añadidos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS y Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    @stack('scripts')
</body>
</html>
