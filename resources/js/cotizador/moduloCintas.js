
// {{-- ==========================================
//     MÓDULO 4 — SWITCHES Y CINTAS
// ========================================== --}}

export function inicializarModuloDecoracion(RUTAS) {
    const togglesModulo4 = [
        { switch: $('#swLazoSimple'), caja: $('#cajaLazoSimple') },
        { switch: $('#swLazoFlor'),   caja: $('#cajaLazoFlor')   },
        { switch: $('#swLazoNombre'), caja: $('#cajaLazoNombre') },
        { switch: $('#swApliques'),   caja: $('#cajaApliques')   }
    ];

    togglesModulo4.forEach(item => {
        item.switch.on('change', function () {
            item.caja.toggleClass('d-none', !$(this).is(':checked'));
        });
    });

    function inicializarCintas() {
        $('.select2-ajax-cintas').not('[data-select2-id]').select2({
            theme: 'bootstrap-5',
            width: 'resolve',
            placeholder: 'Buscar color de cinta...',
            tags: true,
            delay: 250,
            ajax: {
                url: RUTAS.buscarCintas, // <-- Ruta dinámica
                dataType: 'json',
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data })
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term + ' (Cotizar nuevo material)', newTag: true };
            }
        });
    }

    togglesModulo4.forEach(item => {
        item.switch.on('change', function () {
            if ($(this).is(':checked')) inicializarCintas();
        });
    });

    $('#selectCantFlores').on('change', function () {
        let cantidad = parseInt($(this).val());
        let contenedor = $('#contenedorFlores');

        contenedor.empty();

        for (let i = 1; i <= cantidad; i++) {
            contenedor.append(`
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted">Flor ${i}</span>
                    <select class="form-select select2-ajax-cintas" name="cinta_flor_${i}">
                        <option value="" selected disabled>Buscar color...</option>
                    </select>
                </div>
            `);
        }
        inicializarCintas();
    });

    // Dibuja "Flor 1" al iniciar
    $('#selectCantFlores').trigger('change');
}