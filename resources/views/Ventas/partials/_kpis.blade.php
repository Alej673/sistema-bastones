<div class="row g-3 mb-4">
    <!-- Ingresos del Mes -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: #2c1548; border-radius: 12px;">
            <div class="card-body">
                <h6 style="color: #b9a8c9;">Ingresos (Realizados)</h6>
                <h3 class="fw-bold mb-0" style="color: #4ade80;">$ {{ number_format($ingresosMes, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Pedidos en Producción -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: #2c1548; border-radius: 12px;">
            <div class="card-body">
                <h6 style="color: #b9a8c9;">En Producción</h6>
                <h3 class="fw-bold mb-0" style="color: #e879f9;">{{ $enProduccion }}</h3>
            </div>
        </div>
    </div>

    <!-- Cotizaciones Pendientes -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: #2c1548; border-radius: 12px;">
            <div class="card-body">
                <h6 style="color: #b9a8c9;">Cotizaciones Pendientes</h6>
                <h3 class="fw-bold mb-0" style="color: #c084fc;">{{ $cotizacionesPendientes }}</h3>
            </div>
        </div>
    </div>

    <!-- Producto Estrella -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: #2c1548; border-radius: 12px;">
            <div class="card-body">
                <h6 style="color: #b9a8c9;">Base Más Solicitada</h6>
                <h5 class="fw-bold mb-0" style="color: #f5eaff;">{{ $nombreBaseEstrella }}</h5>
            </div>
        </div>
    </div>
</div>