{{--
    Partial: header de imagen + badges para las tarjetas del Buzón de Solicitudes.
    Uso:
        @include('admin.partials._solicitud_card_img', ['solicitud' => $solicitud, 'managed' => false])
        @include('admin.partials._solicitud_card_img', ['solicitud' => $solicitud, 'managed' => true])
--}}
@php
    $managed = $managed ?? false;

    $catColor  = 'bg-secondary';
    $catIcon   = 'fas fa-box';
    $catNombre = ucfirst($solicitud->categoria ?? 'General');

    switch (strtolower($solicitud->categoria ?? '')) {
        case 'baston':
        case 'bastones':
            $catColor  = 'bg-purple';
            $catIcon   = 'fas fa-wand-magic-sparkles';
            $catNombre = 'Bastón';
            break;
        case 'lazo':
        case 'lazos':
            $catColor  = 'bg-info text-dark';
            $catIcon   = 'fas fa-ribbon';
            $catNombre = 'Lazo';
            break;
        case 'aplique':
        case 'apliques':
        case 'flor':
        case 'flores':
            $catColor  = 'bg-danger';
            $catIcon   = 'fas fa-seedling';
            $catNombre = 'Aplique/Flor';
            break;
        case 'manualidad':
        case 'manualidades':
            $catColor  = 'bg-warning text-dark';
            $catIcon   = 'fas fa-hand-sparkles';
            $catNombre = 'Manualidad';
            break;
    }
@endphp

<div class="card-img-wrapper {{ $managed ? 'is-managed' : '' }}">
    @if($solicitud->imagen_path)
        {{-- Fondo difuminado: rellena el marco sin importar la proporción de la imagen --}}
        <div class="img-backdrop" style="background-image: url('{{ asset('storage/'.$solicitud->imagen_path) }}');"></div>
        <img src="{{ asset('storage/' . $solicitud->imagen_path) }}" alt="Referencia" class="img-main">
    @else
        <div class="no-img"><i class="fas fa-image fa-2x mb-2"></i> Sin imagen</div>
    @endif

    {{-- Badge código de solicitud (siempre a la izquierda) --}}
    <span class="badge top-badge {{ $managed ? 'bg-secondary' : 'bg-dark border border-secondary' }}" style="left: 12px; right: auto;">
        RQ-{{ str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) }}
    </span>

    @if($managed)
        {{-- Estado (arriba a la derecha) --}}
        <span class="badge top-badge bg-success" style="left: auto; right: 12px; top: 12px;">
            {{ strtoupper($solicitud->estado) }}
        </span>

        {{-- Categoría, justo debajo del estado --}}
        <span class="badge top-badge {{ $catColor }} shadow-sm" style="right: 12px; left: auto; top: 40px;">
            <i class="{{ $catIcon }} me-1"></i> {{ $catNombre }}
        </span>
    @else
        {{-- Categoría (arriba a la derecha) --}}
        <span class="badge top-badge {{ $catColor }} shadow-sm" style="right: 12px; left: auto; top: 12px;">
            <i class="{{ $catIcon }} me-1"></i> {{ $catNombre }}
        </span>

        {{-- Botón flotante de PDF, solo en pendientes --}}
        <a href="{{ route('cotizacion.pdf', $solicitud->id) }}" target="_blank" class="action-btn-float pdf-btn" title="Descargar PDF de la Cotización">
            <i class="fas fa-file-pdf"></i>
        </a>
    @endif
</div>