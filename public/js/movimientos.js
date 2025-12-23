document.addEventListener('DOMContentLoaded', () => {

    const panel = document.getElementById('offcanvasDetalle');
    const contenido = document.getElementById('detalleContenido');
    if (!panel || !contenido) return;

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(panel);

    document.addEventListener('click', async (e) => {

        const row = e.target.closest('.mov-row');
        if (!row) return;

        const ventaId = row.dataset.refId;
        const tipo = row.dataset.refTipo;
        if (tipo !== 'venta') return;

        offcanvas.show();
        contenido.innerHTML = `<div class="text-muted">Cargando...</div>`;

        try {
            const res = await fetch(`/ventas/${ventaId}/detalle`);
            const v = await res.json();

            const estado = v.estado; // pagado | pendiente | credito
            const saldo  = estado === 'credito' ? Number(v.saldo || 0) : 0;
            // 🔥 ESTE ES EL MONTO REAL A COBRAR
            const montoCobrar = estado === 'credito'
            ? saldo
            : Number(v.total);

            // guardar total para vuelto
            window.__venta_total = Number(v.total);
            // 🔥 ESTE ES EL QUE USARÁ EL PANEL DE COBRO
            window.__venta_total = montoCobrar;

            contenido.innerHTML = `
            <!-- ================= DETALLE ================= -->
            <div id="panel-detalle">

                <div class="card detalle-card mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">${v.tipo} • Valor total</span>
                        <div id="estadoVenta"></div>
                    </div>

                    <div class="detalle-total">
                        S/ ${Number(v.total).toFixed(2)}
                    </div>

                    ${
                        estado === 'credito'
                        ? `<div class="text-danger fw-bold mt-1">
                            Saldo pendiente: S/ ${saldo.toFixed(2)}
                           </div>`
                        : ''
                    }

                    <hr>

                    <div class="detalle-item">
                        <i class="far fa-calendar"></i>
                        <span>Fecha y hora</span>
                        <strong>${v.fecha_formato}</strong>
                    </div>

                    <div class="detalle-item">
                        <i class="far fa-credit-card"></i>
                        <span>Método de pago</span>
                        <strong>${v.metodo_pago ?? '—'}</strong>
                    </div>

                    <div class="detalle-item">
                        <i class="far fa-user"></i>
                        <span>Cliente</span>
                        <strong>${v.cliente}</strong>
                    </div>

                    <div class="detalle-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Ganancia</span>
                        <strong class="text-success">
                            S/ ${Number(v.ganancia).toFixed(2)}
                        </strong>
                    </div>
                </div>

                <h6 class="mt-4 fw-bold">Listado de productos</h6>

                <div class="listado-productos">
                    ${v.productos.map(p => `
                        <div class="producto-item-pro">
                            <img src="${p.imagen}" class="producto-img">
                            <div class="producto-info">
                                <div class="producto-nombre">${p.nombre}</div>
                                ${p.descripcion ? `<div class="producto-desc">${p.descripcion}</div>` : ''}
                                <div class="producto-cantidad">${p.cantidad_txt}</div>
                            </div>
                            <div class="producto-precio">
                                S/ ${Number(p.subtotal).toFixed(2)}
                            </div>
                        </div>
                    `).join('')}
                </div>

                <!-- ================= BOTONES ================= -->
                <div class="acciones-detalle sticky-actions">

                    ${
                        (estado === 'pendiente' || estado === 'credito')
                        ? `
                            <button class="accion-btn warning"
                                    onclick="mostrarCobro('${estado}', ${saldo})">
                                <i class="fas fa-cash-register"></i>
                                <span>Cobrar</span>
                            </button>
                        `
                        : ''
                    }

                    <button class="accion-btn dark">
                        <i class="fas fa-print"></i>
                        <span>Imprimir</span>
                    </button>

                    ${
                        estado === 'pagado'
                        ? `
                            <button class="accion-btn">
                                <i class="fas fa-receipt"></i>
                                <span>Comprobante</span>
                            </button>

                            <button class="accion-btn">
                                <i class="fas fa-pen"></i>
                                <span>Editar</span>
                            </button>

                            <button class="accion-btn danger">
                                <i class="fas fa-trash"></i>
                                <span>Eliminar</span>
                            </button>
                        `
                        : ''
                    }
                </div>
            </div>

            <!-- ================= COBRAR PENDIENTE ================= -->
            <div id="panel-cobro" style="display:none">
                <h6 class="fw-bold mt-3">Cobrar venta</h6>

                <div class="fw-bold mb-2">
                    Total a pagar: S/ ${montoCobrar.toFixed(2)}
                </div>

                <label class="form-label">Monto recibido</label>

                <input type="number"
                    id="cc_monto"
                    class="form-control mb-2"
                    value="0"
                    min="0"
                    step="0.01">

                <select id="cc_metodo" class="form-select mb-2">
                    <option value="">Seleccione método</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                    <option value="transferencia">Transferencia</option>
                </select>

                <div class="fw-bold text-success mt-2">
                    Vuelto: S/ <span id="cc_vuelto">0.00</span>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="button"
                            class="accion-btn"
                            onclick="volverDetalle()">
                        Volver
                    </button>

                    <button type="button"
                            class="accion-btn success"
                            onclick="confirmarCobro(${v.id}, '${estado}')">
                        Registrar pago
                    </button>
                </div>

            </div>
            `;

            // ================= BADGE ESTADO =================
            const estadoEl = document.getElementById('estadoVenta');

            if (estado === 'pagado') {
                estadoEl.innerHTML = `<span class="badge bg-success">Pagado</span>`;
            } else if (estado === 'pendiente') {
                estadoEl.innerHTML = `<span class="badge bg-warning text-dark">Pendiente</span>`;
            } else {
                estadoEl.innerHTML = `<span class="badge bg-danger">Crédito</span>`;
            }

        } catch (err) {
            contenido.innerHTML = `<div class="text-danger">Error al cargar detalle</div>`;
        }
    });
});

/* ================= FUNCIONES GLOBALES ================= */

function mostrarCobro() {
    document.getElementById('panel-detalle').style.display = 'none';
    document.getElementById('panel-cobro').style.display = 'block';
}

function volverDetalle() {
    document.getElementById('panel-cobro').style.display = 'none';
    document.getElementById('panel-detalle').style.display = 'block';
}

// cálculo de vuelto SOLO para pendiente
document.addEventListener('input', (e) => {
    if (e.target.id !== 'cc_monto') return;

    const vueltoEl = document.getElementById('cc_vuelto');
    if (!vueltoEl) return;

    const recibido = Number(e.target.value || 0);
    const total = window.__venta_total || 0;

    const vuelto = recibido - total;
    vueltoEl.innerText = vuelto > 0 ? vuelto.toFixed(2) : '0.00';
});

async function confirmarCobro(ventaId, estado) {

    const monto  = parseFloat(document.getElementById('cc_monto').value);
    const metodo = document.getElementById('cc_metodo').value;

    if (!monto || monto <= 0) {
        alert('Ingrese un monto válido');
        return;
    }

    if (!metodo) {
        alert('Seleccione método');
        return;
    }

    // 🔥 ENDPOINT CORRECTO SEGÚN ESTADO
    const url = estado === 'credito'
        ? `/ventas/${ventaId}/pagar-credito`
        : `/ventas/${ventaId}/cerrar-pendiente`;

    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            monto_pagado: monto,
            metodo_pago: metodo
        })
    });

    const data = await res.json();

    if (data.success) {
        location.reload();
    } else {
        alert(data.message || 'Error al cobrar');
    }
}
