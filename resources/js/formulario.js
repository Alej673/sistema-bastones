// ===== 1. MANEJO DE SCROLL =====
window.addEventListener('beforeunload', function() {
    sessionStorage.setItem('scrollPosition', window.scrollY);
});

window.addEventListener('load', function() {
    let scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition !== null) {
        window.scrollTo({
            top: parseInt(scrollPosition),
            behavior: 'instant'
        });
        sessionStorage.removeItem('scrollPosition');
    }
});

// ===== 2. LÍMITES DE CARRUSEL Y DESTACADOS =====
function mostrarAlertaLimite(tipo) {
    const config = window.CatalogoConfig.limites;
    
    const mensajes = {
        carrusel: {
            titulo: 'Límite del carrusel alcanzado',
            texto: `Ya tienes ${config.carrusel.max} diseños en el carrusel principal. Quita uno antes de añadir otro para no saturar la landing.`
        },
        destacado: {
            titulo: 'Límite de destacados alcanzado',
            texto: `Ya tienes ${config.destacado.max} diseños marcados como destacados. Quita uno antes de añadir otro.`
        }
    };

    Swal.fire({
        icon: 'warning',
        title: mensajes[tipo].titulo,
        text: mensajes[tipo].texto,
        background: '#ffffff',
        color: 'var(--text-main)',
        confirmButtonColor: 'var(--accent-purple)',
        confirmButtonText: 'Entendido'
    });
}

function aplicarEstadoLimiteVisual() {
    const config = window.CatalogoConfig.limites;

    document.querySelectorAll('.form-carrusel-toggle button').forEach(boton => {
        const activo = boton.classList.contains('is-on-info');
        const limiteAlcanzado = config.carrusel.actual >= config.carrusel.max;
        boton.classList.toggle('btn-limite-alcanzado', !activo && limiteAlcanzado);
    });

    document.querySelectorAll('.form-destacado-toggle button').forEach(boton => {
        const activo = boton.classList.contains('is-on-warning');
        const limiteAlcanzado = config.destacado.actual >= config.destacado.max;
        boton.classList.toggle('btn-limite-alcanzado', !activo && limiteAlcanzado);
    });
}

function bindLimitesToggle() {
    const config = window.CatalogoConfig.limites;

    document.querySelectorAll('.form-carrusel-toggle').forEach(form => {
        // Remueve listeners previos para evitar duplicaciones si el AJAX recarga el DOM
        const formClone = form.cloneNode(true);
        form.parentNode.replaceChild(formClone, form);
        
        formClone.addEventListener('submit', function (e) {
            const boton = formClone.querySelector('button');
            const estaActivo = boton.classList.contains('is-on-info');
            if (!estaActivo && config.carrusel.actual >= config.carrusel.max) {
                e.preventDefault();
                mostrarAlertaLimite('carrusel');
            }
        });
    });

    document.querySelectorAll('.form-destacado-toggle').forEach(form => {
        const formClone = form.cloneNode(true);
        form.parentNode.replaceChild(formClone, form);

        formClone.addEventListener('submit', function (e) {
            const boton = formClone.querySelector('button');
            const estaActivo = boton.classList.contains('is-on-warning');
            if (!estaActivo && config.destacado.actual >= config.destacado.max) {
                e.preventDefault();
                mostrarAlertaLimite('destacado');
            }
        });
    });

    aplicarEstadoLimiteVisual();
}

// ===== 3. LÓGICA DE CATEGORÍAS DINÁMICAS =====
function adaptarCamposPorCategoria(categoriaSelec, prefix = '') {
    let categoria = $(categoriaSelec).val();

    let wrapMedida = $('#' + prefix + 'wrapper_medida');
    let wrapDiseno = $('#' + prefix + 'wrapper_diseno');
    let wrapAccesorios = $('#' + prefix + 'wrapper_accesorios');

    let selMedida = $('#' + prefix + 'medida_cm');
    let selDiseno = $('#' + prefix + 'nivel_diseno');
    let selAccesorios = $('#' + prefix + 'nivel_accesorios');

    if (categoria === 'baston') {
        wrapMedida.show();
        wrapDiseno.show();
        wrapAccesorios.show();

        selMedida.prop('required', true);

        if (selMedida.val() === 'na') selMedida.val('').trigger('change');
        if (selDiseno.val() === 'na') selDiseno.val('').trigger('change');
        if (selAccesorios.val() === 'na') selAccesorios.val('').trigger('change');

    } else if (categoria === 'manualidad') {
        wrapMedida.hide();
        wrapAccesorios.hide();
        wrapDiseno.show();

        selMedida.prop('required', false);

        selMedida.val('na').trigger('change');
        selAccesorios.val('na').trigger('change');

        if (selDiseno.val() === 'na') selDiseno.val('').trigger('change');

    } else {
        wrapMedida.hide();
        wrapDiseno.hide();
        wrapAccesorios.hide();

        selMedida.prop('required', false);

        selMedida.val('na').trigger('change');
        selDiseno.val('na').trigger('change');
        selAccesorios.val('na').trigger('change');
    }
}

