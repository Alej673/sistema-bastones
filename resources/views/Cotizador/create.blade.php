@extends('layouts.admin')

@section('titulo', 'Nueva Cotización')

@section('contenido')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        /* Limitar la altura del menú desplegable de Select2 para mostrar ~4 elementos */
        .select2-results__options {
            max-height: 160px !important; 
            overflow-y: auto;
        }
    </style>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
        <h2 class="h3 text-dark fw-bold">Cotizador Automático de Producción</h2>
    </div>

    <div class="row align-items-start">
        
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-primary border-opacity-25">
                <div class="card-header bg-white text-primary fw-bold py-3 border-bottom border-primary border-opacity-25">
                    Configuración del Pedido
                </div>
                <div class="card-body bg-light">
                    <form id="formCotizador">

                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">1. ESTRUCTURA BASE</h6>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label text-muted small mb-1">Cant. Bastones</label>
                                        <input type="number" id="inputCantidad" class="form-control" value="10" min="1">
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label text-muted small mb-1">Tamaño Específico</label>
                                        <select id="selectTamano" class="form-select">
                                            <option value="" selected disabled>Seleccione medida...</option>
                                            <option value="45">45 cm (Consumo Pequeño)</option>
                                            <option value="50">50 cm (Consumo Pequeño)</option>
                                            <option value="55">55 cm (Consumo Grande)</option>
                                            <option value="60">60 cm (Consumo Grande)</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label text-muted small mb-1">Acabado del Bastón</label>
                                        <select id="selectAcabado" class="form-select">
                                            <option value="plomo" selected>Plata</option>
                                            <option value="dorado">Dorado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">2. CUERPO (LANA)</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Cantidad de Colores</label>
                                    <select id="selectCantidadColores" class="form-select">
                                        <option value="1">1 Color (Sólido)</option>
                                        <option value="2" selected>2 Colores (50/50)</option>
                                        <option value="3">3 Colores (33/33/33)</option>
                                    </select>
                                </div>
                                
                                <div id="contenedorColoresLana" class="d-flex flex-column gap-2 mt-2">
                                    </div>
                            </div>
                        </div>

                    <!-- Modulo 3 Cortinas  -->
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">3. CORTINAS (OPCIONALES)</h6>

                                <div class="mb-3 border rounded p-3 bg-white">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="swCortinaLana">
                                        <label class="form-check-label fw-bold text-dark" for="swCortinaLana">Incluir Cortina de Lana</label>
                                    </div>

                                    <div id="cajaCortinaLana" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-1">Cantidad de Colores (Lana)</label>
                                        <select id="selectCantCortinaLana" class="form-select form-select-sm mb-2">
                                            <option value="1" selected>1 Color</option>
                                            <option value="2">2 Colores</option>
                                            <option value="3">3 Colores</option>
                                        </select>
                                        
                                        <div id="contenedorCortinasLana" class="d-flex flex-column gap-2 mt-2"></div>
                                    </div>
                                </div>

                                <div class="border rounded p-3 bg-white">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="swCortinaFiesta">
                                        <label class="form-check-label fw-bold text-dark" for="swCortinaFiesta">Incluir Cortinas de Fiesta</label>
                                    </div>

                                    <div id="cajaCortinaFiesta" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-1">Cantidad de Colores (Fiesta)</label>
                                        <select id="selectCantCortinaFiesta" class="form-select form-select-sm mb-2">
                                            <option value="1" selected>1 Color</option>
                                            <option value="2">2 Colores</option>
                                            <option value="3">3 Colores</option>
                                        </select>

                                        <div id="contenedorCortinasFiesta" class="d-flex flex-column gap-2 mt-2"></div>
                                    </div>
                                </div>

                        </div>
                    </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-dark mb-3">
                <div class="card-header bg-dark text-warning fw-bold py-3">
                    Vista Previa: Impacto en Inventario
                </div>
                <div class="card-body p-4 text-center text-muted bg-light">
                    <i>La tabla de requerimientos se generará aquí...</i>
                </div>
            </div>
        </div>

    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            const contenedorLanas = $('#contenedorColoresLana');
            const selectCantidad = $('#selectCantidadColores');

            // Función que dibuja los contenedores de los buscadores
            function renderizarBuscadores(cantidad) {
                contenedorLanas.empty();

                for (let i = 1; i <= cantidad; i++) {
                    // Creamos el select vacío, la librería se encargará de llenarlo por AJAX
                    let selectHtml = `
                        <div class="input-group input-group-sm mb-2 shadow-sm rounded">
                            <span class="input-group-text bg-white text-secondary border-end-0">
                                <i class="fa-solid fa-palette"></i> &nbsp;Color ${i}
                            </span>
                            <select class="form-select selector-lana select2-ajax" id="lana_${i}" name="lanas[]">
                                <option value="" selected disabled>Buscar o escribir nuevo color...</option>
                            </select>
                        </div>
                    `;
                    contenedorLanas.append(selectHtml);
                }

                // Inicializamos Select2 con el motor AJAX apuntando a la base de datos
                $('.select2-ajax').select2({
                    theme: 'bootstrap-5',
                    width: 'resolve',
                    placeholder: 'Buscar o escribir nuevo color...',
                    tags: true, // Mantenemos la opción de permitir escribir colores nuevos
                    delay: 250, // Espera 250ms después de escribir para no saturar la base de datos
                    ajax: {
                        url: "{{ route('lanas.buscar') }}",
                        dataType: 'json',
                        data: function (params) {
                            return {
                                q: params.term // Término de búsqueda enviado al controlador
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data // Inyecta los resultados devueltos por el controlador
                            };
                        },
                        cache: true
                    },
                    createTag: function (params) {
                        var term = $.trim(params.term);
                        if (term === '') { return null; }
                        
                        return {
                            id: term,
                            text: term + ' (Cotizar nuevo material)',
                            newTag: true 
                        }
                    }
                });
            }

            // Escuchar cambios en la cantidad de colores
            selectCantidad.on('change', function() {
                renderizarBuscadores($(this).val());
            });

            // Inicialización automática al cargar
            renderizarBuscadores(selectCantidad.val());
        });
    </script>

    <!--LÓGICA MÓDULO 3: CORTINAS -->
    <script>

            const swCortinaLana = $('#swCortinaLana');
            const cajaCortinaLana = $('#cajaCortinaLana');
            const selectCantCortinaLana = $('#selectCantCortinaLana');
            const contenedorCortinasLana = $('#contenedorCortinasLana');

            const swCortinaFiesta = $('#swCortinaFiesta');
            const cajaCortinaFiesta = $('#cajaCortinaFiesta');
            const selectCantCortinaFiesta = $('#selectCantCortinaFiesta');
            const contenedorCortinasFiesta = $('#contenedorCortinasFiesta');

            // 1. Función para inyectar buscadores de cortina (Reutilizable)
            function renderizarBuscadoresCortina(cantidad, contenedor, claseSelect) {
                contenedor.empty();

                for (let i = 1; i <= cantidad; i++) {
                    let selectHtml = `
                        <div class="input-group input-group-sm mb-1 shadow-sm rounded">
                            <span class="input-group-text bg-white text-secondary border-end-0">
                                <i class="fa-solid fa-palette"></i> &nbsp;Color ${i}
                            </span>
                            <select class="form-select ${claseSelect}" name="cortinas_${claseSelect}[]">
                                <option value="" selected disabled>Buscar o escribir nuevo color...</option>
                            </select>
                        </div>
                    `;
                    contenedor.append(selectHtml);
                }

                // Reinicializamos Select2 para lanas
                $('.select2-ajax-cortinalana').select2({
                    theme: 'bootstrap-5', width: 'resolve', placeholder: 'Buscar o escribir nuevo color...', tags: true,
                    ajax: { url: "{{ route('lanas.buscar') }}", dataType: 'json', delay: 250, data: params => ({ q: params.term }), processResults: data => ({ results: data }) },
                    createTag: params => ($.trim(params.term) === '' ? null : { id: params.term, text: params.term + ' (Cotizar nuevo material)', newTag: true })
                });

                // Inicializamos Select2 para fiesta apuntando a su nueva ruta
                $('.select2-ajax-cortinafiesta').select2({
                    theme: 'bootstrap-5', width: 'resolve', placeholder: 'Buscar cortina de fiesta...', tags: true,
                    ajax: { 
                        url: "{{ route('cortinas.buscar') }}", // ¡Ruta conectada!
                        dataType: 'json', 
                        delay: 250, 
                        data: params => ({ q: params.term }), 
                        processResults: data => ({ results: data }) 
                    },
                    createTag: params => ($.trim(params.term) === '' ? null : { id: params.term, text: params.term + ' (Cotizar nuevo material)', newTag: true })
                });
            }

            // 2. Control del Switch de Lana
            swCortinaLana.on('change', function() {
                if ($(this).is(':checked')) {
                    cajaCortinaLana.removeClass('d-none');
                    renderizarBuscadoresCortina(selectCantCortinaLana.val(), contenedorCortinasLana, 'select2-ajax-cortinalana');
                } else {
                    cajaCortinaLana.addClass('d-none');
                    contenedorCortinasLana.empty(); // Limpiar si se apaga el switch
                }
            });

            // Escuchar cambios en la cantidad de lana
            selectCantCortinaLana.on('change', function() {
                renderizarBuscadoresCortina($(this).val(), contenedorCortinasLana, 'select2-ajax-cortinalana');
            });


            // 3. Control del Switch de Fiesta
            swCortinaFiesta.on('change', function() {
                if ($(this).is(':checked')) {
                    cajaCortinaFiesta.removeClass('d-none');
                    renderizarBuscadoresCortina(selectCantCortinaFiesta.val(), contenedorCortinasFiesta, 'select2-ajax-cortinafiesta');
                } else {
                    cajaCortinaFiesta.addClass('d-none');
                    contenedorCortinasFiesta.empty(); // Limpiar
                }
            });

            // Escuchar cambios en la cantidad de fiesta
            selectCantCortinaFiesta.on('change', function() {
                renderizarBuscadoresCortina($(this).val(), contenedorCortinasFiesta, 'select2-ajax-cortinafiesta');
            });
    </script>
@endsection