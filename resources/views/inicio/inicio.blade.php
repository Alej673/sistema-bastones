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
         FILA 1: TARJETAS DE INDICADORES (4 KPIs)
         ========================================= -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Ingresos -->
        <div class="col-md-3">
            <div class="card card-kpi">
                <div class="card-body">
                    <h6 class="kpi-titulo">Ingresos del Mes</h6>
                    <h3 class="kpi-valor">$ {{ number_format($ingresosMes ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <!-- KPI 2: En Producción -->
        <div class="col-md-3">
            <div class="card card-kpi">
                <div class="card-body">
                    <h6 class="kpi-titulo">En Producción</h6>
                    <h3 class="kpi-valor color-morado">{{ $enProduccion ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <!-- KPI 3: Pendientes -->
        <div class="col-md-3">
            <div class="card card-kpi">
                <div class="card-body">
                    <h6 class="kpi-titulo">Cotizaciones Pendientes</h6>
                    <h3 class="kpi-valor color-fucsia">{{ $cotizacionesPendientes ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <!-- KPI 4: Alertas Críticas -->
        <div class="col-md-3">
            <div class="card card-kpi">
                <div class="card-body">
                    <h6 class="kpi-titulo" style="color: var(--color-error) !important;">Alertas de Inventario</h6>
                    <h3 class="kpi-valor color-peligro">{{ count($alertasStock ?? []) }}</h3>
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
                    <div class="d-flex flex-column">
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
                    <div class="d-flex flex-column">
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
                    <div class="d-flex flex-column">
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
                                <a href="{{ route('insumos.index') }}" class="btn-neu-icon"  title="Ir al Kardex">
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