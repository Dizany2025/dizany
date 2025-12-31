<div class="header-actions">
    <div class="header-actions-content">

        {{-- IZQUIERDA --}}
        <div class="titulo-venta-wrapper">

            @hasSection('header-back')
                @yield('header-back')
            @endif

            <div class="separador"></div>

            <h5 class="titulo-venta mb-0">
                @yield('header-title')
            </h5>
        </div>

        {{-- DERECHA --}}
        <div class="header-right-actions">
            @yield('header-buttons')
        </div>

    </div>
</div>
