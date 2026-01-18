// ===============================
// REGISTRO DE VENTA / BACKEND
// ===============================

document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // ELEMENTOS
    // ============================
    const tipoComprobanteSelect = document.getElementById("tipo_comprobante");
    const estadoPagoSelect      = document.getElementById("estado_pago");
    const formatoSelect         = document.getElementById("formato_pdf");

    const inputPaga  = document.getElementById("vuelto-paga");
    const inputTotal = document.getElementById("vuelto-total-venta");

    const btnConfirmar3 = document.getElementById("btn-confirmar-venta");
    const btnConfirmarDirecto =
        document.getElementById("btn-confirmar-venta-directo");

    const btnImprimir  = document.getElementById("btnImprimir");
    const btnDescargar = document.getElementById("btn-descargar");
    const btnNuevaVenta = document.getElementById("btnNuevaVenta");

    const modalVentaExitosaElement =
        document.getElementById("modalVentaExitosa");

    let modalVentaExitosa = null;
    if (modalVentaExitosaElement && window.bootstrap) {
        modalVentaExitosa =
            bootstrap.Modal.getOrCreateInstance(modalVentaExitosaElement);
    }

    // ============================
    // BOTONES COMPROBANTE
    // ============================
    function configurarBotonesComprobante(data) {

        if (btnImprimir && data.pdf_url) {
            btnImprimir.href = data.pdf_url;
            btnImprimir.target = "_blank";
        }

        if (btnDescargar) {
            if (data.nombre_archivo) {
                btnDescargar.href =
                    `/storage/comprobantes/${data.nombre_archivo}`;
                btnDescargar.download = data.nombre_archivo;
            } else if (data.pdf_url) {
                btnDescargar.href = data.pdf_url;
                btnDescargar.download = "";
            }
        }
    }

    // ============================
    // REGISTRAR VENTA
    // ============================
    function registrarVenta() {

        if (typeof window.volcarUIaVentaActiva === "function") {
            window.volcarUIaVentaActiva();
        }

        const v = ventaActiva();
        const { total } = calcularTotal();

        if (!v.productos.length) {
            return mostrarAlerta("No hay productos en la venta.");
        }

        const tipoComprobante =
            tipoComprobanteSelect?.value || "boleta";

        const documento = v.cliente?.documento || "";
        const fecha = document.getElementById("fecha_emision")?.value;
        const hora  = document.getElementById("hora_actual")?.value;

        const estadoPago =
            estadoPagoSelect?.value || "pagado";

        const metodoPago = v.metodo_pago || "";
        const formato = formatoSelect?.value || "a4";

        // ============================
        // MONTO PAGADO
        // ============================
        let montoPagado = 0;

        if (estadoPago === "pagado") {
            montoPagado = total;
        } else if (estadoPago === "credito") {
            montoPagado = parseFloat(inputPaga?.value || 0);
        }
        // pendiente => 0

        if (montoPagado > 0 && !metodoPago) {
            return mostrarAlerta(
                "Debes seleccionar un método de pago."
            );
        }

        const productosEnviar =
            v.productos.map(it => ({
                producto_id: it.id,
                cantidad: it.cantidad,
                presentacion: it.tipo_venta
            }));

        fetch("/ventas/registrar", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                tipo_comprobante: tipoComprobante,
                documento: documento,
                fecha: fecha,
                hora: hora,
                monto_pagado: montoPagado,
                metodo_pago: metodoPago,
                productos: productosEnviar,
                formato: formato
            })
        })
        .then(res => res.json())
        .then(data => {

            if (!data.success) {
                return mostrarAlerta(
                    data.message || "Error al registrar venta."
                );
            }

            configurarBotonesComprobante(data);

            // ============================
            // 🔥 LIMPIEZA CORRECTA POS
            // ============================
            const idConfirmada = POS.ventaActivaId;
            delete POS.ventas[idConfirmada];
            asegurarVentaActiva();

            guardarPOSAhora();   // 🔥 CLAVE


            // 🔥 GUARDAR SNAPSHOT LIMPIO (CRÍTICO)
            if (typeof snapshotPOS === "function") {
                posSaveDebounced(snapshotPOS, 0);
            }

            actualizarProductosStock();

            if (modalVentaExitosa) {
                modalVentaExitosa.show();
            }

            try {
                const sonidoExito =
                    new Audio("/sonidos/success.mp3");
                sonidoExito.play().catch(() => {});
            } catch {}

            renderTodo();
        })
        .catch(() =>
            mostrarAlerta(
                "Error inesperado al registrar la venta."
            )
        );
    }

    // ============================
    // CONTINUAR VENDIENDO
    // ============================
   function continuarVendiendo() {

    if (modalVentaExitosa) {
        modalVentaExitosa.hide();
    }

    // 🔥 NO reutilizar ninguna venta existente
    // 🔥 CREAR UNA NUEVA VENTA
    const id = uidVenta();
    POS.ventas[id] = crearVentaVacia(id);
    POS.ventaActivaId = id;

    POS.ventas[id].metodo_pago = "efectivo";

    // Guardar estado real
    if (typeof snapshotPOS === "function") {
        posSaveDebounced(snapshotPOS, 0);
    }

    // Restaurar UI
    if (typeof restaurarVentaActivaEnUI === "function") {
        restaurarVentaActivaEnUI();
    }

    // 🔥 LIMPIAR ESTADO VISUAL DEL RUC
    const estadoRuc = document.getElementById("estado_ruc");
    if (estadoRuc) {
        estadoRuc.textContent = "";
        estadoRuc.classList.remove("text-success", "text-danger");
    }

    // refrescar correlativo
    if (tipoComprobanteSelect) {
        tipoComprobanteSelect.dispatchEvent(new Event("change"));
    }

    renderTodo();

    // 🔥 foco para vender rápido (ENTER)
    setTimeout(() => {
        document.getElementById("buscar_producto")?.focus();
    }, 150);
}


    // ============================
    // EVENTOS
    // ============================
    btnConfirmar3?.addEventListener("click", registrarVenta);
    btnConfirmarDirecto?.addEventListener("click", registrarVenta);
    btnNuevaVenta?.addEventListener("click", continuarVendiendo);

    // ============================
    // EXPONER
    // ============================
    window.registrarVenta = registrarVenta;
    window.continuarVendiendo = continuarVendiendo;

});
