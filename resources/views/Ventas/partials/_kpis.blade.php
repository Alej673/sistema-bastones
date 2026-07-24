<div class="row g-3 mb-4 align-items-stretch">
    
    <!-- 1. Ingresos del Mes (Con Modal) -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 card-panel card-kpi" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalFinanciero">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-lavanda mb-2" style="font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Flujo ({{ $nombreMes }}) <i class="fa-solid fa-circle-info ms-1 text-accent"></i>
                        </h6>
                        <h3 class="mb-0 fw-bold" style="color: var(--text-main);">
                            ${{ number_format($ingresosMes, 2) }}
                        </h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-sack-dollar fs-5"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.75rem;">
                        Click para ver desglose
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Pedidos en Producción -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 card-panel card-kpi">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-lavanda mb-2" style="font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            En Producción
                        </h6>
                        <h3 class="mb-0 fw-bold" style="color: #d97706;">
                            {{ $enProduccion }}
                        </h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-hammer fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Cotizaciones Pendientes -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 card-panel card-kpi">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-lavanda mb-2" style="font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Cotizaciones
                        </h6>
                        <h3 class="mb-0 fw-bold text-accent">
                            {{ $cotizacionesPendientes }}
                        </h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-file-invoice fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Producto Estrella -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 card-panel card-kpi">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-lavanda mb-2" style="font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            Base Estrella
                        </h6>
                        <h5 class="mb-0 fw-bold text-truncate" style="color: var(--text-main); max-width: 140px;" title="{{ $nombreBaseEstrella }}">
                            {{ $nombreBaseEstrella }}
                        </h5>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-star fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>