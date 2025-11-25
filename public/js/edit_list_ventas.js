$(document).ready(function () {
    const $buscador = $('#buscador-productos');
    const $resultados = $('#resultados-busqueda');
    const productosAgregados = new Set();

    // Marcar productos ya cargados
    $('#productos-venta tr').each(function () {
        const id = String($(this).attr('data-producto-id'));
        if (id) productosAgregados.add(id);
    });

    // Buscar productos
    $buscador.on('input', function () {
        const termino = $(this).val().trim();
        if (termino.length < 1) {
            $resultados.addClass('d-none').empty();
            return;
        }

        $.get('/buscar-producto', { search: termino }, function (data) {
            if (data.length === 0) {
                $resultados.removeClass('d-none').html('<div class="text-muted p-2">Sin resultados</div>');
                return;
            }

            // Construir grid de tarjetas
            $resultados.removeClass('d-none').html('<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3" id="grid-productos"></div>');
            const $grid = $('#grid-productos');

            data.forEach(producto => {
                const id = String(producto.id);
                const agregado = productosAgregados.has(id);

                const stock = parseInt(producto.stock);
                const stockClass = stock === 0
                    ? 'text-danger'
                    : stock < 5
                        ? 'text-warning'
                        : 'text-success';

                const tarjeta = `
                    <div class="col">
                        <div class="card resultado-item h-100 shadow-sm border-0"
                             style="cursor:pointer;"
                             data-id="${id}"
                             data-nombre="${producto.nombre}"
                             data-descripcion="${producto.descripcion}"
                             data-precio-venta="${producto.precio_venta}"
                             data-precio-mayor="${producto.precio_mayor}"
                             data-stock="${producto.stock}"
                             data-unidades-mayor="${producto.unidades_por_mayor || 1}">
                            <div class="card-body d-flex flex-column justify-content-between p-3">
                                <div class="mb-2">
                                    <h6 class="fw-semibold text-dark mb-1 text-truncate">
                                        <i class="fas fa-box me-1 text-primary"></i> ${producto.nombre}
                                    </h6>
                                    <p class="small text-muted mb-2 text-truncate">${producto.descripcion}</p>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-tag me-1 text-secondary"></i> S/ ${producto.precio_venta}
                                    </span>
                                </div>
                                <div class="mb-3 small ${stockClass}">
                                    <i class="fas fa-cubes me-1"></i> Stock: ${stock}
                                </div>
                                <div class="text-end">
                                    ${
                                        agregado
                                        ? `<button type="button" class="btn btn-sm btn-outline-secondary w-100" disabled>
                                                <i class="fas fa-check"></i> Agregado
                                           </button>`
                                        : `<button type="button" class="btn btn-sm btn-outline-primary btn-agregar-producto w-100">
                                                <i class="fas fa-plus"></i> Agregar
                                           </button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $grid.append(tarjeta);
            });

        });
    });

    // Agregar producto desde tarjeta
    $resultados.on('click', '.btn-agregar-producto', function (e) {
        e.stopPropagation();
        const card = $(this).closest('.resultado-item');

        const id = String(card.data('id'));
        const nombre = card.data('nombre');
        const descripcion = card.data('descripcion');
        const precioVenta = parseFloat(card.data('precio-venta')).toFixed(2);
        const precioMayor = parseFloat(card.data('precio-mayor')).toFixed(2);
        const stock = parseInt(card.data('stock')) || 0;
        const unidadesMayor = parseInt(card.data('unidades-mayor')) || 1;

        if (productosAgregados.has(id)) return;

        if (stock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin stock',
                text: `El producto "${nombre}" no tiene unidades disponibles.`,
                timer: 3000
            });
            return;
        }

        productosAgregados.add(id);
        $resultados.addClass('d-none').empty();
        $buscador.val('');

        const nuevaFila = `
            <tr data-producto-id="${id}" 
                data-precio-unidad="${precioVenta}"
                data-precio-mayor="${precioMayor}"
                data-stock="${stock}"
                data-unidades-mayor="${unidadesMayor}">
                <td>${nombre}</td>
                <td>${descripcion}</td>
                <td>
                    <select class="form-select tipo-venta" name="productos[${id}][tipo_venta]">
                        <option value="unidad" selected>Unidad</option>
                        <option value="mayor">Mayor</option>
                    </select>
                </td>
                <td class="precio-unitario text-center">S/ ${precioVenta}</td>
                <td>
                    <input type="number" name="productos[${id}][cantidad]" value="1" min="1"
                        class="form-control form-control-sm cantidad" style="max-width:80px; margin:auto;">
                </td>
                <td class="total-item text-center">S/ ${precioVenta}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#productos-venta').append(nuevaFila);
        recalcularTotales();
    });

    // Cambiar tipo de venta
    $(document).on('change', '.tipo-venta', function () {
        const fila = $(this).closest('tr');
        const tipo = $(this).val();

        if (tipo === 'mayor' && !puedeCambiarATipoMayor(fila)) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: 'No hay suficiente stock para venta por mayor.',
                timer: 3000
            });

            $(this).val('unidad');
            fila.find('.cantidad').val(1);
            actualizarFila(fila);
            return;
        }

        recalcularTotales();
    });

    // Cambiar cantidad
    $(document).on('input', '.cantidad', function () {
        recalcularTotales();
    });

    // Eliminar producto
    $(document).on('click', '.btn-eliminar-producto', function () {
        const fila = $(this).closest('tr');
        const id = String(fila.attr('data-producto-id'));
        productosAgregados.delete(id);
        fila.remove();
        recalcularTotales();
    });

    // Validar antes de enviar
    $('#form-editar-venta').on('submit', function (e) {
        e.preventDefault();  // Prevenir el envío normal del formulario

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas guardar los cambios en esta venta?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar cambios',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                let errorStock = false;
                let productoConError = '';

                $('#productos-venta tr').each(function () {
                    const fila = $(this);
                    const stock = parseInt(fila.attr('data-stock')) || 0;
                    const unidadesMayor = parseInt(fila.attr('data-unidades-mayor')) || 1;
                    const tipo = fila.find('.tipo-venta').val();
                    const cantidad = parseInt(fila.find('.cantidad').val()) || 0;
                    const unidades = tipo === 'mayor' ? cantidad * unidadesMayor : cantidad;

                    if (unidades > stock) {
                        errorStock = true;
                        productoConError = fila.find('td:first').text();
                        return false;
                    }
                });

                if (errorStock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de stock',
                        text: `El producto "${productoConError}" supera el stock disponible.`,
                        confirmButtonText: 'Corregir'
                    });
                    return;
                }

                // Si no hay error de stock, se puede proceder con el envío del formulario
                this.submit();  // Enviar el formulario
            }
        });
    });

    // Helpers
    function puedeCambiarATipoMayor(fila) {
        const stock = parseInt(fila.attr('data-stock')) || 0;
        const unidadesMayor = parseInt(fila.attr('data-unidades-mayor')) || 1;
        const cantidad = parseInt(fila.find('.cantidad').val()) || 1;
        const solicitadas = cantidad * unidadesMayor;
        return solicitadas <= stock;
    }

    function validarStock(fila) {
        const stock = parseInt(fila.attr('data-stock')) || 0;
        const tipo = fila.find('.tipo-venta').val();
        const unidadesMayor = parseInt(fila.attr('data-unidades-mayor')) || 1;
        const input = fila.find('.cantidad');
        let cantidad = parseInt(input.val()) || 1;
        let unidades = tipo === 'mayor' ? cantidad * unidadesMayor : cantidad;

        if (unidades > stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: `Solo hay ${stock} unidades disponibles.`,
                timer: 3000
            });

            input.val(1);
            return tipo === 'mayor' ? unidadesMayor : 1;
        }

        return unidades;
    }

    function actualizarFila(fila) {
    const tipo = fila.find('.tipo-venta').val();
    const precioUnidad = parseFloat(fila.attr('data-precio-unidad')) || 0;
    const precioMayor = parseFloat(fila.attr('data-precio-mayor')) || 0;
    const unidadesMayor = parseInt(fila.attr('data-unidades-mayor')) || 1;
    const cantidad = parseInt(fila.find('.cantidad').val()) || 1;

    const precio = (tipo === 'mayor' && precioMayor > 0)
        ? precioMayor / unidadesMayor
        : precioUnidad;

    const total = precio * cantidad;

    fila.find('.precio-unitario').text('S/ ' + precio.toFixed(2));
    fila.find('.total-item').text('S/ ' + total.toFixed(2));
}


    function recalcularTotales() {
    let subtotal = 0;

    const igvInput = document.getElementById('igv-config');
    const igvSpan = document.getElementById('valor-igv-mostrado');
    const igvPorcentaje = igvInput ? parseFloat(igvInput.value) : 0;

    $('#productos-venta tr').each(function () {
        const fila = $(this);
        const tipo = fila.find('.tipo-venta').val();
        const precioUnidad = parseFloat(fila.attr('data-precio-unidad')) || 0;
        const precioMayor = parseFloat(fila.attr('data-precio-mayor')) || 0;
        const unidadesMayor = parseInt(fila.attr('data-unidades-mayor')) || 1;
        const unidades = validarStock(fila);
        const precio = (tipo === 'mayor' && precioMayor > 0) ? precioMayor : precioUnidad;
        const cantidadVisible = tipo === 'mayor' ? unidades / unidadesMayor : unidades;
        const total = precio * cantidadVisible;

        fila.find('.precio-unitario').text('S/ ' + precio.toFixed(2));
        fila.find('.total-item').text('S/ ' + total.toFixed(2));
        subtotal += total;
    });

    const igvCalculado = subtotal * (igvPorcentaje / 100);
    const totalFinal = subtotal + igvCalculado;

    $('#subtotal').text('S/ ' + subtotal.toFixed(2));
    $('#igv').text('S/ ' + igvCalculado.toFixed(2));
    $('#total-venta').text('S/ ' + totalFinal.toFixed(2));

    // Asegura que el % se muestre en el span
    if (igvSpan) {
    igvSpan.textContent = igvPorcentaje.toFixed(2) + '%';
    }

}


    // Forzar tipo de venta trigger
    $('.tipo-venta').each(function () {
        $(this).trigger('change');
    });
});
