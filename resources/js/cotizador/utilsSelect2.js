// ==========================================
//  UTILIDAD CENTRALIZADA — INICIALIZACIÓN DE SELECT2 AJAX
// ==========================================
//
// Antes, cada módulo (lana, cortinas, cintas) repetía su propia config de
// Select2 (theme, width, tags, createTag...), y eso facilitaba que un
// módulo quedara desincronizado de los demás — por ejemplo una clase CSS
// que no coincidía con el selector usado en el .select2() de ese archivo.
//
// Esta función centraliza esa configuración y, sobre todo, corrige el bug
// de raíz: SIEMPRE recibe la colección jQuery exacta de elementos recién
// creados (nunca una clase global tipo $('.select2-ajax-x')), y SIEMPRE
// usa el guard .not('[data-select2-id]') para no intentar re-inicializar
// un select que ya es un widget de Select2 activo.
//
// @param {jQuery} $elementos     Colección de <select> a inicializar (ya
//                                 filtrada al contenedor que corresponde).
// @param {string} rutaBusqueda   URL del endpoint AJAX de búsqueda.
// @param {object} opciones       { placeholder } — cualquier otro override puntual.
export function inicializarSelect2Ajax($elementos, rutaBusqueda, opciones = {}) {
    if (!$elementos || $elementos.length === 0) return;

    $elementos.not('[data-select2-id]').select2({
        theme: 'bootstrap-5',
        width: 'resolve',
        placeholder: opciones.placeholder || 'Buscar o escribir nuevo color...',
        tags: true,
        delay: 250,
        cache: true,
        ajax: {
            url: rutaBusqueda,
            dataType: 'json',
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
        },
        createTag: function (params) {
            const term = $.trim(params.term);
            if (term === '') return null;
            return { id: term, text: term + ' (Cotizar nuevo material)', newTag: true };
        },
    });
}