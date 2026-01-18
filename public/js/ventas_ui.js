// ===============================
// UI / STEPS / CLIENTE / PAGO / VUELTO / SERIE-CORRELATIVO
// ===============================

// ============================
// showStep (GLOBAL)
// ============================
function showStep(n) {
    document.querySelectorAll(".step-panel").forEach(p => p.classList.remove("is-active"));
    document.getElementById("step-" + n)?.classList.add("is-active");

    const v = ventaActiva();
    if (v) v.fase = n;

    if (typeof snapshotPOS === "function") {
        posSaveDebounced(snapshotPOS, 10);
    }
}

// ============================
// HELPERS: estado cliente no guardado
// ============================
function leerEstadoClienteNoGuardado() {
    const iconoSave = document.getElementById("icono-save");
    return iconoSave ? !iconoSave.classList.contains("d-none") : false;
}

// ============================
// Volcar UI -> Venta activa (GLOBAL)
// ============================
function volcarUIaVentaActiva() {
    const v = ventaActiva();
    if (!v) return;

    const documentoInput = document.getElementById("documento");
    const razonInput     = document.getElementById("razon_social");
    const direccionInput = document.getElementById("direccion");
    const hiddenMetodoPago = document.getElementById("metodo_pago");

    if (!v.cliente) {
        v.cliente = { documento: "", razon: "", direccion: "", no_guardado: false };
    }

    v.cliente.documento   = documentoInput?.value || "";
    v.cliente.razon       = razonInput?.value || "";
    v.cliente.direccion   = direccionInput?.value || "";
    v.cliente.no_guardado = leerEstadoClienteNoGuardado();

    v.metodo_pago = hiddenMetodoPago?.value || "";

    if (window.actualizarAliasVentaDesdeCliente) {
        actualizarAliasVentaDesdeCliente();
    }

    if (typeof snapshotPOS === "function") {
        posSaveDebounced(snapshotPOS, 50);
    }

    if (typeof window.renderVentasEsperaPanel === "function") {
        window.renderVentasEsperaPanel();
    }
}

// ============================
// Restaurar venta -> UI (GLOBAL)
// ============================
function restaurarVentaActivaEnUI() {
    const v = ventaActiva();
    if (!v) return;

    const documentoInput = document.getElementById("documento");
    const razonInput     = document.getElementById("razon_social");
    const direccionInput = document.getElementById("direccion");
    const hiddenMetodoPago = document.getElementById("metodo_pago");

    if (documentoInput) documentoInput.value = v.cliente?.documento || "";
    if (razonInput)     razonInput.value     = v.cliente?.razon || "";
    if (direccionInput) direccionInput.value = v.cliente?.direccion || "";

    if (hiddenMetodoPago) hiddenMetodoPago.value = v.metodo_pago || "";

    document.querySelectorAll(".metodo-pago-item").forEach(item => {
        item.classList.toggle("active", (v.metodo_pago || "") === item.dataset.value);
    });

    if (!v.metodo_pago) {
        const efectivo = document.querySelector('.metodo-pago-item[data-value="efectivo"]');
        if (efectivo && hiddenMetodoPago) {
            efectivo.classList.add("active");
            hiddenMetodoPago.value = "efectivo";
            v.metodo_pago = "efectivo";
        }
    }

    showStep(v.fase || 1);
}

// ============================
// Totales / resumen (usa calcularTotal del carrito)
// ============================
function actualizarResumen() {
    if (typeof calcularTotal !== "function") return;

    const { subtotal, igv, total, igvPercent } = calcularTotal();

    const opEl   = document.getElementById("resumen-op-gravadas");
    const igvEl  = document.getElementById("resumen-igv-monto");
    const totEl  = document.getElementById("resumen-total");
    const igvPEl = document.getElementById("resumen-igv-porcentaje");

    if (opEl)   opEl.innerText   = "S/ " + subtotal.toFixed(2);
    if (igvEl)  igvEl.innerText  = "S/ " + igv.toFixed(2);
    if (totEl)  totEl.innerText  = "S/ " + total.toFixed(2);
    if (igvPEl) igvPEl.innerText = igvPercent.toFixed(0) + "%";

    const totalFooter = document.getElementById("total-general-footer");
    if (totalFooter) totalFooter.innerText = total.toFixed(2);

    const opGravadasInput = document.querySelector('[name="op_gravadas"]');
    const totalInput      = document.querySelector('[name="total"]');
    const montoPagadoInput = document.querySelector('[name="monto_pagado"]');

    if (opGravadasInput) opGravadasInput.value = subtotal.toFixed(2);
    if (totalInput) totalInput.value = total.toFixed(2);
    if (montoPagadoInput) montoPagadoInput.value = total.toFixed(2);
}

