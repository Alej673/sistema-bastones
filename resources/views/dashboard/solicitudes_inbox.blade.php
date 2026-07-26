@extends('layouts.admin') 

@section('contenido')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color: var(--text-main);">
            <i class="fa-solid fa-inbox me-2" style="color: var(--accent-purple);"></i> Buzón de Solicitudes Web
        </h3>
        <a href="{{ route('inicio') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>

    <!-- Navegación de Pestañas (Filtros) -->
    <ul class="nav nav-pills mb-4 titi-nav-pills" id="inboxTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pendientes-tab" data-bs-toggle="pill" data-bs-target="#pendientes-pane" type="button" role="tab">
                <i class="fas fa-bell me-1"></i> Nuevas (Pendientes) <span class="badge bg-white text-dark ms-1">{{ $pendientes->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="gestionadas-tab" data-bs-toggle="pill" data-bs-target="#gestionadas-pane" type="button" role="tab">
                <i class="fas fa-check-double me-1"></i> Ya Gestionadas <span class="badge bg-white text-dark ms-1">{{ $gestionadas->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="inboxTabsContent">

        <!-- PESTAÑA 1: NUEVAS (PENDIENTES) -->
        <div class="tab-pane fade show active" id="pendientes-pane" role="tabpanel">
            @if($pendientes->isEmpty())
                <div class="alert text-center py-5 titi-glow-card">
                    <i class="fa-regular fa-envelope-open fa-3x mb-3 text-muted"></i>
                    <h5 class="text-muted">No hay solicitudes nuevas</h5>
                    <p class="small">Todo está al día.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                @foreach($pendientes as $solicitud)
                    <div class="col">
                        <div class="titi-glow-card">

                            @include('dashboard.partials._solicitud_card_img', ['solicitud' => $solicitud, 'managed' => false])

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-3">
                                <h5 class="fw-bold mb-0 text-truncate" style="color: var(--accent-purple);">{{ $solicitud->nombre }}</h5>
                                <small class="text-muted d-block mb-3"><i class="far fa-clock me-1"></i> hace {{ $solicitud->created_at->diffForHumans(null, true) }}</small>

                                <div class="d-flex gap-2 mb-3">
                                    @if(!empty($solicitud->medida_cm) && strtolower($solicitud->medida_cm) !== 'na')
                                        <span class="badge bg-light text-dark border"><i class="fas fa-ruler-vertical text-purple"></i> {{ $solicitud->medida_cm }} cm</span>
                                    @endif

                                    @if(!empty($solicitud->acabado) && strtolower($solicitud->acabado) !== 'na')
                                        <span class="badge bg-light text-dark border"><i class="fas fa-paint-brush text-purple"></i> {{ $solicitud->acabado }}</span>
                                    @endif
                                </div>

                                <p class="mb-1 text-sm"><i class="fas fa-palette text-purple"></i> <strong>Color/Modelo:</strong> {{ $solicitud->colores ?? 'N/A' }}</p>
                                <p class="mb-3 text-sm text-truncate-2"><i class="fas fa-comment-dots text-purple"></i> <strong>Detalles:</strong> {{ $solicitud->descripcion_diseno_especial ?: 'Sin detalles adicionales.' }}</p>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="card-footer d-flex justify-content-between align-items-center bg-transparent border-0 pt-0 pb-3 px-3">
                                <a href="https://wa.me/593{{ ltrim($solicitud->telefono, '0') }}?text={{ urlencode('Hola ' . $solicitud->nombre . ', recibí tu solicitud web. ¡Conversemos sobre tu diseño!') }}" target="_blank" class="btn btn-sm action-btn whatsapp-btn rounded-pill px-3">
                                    <i class="fab fa-whatsapp me-1"></i> Escribir
                                </a>

                                <a href="{{ route('cotizador.create', ['rq' => $solicitud->id]) }}" class="btn btn-sm action-btn cotizar-btn rounded-pill px-3">
                                    <i class="fas fa-calculator me-1"></i> Cotizar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>

        <!-- PESTAÑA 2: YA GESTIONADAS -->
        <div class="tab-pane fade" id="gestionadas-pane" role="tabpanel">
            @if($gestionadas->isEmpty())
                <div class="alert text-center py-5 titi-glow-card">
                    <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                    <h5 class="text-muted">Aún no hay historial</h5>
                    <p class="small">Aquí verás las solicitudes que ya fueron cotizadas o vinculadas al inventario.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 opacity-75">
                @foreach($gestionadas as $solicitud)
                    <div class="col">
                        <div class="titi-glow-card">

                            @include('dashboard.partials._solicitud_card_img', ['solicitud' => $solicitud, 'managed' => true])

                            <div class="card-body p-3">
                                <h5 class="fw-bold mb-0 text-muted text-truncate">{{ $solicitud->nombre }}</h5>
                                <p class="mb-1 mt-2 text-sm"><strong>Modelo:</strong> {{ $solicitud->colores ?? 'N/A' }}</p>
                                @if($solicitud->precio_final)
                                    <p class="mb-0 text-sm text-success fw-bold"><i class="fas fa-tag"></i> Cotizado en: ${{ $solicitud->precio_final }}</p>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent border-0 pb-3 text-center">
                                <a href="{{ route('cotizacion.pdf', $solicitud->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                                    <i class="fas fa-file-pdf me-1"></i> Ver PDF de Respaldo
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection