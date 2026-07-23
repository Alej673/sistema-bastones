// Esperar a que el DOM cargue
document.addEventListener('DOMContentLoaded', () => {

    // ==========================================================
    // 1. INICIALIZACIÓN DE SELECT2 (buscador de clientes)
    //    Esto debe ejecutarse SIEMPRE al cargar la página,
    //    NO dentro del click de otro botón.
    // ==========================================================
    const $buscarCliente = $('#buscar_cliente_ajax');

    // Evita doble inicialización: si ya existe una instancia de Select2
    // sobre este elemento (por hot-reload, doble carga del script, u otro
    // script que también lo inicialice), la destruimos primero.
    if ($buscarCliente.hasClass('select2-hidden-accessible')) {
        $buscarCliente.select2('destroy');
    }

    $buscarCliente.select2({
        placeholder: 'Buscar cliente o escribir término...',
        allowClear: true,
        tags: true, // Permite escribir libremente aunque no exista en resultados
        width: '100%', // Evita problemas de ancho al usarlo dentro de input-group
        dropdownParent: $(document.body), // Evita que el dropdown se vea afectado por overflow/position de los contenedores padre (.card, .input-group, etc.)
        ajax: {
            url: '/buscar-clientes-historial',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    // Evitar que el navegador muestre su propio autocompletado nativo
    // (esa "caja blanca sin estilo" que tapa el dropdown de Select2 son
    // sugerencias del navegador, no de Select2).
    $buscarCliente.on('select2:open', function () {
        // Pequeño timeout: el campo de búsqueda se crea justo al abrir el dropdown
        setTimeout(function () {
            document.querySelectorAll('.select2-search__field').forEach(function (input) {
                input.setAttribute('autocomplete', 'off');
                // Algunos navegadores ignoran autocomplete="off" si reconocen el name;
                // con un name aleatorio no encuentran historial que sugerir.
                input.setAttribute('name', 'buscar_cliente_no_autofill_' + Math.random().toString(36).slice(2));
            });
        }, 0);
    });

    // ==========================================================
    // 2. CONFIGURACIÓN CSRF PARA PETICIONES FETCH
    // ==========================================================
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ==========================================================
    // 3. MANEJO DE CAMBIO DE ESTADO DE PEDIDOS
    // ==========================================================
    const botonesEstado = document.querySelectorAll('.cambiar-estado');

    botonesEstado.forEach(boton => {
        boton.addEventListener('click', async function (e) {
            e.preventDefault();

            const pedidoId = this.getAttribute('data-id');
            const nuevoEstado = this.getAttribute('data-estado');
            const textoEstado = this.innerText;

            // Advertencia de seguridad si se va a descontar inventario
            if (nuevoEstado === 'realizado') {
                const confirmacion = await Swal.fire({
                    title: '¿Confirmar Entrega?',
                    text: 'Esta acción descontará físicamente los materiales del inventario general. ¿Deseas continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a', // Verde oscuro limpio (éxito)
                    cancelButtonColor: '#9ca3af',  // Gris neutro pastel
                    confirmButtonText: 'Sí, descontar stock',
                    cancelButtonText: 'Cancelar',
                    background: '#ffffff',         // Fondo blanco
                    color: 'var(--text-main, #3b0764)', // Texto principal oscuro
                    customClass: { popup: 'border border-light shadow-sm' }
                });

                if (!confirmacion.isConfirmed) return; // Si cancela, detenemos el proceso
            }

            // Ejecutar petición AJAX al backend
            try {
                Swal.showLoading(); // Mostrar spinner de carga

                const response = await fetch(`/pedidos/${pedidoId}/estado`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ estado: nuevoEstado })
                });

                const data = await response.json();

                if (data.success) {
                    let alertaHtml = '';
                    let mostrarAlerta = false;

                    // Bloque Rojo: No encontrados (Error grave) - Cajita rojo pastel
                    if (data.no_encontrados && data.no_encontrados.length > 0) {
                        alertaHtml += `
                            <div style="background-color: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                                <strong style="font-size: 15px;"><i class="fa-solid fa-triangle-exclamation"></i> No Descontados (No existen):</strong>
                                <ul style="text-align: left; margin-top: 8px; font-size: 13px; margin-bottom: 0;">
                                    ${data.no_encontrados.map(item => `<li>${item}</li>`).join('')}
                                </ul>
                            </div>`;
                        mostrarAlerta = true;
                    }

                    // Bloque Amarillo/Naranja: Quedaron en negativo (Advertencia) - Cajita ámbar pastel
                    if (data.en_negativo && data.en_negativo.length > 0) {
                        alertaHtml += `
                            <div style="background-color: #fef3c7; border: 1px solid #fde68a; color: #d97706; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                                <strong style="font-size: 15px;"><i class="fa-solid fa-boxes-stacked"></i> Stock en Negativo (Deuda):</strong>
                                <ul style="text-align: left; margin-top: 8px; font-size: 13px; margin-bottom: 0;">
                                    ${data.en_negativo.map(item => `<li>${item}</li>`).join('')}
                                </ul>
                            </div>`;
                        mostrarAlerta = true;
                    }

                    if (mostrarAlerta) {
                        await Swal.fire({
                            icon: 'warning',
                            title: '¡Pedido Realizado con Observaciones!',
                            html: `<div style="color: var(--text-main, #3b0764); font-size: 15px; margin-bottom: 15px; font-weight: 500;">
                                    El estado se actualizó, pero revisa el inventario:
                                </div>
                                ${alertaHtml}
                                <div style="color: var(--text-muted, #7e57c2); font-size: 13px; margin-top: 15px;">
                                    Puedes corregir esto más tarde en el Dashboard o Inventario.
                                </div>`,
                            background: '#ffffff',
                            color: 'var(--text-main, #3b0764)',
                            confirmButtonColor: 'var(--accent-purple, #9333ea)',
                            customClass: { popup: 'border border-light shadow-sm' }
                        });
                    } else {
                        await Swal.fire({
                            title: '¡Actualizado!',
                            text: `El pedido ahora está marcado como: ${textoEstado}`,
                            icon: 'success',
                            confirmButtonColor: 'var(--accent-purple, #9333ea)',
                            background: '#ffffff',
                            color: 'var(--text-main, #3b0764)',
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'border border-light shadow-sm' }
                        });
                    }

                    // Recargar la página para actualizar KPIs y colores de tabla
                    window.location.reload();

                } else {
                    throw new Error(data.message);
                }

            } catch (error) {
                Swal.fire({
                    title: 'Error de Sistema',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#dc2626', // Rojo oscuro para el botón de error
                    background: '#ffffff',
                    color: 'var(--text-main, #3b0764)',
                    customClass: { popup: 'border border-light shadow-sm' }
                });
            }
        });
    });

    // ==========================================
    // LÓGICA DEL MODAL DE VISTA RÁPIDA (OJO)
    // ==========================================
    const modalElement = document.getElementById('modalDetallePedido');
    if (modalElement) {
        const modalDetalle = new bootstrap.Modal(modalElement);

        document.querySelectorAll('.btn-ver-detalle').forEach(btn => {
            btn.addEventListener('click', async function() {
                const pedidoId = this.getAttribute('data-id');
                
                // Limpiar modal y mostrar mensaje de carga
                document.getElementById('modal-pedido-id').innerText = '#' + String(pedidoId).padStart(4, '0');
                document.getElementById('modal-loading').style.display = 'block';
                document.getElementById('modal-lista-materiales').style.display = 'none';
                document.getElementById('modal-lista-materiales').innerHTML = '';
                
                // Abrir el modal en pantalla
                modalDetalle.show();

                try {
                    // Consultar la base de datos de forma silenciosa
                    const response = await fetch(`/pedidos/${pedidoId}/detalles`);
                    const data = await response.json();
                    
                    let html = '';
                    if(data.materiales && data.materiales.length > 0) {
                        data.materiales.forEach(mat => {
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: var(--color-fondo-base); color: var(--color-texto); border-color: var(--color-fondo-medio);">
                                ${mat.nombre_material}
                                <span class="badge" style="background-color: var(--color-morado);">${mat.cantidad_requerida}</span>
                            </li>`;
                        });
                    } else {
                        html = `<li class="list-group-item text-center text-muted" style="background-color: var(--color-fondo-base); border-color: var(--color-fondo-medio);">No hay insumos registrados</li>`;
                    }
                    
                    // Inyectar el HTML y ocultar el texto de carga
                    document.getElementById('modal-lista-materiales').innerHTML = html;
                    document.getElementById('modal-loading').style.display = 'none';
                    document.getElementById('modal-lista-materiales').style.display = 'block';

                } catch (error) {
                    document.getElementById('modal-loading').innerText = 'Error al cargar los datos del sistema.';
                }
            });
        });
    }
});