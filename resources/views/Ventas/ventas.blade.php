@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/ventas.css'])
@endpush

@section('contenido')
<div class="container-fluid py-4" style="background-color: #1b0f28; min-height: 100vh; color: #f5eaff;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="color-texto fw-bold mb-0">Historial de Ventas y Cotizaciones</h2>
        <span class="badge" style="background-color: #5b21b6;">Módulo Operativo</span>
    </div>

    @include('Ventas.partials._kpis')
    @include('Ventas.partials._filtros')
    @include('Ventas.partials._tabla')

</div>

    <!-- Modal de Vista Rápida de Materiales -->
    <div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--color-fondo-medio); border: 1px solid var(--color-morado-oscuro);">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold text-accent">Materiales del Pedido <span id="modal-pedido-id"></span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
@endsection

@push('js') 
    <!-- CDN de SweetAlert2 (por si no lo tienes globalmente en tu layout) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Tu archivo JS compilado por Vite -->
    @vite(['resources/js/historial.js'])
@endpush