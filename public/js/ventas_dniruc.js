document.addEventListener('DOMContentLoaded', function () {

    const token = "65380308035e6817f4baf2770fd241dfde303c06eec3376cc7694c53bcc8e83d";

    // Inputs
    const inputDocumento = document.getElementById('documento');
    const inputRazon = document.getElementById('razon_social');
    const inputDireccion = document.getElementById('direccion');
    const estadoRUC = document.getElementById('estado_ruc');

    // Íconos del botón de acción
    const btnAccion = document.getElementById('btn-cliente-accion');
    const iconoPlus = document.getElementById('icono-plus');
    const iconoSave = document.getElementById('icono-save');

    // -------------------------------
    // FUNCIONES VISUALES
    // -------------------------------
    function mostrarIconoGuardar() {
        iconoPlus.classList.add("d-none");
        iconoSave.classList.remove("d-none");

        iconoSave.classList.add("icono-animado");
        setTimeout(() => iconoSave.classList.remove("icono-animado"), 600);
    }

    function mostrarIconoAgregar() {
        iconoSave.classList.add("d-none");
        iconoPlus.classList.remove("d-none");

        iconoPlus.classList.add("icono-animado");
        setTimeout(() => iconoPlus.classList.remove("icono-animado"), 600);
    }

    // -------------------------------
    // CONSULTA A BD
    // -------------------------------
    function buscarEnBD(dniRuc) {
        return fetch(`/buscar-cliente/${dniRuc}`)
            .then(r => r.json());
    }

    // -------------------------------
    // CONSULTA A API PERÚ (DNI/RUC)
    // -------------------------------
    function consultarDNI(dni) {
        return fetch(`https://apiperu.dev/api/dni/${dni}`, {
            headers: { Authorization: `Bearer ${token}` }
        }).then(r => r.json());
    }

    function consultarRUC(ruc) {
        return fetch(`https://apiperu.dev/api/ruc/${ruc}`, {
            headers: { Authorization: `Bearer ${token}` }
        }).then(r => r.json());
    }

    // -------------------------------
    // EVENTO PRINCIPAL
    // -------------------------------
    inputDocumento.addEventListener('input', () => {

        const valor = inputDocumento.value.trim();

        if (valor.length < 8) {
            estadoRUC.textContent = "";
            inputRazon.value = "";
            inputDireccion.value = "";
            mostrarIconoAgregar();
            return;
        }

        // 1️⃣ Buscar si existe en BD
        buscarEnBD(valor).then(res => {

            if (res.encontrado) {
                // CLIENTE EXISTE EN BD
                inputRazon.value = res.nombre;
                inputDireccion.value = res.direccion;
                estadoRUC.textContent = "";
                mostrarIconoAgregar();
                return;
            }

            // 2️⃣ NO EXISTE EN BD → Consultar API
            if (valor.length === 8) {
                consultarDNI(valor).then(data => {
                    if (data.success) {
                        inputRazon.value =
                          `${data.data.nombres} ${data.data.apellido_paterno} ${data.data.apellido_materno}`;
                        inputDireccion.value = "No disponible";
                        estadoRUC.textContent = "";
                        mostrarIconoGuardar();
                    } else {
                        inputRazon.value = "";
                        inputDireccion.value = "";
                        estadoRUC.textContent = "❌ DNI no encontrado";
                        mostrarIconoGuardar();
                    }
                });
            }

            if (valor.length === 11) {
                consultarRUC(valor).then(data => {
                    if (data.success) {
                        inputRazon.value = data.data.nombre_o_razon_social;
                        inputDireccion.value = data.data.direccion || "Sin dirección";
                        estadoRUC.textContent = `✔️ ${data.data.estado}`;
                        mostrarIconoGuardar();
                    } else {
                        inputRazon.value = "";
                        inputDireccion.value = "";
                        estadoRUC.textContent = "❌ RUC no encontrado";
                        mostrarIconoGuardar();
                    }
                });
            }

        });

    });

    // -------------------------------
    // GUARDAR CLIENTE EN BD
    // -------------------------------
    const modalCliente = new bootstrap.Modal(document.getElementById('clientModal'));
        btnAccion.addEventListener("click", () => {

        // Si el icono visible es SAVE → Guardar cliente
        if (!iconoSave.classList.contains("d-none")) {

            const dniRuc = inputDocumento.value.trim();
            const razon = inputRazon.value.trim();
            const direccion = inputDireccion.value.trim();

            if (!dniRuc || !razon) {
                Swal.fire("Error", "No hay datos para guardar", "error");
                return;
            }

            fetch('/guardar-cliente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    dni_ruc: dniRuc,
                    razon_social: razon,
                    direccion: direccion
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.exito) {
                    Swal.fire({
                        icon: "success",
                        title: "Cliente guardado",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    mostrarIconoAgregar();
                } else {
                    Swal.fire("Error", res.mensaje, "error");
                }
            });

            return; // 🚀 IMPORTANTÍSIMO
        }

        // Si el icono SAVE NO está visible → es ícono PLUS → abrir modal
        modalCliente.show();
    });


});