// ============================
// Botón carrito (step2)
// ============================
function actualizarBotonCarrito() {
    const btnIrStep2 = document.getElementById("btn-ir-step2");
    if (!btnIrStep2) return;

    const v = ventaActiva();
    const cantidad = (v.productos || []).length;

    if (cantidad === 0) {
        btnIrStep2.innerHTML = `0 Continuar`;
        btnIrStep2.disabled = true;
        return;
    }

    const { total } = calcularTotal();

    btnIrStep2.disabled = false;
    btnIrStep2.innerHTML = `
        <span class="badge bg-dark me-2">${cantidad}</span>
        <span class="flex-grow-1 text-start">Continuar</span>
        <span class="fw-semibold">S/ ${total.toFixed(2)}</span>
        <i class="fas fa-arrow-right ms-2"></i>
    `;
}

// ============================
// Preparar fase 3: PAGADO (vuelto)
// ============================
function prepararFase3() {
    const inputTotalVenta = document.getElementById("vuelto-total-venta");
    const inputPaga       = document.getElementById("vuelto-paga");
    const inputVuelto     = document.getElementById("vuelto-mostrar");

    const { total } = calcularTotal();

    if (inputTotalVenta) inputTotalVenta.value = total.toFixed(2);
    if (inputPaga) inputPaga.value = "";
    if (inputVuelto) inputVuelto.value = "";
}

// ============================
// Preparar fase 3: CRÉDITO (adelanto)
// ============================
function prepararFase3Credito() {
    const inputTotalVenta = document.getElementById("vuelto-total-venta");
    const inputPaga       = document.getElementById("vuelto-paga");
    const inputVuelto     = document.getElementById("vuelto-mostrar");

    const { total } = calcularTotal();

    if (inputTotalVenta) inputTotalVenta.value = total.toFixed(2);

    if (inputPaga) {
        inputPaga.value = "";
        inputPaga.placeholder = "Ingrese adelanto";
    }

    if (inputVuelto) inputVuelto.value = "";
}

// ============================
function activarEfectivoPorDefecto(items, hiddenMetodoPago) {
    items.forEach(i => {
        i.classList.remove("active");
        if (i.dataset.value === "efectivo") {
            i.classList.add("active");
        }
    });

    if (hiddenMetodoPago) {
        hiddenMetodoPago.value = "efectivo";
    }
}
function actualizarBotonesSegunMetodoPagado() {
    const estado = document.getElementById("estado_pago")?.value?.toLowerCase();
    const metodo = document.getElementById("metodo_pago")?.value;

    const btnIrStep3 = document.getElementById("btn-ir-step3");
    const btnConfirmarDirecto = document.getElementById("btn-confirmar-venta-directo");

    if (estado !== "pagado") return;

    if (metodo === "efectivo") {
        if (btnIrStep3) btnIrStep3.style.display = "";
        if (btnConfirmarDirecto) btnConfirmarDirecto.style.display = "none";
    } else if (metodo) {
        if (btnIrStep3) btnIrStep3.style.display = "none";
        if (btnConfirmarDirecto) btnConfirmarDirecto.style.display = "block";
    }
}


