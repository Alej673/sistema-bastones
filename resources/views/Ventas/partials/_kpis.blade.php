<div class="row g-3 mb-4">
    <!-- Ingresos del Mes -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm card-panel card-kpi">
            <div class="card-body">
                <h6 class="text-lavanda">Ingresos (Realizados)</h6>
                <h3 class="fw-bold mb-0" style="color: #3B0764;">$ {{ number_format($ingresosMes, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Pedidos en Producción -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm card-panel card-kpi">
            <div class="card-body">
                <h6 class="text-lavanda">En Producción</h6>
                <h3 class="fw-bold mb-0" style="color: #d97706;">{{ $enProduccion }}</h3>
            </div>
        </div>
    </div>

    <!-- Cotizaciones Pendientes -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm card-panel card-kpi">
            <div class="card-body">
                <h6 class="text-lavanda">Cotizaciones Pendientes</h6>
                <h3 class="fw-bold mb-0 text-accent">{{ $cotizacionesPendientes }}</h3>
            </div>
        </div>
    </div>

    <!-- Producto Estrella -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm card-panel card-kpi">
            <div class="card-body">
                <h6 class="text-lavanda">Base Más Solicitada</h6>
                <h5 class="fw-bold mb-0" style="color: var(--text-main);">{{ $nombreBaseEstrella }}</h5>
            </div>
        </div>
    </div>
</div>