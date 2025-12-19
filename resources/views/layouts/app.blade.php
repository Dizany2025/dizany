<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vista - Panel</title>

    <!-- Bootstrap + Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tus estilos -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="{{ $tema == 'oscuro' ? 'theme-dark' : 'theme-light' }}">

    {{-- HEADER --}}
    @include('components.header')

    {{-- SIDEBAR --}}
    @include('components.sidebar')

    {{-- CONTENIDO PRINCIPAL --}}
    <main id="content">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @include('components.footer')

    <!-- Script: Toggle de Sidebar -->
    <script>
        const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');

        btnToggleSidebar.addEventListener('click', () => {
            const mobile = window.innerWidth <= 768;

            if (mobile) {
                document.body.classList.toggle('sidebar-visible');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        });

        // Cerrar sidebar en móvil al hacer clic en enlaces
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    document.body.classList.remove('sidebar-visible');
                }
            });
        });

        // Configuración inicial según ancho
        window.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth > 768) {
                document.body.classList.remove('sidebar-visible');
            }
        });

        // Ajuste al redimensionar
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                document.body.classList.remove('sidebar-visible');
            }
        });
    </script>

    <!-- Script: Submenús -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.submenu-toggle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const submenu = btn.nextElementSibling;
                    const icon = btn.querySelector('.toggle-icon');

                    submenu.classList.toggle('show');
                    icon.classList.toggle('rotated');
                });
            });

            // Si ya hay un submenu abierto por la URL, mantener ícono girado
            document.querySelectorAll('.submenu-items.show').forEach(sub => {
                const icon = sub.previousElementSibling.querySelector('.toggle-icon');
                if (icon) icon.classList.add('rotated');
            });
        });
    </script>

    <!-- Librerías JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: @json(session('success')),
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif

    @if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            html: `{!! implode('<br>', $errors->all()) !!}`,
        });
    </script>
    @endif

    @stack('scripts')

</body>
</html>
