@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/variables.css', 'resources/css/ventas.css'])
@endpush

@section('contenido')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--text-main);">Historial de Ventas y Cotizaciones</h2>
        <span class="badge" style="background-color: var(--accent-purple); color: #fff;">Módulo Operativo</span>
    </div>

    @include('Ventas.partials._kpis')
    @include('Ventas.partials._filtros')
    @include('Ventas.partials._tabla')

</div>

    <!-- Modal de Vista Rápida de Materiales -->
    <div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-panel" style="background-color: var(--bg-elevated) !important;">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold text-accent">Materiales del Pedido <span id="modal-pedido-id"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
             <div class="text-center p-4 text-lavanda" id="modal-loading">
                 Cargando detalles del diseño...
             </div>
             <!-- Aquí se inyectará la lista con JavaScript -->
             <ul class="list-group list-group-flush" id="modal-lista-materiales" style="display:none; border-radius: 0 0 12px 12px;">
             </ul>
          </div>
        </div>
      </div>
    </div>

<!-- MODAL: Desglose Financiero Referencial -->
<div class="modal fade" id="modalFinanciero" tabindex="-1" aria-labelledby="modalFinancieroLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none; background-color: var(--bg-base); box-shadow: 0 8px 24px rgba(59, 7, 100, 0.15);">

            <div class="modal-header pb-0 border-0" style="background-color: transparent;">
                <h5 class="modal-title fw-bold" id="modalFinancieroLabel" style="color: var(--text-main);">
                    <i class="fa-solid fa-chart-pie me-2" style="color: var(--accent-purple);"></i> Desglose Estimado de {{ $nombreMes }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-4">

                <!-- Ingresos Brutos -->
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background-color: var(--bg-base); box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light); border-radius: 12px;">
                    <div>
                        <span class="d-block" style="color: var(--text-muted); font-size: 0.85rem;">Ingresos Estimados (Ventas Totales)</span>
                        <strong class="fs-5" style="color: var(--text-main);">${{ number_format($ingresosMes, 2) }}</strong>
                    </div>
                    <i class="fa-solid fa-cash-register fs-3 opacity-50" style="color: var(--accent-purple);"></i>
                </div>

                <!-- Desglose: Insumos vs Mano de Obra -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 rounded h-100" style="background-color: var(--bg-base); box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light); border-radius: 12px;">
                            <span class="d-block mb-1" style="font-size: 0.80rem; font-weight: 600; color: var(--color-error);">
                                <i class="fa-solid fa-boxes-packing me-1"></i> Costo Insumos
                            </span>
                            <strong class="fs-5" style="color: var(--color-error);">${{ number_format($costoInsumosEstimado, 2) }}</strong>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="p-3 rounded h-100" style="background-color: var(--bg-base); box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light); border-radius: 12px;">
                            <span class="d-block mb-1" style="font-size: 0.80rem; font-weight: 600; color: #16a34a;">
                                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Margen / Mano de Obra
                            </span>
                            <strong class="fs-5" style="color: #16a34a;">${{ number_format($manoObraEstimada, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Nota Aclaratoria Técnica -->
                <div class="alert mt-4 mb-0" style="background-color: var(--bg-base); box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light); color: var(--text-muted); font-size: 0.8rem; border-radius: 10px; border: none;">
                    <i class="fa-solid fa-triangle-exclamation me-1" style="color: #d97706;"></i>
                    <strong style="color: var(--text-main);">Nota Metodológica:</strong> Este es un cálculo referencial de flujo de caja. Para ensamblajes, el margen se calcula restando el costo exacto de materiales al precio final (incluyendo diseños). Para manufactura directa, se proyecta un margen artesanal del 60%. No reemplaza un balance contable estricto.
                </div>

            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-neu-glow w-100 justify-content-center" data-bs-dismiss="modal">Entendido</button>
            </div>

        </div>
    </div>
</div>

    {{-- MODAL: VINCULAR PEDIDO Y ENVIAR CORREO --}}
    <div class="modal fade" id="modalVincularPedido" tabindex="-1" aria-labelledby="modalVincularPedidoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; background-color: var(--bg-base); box-shadow: 0 8px 24px rgba(59, 7, 100, 0.15);">

                <div class="modal-header pb-0 border-0" style="background-color: transparent;">
                    <h5 class="modal-title fw-bold" id="modalVincularPedidoLabel" style="color: var(--text-main);">
                        <i class="fa-solid fa-link me-2" style="color: var(--accent-purple);"></i> Vincular Pedido #<span id="txtVincularPedidoId"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-4">

                    {{-- Selector para vincular con la web --}}
                    <div class="mb-3 p-3 rounded" style="background-color: var(--bg-base); box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light); border-radius: 12px;">
                        <span class="d-block mb-1" style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Vincular Solicitud Web</span>
                        <p class="mb-2" style="color: var(--text-muted); font-size: 0.75rem;">Si este pedido pertenece a un cliente de la web, búscalo aquí para sincronizar su portal.</p>
                        <select class="form-control" id="selectVincularWeb" style="width: 100%;">
                            <option value="">-- Buscar solicitud pendiente --</option>
                        </select>
                    </div>

                    {{-- Opcional: Reenviar correo --}}
                    <div class="mb-3 p-3 rounded" style="background-color: var(--bg-base); box-shadow: inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light); border-radius: 12px;">
                        <span class="d-block mb-2" style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                            <i class="fa-solid fa-envelope me-1"></i> Reenviar Nota de Venta (Opcional)
                        </span>
                        <input type="email" class="form-control" id="inputReenviarCorreo" placeholder="ejemplo@correo.com"
                            style="background-color: #f3ebff; border: 1px solid rgba(var(--accent-purple-rgb), 0.15); border-radius: 8px; color: var(--text-main); padding: 0.5rem 0.75rem;">
                    </div>

                    {{-- Nota Aclaratoria --}}
                    <div class="alert mt-3 mb-0" style="background-color: var(--bg-base); box-shadow: inset 3px 3px 6px var(--shadow-dark), inset -3px -3px 6px var(--shadow-light); color: var(--text-muted); font-size: 0.8rem; border-radius: 10px; border: none;">
                        <i class="fa-solid fa-circle-info me-1" style="color: var(--accent-purple);"></i>
                        Al vincular, el estado se sincronizará automáticamente con el portal del cliente.
                    </div>

                    {{-- Campo oculto para el ID del pedido físico --}}
                    <input type="hidden" id="hiddenPedidoFisicoId">

                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn-neu-glow w-100 justify-content-center" id="btnProcesarVinculacion">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Actualizar Pedido
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- =========================================================
         OFFCANVAS: TOP 5 MODELOS MÁS POPULARES
    ========================================================= -->
    <div class="offcanvas offcanvas-end shadow" tabindex="-1" id="offcanvasTop5" style="background-color: var(--bg-elevated); border-left: 1px solid var(--shadow-dark);">
        <div class="offcanvas-header border-bottom glass-border">
            <h5 class="offcanvas-title fw-bold" style="color: var(--text-main);">
                <i class="fa-solid fa-fire text-danger me-2"></i> Top 5 Más Consultados
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body px-4">
            <p class="text-muted small mb-4">
                Estos son los diseños del catálogo que más interés están generando en los clientes a través de la página web.
            </p>
            
            @if($top5Populares->isEmpty())
                <div class="text-center text-muted mt-5">
                    <i class="fa-solid fa-chart-line fa-3x mb-3 opacity-50"></i>
                    <p>Aún no hay suficientes datos de consultas.</p>
                </div>
            @else
                <div class="list-group list-group-flush border-0">
                    @foreach($top5Populares as $index => $item)
                    <div class="list-group-item d-flex align-items-center gap-3 bg-transparent py-3 glass-border border-bottom">
                        
                        <!-- Posición (1, 2, 3...) -->
                        <div class="fw-bold fs-5 text-muted opacity-50" style="width: 20px;">
                            #{{ $index + 1 }}
                        </div>

                        <!-- Imagen redondita -->
                        <img src="{{ asset('storage/' . $item->imagen_path) }}" class="rounded-circle object-fit-cover border shadow-sm" width="55" height="55" alt="{{ $item->titulo }}">
                        
                        <!-- Info del diseño -->
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold" style="color: var(--accent-purple);">{{ $item->titulo }}</h6>
                            <small class="text-muted d-block" style="font-size: 0.8rem;">
                                Cat: {{ ucfirst($item->categoria) }}
                            </small>
                        </div>

                        <!-- El contador (Fuego) -->
                        <div class="text-end">
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold">
                                <i class="fa-solid fa-fire me-1"></i> {{ $item->contador_consultas }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js') 
    <!-- CDN de SweetAlert2 (por si no lo tienes globalmente en tu layout) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Tu archivo JS compilado por Vite -->
    @vite(['resources/js/historial.js'])
@endpush