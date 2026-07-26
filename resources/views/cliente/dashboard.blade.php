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
            <a href="{{ route('cotizacion.crear') }}" class="btn btn-solid px-4 py-2">
                <i class="bi bi-plus-circle me-2"></i> Cotizar Nuevo Pedido
            </a>
        </div>
    </div>

    <!-- Tarjeta del Historial -->
    <div class="card border-0 rounded-4 p-1" style="background-color: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid var(--color-lila-suave); box-shadow: 0 10px 30px rgba(107, 47, 163, 0.12);">
        <div class="card-body p-4">

            @if($pedidos->isEmpty())
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
                                    <div class="d-inline-flex gap-2">
                                        {{-- Siempre visible: la solicitud original enviada por el cliente --}}
                                        <a href="{{ route('cotizacion.pdf', $pedido->id) }}" target="_blank" class="btn btn-sm btn-ver-mas" title="Ver Solicitud Original">
                                            <i class="bi bi-clipboard-check"></i> Ver Pedido
                                        </a>

                                        @if($pedido->estado === 'pendiente')
                                            {{-- Aún no hay cotización final: mostramos el estado / WhatsApp --}}
                                            <button class="btn btn-sm btn-ver-mas" onclick="verDetallePendiente('{{ $pedido->id }}')">
                                                <i class="bi bi-eye"></i> Ver Estado
                                            </button>
                                        @else
                                            @php
                                                $pedidoInterno = \App\Models\Pedido::where('quote_request_id', $pedido->id)->first();
                                            @endphp

                                            @if($pedidoInterno)
                                                <a href="{{ route('pedidos.pdf_nota', $pedidoInterno->id) }}" target="_blank" class="btn btn-sm btn-outline-purple" title="Descargar Cotización Final">
                                                    <i class="bi bi-file-earmark-pdf"></i> Ver Cotización
                                                </a>
                                            @else
                                                <span class="badge bg-light text-muted border">Error de enlace</span>
                                            @endif
                                        @endif
                                    </div>
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

@push('js')
<script>
    function verDetallePendiente(id) {
        const idFormateado = String(id).padStart(4, '0');
        
        // Capturamos el número dinámico directamente del dataset del body
        const numeroTaller = document.body.dataset.telefono; 
        
        const mensajeWa = encodeURIComponent(`Hola, quisiera consultar el estado de mi solicitud web RQ-${idFormateado}.`);

        Swal.fire({
            title: '¡Cotización en Proceso!',
            html: `
                <div style="text-align: center;">
                    <p style="color: var(--color-texto-principal); font-size: 0.95rem; margin-bottom: 10px;">
                        Tu solicitud <strong>RQ-${idFormateado}</strong> está siendo evaluada por nuestro taller.
                    </p>
                    <p style="color: var(--color-texto-mutado); font-size: 0.85rem; margin-bottom: 25px;">
                        Aún no hemos establecido el precio final. Si deseas acelerar el proceso o darnos más detalles, contáctanos:
                    </p>
                    <a href="https://wa.me/${numeroTaller}?text=${mensajeWa}"
                       target="_blank"
                       style="background-color: #25D366; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);">
                        <i class="bi bi-whatsapp me-1"></i> Hablar por WhatsApp
                    </a>
                </div>
            `,
            icon: 'info',
            showConfirmButton: false,
            showCloseButton: true,
            background: 'rgba(255, 255, 255, 0.98)',
            backdrop: `rgba(107, 47, 163, 0.2)`
        });
    }
</script>
@endpush
@endsection