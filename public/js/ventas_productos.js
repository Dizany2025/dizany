document.addEventListener("DOMContentLoaded", () => {
    const buscarInput = document.getElementById("buscar_producto");
    const resultadosDiv = document.getElementById("resultados-busqueda");
    const tablaBody = document.getElementById("productos-seleccionados");
    const continuarVentaBtn = document.getElementById("continuar-venta-btn");
    const modalConfirmarVenta = new bootstrap.Modal(document.getElementById('modalConfirmarVenta'));
    const confirmarVentaBtn = document.getElementById("confirmar-venta");
    const valorVentaModal = document.getElementById("valor_venta");
    const valorPagarModal = document.getElementById("valor_pagar");
    const valorDevolverModal = document.getElementById("valor_devolver");
    const modalCloseBtn = document.getElementById("cerrarModal");
    const documentoInput = document.getElementById('documento');
    const metodoPagoSelect = document.getElementById("metodo_pago");

    // ✅ Instancia para el modal de venta exitosa
    const modalVentaExitosaElement = document.getElementById('modalVentaExitosa');
    const modalVentaExitosa = new bootstrap.Modal(modalVentaExitosaElement);

    const btnImprimir = document.getElementById("btnImprimir");
    const btnDescargar = document.getElementById("btn-descargar");
    const btnNuevaVenta = document.getElementById("btnNuevaVenta");

    const sonidoError = new Audio('/sonidos/error-alert.mp3');
    const sonidoExito = new Audio('/sonidos/success.mp3');

    documentoInput.addEventListener('keydown', e => e.key === 'Enter' && e.preventDefault());
    buscarInput.addEventListener('keydown', e => e.key === 'Enter' && e.preventDefault());

    let productosSeleccionados = [];

    // Obtener serie y correlativo automáticamente según tipo de comprobante
    const tipoComprobanteSelect = document.getElementById("tipo_comprobante");
    const inputSerieCorrelativo = document.getElementById("serie_correlativo");

    tipoComprobanteSelect.addEventListener("change", () => {
        const tipo = tipoComprobanteSelect.value;

    fetch(`/ventas/obtener-serie-correlativo?tipo=${tipo}`)
            .then(res => res.json())
            .then(data => {
                if (inputSerieCorrelativo && data.serie && data.correlativo) {
                    inputSerieCorrelativo.value = `${data.serie}-${String(data.correlativo).padStart(6, '0')}`;
                }
            })
            .catch(err => console.error("Error al obtener serie y correlativo:", err));
    });

    tipoComprobanteSelect.dispatchEvent(new Event("change"));

    // Buscar productos
    buscarInput.addEventListener("input", () => {
        const query = buscarInput.value.trim();
        if (query.length < 1) {
            resultadosDiv.innerHTML = "";
            resultadosDiv.classList.add("d-none");
            return;
        }

        fetch(`/buscar-producto?search=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                resultadosDiv.innerHTML = "";
                resultadosDiv.classList.remove("d-none");

                if (data.length === 0) {
                    resultadosDiv.innerHTML = `<div class="col text-center text-muted">No se encontraron productos</div>`;
                    return;
                }

                data.forEach(producto => {
                    const col = document.createElement("div");
                    col.className = "col-md-3";

                    col.innerHTML = `
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-primary">${producto.nombre}</h5>
                                <p class="card-text small mb-1"><strong>Descripción:</strong> ${producto.descripcion || 'Sin descripción'}</p>
                                <p class="card-text small mb-1"><strong>Precio:</strong> S/ ${parseFloat(producto.precio_venta).toFixed(2)}</p>
                                <p class="card-text small mb-3"><strong>Stock:</strong> ${producto.stock}</p>
                                <button type="button" class="btn btn-success btn-sm mt-auto agregar-carrito" data-id="${producto.id}">
                                    <i class="fas fa-plus-circle"></i> Agregar
                                </button>
                            </div>
                        </div>
                    `;

                    col.querySelector(".agregar-carrito").addEventListener("click", function () {
                        const existente = productosSeleccionados.find(p => p.id === producto.id);

                        if (existente) {
                            mostrarAlerta(`El producto "${producto.nombre}" ya está en la tabla.`);
                            return;
                        }

                        if (producto.stock < 1) {
                            mostrarAlerta(`El producto "${producto.nombre}" no tiene stock disponible.`);
                            return;
                        }

                        agregarProducto(producto);
                        resultadosDiv.innerHTML = "";
                        buscarInput.value = "";
                    });
                    resultadosDiv.appendChild(col);
                });
            });
    });

    function agregarProducto(producto) {
    const existente = productosSeleccionados.find(p => p.id === producto.id);

    if (existente) {
        if (existente.cantidad + 1 > producto.stock) {
            mostrarAlerta(`No hay suficiente stock disponible para ${producto.nombre}`);
            return;
        }
        existente.cantidad += 1;
    } else {
        if (producto.stock < 1) {
            mostrarAlerta(`El producto ${producto.nombre} no tiene stock disponible.`);
            return;
        }

        productosSeleccionados.push({
            ...producto,
            cantidad: 1,
            tipo_venta: 'unidad',
            precio_venta: parseFloat(producto.precio_venta) || 0,
            precio_mayor: parseFloat(producto.precio_mayor) || 0,
            unidades_por_mayor: parseInt(producto.unidades_por_mayor) || 1
        });
    }

    renderTabla();
}


     // Función para renderizar la tabla actualizada
    function renderTabla() {
        tablaBody.innerHTML = "";

        productosSeleccionados.forEach((producto, index) => {
            const tipoVentaSelect = `
                <select class="form-select form-select-sm tipo-venta" data-index="${index}">
                    <option value="unidad" ${producto.tipo_venta === 'unidad' ? 'selected' : ''}>Unidad</option>
                    <option value="mayor" ${producto.tipo_venta === 'mayor' ? 'selected' : ''}>Mayor</option>
                </select>`;

            const precioFinal = producto.tipo_venta === 'mayor' && producto.precio_unitario > 0
                ? producto.precio_unitario
                : producto.precio_venta;

            const total = (precioFinal * producto.cantidad).toFixed(2);

            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td>${producto.nombre}</td>
                <td>${producto.descripcion}</td>
                <td>${tipoVentaSelect}</td>
                <td>S/ ${precioFinal.toFixed(2)}</td> <!-- Precio actualizado -->
                <td>
                    <input type="number" min="1" class="form-control form-control-sm text-center"
                        value="${producto.cantidad}" data-index="${index}" data-action="cambiar-cantidad" />
                </td>
                <td>S/ ${total}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger" data-index="${index}" data-action="eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tablaBody.appendChild(fila);
        });

        actualizarResumen();
    }

 // Aseguramos que cuando el tipo de venta cambie, se realicen los cálculos correspondientes
    tablaBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("tipo-venta")) {
        const index = parseInt(e.target.dataset.index);
        const producto = productosSeleccionados[index];
        const nuevoTipo = e.target.value;

        if (nuevoTipo === 'mayor') {
            const unidadesPorMayor = producto.unidades_por_mayor || 12;
            const stockDisponible = producto.stock;

            if (unidadesPorMayor > stockDisponible) {
                mostrarAlerta(`No hay suficiente stock para venta por mayor de ${producto.nombre}`);
                e.target.value = 'unidad';
                return;
            }

            producto.cantidad = unidadesPorMayor;
            producto.precio_unitario = producto.precio_mayor / unidadesPorMayor;
        } else {
            if (producto.stock < 1) {
                mostrarAlerta(`El producto ${producto.nombre} no tiene stock para venta por unidad`);
                return;
            }

            producto.cantidad = 1;
            producto.precio_unitario = producto.precio_venta;
        }

        producto.tipo_venta = nuevoTipo;
        renderTabla();
    }
});


    // Al cambiar la cantidad, se debe verificar si es por mayor o unidad
    tablaBody.addEventListener("input", (e) => {
    const index = e.target.dataset.index;
    const action = e.target.dataset.action;

    if (action === "cambiar-cantidad") {
        const producto = productosSeleccionados[index];
        let cantidad = parseInt(e.target.value) || 1;

        if (cantidad > producto.stock) {
            mostrarAlerta(`No puedes ingresar más de ${producto.stock} unidades para ${producto.nombre}`);
            cantidad = producto.stock;
            e.target.value = cantidad;
        }

        producto.cantidad = cantidad;
        renderTabla();
    }
});


    function actualizarResumen() {
    const igvInputElement = document.getElementById('igv-config');
    const igvPorcentaje = igvInputElement ? parseFloat(igvInputElement.value) : 0;

    const opGravadasInput = document.querySelector('[name="op_gravadas"]');
    const igvInput = document.querySelector('[name="igv"]');
    const totalInput = document.querySelector('[name="total"]');
    const montoPagadoInput = document.querySelector('[name="monto_pagado"]');

    let subtotal = productosSeleccionados.reduce((sum, p) => {
        const precio = (p.tipo_venta === 'mayor' && p.precio_mayor > 0)
            ? parseFloat(p.precio_mayor / p.unidades_por_mayor) // Precio unitario cuando es mayor
            : parseFloat(p.precio_venta); // Precio de venta para unidad
        return sum + (precio * p.cantidad); // Acumular el subtotal
    }, 0);

    const igv = subtotal * (igvPorcentaje / 100); // Calcular el IGV
    const total = subtotal + igv; // El total es el subtotal + IGV

    // Mostrar los valores en los campos correspondientes
    if (opGravadasInput) opGravadasInput.value = subtotal.toFixed(2);
    if (igvInput) igvInput.value = igv.toFixed(2);
    if (totalInput) totalInput.value = total.toFixed(2);
    if (montoPagadoInput) montoPagadoInput.value = total.toFixed(2);

    // Mostrar el valor del IGV en la interfaz
    const igvSpan = document.getElementById('valor-igv-mostrado');
    if (igvSpan) igvSpan.textContent = `${igvPorcentaje.toFixed(2)}%`;
}

// Función para calcular el total de la venta
function calcularTotal() {
    return productosSeleccionados.reduce((total, p) => {
        const precioFinal = p.tipo_venta === 'mayor'
            ? p.precio_unitario // Usamos el precio unitario ya ajustado para "mayor"
            : p.precio_venta;   // Para "unidad", usamos el precio normal por unidad

        return total + (precioFinal * p.cantidad); // Calculamos el total de la venta correctamente
    }, 0).toFixed(2);
}

// Cuando el botón de continuar venta es presionado
continuarVentaBtn.addEventListener("click", () => {
    const totalVenta = calcularTotal(); // Calculamos el total correcto
    valorVentaModal.value = totalVenta; // Asignamos al campo del modal
    modalConfirmarVenta.show(); // Mostramos el modal
});

// Al confirmar la venta en el modal
confirmarVentaBtn.addEventListener("click", () => {
    const tipoComprobante = document.getElementById("tipo_comprobante").value;
    const documento = document.getElementById("documento").value;
    const totalVenta = parseFloat(valorVentaModal.value); // Aquí tomamos el valor del modal (debe ser el correcto)
    const fechaEmision = document.getElementById("fecha_emision").value;
    const horaActual = document.getElementById("hora_actual").value;
    const metodoPago = metodoPagoSelect.value;
    const formatoPDF = document.getElementById("formato_pdf") ? document.getElementById("formato_pdf").value : "a4";

    const productosEnviar = productosSeleccionados.map(p => {
        const unidades_descuento = p.tipo_venta === 'mayor'
            ? p.cantidad * p.unidades_por_mayor
            : p.cantidad;

        return {
            producto_id: p.id,
            cantidad: p.cantidad,
            tipo_venta: p.tipo_venta,
            precio_unitario: p.precio_venta,
            precio_mayor: p.precio_mayor,
            unidades_por_mayor: p.unidades_por_mayor,
            unidades_descuento: unidades_descuento
        };
    });

    fetch('/ventas/registrar', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            tipo_comprobante: tipoComprobante,
            documento: documento,
            total_venta: totalVenta, // Aquí estamos usando el valor del modal
            fecha: fechaEmision,
            hora: horaActual,
            metodo_pago: metodoPago,
            productos: productosEnviar,
            formato: formatoPDF
        })
    })
    .then(async res => {
        if (!res.ok) {
            const errorText = await res.text();
            console.error("Error del servidor (HTML):", errorText);
            throw new Error("Error al procesar la venta. Ver detalles en consola.");
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            sonidoExito.play().catch(err => console.warn("No se pudo reproducir sonido:", err));
            if (btnImprimir && btnDescargar && data.pdf_url) {
                btnImprimir.href = data.pdf_url;
                btnImprimir.setAttribute('target', '_blank');

                if (data.nombre_archivo) {
                    btnDescargar.href = `/comprobantes/descargar/${data.nombre_archivo}`;
                } else {
                    btnDescargar.href = data.pdf_url;
                }
            } else {
                console.warn("Botones o URL de PDF no disponibles.");
            }

            modalConfirmarVenta.hide();
            modalVentaExitosa.show();
        } else {
            mostrarAlerta(data.message || 'Error al registrar venta');
        }
    })
    .catch(err => {
        console.error("Error inesperado:", err);
        mostrarAlerta('Ocurrió un error al registrar la venta. Revisa la consola.');
    });

    modalConfirmarVenta.hide();
});


///////////
    valorPagarModal.addEventListener("input", () => {
        const valorVenta = parseFloat(valorVentaModal.value);
        const valorPagar = parseFloat(valorPagarModal.value);
        const valorDevolver = (valorPagar - valorVenta).toFixed(2);
        valorDevolverModal.value = valorDevolver >= 0 ? `S/ ${valorDevolver}` : 'S/ 0.00';
    });

    modalCloseBtn.addEventListener("click", () => {
        valorVentaModal.value = '';
        valorPagarModal.value = '';
        valorDevolverModal.value = '';
    });

     // ✅ Evento para iniciar nueva venta
   btnNuevaVenta.addEventListener("click", () => {
    // 1. Vaciar productos seleccionados y la tabla
    productosSeleccionados = [];
    renderTabla();

    // 2. Limpiar los campos del formulario
    documentoInput.value = "";
    document.getElementById("razon_social").value = "";
    document.getElementById("direccion").value = "";
    buscarInput.value = "";
    resultadosDiv.innerHTML = "";
    resultadosDiv.classList.add("d-none");

    // 3. Resetear selects
    metodoPagoSelect.selectedIndex = 0;
    tipoComprobanteSelect.selectedIndex = 0;
    inputSerieCorrelativo.value = "";

    // 4. Regenerar serie y correlativo automáticamente
    tipoComprobanteSelect.dispatchEvent(new Event("change"));

    // 5. Limpiar valores en el modal de confirmar venta
    valorVentaModal.value = "";
    valorPagarModal.value = "";
    valorDevolverModal.value = "";

    // 6. Resetear totales en el resumen
    const opGravadasInput = document.querySelector('[name="op_gravadas"]');
    const igvInput = document.querySelector('[name="igv"]');
    const totalInput = document.querySelector('[name="total"]');
    const montoPagadoInput = document.querySelector('[name="monto_pagado"]');

    if (opGravadasInput) opGravadasInput.value = "0.00";
    if (igvInput) igvInput.value = "0.00";
    if (totalInput) totalInput.value = "0.00";
    if (montoPagadoInput) montoPagadoInput.value = "0.00";

    // 7. Ocultar el modal de venta exitosa
    modalVentaExitosa.hide();

    // 8. Scroll al inicio
    window.scrollTo({ top: 0, behavior: "smooth" });

    console.log("✅ Interfaz reiniciada para nueva venta");
});


    function mostrarAlerta(mensaje) {
        Swal.fire({
            icon: 'warning',
            title: '¡Atención!',
            text: mensaje,
            showConfirmButton: false,
            timer: 3000
        });
        sonidoError.play().catch(err => console.warn("Error al reproducir sonido:", err));
    }
});
