document.addEventListener('DOMContentLoaded', function () {

    // =======================================================
    // 1. LÓGICA DEL SWITCH DUAL (BASTONES VS RÁPIDO)
    // =======================================================
    const switchModo = $('#swModoCotizacion');
    const contenedorBaston = $('#contenedorModoBaston');
    const contenedorRapido = $('#contenedorModoRapido');
    const lblBaston = $('#lblModoBaston');
    const lblRapido = $('#lblModoRapido');

    switchModo.on('change', function () {
        if ($(this).is(':checked')) {
            // Activar Modo Rápido
            contenedorBaston.addClass('d-none');
            contenedorRapido.removeClass('d-none');
            lblBaston.css('color', ''); // Quita el morado
            lblRapido.css('color', 'var(--accent-purple)'); // Pone morado
        } else {
            // Volver a Modo Bastones
            contenedorRapido.addClass('d-none');
            contenedorBaston.removeClass('d-none');
            lblRapido.css('color', '');
            lblBaston.css('color', 'var(--accent-purple)');
        }
    });

    // =======================================================
    // 2. INICIALIZAR EL BUSCADOR BTO (SELECT2) Y AUTOCOMPLETAR
    // =======================================================
    const selectSolicitud = $('#selectSolicitudWebRapida');
    const inputNombre = $('#inputNombreClienteRapido');
    const inputCorreo = $('#inputCorreoClienteRapido');

    selectSolicitud.select2({
        theme: 'bootstrap-5',
        placeholder: '-- Buscar solicitud pendiente --',
        allowClear: true,
        ajax: {
            url: window.KardexConfig.rutas.buscarSolicitudes,
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        // AQUÍ CAPTURAMOS LOS DATOS EXTRA DEL CLIENTE
                        return {
                            id: item.id,
                            text: item.text,
                            nombreCliente: item.nombre,
                            correoCliente: item.user ? item.user.email : ''
                        };
                    })
                };
            }
        }
    });

    // Evento: Cuando se selecciona una solicitud
    selectSolicitud.on('select2:select', function (e) {
        var data = e.params.data;
        // Llenamos los inputs con la info que extrajimos
        if(data.nombreCliente) inputNombre.val(data.nombreCliente);
        if(data.correoCliente) inputCorreo.val(data.correoCliente);
        
        // Un pequeño destello visual para indicar que se autocompletó
        inputNombre.add(inputCorreo).css('background-color', 'var(--bg-elevated)');
        setTimeout(() => inputNombre.add(inputCorreo).css('background-color', ''), 500);
    });

    // Evento: Cuando se borra la selección (la 'X' del select2)
    selectSolicitud.on('select2:unselect', function (e) {
        inputNombre.val('');
        inputCorreo.val('');
    });

    // =======================================================
    // 3. GUARDAR LA COTIZACIÓN RÁPIDA (BYPASS)
    // =======================================================
    $('#btnGuardarCotizacionRapida').on('click', function (e) {
        e.preventDefault();

        // Capturamos los datos
        const cliente = $('#inputNombreClienteRapido').val().trim();
        const correo = $('#inputCorreoClienteRapido').val().trim();
        const concepto = $('#inputConceptoRapido').val().trim();
        const detalles = $('#inputDetallesRapido').val().trim();
        const precio = $('#inputPrecioRapido').val();
        const quoteId = $('#selectSolicitudWebRapida').val();

        // Validación básica
        const precioNum = parseFloat(precio);
        if (!cliente || !concepto || isNaN(precioNum) || precioNum <= 0) {
            Swal.fire('Atención', 'Por favor llena los campos obligatorios con valores válidos.', 'warning');
            return;
        }

        // Bloqueamos el botón
        const btn = $(this);
        const textoOriginal = btn.html();
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...');

        // ==========================================
        // NUEVA FORMA: Usando FormData nativo
        // ==========================================
        let formData = new FormData();
        formData.append('modo_rapido', true);
        if (quoteId) formData.append('quote_request_id', quoteId);
        formData.append('nombre_cliente', cliente);
        formData.append('correo_cliente', correo);
        formData.append('concepto_rapido', concepto);
        formData.append('detalles_rapido', detalles);
        formData.append('costo_total', precio);
        
        // Capturamos el archivo de la imagen (NUEVO)
        const inputFileRapido = document.getElementById('inputImagenReferenciaRapido');
        if (inputFileRapido && inputFileRapido.files[0]) {
            const file = inputFileRapido.files[0];
            const tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
            const maxMb = 5;

            if (!tiposPermitidos.includes(file.type)) {
                Swal.fire('Formato inválido', 'Solo se permiten imágenes JPG, PNG o WEBP.', 'warning');
                return;
            }

            if (file.size > maxMb * 1024 * 1024) {
                Swal.fire('Archivo muy pesado', `La imagen no puede pesar más de ${maxMb}MB.`, 'warning');
                return;
            }

            formData.append('imagen_referencia', file);
        }

        // Enviamos por AJAX a la MISMA ruta de siempre
        $.ajax({
            url: '/cotizaciones/guardar', 
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData, // Mandamos el FormData
            processData: false, // CLAVE
            contentType: false, // CLAVE
            success: function (response) {
                // Ocultamos el formulario y mostramos el panel de éxito
                $('#formCotizadorRapido').slideUp();
                $('#panelExitoRapido').removeClass('d-none').hide().slideDown();
                
                // Inyectamos el ID en el texto y en el botón del PDF
                $('#txtIdPedidoRapido').text(response.id);
                $('#btnPdfNotaRapida').attr('href', `/pedidos/${response.id}/pdf-nota`);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                Swal.fire('Error', 'Ocurrió un problema al guardar la cotización.', 'error');
                btn.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // =======================================================
    // 4. RESETEAR EL FORMULARIO (NUEVO PEDIDO)
    // =======================================================
    $('#btnResetRapido').on('click', function (e) {
        e.preventDefault();
        
        // Limpiamos todo
        $('#formCotizadorRapido')[0].reset();
        $('#selectSolicitudWebRapida').val(null).trigger('change'); // Resetea el Select2
        
        // Restauramos el botón y ocultamos el panel
        $('#btnGuardarCotizacionRapida').prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-2"></i> Confirmar y Generar PDF');
        
        $('#panelExitoRapido').slideUp(function () {
            $('#formCotizadorRapido').slideDown();
        });
    });

});