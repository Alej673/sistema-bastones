@extends('layouts.public')

@section('content')
<div class="container py-5">

    <!-- Encabezado y Botón de Nueva Cotización -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-family:'Playfair Display', serif; color: var(--color-lila-fuerte);">
                Mis Pedidos
            </h2>
            <p class="mb-0" style="color: var(--color-texto-mutado);">
                Gestiona tus cotizaciones y sigue el estado de producción de tus bastones.
            </p>
        </div>
        <div>
            <a href="{{ route('catalogo.index') }}" class="btn btn-solid px-4 py-2">
                <i class="bi bi-plus-circle me-2"></i> Cotizar Nuevo Pedido
            </a>
        </div>
    </div>

    <!-- Tarjeta del Historial -->
    <div class="card border-0 rounded-4 p-1" style="background-color: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid var(--color-lila-suave); box-shadow: 0 10px 30px rgba(107, 47, 163, 0.12);">
        <div class="card-body p-4">

            @if($pedidos->isEmpty())
                <!-- Estado vacío, mismo lenguaje visual que .review-empty -->
                <div class="text-center py-5 rounded-4" style="background-color: var(--color-lila-pastel); border: 1px dashed var(--color-lila-medio);">
                    <i class="bi bi-inbox" style="font-size: 3.5rem; color: var(--color-lila-medio); opacity: 0.6;"></i>
                    <h5 class="mt-3 fw-bold" style="color: var(--color-lila-fuerte); font-family:'Playfair Display', serif;">
                        Aún no tienes pedidos registrados
                    </h5>
                    <p class="mb-0" style="color: var(--color-texto-mutado);">
                        Explora nuestro catálogo y personaliza tu primer bastón.
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 titi-pedidos-table">
                        <thead>
                            <tr>
                                <th># Solicitud</th>
                                <th>Fecha</th>
                                <th>Detalles del Bastón</th>
                                <th>Estado</th>
                                <th>Total Cotizado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                            <tr>
                                <td class="fw-bold" style="color: var(--color-lila-fuerte);">
                                    RQ-{{ str_pad($pedido->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td style="color: var(--color-texto-mutado);">
                                    {{ $pedido->created_at->format('d M, Y') }}
                                </td>
                                <td>
                                    {{ $pedido->cantidad_bastones }}x Bastón ({{ $pedido->medida_cm }}cm) <br>
                                    <small style="color: var(--color-texto-mutado);">Acabado: {{ $pedido->acabado }}</small>
                                </td>
                                <td>
                                    @if($pedido->estado == 'pendiente')
                                        <span class="badge-pedido badge-pendiente"><i class="bi bi-hourglass-split me-1"></i> Pendiente</span>
                                    @elseif($pedido->estado == 'cotizado')
                                        <span class="badge-pedido badge-cotizado"><i class="bi bi-file-earmark-text me-1"></i> Cotizado</span>
                                    @elseif($pedido->estado == 'en_produccion')
                                        <span class="badge-pedido badge-produccion"><i class="bi bi-tools me-1"></i> En Producción</span>
                                    @elseif($pedido->estado == 'entregado')
                                        <span class="badge-pedido badge-entregado"><i class="bi bi-check-circle me-1"></i> Entregado</span>
                                    @elseif($pedido->estado == 'cancelado')
                                        <span class="badge-pedido badge-cancelado"><i class="bi bi-x-circle me-1"></i> Cancelado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pedido->precio_final)
                                        <span class="fw-bold" style="color: var(--color-texto-principal);">
                                            ${{ number_format($pedido->precio_final, 2) }}
                                        </span>
                                    @else
                                        <span class="fst-italic" style="color: var(--color-texto-mutado);">Por definir</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-ver-mas" onclick="verDetalle({{ $pedido->id }})">
                                        Ver Detalle
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .titi-pedidos-table thead th {
        color: var(--color-texto-mutado);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
        border-bottom: 2px solid var(--color-lila-pastel);
    }

    .titi-pedidos-table tbody tr {
        transition: background-color 0.2s ease;
    }

    .titi-pedidos-table tbody tr:hover {
        background-color: var(--color-lila-pastel);
    }

    .titi-pedidos-table td {
        border-bottom: 1px solid rgba(217, 191, 250, 0.5);
        font-size: 0.9rem;
    }

    /* Badges de estado en la paleta lila/oro */
    .badge-pedido {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-pendiente {
        background-color: rgba(201, 160, 82, 0.18);
        color: #8a6b2e;
        border: 1px solid var(--color-oro);
    }

    .badge-cotizado {
        background-color: rgba(157, 92, 224, 0.15);
        color: var(--color-lila-fuerte);
        border: 1px solid var(--color-lila-medio);
    }

    .badge-produccion {
        background-color: var(--color-lila-fuerte);
        color: #fff;
        border: 1px solid var(--color-lila-oscuro);
    }

    .badge-entregado {
        background-color: rgba(40, 167, 69, 0.15);
        color: #1e7e34;
        border: 1px solid #28a745;
    }

    .badge-cancelado {
        background-color: rgba(220, 53, 69, 0.12);
        color: #b02a37;
        border: 1px solid #dc3545;
    }
</style>
@endpush
@endsection