// Estado de venta: dinámica pago
// ============================
function manejarEstadoVenta() {
    const estadoPagoSelect = document.getElementById("estado_pago");
    const hiddenMetodoPago = document.getElementById("metodo_pago");
    const items = document.querySelectorAll(".metodo-pago-item");
    const btnIrStep3 = document.getElementById("btn-ir-step3");
    const btnConfirmarDirecto = document.getElementById("btn-confirmar-venta-directo");

    const estado = (estadoPagoSelect?.value || "pagado").toLowerCase();

    const labelVuelto = Array.from(
        document.querySelectorAll("label.form-label")
        ).find(l => l.textContent.trim().toLowerCase() === "vuelto");


    items.forEach(i => i.classList.remove("d-none", "active"));

    if (btnIrStep3) {
        btnIrStep3.style.display = "";
        btnIrStep3.innerHTML = `Continuar venta <i class="fas fa-arrow-right ms-2"></i>`;
    }

    if (btnConfirmarDirecto) btnConfirmarDirecto.style.display = "none";

    if (hiddenMetodoPago) hiddenMetodoPago.value = "";

    // 🟡 PENDIENTE
    if (estado === "pendiente") {
        items.forEach(i => {
            if (i.dataset.value !== "otro") i.classList.add("d-none");
            else i.classList.add("active");
        });

        if (hiddenMetodoPago) hiddenMetodoPago.value = "otro";
        if (btnIrStep3) btnIrStep3.style.display = "none";
        // 🔥 MOSTRAR TEXTO "Vuelto"
        if (labelVuelto) {
            labelVuelto.classList.remove("d-none");
        }

        if (btnConfirmarDirecto) btnConfirmarDirecto.style.display = "block";
        return;
    }

    // 🔵 CRÉDITO
    if (estado === "credito") {

        // ✅ MOSTRAR TODOS LOS MÉTODOS DE PAGO
        items.forEach(i => {
            i.classList.remove("d-none");
            i.classList.remove("active");
        });
        // ✅ efectivo activo por defecto
        activarEfectivoPorDefecto(items, hiddenMetodoPago);
                // ❌ NO forzar método
        

        // botón continuar
        if (btnIrStep3) {
            btnIrStep3.style.display = "";
            btnIrStep3.innerHTML = `Continuar venta <i class="fas fa-arrow-right ms-2"></i>`;
        }
        // 🔥 OCULTAR TEXTO "Vuelto" EN CRÉDITO
        if (labelVuelto) {
            labelVuelto.classList.add("d-none");
        }

        if (btnConfirmarDirecto) btnConfirmarDirecto.style.display = "none";
        return;
    }

    // 🟢 PAGADO (por defecto) -> todo visible

if (estado === "pagado") {

    // mostrar métodos
    items.forEach(i => i.classList.remove("d-none", "active"));

    // efectivo por defecto
    const efectivo = document.querySelector(
        '.metodo-pago-item[data-value="efectivo"]'
    );
    if (efectivo) {
        efectivo.classList.add("active");
        if (hiddenMetodoPago) hiddenMetodoPago.value = "efectivo";
    }

    // mostrar texto vuelto
    if (labelVuelto) {
        labelVuelto.classList.remove("d-none");
    }

    // 🔥 DECIDIR BOTÓN SEGÚN MÉTODO
    actualizarBotonesSegunMetodoPagado();
    return;
}

}

