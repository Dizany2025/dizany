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
            const res = await fetch(`/ventas/${ventaId}`);
            const v = await res.json();

            contenido.innerHTML = `
                
                <div class="card detalle-card mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"> ${v.tipo} • Valor total</span>
                        <div id="estadoVenta"></div>
                    </div>

                    <div class="detalle-total">S/ ${Number(v.total).toFixed(2)}</div>

                    <hr>

                    <div class="detalle-item">
                        <i class="far fa-calendar"></i>
                        <span>Fecha y hora</span>
                        <strong>${v.fecha_formato}</strong>
                    </div>

                    <div class="detalle-item">
                        <i class="far fa-credit-card"></i>
                        <span>Método de pago</span>
                        <strong>${v.metodo_pago}</strong>
                    </div>

                    <div class="detalle-item">
                        <i class="far fa-user"></i>
                        <span>Cliente</span>
                        <strong>${v.cliente}</strong>
                    </div>

                    <div class="detalle-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Ganancia</span>
                        <strong class="text-success">S/ ${Number(v.ganancia).toFixed(2)}</strong>
                    </div>
                </div>

                <h6 class="mt-4 fw-bold">Listado de productos</h6>

                <div class="listado-productos">
                    ${v.productos.map(p => `
                        <div class="producto-item-pro">
                            <img src="${p.imagen}" class="producto-img">

                            <div class="producto-info">
                                <div class="producto-nombre">${p.nombre}</div>

                                ${p.descripcion
                                    ? `<div class="producto-desc">${p.descripcion}</div>`
                                    : ''
                                }

                                <div class="producto-cantidad">${p.cantidad_txt}</div>
                            </div>

                            <div class="producto-precio">
                                S/ ${Number(p.subtotal).toFixed(2)}
                            </div>
                        </div>
                    `).join('')}
                </div>

                <div class="acciones-detalle sticky-actions">
                    <button class="accion-btn dark">
                        <i class="fas fa-print"></i>
                        <span>Imprimir</span>
                    </button>

                    <button class="accion-btn">
                        <i class="fas fa-receipt"></i>
                        <span>Comprobante</span>
                    </button>

                    <a href="#" class="accion-btn">
                        <i class="fas fa-pen"></i>
                        <span>Editar</span>
                    </a>

                    <button class="accion-btn danger">
                        <i class="fas fa-trash"></i>
                        <span>Eliminar</span>
                    </button>
                </div>
            `;
            
            // ================= ESTADO (MUY IMPORTANTE) =================
            const estadoEl = document.getElementById('estadoVenta');

            if (v.estado === 'pagado') {
                estadoEl.innerHTML = `
                    <span class="badge bg-success px-3 py-1">
                        Pagado
                    </span>
                `;
            } else {
                estadoEl.innerHTML = `
                    <span class="badge bg-warning text-dark px-3 py-1">
                        Pendiente
                    </span>
                `;
            }
            

        } catch (err) {
            contenido.innerHTML = `<div class="text-danger">Error al cargar detalle</div>`;
        }
    });
});
