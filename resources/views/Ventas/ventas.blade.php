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
                    <strong style="color: var(--text-main);">Nota:</strong> Este es un cálculo referencial de flujo de caja para manufactura. El margen de mano de obra se estima en $3.00 base por bastón fabricado. No reemplaza un balance contable estricto.
                </div>

            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn-neu-glow w-100 justify-content-center" data-bs-dismiss="modal">Entendido</button>
            </div>

        </div>
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