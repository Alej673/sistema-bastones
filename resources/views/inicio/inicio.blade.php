@extends('layouts.admin')

@push('css')
    @vite(['resources/css/inico.css' , 'resources/css/variables.css'])
@endpush

@section('contenido')
<div class="container-fluid pt-3 pb-4">
    
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--text-main);">Panel de Control</h2>
        <span class="small" style="color: var(--text-muted);">{{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
    </div>

    <!-- =========================================
     FILA 1: TARJETAS DE INDICADORES (4 KPIs) — MISMO DISEÑO
     ========================================= -->
    <div class="row g-3 mb-4">

        <!-- KPI 1: Flujo Operativo -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-kpi kpi-clickable" data-bs-toggle="modal" data-bs-target="#modalFinanciero">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="kpi-titulo">
                                Flujo Operativo ({{ $nombreMes }})
                            </h6>
                            <h3 class="kpi-valor color-exito">${{ number_format($ingresosMes, 2) }}</h3>
                        </div>
                        <div class="icon-shape icon-exito">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                    </div>
                    <span class="kpi-hint">Click para ver desglose</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: En Producción -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-kpi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="kpi-titulo">En Producción</h6>
                            <h3 class="kpi-valor color-morado">{{ $enProduccion ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-morado">
                            <i class="fa-solid fa-industry"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Cotizaciones Pendientes -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-kpi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="kpi-titulo">Cotizaciones Pendientes</h6>
                            <h3 class="kpi-valor color-fucsia">{{ $cotizacionesPendientes ?? 0 }}</h3>
                        </div>
                        <div class="icon-shape icon-fucsia">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 4: Alertas de Inventario -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-kpi">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="kpi-titulo" style="color: var(--color-error) !important;">Alertas de Inventario</h6>
                            <h3 class="kpi-valor color-peligro">
                                {{ count($alertasStock ?? []) + count($materialesHuerfanos ?? []) + count($insumosBajos ?? []) }}
                            </h3>
                        </div>
                        <div class="icon-shape icon-peligro">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- =========================================
         FILA 2: TABLA PRINCIPAL (ANCHO COMPLETO - 12)
         ========================================= -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-panel">
                <div class="card-panel-header d-flex justify-content-between align-items-center">
                    <h5 class="card-panel-title">Últimos Pedidos Registrados</h5>
                    <a href="{{ route('ventas.index') }}" class="btn-neu-glow" >Ver Todos</a>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <!-- Cambio: Quitamos table-dark y usamos table-custom -->
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">N° Doc</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actividadReciente ?? [] as $actividad)
                                    <tr>
                                        <td class="ps-4 fw-bold" style="color: var(--text-muted);">#{{ str_pad($actividad->id, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td class="fw-bold">{{ $actividad->cliente_nombre }}</td>
                                        <td>$ {{ number_format($actividad->costo_total, 2) }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $actividad->estado == 'Realizado' ? 'rgba(22, 163, 74, 0.15)' : 'rgba(147, 51, 234, 0.15)' }}; color: {{ $actividad->estado == 'Realizado' ? '#16a34a' : 'var(--accent-purple)' }};">
                                                {{ ucfirst(str_replace('_', ' ', $actividad->estado)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4" style="color: var(--text-muted);">No hay actividad reciente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================
         FILA 3: PANELES DE ALERTAS DIVIDIDOS (4, 4 y 4)
         ========================================= -->
    <div class="row g-3">
        
        <!-- Columna Izquierda: Deudas -->
        <div class="col-lg-4">
            <div class="card card-panel">
                <div class="card-panel-header">
                    <h5 class="card-panel-title" style="color: #d97706; font-size: 1rem;">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Deuda (Negativo)
                    </h5>
                </div>
                <div class="card-body p-3 mt-2">
                    <div class="d-flex flex-column lista-alertas-scroll">
                        @forelse($alertasStock ?? [] as $alerta)
                            <div class="alert-item">
                                <div>
                                    <span class="alert-item-title">{{ $alerta->nombre }}</span>
                                    <span class="badge bg-danger text-white mt-1">Saldo: {{ $alerta->stock_actual }}</span>
                                </div>
                                <button onclick="arreglarStock({{ $alerta->id }})" class="btn-neu-warning">
                                    Arreglar a 0
                                </button>
                            </div>
                        @empty
                            <div class="small fst-italic text-center py-3" style="color: var(--text-muted);">No hay deudas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Central: Insumos Huérfanos -->
        <div class="col-lg-4">
            <div class="card card-panel">
                <div class="card-panel-header">
                    <h5 class="card-panel-title" style="color: var(--color-error); font-size: 1rem;">
                        <i class="fa-solid fa-circle-xmark me-2"></i> No Encontrados
                    </h5>
                </div>
                <div class="card-body p-3 mt-2">
                    <div class="d-flex flex-column lista-alertas-scroll">
                        @forelse($materialesHuerfanos ?? [] as $huerfano)
                            <div class="alert-item" id="alerta-huerfano-{{ $huerfano['detalle_id'] }}">
                                <div>
                                    <span class="alert-item-title">{{ $huerfano['nombre_material'] }}</span>
                                    <span class="alert-item-subtitle">En Pedido #{{ str_pad($huerfano['pedido_id'], 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <button onclick="descartarHuerfano({{ $huerfano['detalle_id'] }})" class="btn-neu-close" title="Ignorar esta alerta">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <div class="small fst-italic text-center py-3" style="color: var(--text-muted);">Todo enlazado.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Stock Bajo -->
        <div class="col-lg-4">
            <div class="card card-panel">
                <div class="card-panel-header">
                    <h5 class="card-panel-title" style="color: #2563eb; font-size: 1rem;">
                        <i class="fa-solid fa-boxes-stacked me-2"></i> Por Agotarse
                    </h5>
                </div>
                <div class="card-body p-3 mt-2">
                    <div class="d-flex flex-column lista-alertas-scroll">
                        @forelse($insumosBajos ?? [] as $insumo)
                            <div class="alert-item">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="alert-item-title">{{ $insumo->nombre }}</span>
                                        @if($insumo->stock_actual <= 0)
                                            <span class="badge ms-2" style="background-color: rgba(239, 68, 68, 0.15); color: #dc2626; font-size: 0.65rem;">Agotado</span>
                                        @else
                                            <span class="badge ms-2" style="background-color: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 0.65rem;">Bajo</span>
                                        @endif
                                    </div>
                                    <span class="alert-item-subtitle">Actual: {{ $insumo->stock_actual }} | Mín: {{ $insumo->stock_minimo }}</span>
                                </div>
                                    <a href="{{ route('insumos.index', ['buscar' => $insumo->nombre]) }}" class="btn-neu-icon" title="Ver en Kardex">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                            </div>
                        @empty
                            <div class="small fst-italic text-center py-3" style="color: var(--text-muted);">Inventario óptimo.</div>
                        @endforelse
                    </div>
                </div>
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

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function descartarHuerfano(detalleId) {
    fetch(`/dashboard/alerta/descartar/${detalleId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            let tarjeta = document.getElementById('alerta-huerfano-' + detalleId);
            tarjeta.style.transition = "all 0.3s ease";
            tarjeta.style.opacity = "0";
            tarjeta.style.transform = "translateX(20px)"; 
            setTimeout(() => tarjeta.remove(), 300);
        }
    })
    .catch(error => console.error('Error:', error));
}

function arreglarStock(insumoId) {
    Swal.fire({
        title: '¿Saldar deuda de inventario?',
        text: "Esto inyectará material al Kardex para que el stock vuelva a 0.",
        icon: 'warning',
        showCancelButton: true,
        // ESTILOS DE SWEETALERT ADAPTADOS AL NEUMORFISMO LILA
        background: 'var(--bg-base)',
        color: 'var(--text-main)',
        confirmButtonColor: 'var(--accent-purple)',
        cancelButtonColor: 'var(--color-error)',
        confirmButtonText: 'Sí, arreglar a 0',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/dashboard/stock/arreglar/${insumoId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: '¡Arreglado!',
                        text: 'El Kardex ha sido nivelado a 0.',
                        icon: 'success',
                        background: 'var(--bg-base)',
                        color: 'var(--text-main)',
                        confirmButtonColor: 'var(--accent-purple)'
                    }).then(() => {
                        window.location.reload(); 
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}
</script>
@endpush
@endsection