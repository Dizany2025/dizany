    const modalEditar = document.getElementById('modalEditarUsuario');
    modalEditar.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const usuario = button.getAttribute('data-usuario');
        const email = button.getAttribute('data-email'); // ✅ nuevo
        const rol = button.getAttribute('data-rol');

        modalEditar.querySelector('#editar-id').value = id;
        modalEditar.querySelector('#editar-nombre').value = nombre;
        modalEditar.querySelector('#editar-usuario').value = usuario;
        modalEditar.querySelector('#editar-email').value = email; // ✅ nuevo
        modalEditar.querySelector('#editar-rol').value = rol;

        modalEditar.querySelector('#formEditarUsuario').action = `/usuarios/${id}`;
    });

    document.addEventListener('DOMContentLoaded', function () {
        const buscador = document.getElementById('buscadorUsuarios');
        const filas = document.querySelectorAll('#tablaUsuarios tbody tr');

        buscador.addEventListener('input', function () {
            const filtro = this.value.toLowerCase();
            filas.forEach(fila => {
                const texto = fila.innerText.toLowerCase();
                fila.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    });
    // cambiar clave
    document.querySelectorAll('.cambiar-clave-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('usuario_id_cambiar_clave').value = this.dataset.id;
            document.getElementById('nombre_usuario_label').textContent = "Usuario: " + this.dataset.nombre;
        });
    });