// ===== 4. CARGA DE DATOS AL MODAL =====
// Se expone globalmente para que el HTML pueda llamarla (onclick="cargarDatosEditar(...)")
window.cargarDatosEditar = function(id, titulo, descripcion, categoria, medida, diseno, accesorios) {
    let baseUrl = window.CatalogoConfig.baseUrl;
    document.getElementById('formEditar').action = baseUrl + '/' + id;

    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_descripcion').value = descripcion;
    document.getElementById('edit_imagen').value = '';

    $('#edit_categoria').val(categoria).trigger('change');
    $('#edit_medida_cm').val(medida || '').trigger('change');
    $('#edit_nivel_diseno').val(diseno || '').trigger('change');
    $('#edit_nivel_accesorios').val(accesorios || '').trigger('change');

    adaptarCamposPorCategoria(document.getElementById('edit_categoria'), 'edit_');
};

// ===== 5. INICIALIZACIÓN PRINCIPAL (DOM Ready) =====
document.addEventListener('DOMContentLoaded', function () {

    function initSelect2() {
        $('.select2-form').select2({
            width: '100%',
            placeholder: function(){ $(this).data('placeholder'); },
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        $('.select2-filtro').select2({
            width: '100%',
            minimumResultsForSearch: Infinity
        });

        $('.select2-modal').select2({
            width: '100%',
            dropdownParent: $('#modalEditar'),
            placeholder: function(){ $(this).data('placeholder'); },
            allowClear: true,
            minimumResultsForSearch: Infinity
        });
    }

    initSelect2();

    // Lanzar alertas de sesión leyendo el objeto global
    const sessionAlerts = window.CatalogoConfig.session;
    
    if (sessionAlerts.success) {
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: sessionAlerts.success,
            background: '#ffffff',
            color: 'var(--text-main)',
            confirmButtonColor: 'var(--accent-purple)',
            timer: 3000,
            timerProgressBar: true
        });
    }

    if (sessionAlerts.error) {
        Swal.fire({
            icon: 'error',
            title: 'No se pudo completar la acción',
            text: sessionAlerts.error,
            background: '#ffffff',
            color: 'var(--text-main)',
            confirmButtonColor: 'var(--accent-purple)',
        });
    }

    bindLimitesToggle();

    // Eventos de Categoría
    $('#categoria').on('change', function() {
        adaptarCamposPorCategoria(this, '');
    });

    $('#edit_categoria').on('change', function() {
        adaptarCamposPorCategoria(this, 'edit_');
    });

    $('#modalEditar').on('shown.bs.modal', function () {
        adaptarCamposPorCategoria(document.getElementById('edit_categoria'), 'edit_');
    });

    // ===== LÓGICA AJAX Y FILTROS =====
    // AQUÍ SE AÑADE EL FILTRO DE FECHA PARA QUE DISPARE EL AJAX
    $('#filtro_categoria, #filtro_estado, #filtro_fecha').on('change', function () {
        ejecutarFiltroAjax();
    });

    $('#form-filtros').on('submit', function (e) {
        e.preventDefault();
        ejecutarFiltroAjax();
    });
    
    // Función para limpiar filtros
    $('#btn-limpiar-filtros').on('click', function (e) {
        e.preventDefault();
        $('#filtro_buscar').val('');
        $('#filtro_categoria').val('todas').trigger('change.select2');
        $('#filtro_estado').val('todos').trigger('change.select2');
        
        // AQUÍ SE RESETEA EL FILTRO DE FECHAS A SU VALOR POR DEFECTO ('recientes')
        $('#filtro_fecha').val('recientes').trigger('change.select2');
        
        ejecutarFiltroAjax();
    });

    $(document).on('click', '#contenedor-resultados .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        ejecutarFiltroAjax(url);
    });

    function ejecutarFiltroAjax(urlPaginacion = null) {
        let form = $('#form-filtros');
        // form.serialize() captura automáticamente la variable 'fecha'
        let urlDestino = urlPaginacion ? urlPaginacion : (form.attr('action') + '?' + form.serialize());

        $('#contenedor-resultados').css('opacity', '0.5');

        $.ajax({
            url: urlDestino,
            type: 'GET',
            success: function(response) {
                let nuevoContenido = $(response).find('#contenedor-resultados').html();
                $('#contenedor-resultados').html(nuevoContenido).css('opacity', '1');
                window.history.pushState(null, '', urlDestino);
                
                // Re-vincular eventos a los elementos del DOM recién inyectados
                bindSweetAlertEliminar();
                bindLimitesToggle();
            },
            error: function() {
                $('#contenedor-resultados').css('opacity', '1');
                console.error('Error al cargar los datos.');
            }
        });
    }

    function bindSweetAlertEliminar() {
        const botonesEliminar = document.querySelectorAll('.btn-eliminar');
        botonesEliminar.forEach(boton => {
            const botonClone = boton.cloneNode(true);
            boton.parentNode.replaceChild(botonClone, boton); 
        });
        
        document.querySelectorAll('.btn-eliminar').forEach(boton => {
            boton.addEventListener('click', function (e) {
                e.preventDefault();
                const formulario = this.closest('.form-eliminar');

                Swal.fire({
                    title: '¿Eliminar este diseño?',
                    text: "Se borrará del catálogo y de la landing page. Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#ffffff',
                    color: 'var(--text-main)',
                    confirmButtonColor: 'var(--color-error)',
                    cancelButtonColor: '#9ca3af',
                    confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: { popup: 'border border-light' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        formulario.submit();
                    }
                });
            });
        });
    }

    bindSweetAlertEliminar();
});