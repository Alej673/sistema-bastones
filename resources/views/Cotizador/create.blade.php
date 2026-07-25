@extends('layouts.admin')

@section('titulo', 'Nueva Cotización')

@push('css')
    {{-- Dependencias CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    @vite(['resources/css/variables.css', 'resources/css/cotizador.css'])
@endpush

@section('contenido')

<div class="dark-glass-cotizador pb-5">

    {{-- ENCABEZADO DE PÁGINA --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom glass-border">
        <h2 class="h3 fw-bold">Cotizador Automático de Producción</h2>
    </div>

    {{-- LAYOUT PRINCIPAL: FORMULARIO + RESULTADO --}}
    <div class="row align-items-start">

        {{-- ======================================
             COLUMNA IZQUIERDA — FORMULARIO
        ====================================== --}}
        <div class="col-lg-5 mb-4">
            <div class="card glass-card shadow-sm mb-3">

                <div class="card-header glass-card-header fw-bold py-3">
                    Configuración del Pedido
                </div>

                <div class="card-body">
                    <form id="formCotizador">

                        {{-- MÓDULO 1: ESTRUCTURA BASE --}}
                        <div class="card glass-subcard mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">1. ESTRUCTURA BASE</h6>
                                <div class="row g-2">
                                    <div class="col-sm-4 col-12">
                                        <label class="form-label text-muted small mb-1">Cant. Bastones</label>
                                        <input type="number" id="inputCantidad" name="cantidad_total_bastones" class="form-control glass-input" value="1" min="1">
                                    </div>
                                    <div class="col-sm-8 col-12">
                                        <label class="form-label text-muted small mb-1">Tamaño Específico</label>
                                        <select id="selectTamano" class="form-select glass-select">
                                            <option value="" selected disabled>Seleccione medida...</option>
                                            <option value="45">45 cm (Consumo Pequeño)</option>
                                            <option value="50">50 cm (Consumo Pequeño)</option>
                                            <option value="55">55 cm (Consumo Grande)</option>
                                            <option value="60">60 cm (Consumo Grande)</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label text-muted small mb-1">Acabado del Bastón</label>
                                        <select id="selectAcabado" class="form-select glass-select">
                                            <option value="plata" selected>Plata</option>
                                            <option value="dorado">Dorado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MÓDULO 2: CUERPO (LANA) --}}
                        <div class="card glass-subcard mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">2. CUERPO (LANA)</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Cantidad de Colores</label>
                                    <select id="selectCantidadColores" class="form-select glass-select">
                                        <option value="1" selected>1 Color (Sólido)</option>
                                        <option value="2">2 Colores (50/50)</option>
                                        <option value="3">3 Colores (33/33/33)</option>
                                    </select>
                                </div>
                                <div id="contenedorColoresLana" class="d-flex flex-column gap-2 mt-2"></div>
                            </div>
                        </div>

                        {{-- MÓDULO 3: CORTINAS --}}
                        <div class="card glass-subcard mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">3. CORTINAS (OPCIONALES)</h6>

                                {{-- Cortina de Lana --}}
                                <div class="border glass-border rounded p-3 mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swCortinaLana">
                                        <label class="form-check-label fw-bold" for="swCortinaLana">Incluir Cortina de Lana</label>
                                    </div>
                                    <div id="cajaCortinaLana" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-1">Cantidad de Colores (Lana)</label>
                                        <select id="selectCantCortinaLana" class="form-select glass-select form-select-sm mb-2">
                                            <option value="1" selected>1 Color</option>
                                            <option value="2">2 Colores</option>
                                            <option value="3">3 Colores</option>
                                        </select>
                                        <div id="contenedorCortinasLana" class="d-flex flex-column gap-2 mt-2"></div>
                                    </div>
                                </div>

                                {{-- Cortina de Fiesta --}}
                                <div class="border glass-border rounded p-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swCortinaFiesta">
                                        <label class="form-check-label fw-bold" for="swCortinaFiesta">Incluir Cortinas de Fiesta</label>
                                    </div>
                                    <div id="cajaCortinaFiesta" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-1">Cantidad de Colores (Fiesta)</label>
                                        <select id="selectCantCortinaFiesta" class="form-select glass-select form-select-sm mb-2">
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
                        <div class="card glass-subcard mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">4. DECORACIÓN Y APLIQUES</h6>

                                <p class="text-muted small fw-bold border-bottom glass-border pb-1 mb-2">Lazos (Inventario)</p>

                                <div class="border glass-border rounded p-2 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swLazoSimple">
                                        <label class="form-check-label fw-bold" for="swLazoSimple">Lazo Simple (1.5m)</label>
                                    </div>
                                    <div id="cajaLazoSimple" class="mt-2 d-none">
                                        <select class="form-select glass-select select2-ajax-cintas" name="cinta_lazo_simple">
                                            <option value="" selected disabled>Buscar color de cinta...</option>
                                        </select>
                                    </div>
                                </div>  

                                <div class="border glass-border rounded p-2 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swLazoFlor">
                                        <label class="form-check-label fw-bold" for="swLazoFlor">Flores (1.0m c/u)</label>
                                    </div>
                                    <div id="cajaLazoFlor" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-1">Cantidad de Flores</label>
                                        <select id="selectCantFlores" class="form-select glass-select form-select-sm mb-2">
                                            <option value="1" selected>1 Flor</option>
                                            <option value="2">2 Flores</option>
                                            <option value="3">3 Flores</option>
                                            <option value="4">4 Flores</option>
                                            <option value="5">5 Flores</option>
                                        </select>
                                        <div id="contenedorFlores" class="d-flex flex-column gap-2 mt-2"></div>
                                    </div> 
                                </div> 

                                <div class="border glass-border rounded p-2 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swLazoNombre">
                                        <label class="form-check-label fw-bold" for="swLazoNombre">Lazo c/ Nombre (1.0m + $0.70)</label>
                                    </div>
                                    <div id="cajaLazoNombre" class="mt-2 d-none">
                                        <select class="form-select glass-select select2-ajax-cintas" name="cinta_lazo_nombre">
                                            <option value="" selected disabled>Buscar color de cinta...</option>
                                        </select>
                                    </div>
                                </div> 

                                <p class="text-muted small fw-bold border-bottom glass-border pb-1 mb-2 mt-3">Detalles Manuales (Solo Cotización)</p>

                                <div class="border glass-border rounded p-2 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swApliques">
                                        <label class="form-check-label fw-bold" for="swApliques">Incluir Apliques ($0.50 c/u)</label>
                                    </div>
                                    <div id="cajaApliques" class="mt-2 d-none">
                                        <label class="form-label text-muted small mb-0">Cantidad de apliques por bastón</label>
                                        <input type="number" class="form-control glass-input form-control-sm w-50" id="cantApliques" value="0" min="1">
                                    </div>
                                </div>  

                            </div>  
                        </div>  

                        {{-- MÓDULO 5: DISEÑOS PERSONALIZADOS --}}
                        <div class="card glass-subcard mb-3 border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="text-secondary fw-bold mb-3 small">5. DISEÑOS PERSONALIZADOS (OPCIONAL)</h6>

                                <div class="border glass-border rounded p-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input glass-switch" type="checkbox" id="swDisenoPersonalizado">
                                        <label class="form-check-label fw-bold" for="swDisenoPersonalizado">
                                            <i class="fa-solid"></i> Incluir Diseño Especial en Lana
                                        </label>
                                    </div>

                                    <div id="cajaDisenoPersonalizado" class="mt-3 d-none">
                                        <label class="form-label text-muted small mb-1">Nivel de Complejidad (Mano de Obra)</label>
                                        <select class="form-select glass-select form-select-sm" id="selectNivelDiseno" name="tarifa_diseno">
                                            <option value="1.50" selected>Básico ($1.50)</option>
                                            <option value="2.00">Intermedio ($2.00)</option>
                                            <option value="3.00">Premium ($3.00)</option>
                                        </select>
                                        <div class="form-text mt-2 text-muted" style="font-size: 0.75rem;">
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
            
            <div class="vista-previa-container">
                
                <div class="card glass-card shadow-sm mb-3 card-vista-previa">
                    <div class="card-header glass-card-header fw-bold py-3 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-boxes-stacked"></i> Vista Previa: Impacto en Inventario</span>
                        <span id="lblResumenBastones" class="badge glass-badge-warning">0 Bastones</span>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table glass-table align-middle mb-0" id="tablaImpacto">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Material</th>
                                        <th style="width: 30%;">Cálculo Interno</th>
                                        <th style="width: 15%;">Subtotal</th>
                                        <th class="text-end" style="width: 20%;">A Descontar</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaImpacto">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-calculator fa-2x mb-2" style="opacity: 0.5;"></i><br>
                                            Esperando configuración del pedido...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card glass-card shadow-sm card-costos-totales">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center border-bottom glass-border pb-2 mb-2">
                            <span class="text-muted fw-bold">Costo Base Materiales:</span>
                            <span class="fs-5 fw-bold" id="txtCostoMateriales">$ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom glass-border pb-2 mb-2">
                            <span class="text-muted fw-bold">Extras:</span>
                            <span class="fs-5 fw-bold" id="txtCostoManoObra">$ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary fw-bold">Mano de Obra (Ganancia Base):</span>
                            <span class="fs-5 fw-bold" id="txtGananciaFija">$ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold fs-5" style="color: #16a34a;">COSTO TOTAL PRODUCCIÓN:</span>
                            <span class="fs-3 fw-bold" style="color: #16a34a;" id="txtCostoTotal">$ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted small">Costo sugerido por unidad:</span>
                            <span class="fw-bold text-secondary" id="txtCostoUnitario">$ 0.00 c/u</span>
                        </div>
                        
                        <button type="button" class="btn glass-btn-primary w-100 fw-bold mt-4 py-2" id="btnGuardarCotizacion" disabled>
                            <i class="fa-solid fa-floppy-disk"></i> Confirmar y Guardar Pedido
                        </button>
                    </div>
                </div>

                {{-- PANEL DE EXPORTACIÓN --}}
                <div id="panelExportar" class="panel-exportar mt-4 d-none">
                    <div class="card glass-card border-0 rounded-4">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                                     style="width:44px; height:44px; min-width:44px; background: rgba(22, 163, 74, 0.12); border: 1px solid rgba(22, 163, 74, 0.3);">
                                    <i class="fa-solid fa-check" style="font-size:17px; color: #16a34a;"></i>
                                </div>
                                <div>
                                    <p class="fw-semibold mb-0" style="font-size:15px;">
                                        Pedido guardado &mdash; #<span id="txtNumeroCotizacionExport">---</span>
                                    </p>
                                    <p class="text-muted mb-0" style="font-size:13px;">¿Qué deseas hacer ahora?</p>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <a href="#" target="_blank" class="btn glass-btn-action w-100 py-2" id="btnPdfReceta" style="color: var(--color-error) !important;">
                                            <i class="fa-solid fa-clipboard-list me-2"></i> Receta Bodega
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <a href="#" target="_blank" class="btn glass-btn-action w-100 py-2" id="btnPdfNota" style="color: var(--accent-purple) !important;">
                                            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Nota de Venta
                                        </a>
                                    </div>
                                </div>

                                <button type="button" class="btn glass-btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalEnviarCorreo">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Enviar Nota por Correo
                                </button>

                                <hr class="glass-border opacity-25 my-2">

                                <button type="button" class="btn glass-btn-action text-secondary w-100 py-2" id="btnCerrarFlujoExportacion">
                                    <i class="fa-solid fa-rotate-right me-2"></i> Cerrar y Crear Nuevo Pedido
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>{{-- /vista-previa-container --}}

        </div>
    </div>{{-- /row --}}

    {{-- ==========================================
         MODALES CON ESTILO NEUMÓRFICO PASTEL
    ========================================== --}}

    {{-- MODAL: ENVIAR COTIZACIÓN POR CORREO --}}
    <div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-labelledby="modalCorreoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header glass-card-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalCorreoLabel"><i class="fa-solid fa-envelope" style="color: var(--accent-purple);"></i> Enviar Nota de Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Se adjuntará un PDF con la nota de venta al cliente. (Los detalles internos de producción no serán enviados).</p>
                    <div class="mb-3">
                        <label for="emailCliente" class="form-label text-muted small fw-bold">Correo electrónico del cliente</label>
                        <input type="email" class="form-control glass-input" id="emailCliente" placeholder="ejemplo@correo.com">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn glass-btn-action px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn glass-btn-primary fw-bold px-4" id="btnConfirmarEnvio">
                        <i class="fa-solid fa-paper-plane"></i> Enviar Ahora
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: DATOS DEL CLIENTE (PRE-GUARDADO) --}}
    <div class="modal fade" id="modalDatosCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content glass-card">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h6 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-tag me-2" style="color: var(--accent-purple);"></i> Finalizar Pedido
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <p class="text-muted small mb-4">Ingresa los datos o vincula una solicitud de la página web.</p>
                    
                    {{-- NUEVO CAMPO: Selector de Solicitud Web --}}
                    <div class="mb-4 p-3 rounded-3" style="background-color: rgba(var(--accent-purple-rgb), 0.05); border: 1px dashed rgba(var(--accent-purple-rgb), 0.3);">
                        <label class="form-label text-muted small fw-bold mb-1">Vincular Solicitud Web (Opcional)</label>
                        <select class="form-control" id="selectSolicitudWeb" style="width: 100%;">
                            <option value="">-- Cliente Presencial (Ninguna) --</option>
                            <!-- Select2 cargará las opciones aquí -->
                        </select>
                    </div>
                    
                    {{-- CAMPOS TRADICIONALES --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Nombre / Institución</label>
                        <input type="text" class="form-control glass-input" id="inputNombreCliente" placeholder="Ej. Unidad Educativa Sucre" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold mb-1">Correo Electrónico (Opcional)</label>
                        <input type="email" class="form-control glass-input" id="inputCorreoCliente" placeholder="ejemplo@correo.com">
                    </div>

                    <button type="button" class="btn glass-btn-primary w-100 fw-bold rounded-3 py-2 shadow-sm" id="btnConfirmarPedidoModal">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Confirmar y Guardar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE ÉXITO --}}
    <div class="modal fade" id="modalExito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content glass-card">
                <div class="modal-body text-center px-4 py-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                         style="width:40px; height:40px; background: rgba(22, 163, 74, 0.12); border: 1px solid rgba(22, 163, 74, 0.3);">
                        <i class="fa-solid fa-check" style="font-size:16px; color: #16a34a;"></i>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size:15px;">Pedido guardado</p>
                    <p class="text-muted mb-0" style="font-size:13px; line-height:1.6;">
                        El pedido <strong>#<span id="modalNumCotizacion"></span></strong>
                        fue registrado exitosamente.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE VALIDACIÓN (El JS inyecta colores, pero este es su HTML base) --}}
    <div class="modal fade" id="modalValidacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content glass-card">
                <div class="px-4 pt-4 pb-0">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                             style="width:36px; height:36px; min-width:36px; margin-top:2px;">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:15px;"></i>
                        </div>
                        <div>
                            <p class="fw-semibold mb-1" id="modalValidacionTitulo"
                               style="font-size:15px; line-height:1.3;">Faltan campos obligatorios</p>
                            <p class="text-muted mb-0" style="font-size:13px;">
                                Completa lo siguiente antes de guardar
                            </p>
                        </div>
                    </div>
                </div>
                <div class="px-4 pt-3 pb-0">
                    <ul id="listaValidacion" class="list-unstyled mb-0 d-flex flex-column gap-2"></ul>
                </div>
                <div class="px-4 pt-3 pb-4">
                    <button type="button" class="btn w-100 rounded-3" data-bs-dismiss="modal"></button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: CORREO ENVIADO CON ÉXITO --}}
    <div class="modal fade" id="modalCorreoExito" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
            <div class="modal-content glass-card">
                <div class="modal-body text-center px-4 py-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                         style="width:40px; height:40px; background: rgba(147, 51, 234, 0.12); border: 1px solid rgba(147, 51, 234, 0.3);">
                        <i class="fa-solid fa-paper-plane" style="font-size:15px; color: var(--accent-purple);"></i>
                    </div>
                    <p class="fw-semibold mb-1" style="font-size:15px;">Correo enviado</p>
                    <p class="text-muted mb-4" style="font-size:13px; line-height:1.6;">
                        La nota de venta fue despachada correctamente al cliente.
                    </p>
                    <button type="button" class="btn glass-btn-action w-100 rounded-3 py-2" data-bs-dismiss="modal" style="color: var(--accent-purple) !important;">
                        <i class="fa-solid fa-check me-2"></i> Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ==========================================
     DEPENDENCIAS JS Y PUENTE DE DATOS
========================================== --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="application/json" id="datos-inventario">
    @json($insumos)
</script>

<script>
    window.KardexConfig = {
        rutas: {
            buscarLanas: "{{ route('lanas.buscar') }}",
            buscarCortinas: "{{ route('cortinas.buscar') }}",
            buscarCintas: "{{ route('cintas.buscar') }}"
        }
    };
</script>

<script>
    window.KardexConfig = {
        rutas: {
            buscarLanas: "{{ route('lanas.buscar') }}",
            buscarCortinas: "{{ route('cortinas.buscar') }}",
            buscarCintas: "{{ route('cintas.buscar') }}",
            // Agregamos la ruta para buscar las solicitudes web
            buscarSolicitudes: "{{ route('admin.solicitudes.pendientes') }}" 
        }
    };
</script>

@push('js')
    @vite(['resources/js/cotizador.js'])
@endpush

@endsection