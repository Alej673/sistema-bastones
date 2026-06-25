// ==========================================
//  MÓDULO 2 — BUSCADORES DE LANA
// ========================================== 
export function inicializarModuloLana(RUTAS) {
    const contenedorLanas   = $('#contenedorColoresLana');
    const selectCantidad    = $('#selectCantidadColores');

    // Genera los selects de color según la cantidad elegida
    function renderizarBuscadores(cantidad) {
        contenedorLanas.empty();

        for (let i = 1; i <= cantidad; i++) {
            contenedorLanas.append(`
                <div class="input-group input-group-sm mb-2 shadow-sm rounded">
                    <span class="input-group-text bg-white text-secondary border-end-0">
                        <i class="fa-solid fa-palette"></i> &nbsp;Color ${i}
                    </span>
                    <select class="form-select selector-lana select2-ajax" id="lana_${i}" name="lanas[]">
                        <option value="" selected disabled>Buscar o escribir nuevo color...</option>
                    </select>
                </div>
            `);
        }

        // Conecta los selects recién creados al motor AJAX de lanas
        $('.select2-ajax').select2({
            theme: 'bootstrap-5',
            width: 'resolve',
            placeholder: 'Buscar o escribir nuevo color...',
            tags: true,
            delay: 250,
            ajax: {
                url: RUTAS.buscarLanas, // <-- Usamos la ruta dinámica del puente
                dataType: 'json',
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data }),
                cache: true
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term + ' (Cotizar nuevo material)', newTag: true };
            }
        });
    }

    // Evento cuando cambia la cantidad de colores
    selectCantidad.on('change', function () {
        renderizarBuscadores($(this).val());
    });

    // Carga inicial al abrir la página
    if (selectCantidad.length) {
        renderizarBuscadores(selectCantidad.val());
    }
}