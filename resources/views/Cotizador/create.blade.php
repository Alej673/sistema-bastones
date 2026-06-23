@extends('layouts.admin')

@section('titulo', 'Nueva Cotización')

@section('contenido')

    {{-- ==========================================
         DEPENDENCIAS CSS
    ========================================== --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* Limita el dropdown de Select2 para mostrar ~4 opciones */
        .select2-results__options {
            max-height: 160px !important;
            overflow-y: auto;
        }
    </style>


    {{-- ==========================================
         ENCABEZADO DE PÁGINA
    ========================================== --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
        <h2 class="h3 text-dark fw-bold">Cotizador Automático de Producción</h2>
    </div>


    {{-- ==========================================
         LAYOUT PRINCIPAL: FORMULARIO + RESULTADO
    ========================================== --}}
    <div class="row align-items-start">


        {{-- ======================================
             COLUMNA IZQUIERDA — FORMULARIO
        ====================================== --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-primary border-opacity-25">

                <div class="card-header bg-white text-primary fw-bold py-3 border-bottom border-primary border-opacity-25">
                    Configuración del Pedido
                </div>

                <div class="card-body bg-light">
                    <form id="formCotizador">


                        {{-- MÓDULO 1: ESTRUCTURA BASE --}}
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">1. ESTRUCTURA BASE</h6>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label text-muted small mb-1">Cant. Bastones</label>
                                        <input type="number" id="inputCantidad" class="form-control" value="" min="1">
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
                                            <option value="plata" selected>Plata</option>
                                            <option value="dorado">Dorado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- MÓDULO 2: CUERPO (LANA) --}}
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">2. CUERPO (LANA)</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Cantidad de Colores</label>
                                    <select id="selectCantidadColores" class="form-select">
                                        <option value="1" selected>1 Color (Sólido)</option>
                                        <option value="2">2 Colores (50/50)</option>
                                        <option value="3">3 Colores (33/33/33)</option>
                                    </select>
                                </div>
                                {{-- Los buscadores de color se inyectan aquí por JS --}}
                                <div id="contenedorColoresLana" class="d-flex flex-column gap-2 mt-2"></div>
                            </div>
                        </div>


                        {{-- MÓDULO 3: CORTINAS --}}
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">3. CORTINAS (OPCIONALES)</h6>

                                {{-- Cortina de Lana --}}
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

                                {{-- Cortina de Fiesta --}}
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


                        {{-- MÓDULO 4: DECORACIÓN Y APLIQUES --}}
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">4. DECORACIÓN Y APLIQUES</h6>

                                {{-- Lazos (usan inventario de cintas) --}}
                                <p class="text-muted small fw-bold border-bottom pb-1 mb-2">Lazos (Inventario)</p>

                                <div class="border rounded p-2 mb-2 bg-light">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="swLazoSimple">
                                        <label class="form-check-label fw-bold text-dark" for="swLazoSimple">Lazo Simple (1.5m)</label>
                                    </div>
                                    <div id="cajaLazoSimple" class="mt-2 d-none">
                                        {{-- Buscador AJAX de cintas --}}
                                        <select class="form-select select2-ajax-cintas" name="cinta_lazo_simple">
                                            <option value="" selected disabled>Buscar color de cinta...</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="border rounded p-2 mb-2 bg-light">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="swLazoFlor">
                                        <label class="form-check-label fw-bold text-dark" for="swLazoFlor">Flores (1.0m c/u)</label>
                                    </div>
                                    <div id="cajaLazoFlor" class="mt-2 d-none row g-2">
                                        <div class="col-4">
                                            <label class="form-label text-muted small mb-0">Cant. (1 a 5)</label>
                                            <input type="number" class="form-control form-control-sm" id="cantFlores" value="1" min="1" max="5">
                                        </div>
                                        <div class="col-8">
                                            <label class="form-label text-muted small mb-0">Color de Cinta</label>
                                            {{-- Buscador AJAX de cintas --}}
                                            <select class="form-select select2-ajax-cintas" name="cinta_lazo_flor">
                                                <option value="" selected disabled>Buscar color...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded p-2 mb-2 bg-light">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="swLazoNombre">
                                        <label class="form-check-label fw-bold text-dark" for="swLazoNombre">Lazo c/ Nombre (1.0m + $0.70)</label>
                                    </div>
                                    <div id="cajaLazoNombre" class="mt-2 d-none">
                                        {{-- Buscador AJAX de cintas --}}
                                        <select class="form-select select2-ajax-cintas" name="cinta_lazo_nombre">
                                            <option value="" selected disabled>Buscar color de cinta...</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Apliques manuales (solo cotizan, no afectan inventario) --}}
                                <p class="text-muted small fw-bold border-bottom pb-1 mb-2 mt-3">Detalles Manuales (Solo Cotización)</p>

                                <div class="border rounded p-2 mb-2 bg-white">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="swApliques">
                                        <label class="form-check-label fw-bold text-dark" for="swApliques">Incluir Apliques ($0.50 c/u)</label>
                                    </div>
                                    <div id="cajaApliques" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-0">Cantidad de apliques por bastón</label>
                                        <input type="number" class="form-control form-control-sm w-50" id="cantApliques" value="1" min="1">
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- MÓDULO 5: DISEÑOS PERSONALIZADOS --}}
                        <div class="card mb-3 border-0 shadow-sm border-warning border-opacity-25">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">5. DISEÑOS PERSONALIZADOS (OPCIONAL)</h6>

                                <div class="border rounded p-3 bg-white">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="swDisenoPersonalizado">
                                        <label class="form-check-label fw-bold text-dark" for="swDisenoPersonalizado">
                                            <i class="fa-solid fa-star text-warning"></i> Incluir Diseño Especial en Lana
                                        </label>
                                    </div>

                                    <div id="cajaDisenoPersonalizado" class="mt-3 d-none">
                                        <label class="form-label text-muted small mb-1">Nivel de Complejidad (Mano de Obra)</label>
                                        <select class="form-select form-select-sm" id="selectNivelDiseno" name="tarifa_diseno">
                                            <option value="1.50" selected>Básico ($1.50)</option>
                                            <option value="2.00">Intermedio ($2.00)</option>
                                            <option value="3.00">Premium ($3.00)</option>
                                        </select>
                                        <div class="form-text mt-2" style="font-size: 0.75rem; color: #64748b;">
                                            <i class="fa-solid fa-circle-info"></i> Este valor se sumará como costo directo al precio base de cada bastón cotizado.
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        {{-- ======================================
             COLUMNA DERECHA — VISTA PREVIA
        ====================================== --}}
    <div class="col-lg-7">
            
            <div class="card shadow-sm border-dark mb-3 sticky-top" style="top: 20px;">
                <div class="card-header bg-dark text-warning fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-boxes-stacked"></i> Vista Previa: Impacto en Inventario</span>
                    <span id="lblResumenBastones" class="badge bg-warning text-dark">0 Bastones</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless align-middle mb-0" id="tablaImpacto">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="text-secondary small fw-bold" style="width: 40%;">Material</th>
                                    <th class="text-secondary small fw-bold" style="width: 30%;">Cálculo Interno</th>
                                    <th class="text-secondary small fw-bold text-end" style="width: 30%;">A Descontar</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaImpacto">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-calculator fa-2x mb-2 text-light"></i><br>
                                        Esperando configuración del pedido...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-success border-opacity-25 mt-3">
                <div class="card-body bg-light">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <span class="text-muted fw-bold">Costo Base Materiales:</span>
                        <span class="fs-5 fw-bold text-dark" id="txtCostoMateriales">$ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <span class="text-muted fw-bold">Mano de Obra / Extras:</span>
                        <span class="fs-5 fw-bold text-dark" id="txtCostoManoObra">$ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-success fw-bold fs-5">COSTO TOTAL PRODUCCIÓN:</span>
                        <span class="fs-3 fw-bold text-success" id="txtCostoTotal">$ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="text-muted small">Costo sugerido por unidad:</span>
                        <span class="fw-bold text-secondary" id="txtCostoUnitario">$ 0.00 c/u</span>
                    </div>
                    
                    <button type="button" class="btn btn-success w-100 fw-bold mt-4 py-2 shadow-sm" id="btnGuardarCotizacion" disabled>
                        <i class="fa-solid fa-floppy-disk"></i> Confirmar y Guardar Pedido
                    </button>
                </div>
            </div>

        </div>


    </div>{{-- /row --}}

    {{-- ==========================================
         EXPORTAR COTIZACIÓN
    ========================================== --}}
    <div id="panelExportar" class="mt-3 d-none border-top pt-3">
        <p class="text-muted small fw-bold mb-2 text-center">Exportar Cotización #<span id="txtNumeroCotizacion">---</span></p>
            <div class="row g-2">
                <div class="col-12">
                    <button type="button" class="btn btn-outline-danger w-100 fw-bold shadow-sm text-start" id="btnPdfReceta">
                        <i class="fa-solid fa-clipboard-list ms-2 me-2"></i> 1. Generar Receta (Bodega)
                    </button>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-outline-primary w-100 fw-bold shadow-sm text-start" id="btnPdfNota">
                        <i class="fa-solid fa-file-invoice-dollar ms-2 me-2"></i> 2. Generar Nota de Venta
                    </button>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-info text-white w-100 fw-bold shadow-sm text-start" data-bs-toggle="modal" data-bs-target="#modalEnviarCorreo">
                        <i class="fa-solid fa-paper-plane ms-2 me-2"></i> 3. Enviar Nota por Correo
                    </button>
                </div>
            </div>
    </div>

    {{-- ==========================================
         MODAL: ENVIAR COTIZACIÓN POR CORREO    
    ========================================== --}}
    <div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-labelledby="modalCorreoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold" id="modalCorreoLabel"><i class="fa-solid fa-envelope"></i> Enviar Nota de Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Se adjuntará un PDF con la nota de venta al cliente. (Los detalles internos de producción no serán enviados).</p>
                    <div class="mb-3">
                        <label for="emailCliente" class="form-label fw-bold">Correo electrónico del cliente</label>
                        <input type="email" class="form-control" id="emailCliente" placeholder="ejemplo@correo.com">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info text-white fw-bold" id="btnConfirmarEnvio">
                        <i class="fa-solid fa-paper-plane"></i> Enviar Ahora
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ==========================================
         DEPENDENCIAS JS
    ========================================== --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    {{-- ==========================================
         MÓDULO 2 — BUSCADORES DE LANA
    ========================================== --}}
    <script>
        $(document).ready(function () {

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
                        url: "{{ route('lanas.buscar') }}",
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

            selectCantidad.on('change', function () {
                renderizarBuscadores($(this).val());
            });

            // Carga inicial al abrir la página
            renderizarBuscadores(selectCantidad.val());
        });
    </script>


    {{-- ==========================================
         MÓDULO 3 — CORTINAS (LANA Y FIESTA)
    ========================================== --}}
    <script>
        $(document).ready(function () {

            // Referencias a los elementos de la UI
            const swCortinaLana             = $('#swCortinaLana');
            const cajaCortinaLana           = $('#cajaCortinaLana');
            const selectCantCortinaLana     = $('#selectCantCortinaLana');
            const contenedorCortinasLana    = $('#contenedorCortinasLana');

            const swCortinaFiesta           = $('#swCortinaFiesta');
            const cajaCortinaFiesta         = $('#cajaCortinaFiesta');
            const selectCantCortinaFiesta   = $('#selectCantCortinaFiesta');
            const contenedorCortinasFiesta  = $('#contenedorCortinasFiesta');

            // Genera selects de color para cualquier tipo de cortina
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
                        url: "{{ route('lanas.buscar') }}",
                        dataType: 'json', delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data })
                    },
                    createTag: params => ($.trim(params.term) === '' ? null : { id: params.term, text: params.term + ' (Cotizar nuevo material)', newTag: true })
                });

                // Motor AJAX para cortinas de fiesta (ruta separada)
                $('.select2-ajax-cortinafiesta').select2({
                    theme: 'bootstrap-5', width: 'resolve', placeholder: 'Buscar cortina de fiesta...', tags: true,
                    ajax: {
                        url: "{{ route('cortinas.buscar') }}",
                        dataType: 'json', delay: 250,
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data })
                    },
                    createTag: params => ($.trim(params.term) === '' ? null : { id: params.term, text: params.term + ' (Cotizar nuevo material)', newTag: true })
                });
            }

            // Switch de cortina de lana
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

            // Switch de cortina de fiesta
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

        });
    </script>


    {{-- ==========================================
         MÓDULO 4 — SWITCHES Y CINTAS
    ========================================== --}}
    <script>
        $(document).ready(function () {

            // --- Switches de mostrar/ocultar cajas --- //
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

            // --- Motor AJAX para buscadores de cintas --- //
            // Se llama cada vez que se abre un select de cinta para inicializarlo
            function inicializarCintas() {
                $('.select2-ajax-cintas').not('[data-select2-id]').select2({
                    theme: 'bootstrap-5',
                    width: 'resolve',
                    placeholder: 'Buscar color de cinta...',
                    tags: true,
                    delay: 250,
                    ajax: {
                        url: "{{ route('cintas.buscar') }}",
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

            // Reinicia cintas cada vez que un switch del módulo se activa
            togglesModulo4.forEach(item => {
                item.switch.on('change', function () {
                    if ($(this).is(':checked')) inicializarCintas();
                });
            });

        });
    </script>

    {{-- ==========================================
    MÓDULO 5 — SWITCHES PARA DISEÑOS PERSONALIZADOS
    ========================================== --}}
    <script>
            const swDiseno = $('#swDisenoPersonalizado');
            const cajaDiseno = $('#cajaDisenoPersonalizado');

            swDiseno.on('change', function() {
                if ($(this).is(':checked')) {
                    cajaDiseno.removeClass('d-none');
                } else {
                    cajaDiseno.addClass('d-none');
                }
            });
    </script>

    {{-- ==========================================
            MOTOR DE CÁLCULO — COTIZADOR AUTOMÁTICO, Recalcula todo cada vez que el usuario
            cambia cualquier campo del formulario.
        ========================================== --}}

    {{-- Inventario inyectado desde el controlador como JSON --}}
    <script type="application/json" id="datos-inventario">
        @json($insumos)
    </script>

    <script>

        // Leemos el inventario que Laravel dejó en el DOM
        const INVENTARIO = JSON.parse(document.getElementById('datos-inventario').textContent);

        $(document).ready(function () {

            // -------------------------------------------------------
            // PRECIOS DE RESPALDO
            // Se usan cuando el material no está en el inventario
            // (tags nuevos o insumos sin registro en BD)
            // -------------------------------------------------------
            const PRECIOS_FANTASMA = {
                lana:                 0.0127,  // $1.15 / 90g
                cinta_garza:          0.11,    // $5.00 / 45.72m
                cinta_satin:          0.16,    // $3.00 / 18.28m
                cinta_gross:          0.15,    // $3.50 / 22.86m
                elastico:             0.09,    // $0.90 / 10m
                cinchos:              0.02,    // $2.00 / 100u
                cortina_fiesta_menor: 1.00,    // pedido < 12 bastones
                cortina_fiesta_mayor: 0.50,    // pedido >= 12 bastones
            };

            // -------------------------------------------------------
            // RECETA BASE (insumos fijos por cada bastón)
            // Siempre se consumen; no dependen de ningún switch.
            // -------------------------------------------------------
            const RECETA = {
                cinchos_por_baston:  3,     // 3 cinchos por unidad
                elastico_por_baston: 0.40,  // 0.40 m (40 cm) por unidad
            };


            // =======================================================
            // FUNCIÓN MAESTRA — recalcula toda la tabla y los totales
            // =======================================================
            function calcularCotizacion() {

                const tabla = $('#cuerpoTablaImpacto');
                tabla.empty();

                let costoTotalMateriales = 0;
                let costoTotalManoObra   = 0;

                const cantidadBastones = parseInt($('#inputCantidad').val()) || 0;
                const colorBase        = $('#selectAcabado').val();
                const tamanoBase       = $('#selectTamano').val();

                // Guarda del formulario: si faltan campos obligatorios, mostramos estado vacío
                if (cantidadBastones <= 0 || !colorBase || !tamanoBase) {
                    tabla.append(`
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="fa-solid fa-calculator fa-2x mb-2 text-light"></i><br>
                                Esperando configuración del pedido...
                            </td>
                        </tr>
                    `);
                    $('#txtCostoMateriales, #txtCostoManoObra, #txtCostoTotal').text('$ 0.00');
                    $('#txtCostoUnitario').text('$ 0.00 c/u');
                    $('#lblResumenBastones').text('0 Bastones');
                    $('#btnGuardarCotizacion').prop('disabled', true);
                    return;
                }

                $('#lblResumenBastones').text(`${cantidadBastones} Bastones`);


                // ---------------------------------------------------
                // FASE 1: BASE DEL BASTÓN
                // Precio según acabado y escala (mayoreo a partir de 12)
                // ---------------------------------------------------
                let precioBaseUnitario = 0;
                let nombreAcabado = '';

                // 1. Definir precios según mayoreo y color
                if (colorBase === 'dorado') {
                    nombreAcabado = 'Dorado';
                    precioBaseUnitario = (cantidadBastones >= 12) ? 5.00 : 5.50;
                } else { 
                    nombreAcabado = 'Plata';
                    precioBaseUnitario = (cantidadBastones >= 12) ? 4.50 : 5.00;
                }

                // 2. Sumar el costo al dinero total
                let costoTotalBases = precioBaseUnitario * cantidadBastones;
                costoTotalMateriales += costoTotalBases; 

                // 3. Lógica visual del tamaño
                let esGrande = (tamanoBase === '55' || tamanoBase === '60');
                let tamanoVisual = esGrande ? '55-60 cm' : '45-50 cm';

                // 4. BÚSQUEDA DE LA BASE EN EL INVENTARIO (Con escudo de seguridad)
                let baseEnKardex = INVENTARIO.find(item => {
                    if (item.categoria !== 'base_baston' || !item.nombre) return false;
                    
                    let nombreBD = item.nombre.toLowerCase();
                    let colorBuscado = colorBase.toLowerCase(); // 'plata' o 'dorado'
                    let tamanoBuscado = tamanoBase + 'cm'; // '45cm', '50cm', etc.

                    // Retorna verdadero SOLO si el nombre en la BD tiene ambas palabras
                    return nombreBD.includes(colorBuscado) && nombreBD.includes(tamanoBuscado);
                });

                let alertaBase = '';
                
                if (baseEnKardex && baseEnKardex.stock_actual >= cantidadBastones) {
                    // Sí hay registradas en la base de datos y alcanzan
                    alertaBase = `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`;
                } else {
                    // No hay suficientes o no está registrada aún
                    alertaBase = `<span class="text-danger fw-bold">- ${cantidadBastones} u. (Falta Comprar)</span>`;
                }

                // 5. Dibujar fila en la tabla
                tabla.append(`
                    <tr>
                        <td class="fw-bold text-dark">Base ${nombreAcabado} (${tamanoVisual})</td>
                        <td class="text-muted small">${cantidadBastones} u. × $${precioBaseUnitario.toFixed(2)} c/u</td>
                        <td class="text-end">${alertaBase}</td>
                    </tr>
                `);


                // ---------------------------------------------------
                // FASE 2: INSUMOS FIJOS DE ENSAMBLAJE (Cinchos y Elástico)
                // Van en cada bastón sin excepción; se muestran como
                // una sola fila agrupada para no saturar la tabla.
                //
                const totalCinchos  = RECETA.cinchos_por_baston  * cantidadBastones;
                const totalElastico = RECETA.elastico_por_baston * cantidadBastones;

                const costoCinchos  = totalCinchos  * PRECIOS_FANTASMA.cinchos;
                const costoElastico = totalElastico * PRECIOS_FANTASMA.elastico;

                costoTotalMateriales += costoCinchos + costoElastico;

                // Fila agrupada (opción activa por defecto)
                tabla.append(`
                    <tr>
                        <td class="fw-bold text-dark">Insumos de Ensamblaje</td>
                        <td class="text-muted small">
                            ${totalCinchos} cinchos · ${totalElastico.toFixed(2)}m elástico
                        </td>
                        <td class="text-end fw-bold text-muted">
                            $${(costoCinchos + costoElastico).toFixed(2)}
                        </td>
                    </tr>
                `);

                tabla.append(`
                    <tr>
                        <td class="text-dark">Cinchos</td>
                        <td class="text-muted small">${totalCinchos} u. × $${PRECIOS_FANTASMA.cinchos.toFixed(2)}</td>
                        <td class="text-end text-muted small">$${costoCinchos.toFixed(2)}</td>
                    </tr>
                `);
                tabla.append(`
                    <tr>
                        <td class="text-dark">Elástico</td>
                        <td class="text-muted small">${totalElastico.toFixed(2)}m × $${PRECIOS_FANTASMA.elastico.toFixed(2)}</td>
                        <td class="text-end text-muted small">$${costoElastico.toFixed(2)}</td>
                    </tr>
                `);

                // ---------------------------------------------------
                // FASE 3: CUERPO (LANA)
                // Consumo total dividido entre los colores activos.
                // Solo procesa selects que tengan una selección real.
                // ---------------------------------------------------
                const consumoLana_g = esGrande ? 150 : 135;

                // Contamos cuántos selects tienen algo elegido
                let coloresActivos = 0;
                $('#contenedorColoresLana .select2-ajax').each(function () {
                    const data = $(this).select2('data');
                    if (data && data.length > 0 && data[0].id !== '') coloresActivos++;
                });
                if (coloresActivos === 0) coloresActivos = 1; // Evita división por cero

                const gramosPorColorTotal = (consumoLana_g / coloresActivos) * cantidadBastones;

                $('#contenedorColoresLana .select2-ajax').each(function () {
                    const dataSelect = $(this).select2('data');
                    if (!dataSelect || dataSelect.length === 0) return;

                    const seleccion  = dataSelect[0];
                    if (!seleccion.id) return;

                    let nombreLana   = seleccion.text;
                    const esTagNuevo = seleccion.newTag || isNaN(seleccion.id);
                    let costoLana    = 0;
                    let stockActual  = 0;

                    if (!esTagNuevo) {
                        // Material existente: tomamos precio y stock desde la BD
                        const insumoBD = INVENTARIO.find(item => item.id == seleccion.id);
                        if (insumoBD) {
                            costoLana   = gramosPorColorTotal * insumoBD.costo_unitario;
                            stockActual = insumoBD.stock_actual;
                        }
                    } else {
                        // Tag nuevo: usamos precio fantasma y limpiamos el texto del select
                        costoLana  = gramosPorColorTotal * PRECIOS_FANTASMA.lana;
                        nombreLana = nombreLana.replace(' (Cotizar nuevo material)', '');
                    }

                    costoTotalMateriales += costoLana;

                    // Alerta visual de stock: verde si alcanza, rojo con madejas si no
                    const madejasNecesarias = Math.ceil(gramosPorColorTotal / 90);
                    const stockSuficiente   = !esTagNuevo && stockActual >= gramosPorColorTotal;
                    const textoStock        = stockSuficiente
                        ? `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`
                        : `<span class="text-danger fw-bold">- ${gramosPorColorTotal.toFixed(1)}g (${madejasNecesarias} Madejas)</span>`;

                    tabla.append(`
                        <tr>
                            <td class="fw-bold text-dark">Cuerpo: ${nombreLana}</td>
                            <td class="text-muted small">${gramosPorColorTotal.toFixed(1)}g calculados</td>
                            <td class="text-end">${textoStock}</td>
                        </tr>
                    `);
                });

                // ---------------------------------------------------
                // TOTALES — actualiza el panel financiero de la derecha
                // ---------------------------------------------------
                const granTotal     = costoTotalMateriales + costoTotalManoObra;
                const costoUnitario = granTotal / cantidadBastones;

                $('#txtCostoMateriales').text(`$ ${costoTotalMateriales.toFixed(2)}`);
                $('#txtCostoManoObra').text(`$ ${costoTotalManoObra.toFixed(2)}`);
                $('#txtCostoTotal').text(`$ ${granTotal.toFixed(2)}`);
                $('#txtCostoUnitario').text(`$ ${costoUnitario.toFixed(2)} c/u`);
                $('#btnGuardarCotizacion').prop('disabled', false);

            } 

            // =======================================================
            // TRIGGERS — cualquier cambio en el formulario recalcula
            // =======================================================

            // Campos directos del formulario
            $('#inputCantidad, #selectAcabado, #selectTamano, #selectCantidadColores')
                .on('input change', calcularCotizacion);

            // Selects de lana (creados dinámicamente, delegamos al body)
            $('body').on('change', '.select2-ajax', calcularCotizacion);

            // Carga inicial al abrir la página
            calcularCotizacion();
        });
    </script>
@endsection