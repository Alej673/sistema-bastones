@extends('layouts.admin')

@push('css')
    @vite(['resources/css/inico.css'])
@endpush

@section('contenido')
<div class="container-fluid pt-3 pb-4">
    
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-lavanda fw-bold mb-0">Panel de Control</h2>
        <span class="text-muted small">{{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
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
                    <h3 class="kpi-valor color-exito">$ {{ number_format($ingresosMes ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <!-- KPI 2: En Producción -->
        <div class="col-md-3">
            <div class="card card-kpi">
                <div class="card-body">
                    <h6 class="kpi-titulo">En Producción</h6>
                    <h3 class="kpi-valor color-morado">{{ $pedidosEnProduccion ?? 0 }}</h3>
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
        <!-- KPI 4: Alertas Críticas (Nuevo) -->
        <div class="col-md-3">
            <div class="card card-kpi" style="border-color: rgba(248, 113, 113, 0.4);">
                <div class="card-body">
                    <h6 class="kpi-titulo" style="color: #fca5a5;">Alertas de Inventario</h6>
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
                    <h5 class="card-panel-title text-accent">Últimos Pedidos Registrados</h5>
                    <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-secondary" style="border-color: var(--color-morado-oscuro); color: var(--color-texto-mutado);">Ver Todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
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
                                        <td class="ps-4 text-muted fw-bold">#{{ str_pad($actividad->id, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $actividad->cliente_nombre }}</td>
                                        <td>$ {{ number_format($actividad->costo_total, 2) }}</td>
                                        <td>
                                            <span class="badge" style="background-color: {{ $actividad->estado == 'Realizado' ? 'rgba(74, 222, 128, 0.15)' : 'rgba(192, 132, 252, 0.15)' }}; color: {{ $actividad->estado == 'Realizado' ? 'var(--color-exito)' : 'var(--color-morado-claro)' }}; border: 1px solid {{ $actividad->estado == 'Realizado' ? 'rgba(74, 222, 128, 0.3)' : 'rgba(192, 132, 252, 0.3)' }};">
                                                {{ ucfirst(str_replace('_', ' ', $actividad->estado)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No hay actividad reciente.</td>
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
        
        <!-- Columna Izquierda (4): Deudas de Inventario -->
        <div class="col-lg-4">
            <div class="card card-panel" style="border-color: rgba(245, 158, 11, 0.4);">
                <div class="card-panel-header" style="border-bottom-color: rgba(245, 158, 11, 0.2);">
                    <h5 class="card-panel-title" style="color: #f59e0b; font-size: 1rem;">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Deuda (Negativo)
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        @forelse($alertasStock ?? [] as $alerta)
                            <div class="d-flex justify-content-between align-items-center p-2 rounded" style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">
                                <div>
                                    <span class="d-block text-white fw-bold" style="font-size: 0.9rem;">{{ $alerta->nombre }}</span>
                                    <span class="badge bg-danger text-white mt-1">Saldo: {{ $alerta->stock_actual }}</span>
                                </div>
                                <button onclick="arreglarStock({{ $alerta->id }})" class="btn btn-sm btn-arreglar-stock" style="background-color: #f59e0b; color: #000; font-size: 0.75rem; font-weight: 600;">
                                    Arreglar a 0
                                </button>
                            </div>
                        @empty
                            <div class="text-muted small fst-italic text-center py-3">No hay deudas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Central (4): Insumos Huérfanos -->
        <div class="col-lg-4">
            <div class="card card-panel" style="border-color: rgba(239, 68, 68, 0.4);">
                <div class="card-panel-header" style="border-bottom-color: rgba(239, 68, 68, 0.2);">
                    <h5 class="card-panel-title" style="color: #ef4444; font-size: 1rem;">
                        <i class="fa-solid fa-circle-xmark me-2"></i> No Encontrados
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        @forelse($materialesHuerfanos ?? [] as $huerfano)
                            <div class="d-flex justify-content-between align-items-center p-2 rounded mb-1" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);" id="alerta-huerfano-{{ $huerfano['detalle_id'] }}">
                                <div>
                                    <span class="d-block text-white fw-bold" style="font-size: 0.9rem;">{{ $huerfano['nombre_material'] }}</span>
                                    <span class="text-muted d-block mt-1" style="font-size: 0.8rem;">En Pedido #{{ str_pad($huerfano['pedido_id'], 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <button onclick="descartarHuerfano({{ $huerfano['detalle_id'] }})" class="btn btn-sm btn-link text-danger p-0" title="Ignorar esta alerta" style="text-decoration: none; font-size: 1.5rem; line-height: 1;">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <div class="text-muted small fst-italic text-center py-3">Todo enlazado.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha (4): Stock Bajo / Por Agotarse -->
        <div class="col-lg-4">
            <div class="card card-panel" style="border-color: rgba(59, 130, 246, 0.4);">
                <div class="card-panel-header" style="border-bottom-color: rgba(59, 130, 246, 0.2);">
                    <h5 class="card-panel-title" style="color: #3b82f6; font-size: 1rem;">
                        <i class="fa-solid fa-boxes-stacked me-2"></i> Por Agotarse
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        @forelse($insumosBajos ?? [] as $insumo)
                            <div class="d-flex justify-content-between align-items-center p-2 rounded" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="d-block text-white fw-bold" style="font-size: 0.9rem;">{{ $insumo->nombre }}</span>
                                        
                                        <!-- Insignia dinámica: Rojo si es 0, Amarillo si aún queda algo -->
                                        @if($insumo->stock_actual <= 0)
                                            <span class="badge ms-2" style="background-color: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); font-size: 0.65rem;">Agotado</span>
                                        @else
                                            <span class="badge ms-2" style="background-color: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); font-size: 0.65rem;">Bajo</span>
                                        @endif
                                    </div>
                                    
                                    <span class="text-muted d-block" style="font-size: 0.8rem;">Actual: {{ $insumo->stock_actual }} | Mín: {{ $insumo->stock_minimo }}</span>
                                </div>
                                
                                <!-- Botón corregido usando el helper route() de Laravel -->
                                <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-link p-0" style="color: #3b82f6;" title="Ir al Kardex para reabastecer">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        @empty
                            <div class="text-muted small fst-italic text-center py-3">Inventario óptimo.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

@push('js')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Función para borrar la alerta ROJA (Material no encontrado)
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
            tarjeta.style.transform = "translateX(20px)"; // Efecto de deslizar a la derecha
            setTimeout(() => tarjeta.remove(), 300);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para sanear la deuda AMARILLA (Poner stock a 0)
function arreglarStock(insumoId) {
    Swal.fire({
        title: '¿Saldar deuda de inventario?',
        text: "Esto inyectará material al Kardex para que el stock vuelva a 0.",
        icon: 'warning',
        showCancelButton: true,
        background: 'var(--color-fondo-medio)',
        color: '#fff',
        confirmButtonColor: 'var(--color-violeta-boton)',
        cancelButtonColor: '#ef4444',
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
                        background: 'var(--color-fondo-medio)',
                        color: '#fff',
                        confirmButtonColor: 'var(--color-violeta-boton)'
                    }).then(() => {
                        window.location.reload(); // Recargamos para actualizar los KPIs
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