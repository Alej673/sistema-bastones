@extends('layouts.admin') {{-- Ajusta al nombre de tu layout principal --}}

@section('contenido')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color: var(--text-main);">
            <i class="fa-solid fa-inbox me-2" style="color: var(--accent-purple);"></i> Buzón de Solicitudes Web
        </h3>
        <a href="{{ route('inicio') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>

    @if($solicitudes->isEmpty())
        <div class="alert text-center py-5 glass-card">
            <i class="fa-regular fa-envelope-open fa-3x mb-3 text-muted"></i>
            <h5 class="text-muted">No hay solicitudes nuevas</h5>
            <p class="small">Las cotizaciones solicitadas desde la web aparecerán aquí.</p>
        </div>
    @else
        <div class="row">
            @foreach($solicitudes as $solicitud)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card glass-card h-100 border-0 shadow-sm">
                        <div class="card-header border-bottom-0 bg-transparent pt-4 pb-0 d-flex justify-content-between">
                            <span class="badge" style="background-color: rgba(var(--accent-purple-rgb), 0.1); color: var(--accent-purple);">
                                RQ-{{ str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                            <small class="text-muted">{{ $solicitud->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="card-body">
                            <h5 class="fw-bold mb-1" style="color: var(--text-main);">{{ $solicitud->user->name }}</h5>
                            <p class="text-muted small mb-3"><i class="fa-solid fa-envelope me-1"></i> {{ $solicitud->user->email }}</p>

                            {{-- Detalles Rápidos --}}
                            <div class="p-3 rounded-3 mb-3" style="background-color: var(--bg-base);">
                                <div class="row text-center text-muted small">
                                    <div class="col-6 border-end">
                                        <span class="d-block fw-bold" style="color: var(--text-main);">{{ $solicitud->medida_cm }} cm</span>
                                        Medida
                                    </div>
                                    <div class="col-6">
                                        <span class="d-block fw-bold" style="color: var(--text-main);">{{ $solicitud->acabado }}</span>
                                        Acabado
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Resumen de la solicitud --}}
                            <p class="small text-muted mb-4">
                                <i class="fa-solid fa-palette me-1"></i> Cuerpo: {{ implode(', ', json_decode($solicitud->colores_cuerpo ?? '[]')) }}<br>
                                @if($solicitud->incluye_cortina_lana)
                                    <i class="fa-solid fa-check text-success me-1"></i> Cortina Lana<br>
                                @endif
                                <i class="fa-solid fa-comment-dots me-1"></i> <strong>Detalles:</strong> {{ $solicitud->descripcion_diseno_especial ?: 'Sin detalles adicionales.' }}
                            </p>

                            {{-- Botones de Acción --}}
                            <div class="d-flex gap-2 mt-auto">
                                <a href="https://wa.me/593{{ ltrim($solicitud->telefono, '0') }}?text={{ urlencode('Hola ' . $solicitud->user->name . ', recibí tu solicitud para el bastón de ' . $solicitud->medida_cm . 'cm. ¡Conversemos sobre los detalles!') }}" 
                                   target="_blank" 
                                   class="btn btn-success flex-grow-1 rounded-3 fw-bold small">
                                    <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <a href="{{ route('cotizador.create') }}" class="btn glass-btn-primary flex-grow-1 rounded-3 fw-bold small">
                                    <i class="fa-solid fa-calculator me-1"></i> Cotizar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection