document.addEventListener('DOMContentLoaded', function () {

    // 1. BUSCADOR EN TIEMPO REAL
    const buscadorInventario = document.getElementById('buscadorInventario');
    if (buscadorInventario) {
        buscadorInventario.addEventListener('keyup', function () {
            const texto = this.value.toLowerCase();
            document.querySelectorAll('.fila-insumo').forEach(fila => {
                const nombre = fila.cells[1].textContent.toLowerCase();
                fila.style.display = nombre.includes(texto) ? '' : 'none';
            });
        });
    }

    // 1.5 Leemos la URL buscando el parámetro (ej. ?buscar=Lana%20verde)
    const urlParams = new URLSearchParams(window.location.search);
    const terminoBusqueda = urlParams.get('buscar');

    if (terminoBusqueda) {
        // Buscamos el input EXACTO por su ID
        const inputBuscador = document.getElementById('buscadorInventario');
        
        if (inputBuscador) {
            //Escribimos la palabra automáticamente
            inputBuscador.value = terminoBusqueda;
            
            //Simulamos el "keyup" para engañar a tu función original y que filtre la tabla
            inputBuscador.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));

            //EFECTO VISUAL: Resaltamos el buscador con un "glow" usando tu variable de acento
            inputBuscador.style.transition = "box-shadow 0.4s ease, border-color 0.4s ease";
            inputBuscador.style.borderColor = "var(--accent-purple, #9333ea)";
            inputBuscador.style.boxShadow = "0 0 0 5px rgba(147, 51, 234, 0.25)";
            
            // Hacemos que el brillo desaparezca suavemente después de 2 segundos
            setTimeout(() => {
                inputBuscador.style.borderColor = "";
                inputBuscador.style.boxShadow = "";
            }, 2000);
        }
    }

    // 2. SELECT PERSONALIZADO
    function crearSelectPersonalizado(idContenedor) {
        const cont = document.getElementById(idContenedor);
        if (!cont) return null;

        const trigger = cont.querySelector('.custom-select-trigger');
        const valueSpan = cont.querySelector('.custom-select-value');
        const opciones = cont.querySelectorAll('.custom-select-options li');
        const hiddenInput = cont.querySelector('input[type="hidden"]');

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            document.querySelectorAll('.custom-select.abierto').forEach(otro => {
                if (otro !== cont) otro.classList.remove('abierto');
            });
            cont.classList.toggle('abierto');
        });

        opciones.forEach(opcion => {
            opcion.addEventListener('click', function () {
                hiddenInput.value = this.dataset.value;
                valueSpan.textContent = this.textContent;
                valueSpan.classList.remove('placeholder');

                opciones.forEach(o => o.classList.remove('seleccionada'));
                this.classList.add('seleccionada');

                cont.classList.remove('abierto');
                cont.classList.remove('error');

                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        return cont;
    }

    document.addEventListener('click', function (e) {
        document.querySelectorAll('.custom-select.abierto').forEach(cont => {
            if (!cont.contains(e.target)) cont.classList.remove('abierto');
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-select.abierto').forEach(cont => cont.classList.remove('abierto'));
        }
    });

    crearSelectPersonalizado('customCategoria');
    crearSelectPersonalizado('customColorBaston');
    crearSelectPersonalizado('customTamanoBaston');

    // 3. MODAL NUEVO INSUMO
    const selectorNuevo = document.getElementById('selectorCategoria');
    const inputNombre = document.getElementById('inputNombreInsumo');
    const opcionesBaston = document.getElementById('opcionesBaston');
    const selectorColor = document.getElementById('selectorColorBaston');
    const selectorTamano = document.getElementById('selectorTamanoBaston');
    const labelCant = document.getElementById('labelCantidad');
    const labelAlert = document.getElementById('labelAlerta');

    const textosNuevoInsumo = {
        'lana': { cant: 'Cant. de Madejas compradas', alert: 'Avisar cuando queden (Madejas):' },
        'cinta_garza': { cant: 'Cant. de Rollos comprados', alert: 'Avisar cuando queden (Rollos):' },
        'cinta_satin': { cant: 'Cant. de Rollos comprados', alert: 'Avisar cuando queden (Rollos):' },
        'cinta_gross': { cant: 'Cant. de Rollos comprados', alert: 'Avisar cuando queden (Rollos):' },
        'cortina_fiesta': { cant: 'Cant. de Paquetes comprados', alert: 'Avisar cuando queden (Paquetes):' },
        'elastico': { cant: 'Cant. de Piezas (10m) compradas', alert: 'Avisar cuando queden (Piezas):' },
        'cinchos': { cant: 'Cant. de Paquetes (100u) compr.', alert: 'Avisar cuando queden (Paquetes):' },
        'base_baston': { cant: 'Cant. de Bases compradas', alert: 'Avisar cuando queden (Bases):' },
        'unidad_simple': { cant: 'Cant. de Unidades compradas', alert: 'Avisar cuando queden (Unidades):' }
    };

    function actualizarNombreBaston() {
        if (selectorNuevo && selectorNuevo.value === 'base_baston') {
            // 1. Limpiamos el valor para quitarle el 'cm' pegado (ej. de '45cm' a '45')
            let tamanoNumero = selectorTamano.value.replace('cm', '');
            
            // 2. Armamos el string con paréntesis y espacio EXACTAMENTE igual al cotizador
            inputNombre.value = `Base ${selectorColor.value} (${tamanoNumero} cm)`;
        }
    }

    if (selectorNuevo) {
        selectorNuevo.addEventListener('change', function () {
            const cat = this.value;
            if (!textosNuevoInsumo[cat]) return;

            labelCant.textContent = textosNuevoInsumo[cat].cant;
            labelAlert.textContent = textosNuevoInsumo[cat].alert;

            if (cat === 'base_baston') {
                opcionesBaston.classList.remove('d-none');
                inputNombre.readOnly = true;
                inputNombre.classList.add('bg-secondary', 'bg-opacity-10', 'fw-bold', 'text-secondary');
                actualizarNombreBaston();

            } else if (cat === 'cinta_garza' || cat === 'cinta_satin' || cat === 'cinta_gross') {
                // NUEVO: Estandarización estricta para Cintas
                opcionesBaston.classList.add('d-none');
                inputNombre.readOnly = false;
                inputNombre.classList.remove('bg-secondary', 'bg-opacity-10', 'fw-bold', 'text-primary');

                // Inyectamos el prefijo automáticamente
                let prefijo = '';
                if (cat === 'cinta_garza') prefijo = 'Cinta Garza ';
                if (cat === 'cinta_satin') prefijo = 'Cinta Satín ';
                if (cat === 'cinta_gross') prefijo = 'Cinta Gross ';

                inputNombre.value = prefijo;

                // Ponemos el cursor al final para que la usuaria solo escriba el color
                setTimeout(() => inputNombre.focus(), 100);

            } else {
                opcionesBaston.classList.add('d-none');
                inputNombre.readOnly = false;
                inputNombre.classList.remove('bg-secondary', 'bg-opacity-10', 'fw-bold', 'text-primary');
                inputNombre.value = '';
            }
        });
    }

    if (selectorColor) selectorColor.addEventListener('change', actualizarNombreBaston);
    if (selectorTamano) selectorTamano.addEventListener('change', actualizarNombreBaston);

    const formNuevoInsumo = document.querySelector('#modalNuevoInsumo form');
    if (formNuevoInsumo && selectorNuevo) {
        formNuevoInsumo.addEventListener('submit', function (e) {
            const cat = selectorNuevo.value;

            if (!cat) {
                e.preventDefault();
                const cont = document.getElementById('customCategoria');
                cont.classList.add('error');
                cont.querySelector('.custom-select-trigger').focus();
            } else {
                // BLINDAJE DE SEGURIDAD AL GUARDAR
                // Si la usuaria borró el prefijo "Cinta..." por error, el sistema lo repara antes de guardar.
                let nombreActual = inputNombre.value.trim();

                if (cat === 'cinta_garza' && !nombreActual.toLowerCase().includes('garza')) {
                    inputNombre.value = 'Cinta Garza ' + nombreActual.replace(/cinta/gi, '').trim();
                } else if (cat === 'cinta_satin' && !nombreActual.toLowerCase().includes('satín') && !nombreActual.toLowerCase().includes('satin')) {
                    inputNombre.value = 'Cinta Satín ' + nombreActual.replace(/cinta/gi, '').trim();
                } else if (cat === 'cinta_gross' && !nombreActual.toLowerCase().includes('gross')) {
                    inputNombre.value = 'Cinta Gross ' + nombreActual.replace(/cinta/gi, '').trim();
                }
            }
        });
    }

    // 4. MODAL AJUSTE DE STOCK
    const textosEntrada = {
        'lana': '¿Cuántas MADEJAS vas a ingresar?:',
        'cinta_garza': '¿Cuántos ROLLOS (50yd) vas a ingresar?:',
        'cinta_satin': '¿Cuántos ROLLOS (20yd) vas a ingresar?:',
        'cortina_fiesta':'¿Cuántos PAQUETES (4u) vas a ingresar?:',
        'elastico': '¿Cuántas PIEZAS (10m) vas a ingresar?:',
        'cinchos': '¿Cuántos PAQUETES (100u) vas a ingresar?:',
        'base_baston': '¿Cuántas BASES vas a ingresar?:',
        'unidad_simple': '¿Cuántas UNIDADES vas a ingresar?:'
    };

    const textosSalida = {
        'lana': '¿Cuántos GRAMOS vas a retirar (Desperdicio)?:',
        'cinta_garza': '¿Cuántos METROS vas a retirar (Desperdicio)?:',
        'cinta_satin': '¿Cuántos METROS vas a retirar (Desperdicio)?:',
        'cortina_fiesta':'¿Cuántas CORTINAS SUELTAS vas a retirar?:',
        'elastico': '¿Cuántos METROS vas a retirar?:',
        'cinchos': '¿Cuántos CINCHOS SUELTOS vas a retirar?:',
        'base_baston': '¿Cuántas BASES vas a retirar?:',
        'unidad_simple': '¿Cuántas UNIDADES vas a retirar?:'
    };

    document.querySelectorAll('.btn-ajuste').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;
            const categoria = this.dataset.categoria;
            const operacion = this.dataset.operacion;

            document.getElementById('formAjusteStock').action = `/insumos/${id}/ajustar`;
            document.getElementById('tipoMovimiento').value = operacion;
            document.getElementById('subtituloAjuste').innerHTML = `Material: <strong>${nombre}</strong>`;

            if (operacion === 'entrada') {
                document.getElementById('tituloAjuste').innerText = '➕ Registrar Entrada';
                document.getElementById('labelMover').innerText = textosEntrada[categoria] || 'Cantidad:';
                document.getElementById('btnGuardarAjuste').className = 'btn btn-success btn-sm fw-bold px-3';
            } else {
                document.getElementById('tituloAjuste').innerText = '➖ Registrar Salida';
                document.getElementById('labelMover').innerText = textosSalida[categoria] || 'Cantidad:';
                document.getElementById('btnGuardarAjuste').className = 'btn btn-danger btn-sm fw-bold px-3';
            }
        });
    });

    // 5. MODAL EDITAR NOMBRE
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('formEditarInsumo').action = `/insumos/${this.dataset.id}`;
            document.getElementById('inputEditarNombre').value = this.dataset.nombre;
        });
    });

    // 6. OVERLAY DE CONFIRMACIÓN DE BORRADO
    const overlay = document.getElementById('overlayConfirmarBorrado');
    const formBorrado = document.getElementById('formConfirmarBorrado');
    const nombreABorrar = document.getElementById('nombreInsumoABorrar');
    const btnCancelarBorrado = document.getElementById('btnCancelarBorrado');

    if (overlay) {
        document.querySelectorAll('.btn-confirmar-borrado').forEach(btn => {
            btn.addEventListener('click', function () {
                formBorrado.action = this.dataset.action;
                nombreABorrar.textContent = this.dataset.nombre;
                overlay.classList.add('activo');
            });
        });

        btnCancelarBorrado.addEventListener('click', () => {
            overlay.classList.remove('activo');
        });

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('activo');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') overlay.classList.remove('activo');
        });
    }

});