// {{-- ==========================================
//     MÓDULO 4 — SWITCHES Y CINTAS
// ========================================== --}}
import { inicializarSelect2Ajax } from './utilsSelect2.js';

export function inicializarModuloDecoracion(RUTAS) {
    const togglesModulo4 = [
        { switch: $('#swLazoSimple'), caja: $('#cajaLazoSimple') },
        { switch: $('#swLazoFlor'),   caja: $('#cajaLazoFlor')   },
        { switch: $('#swLazoNombre'), caja: $('#cajaLazoNombre') },
        { switch: $('#swApliques'),   caja: $('#cajaApliques')   }
    ];

    // Un único listener por switch: muestra/oculta la caja Y, si se activa,
    // inicializa Select2 SOLO en los selects que viven dentro de esa caja
    // (antes eran dos listeners separados sobre el mismo switch).
    togglesModulo4.forEach(item => {
        item.switch.on('change', function () {
            const activo = $(this).is(':checked');
            item.caja.toggleClass('d-none', !activo);

            if (activo) {
                inicializarSelect2Ajax(item.caja.find('.select2-ajax-cintas'), RUTAS.buscarCintas, {
                    placeholder: 'Buscar color de cinta...',
                });
            }
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

        // Escopado solo a los selects de flores recién creados.
        inicializarSelect2Ajax(contenedor.find('.select2-ajax-cintas'), RUTAS.buscarCintas, {
            placeholder: 'Buscar color de cinta...',
        });
    });

    // Dibuja "Flor 1" al iniciar
    $('#selectCantFlores').trigger('change');
}