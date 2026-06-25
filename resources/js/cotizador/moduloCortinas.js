// {{-- ==========================================
//      MÓDULO 3 — CORTINAS (LANA Y FIESTA)
// ========================================== --}}

export function inicializarModuloCortinas(RUTAS) {
    const swCortinaLana             = $('#swCortinaLana');
    const cajaCortinaLana           = $('#cajaCortinaLana');
    const selectCantCortinaLana     = $('#selectCantCortinaLana');
    const contenedorCortinasLana    = $('#contenedorCortinasLana');

    const swCortinaFiesta           = $('#swCortinaFiesta');
    const cajaCortinaFiesta         = $('#cajaCortinaFiesta');
    const selectCantCortinaFiesta   = $('#selectCantCortinaFiesta');
    const contenedorCortinasFiesta  = $('#contenedorCortinasFiesta');

    function renderizarBuscadoresCortina(cantidad, contenedor, claseSelect) {
        contenedor.empty();

        for (let i = 1; i <= cantidad; i++) {
            contenedor.append(`
                <div class="input-group input-group-sm mb-1 shadow-sm rounded">
                    <span class="input-group-text bg-white text-secondary border-end-0">
                        <i class="fa-solid fa-palette"></i> &nbsp;Color ${i}
                    </span>
                    <select class="form-select ${claseSelect}" name="cortinas_${claseSelect}[]">
                        <option value="" selected disabled>Buscar o escribir nuevo color...</option>
                    </select>
                </div>
            `);
        }

        // Motor AJAX para cortinas de lana
        $('.select2-ajax-cortinalana').select2({
            theme: 'bootstrap-5', width: 'resolve', placeholder: 'Buscar o escribir nuevo color...', tags: true,
            ajax: {
                url: RUTAS.buscarLanas, // <-- Ruta dinámica
                dataType: 'json', delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data })
            },
            createTag: params => ($.trim(params.term) === '' ? null : { id: params.term, text: params.term + ' (Cotizar nuevo material)', newTag: true })
        });

        // Motor AJAX para cortinas de fiesta
        $('.select2-ajax-cortinafiesta').select2({
            theme: 'bootstrap-5', width: 'resolve', placeholder: 'Buscar cortina de fiesta...', tags: true,
            ajax: {
                url: RUTAS.buscarCortinas, // <-- Ruta dinámica
                dataType: 'json', delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data })
            },
            createTag: params => ($.trim(params.term) === '' ? null : { id: params.term, text: params.term + ' (Cotizar nuevo material)', newTag: true })
        });
    }

    // Eventos Cortina de Lana
    swCortinaLana.on('change', function () {
        if ($(this).is(':checked')) {
            cajaCortinaLana.removeClass('d-none');
            renderizarBuscadoresCortina(selectCantCortinaLana.val(), contenedorCortinasLana, 'select2-ajax-cortinalana');
        } else {
            cajaCortinaLana.addClass('d-none');
            contenedorCortinasLana.empty();
        }
    });

    selectCantCortinaLana.on('change', function () {
        renderizarBuscadoresCortina($(this).val(), contenedorCortinasLana, 'select2-ajax-cortinalana');
    });

    // Eventos Cortina de Fiesta
    swCortinaFiesta.on('change', function () {
        if ($(this).is(':checked')) {
            cajaCortinaFiesta.removeClass('d-none');
            renderizarBuscadoresCortina(selectCantCortinaFiesta.val(), contenedorCortinasFiesta, 'select2-ajax-cortinafiesta');
        } else {
            cajaCortinaFiesta.addClass('d-none');
            contenedorCortinasFiesta.empty();
        }
    });

    selectCantCortinaFiesta.on('change', function () {
        renderizarBuscadoresCortina($(this).val(), contenedorCortinasFiesta, 'select2-ajax-cortinafiesta');
    });
}