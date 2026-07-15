// ==========================================
//  MÓDULO 2 — BUSCADORES DE LANA
// ==========================================
import { inicializarSelect2Ajax } from './utilsSelect2.js';

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

        // Solo inicializamos los selects que acabamos de crear dentro de
        // este contenedor, nunca la clase .select2-ajax de forma global.
        inicializarSelect2Ajax(contenedorLanas.find('.select2-ajax'), RUTAS.buscarLanas, {
            placeholder: 'Buscar o escribir nuevo color...',
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