// ============================
// DOM
// ============================
document.addEventListener("DOMContentLoaded", () => {

    // Serie/correlativo
    const tipoComprobanteSelect = document.getElementById("tipo_comprobante");
    const inputSerieCorrelativo = document.getElementById("serie_correlativo");

    if (tipoComprobanteSelect && inputSerieCorrelativo) {
        tipoComprobanteSelect.addEventListener("change", () => {
            fetch(`/ventas/obtener-serie-correlativo?tipo=${tipoComprobanteSelect.value}`)
                .then(res => res.json())
                .then(data => {
                    if (data.serie && data.correlativo != null) {
                        inputSerieCorrelativo.value =
                            `${data.serie}-${String(data.correlativo).padStart(6, "0")}`;
                    }
                })
                .catch(() => console.error("Error al obtener serie y correlativo"));
        });
        tipoComprobanteSelect.dispatchEvent(new Event("change"));
    }

    // Estado pago dinámica
    const estadoPagoSelect = document.getElementById("estado_pago");
    estadoPagoSelect?.addEventListener("change", manejarEstadoVenta);
    manejarEstadoVenta();

    // ============================
    // MÉTODOS DE PAGO - SELECCIÓN ÚNICA
    // ============================
    document.querySelectorAll(".metodo-pago-item").forEach(item => {
        item.addEventListener("click", () => {

            const hiddenMetodoPago = document.getElementById("metodo_pago");

            // 🔥 1. DESACTIVAR TODOS
            document.querySelectorAll(".metodo-pago-item")
                .forEach(i => i.classList.remove("active"));

            // 🔥 2. ACTIVAR SOLO EL CLICKEADO
            item.classList.add("active");

            // 🔥 3. GUARDAR VALOR REAL
            if (hiddenMetodoPago) {
                hiddenMetodoPago.value = item.dataset.value;
            }

            // 🔥 4. AJUSTAR BOTONES SEGÚN ESTADO + MÉTODO
            if (typeof actualizarBotonesSegunMetodoPagado === "function") {
                actualizarBotonesSegunMetodoPagado();
            }
        });
    });

    // Cliente/método pago -> volcar
    const documentoInput = document.getElementById("documento");
    const razonInput     = document.getElementById("razon_social");
    const direccionInput = document.getElementById("direccion");
    const hiddenMetodoPago = document.getElementById("metodo_pago");

    documentoInput?.addEventListener("input", () => volcarUIaVentaActiva());
    razonInput?.addEventListener("input", () => volcarUIaVentaActiva());
    direccionInput?.addEventListener("input", () => volcarUIaVentaActiva());

    document.querySelectorAll(".metodo-pago-item").forEach(item => {
        item.addEventListener("click", () => {
            document.querySelectorAll(".metodo-pago-item").forEach(i => i.classList.remove("active"));
            item.classList.add("active");
            if (hiddenMetodoPago) hiddenMetodoPago.value = item.dataset.value;
            volcarUIaVentaActiva();
        });
    });

    // Navegación steps
    const btnIrStep2 = document.getElementById("btn-ir-step2");
    const btnVolverStep1 = document.getElementById("btn-volver-step1") || document.getElementById("btn-volver-carrito");
    const btnIrStep3 = document.getElementById("btn-ir-step3");
    const btnVolverStep2 = document.getElementById("btn-volver-step2") || document.getElementById("btn-vuelto-atras");

    btnIrStep2?.addEventListener("click", () => {
        const v = ventaActiva();
        if (!v.productos.length) return mostrarAlerta("Agrega al menos un producto antes de continuar.");
        // 🔥 VALIDAR STOCK ANTES DE CONTINUAR
        if (!validarStockVentaActiva()) {
            return; // 🚫 no avanzar
        }
        showStep(2);
    });

    btnVolverStep1?.addEventListener("click", () => showStep(1));
    btnVolverStep2?.addEventListener("click", () => showStep(2));

    btnIrStep3?.addEventListener("click", (e) => {
        e.preventDefault();

        volcarUIaVentaActiva();

        const v = ventaActiva();
        const estado = (estadoPagoSelect?.value || "pagado").toLowerCase();

        const documento  = (v.cliente?.documento || "").trim();
        const razon      = (v.cliente?.razon || "").trim();
        const noGuardado = !!v.cliente?.no_guardado;
        const metodo     = (v.metodo_pago || "").trim();

        if (!documento || !razon) {
            Swal.fire("Cliente requerido", "Debes ingresar el cliente.", "warning");
            return;
        }

        if (noGuardado) {
            Swal.fire("Cliente no guardado", "Debes guardar el cliente.", "warning");
            return;
        }

        // pendiente -> registrar directo (si existe)
        if (estado === "pendiente") {
            if (typeof window.registrarVenta === "function") window.registrarVenta();
            return;
        }

        // crédito -> fase 3 con adelanto
        if (estado === "credito") {
            prepararFase3Credito();
            showStep(3);
            return;
        }

        // pagado -> requiere método
        if (!metodo) {
            Swal.fire("Método de pago", "Selecciona un método de pago.", "warning");
            return;
        }

        if (!validarStockVentaActiva()) {
            return;
        }

        prepararFase3();
        showStep(3);
    });

    // Vuelto / saldo
    const inputTotalVenta = document.getElementById("vuelto-total-venta");
    const inputPaga       = document.getElementById("vuelto-paga");
    const inputVuelto     = document.getElementById("vuelto-mostrar");

    inputPaga?.addEventListener("input", () => {
        const monto = parseFloat(inputPaga.value || 0);
        const total = parseFloat(inputTotalVenta?.value || 0);
        const estado = (estadoPagoSelect?.value || "pagado").toLowerCase();

        if (estado === "credito") {
            let saldo = total - monto;
            if (saldo < 0) saldo = 0;
            if (inputVuelto) inputVuelto.value = `Saldo pendiente: S/ ${saldo.toFixed(2)}`;
            return;
        }

        let vuelto = monto - total;
        if (vuelto < 0) vuelto = 0;
        if (inputVuelto) inputVuelto.value = `S/ ${vuelto.toFixed(2)}`;
    });

    // Inicial UI
    actualizarResumen();
    actualizarBotonCarrito();

});

// ============================
// EXPONER UI (OBLIGATORIO)
// ============================
window.showStep = showStep;
window.volcarUIaVentaActiva = volcarUIaVentaActiva;
window.restaurarVentaActivaEnUI = restaurarVentaActivaEnUI;
window.actualizarResumen = actualizarResumen;
window.actualizarBotonCarrito = actualizarBotonCarrito;
window.manejarEstadoVenta = manejarEstadoVenta;
window.prepararFase3 = prepararFase3;
window.prepararFase3Credito = prepararFase3Credito;
