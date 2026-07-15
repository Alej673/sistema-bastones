// {{-- ==========================================
//      MÓDULO 3 — CORTINAS (LANA Y FIESTA)
// ========================================== --}}
import { inicializarSelect2Ajax } from './utilsSelect2.js';

export function inicializarModuloCortinas(RUTAS) {
    const swCortinaLana             = $('#swCortinaLana');
    const cajaCortinaLana           = $('#cajaCortinaLana');
    const selectCantCortinaLana     = $('#selectCantCortinaLana');
    const contenedorCortinasLana    = $('#contenedorCortinasLana');

    const swCortinaFiesta           = $('#swCortinaFiesta');
    const cajaCortinaFiesta         = $('#cajaCortinaFiesta');
    const selectCantCortinaFiesta   = $('#selectCantCortinaFiesta');
    const contenedorCortinasFiesta  = $('#contenedorCortinasFiesta');

    function renderizarBuscadoresCortina(cantidad, contenedor, claseSelect, rutaBusqueda, placeholder) {
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

        // === FIX DEL BUG PRINCIPAL ===
        // Antes esto llamaba $('.select2-ajax-cortinalana').select2(...) y
        // $('.select2-ajax-cortinafiesta').select2(...) de forma GLOBAL en
        // cada re-render, sin importar qué sección disparó el cambio. Si
        // Cortina de Lana Y Cortina de Fiesta estaban activas a la vez, al
        // cambiar la cantidad de una se reintentaba inicializar Select2
        // sobre los selects YA ACTIVOS de la otra sección, y eso rompía
        // el widget silenciosamente (de ahí el "con unos sí, con otros no").
        //
        // Ahora solo tocamos los selects recién creados DENTRO de este
        // contenedor específico.
        inicializarSelect2Ajax(contenedor.find(`.${claseSelect}`), rutaBusqueda, { placeholder });
    }

    // ---- Eventos Cortina de Lana ----
    swCortinaLana.on('change', function () {
        if ($(this).is(':checked')) {
            cajaCortinaLana.removeClass('d-none');
            renderizarBuscadoresCortina(
                selectCantCortinaLana.val(), contenedorCortinasLana,
                'select2-ajax-cortinalana', RUTAS.buscarLanas,
                'Buscar o escribir nuevo color...'
            );
        } else {
            cajaCortinaLana.addClass('d-none');
            contenedorCortinasLana.empty();
        }
    });

    selectCantCortinaLana.on('change', function () {
        renderizarBuscadoresCortina(
            $(this).val(), contenedorCortinasLana,
            'select2-ajax-cortinalana', RUTAS.buscarLanas,
            'Buscar o escribir nuevo color...'
        );
    });

    // ---- Eventos Cortina de Fiesta ----
    swCortinaFiesta.on('change', function () {
        if ($(this).is(':checked')) {
            cajaCortinaFiesta.removeClass('d-none');
            renderizarBuscadoresCortina(
                selectCantCortinaFiesta.val(), contenedorCortinasFiesta,
                'select2-ajax-cortinafiesta', RUTAS.buscarCortinas,
                'Buscar cortina de fiesta...'
            );
        } else {
            cajaCortinaFiesta.addClass('d-none');
            contenedorCortinasFiesta.empty();
        }
    });

    selectCantCortinaFiesta.on('change', function () {
        renderizarBuscadoresCortina(
            $(this).val(), contenedorCortinasFiesta,
            'select2-ajax-cortinafiesta', RUTAS.buscarCortinas,
            'Buscar cortina de fiesta...'
        );
    });
}