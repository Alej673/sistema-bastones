<div class="col-md-4">
    <div class="card h-100 border-0 shadow-sm titi-card scroll-hidden">

        <div class="position-relative text-center pt-4 pb-3 titi-card-img-container titi-card-img-bg">

            <div class="position-absolute top-0 end-0 p-3 z-1">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-light btn-sm rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center titi-fav-btn" title="Inicia sesión para guardar" data-bs-toggle="tooltip">
                        <i class="fa-regular fa-heart text-secondary fs-5"></i>
                    </a>
                @else
                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center titi-fav-btn btn-favorito" data-id="{{ $item->id }}" title="Guardar en favoritos" data-bs-toggle="tooltip">
                        <i class="fa-regular fa-heart text-danger fs-5"></i>
                    </button>
                @endguest
            </div>

            <img src="{{ asset('storage/' . $item->imagen_path) }}" class="img-fluid shadow-sm titi-card-img" alt="{{ $item->titulo }}" style="height: 220px; width: 100%; object-fit: contain; padding: 15px;">
        </div>

        <hr style="margin: 0; border-top: 2px solid #d4af37; opacity: 1;">

        <div class="card-body d-flex flex-column text-center" style="background-color: #ffffff;">
            <h5 class="card-title mb-2 titi-item-title">
                {{ $item->titulo }}
            </h5>

            @if($item->descripcion)
                <p class="card-text titi-item-desc mb-3">
                    {{ Str::limit($item->descripcion, 50) }}
                </p>
            @endif

            <div class="mb-4 d-flex justify-content-center flex-wrap gap-1">
                @if($item->medida_cm !== 'na')
                    <span class="badge titi-badge-medida">{{ $item->medida_cm }} cm</span>
                @endif
                @if($item->nivel_diseno !== 'na')
                    <span class="badge titi-badge-nivel">{{ ucfirst($item->nivel_diseno) }}</span>
                @endif
            </div>

            <div class="mt-auto">
                @guest
                    <button onclick="enviarWhatsAppDirecto({{ $item->id }}, '{{ $item->titulo }}', '{{ asset('storage/' . $item->imagen_path) }}')" class="btn w-100 rounded-pill shadow-sm btn-whatsapp-direct mb-2">
                        <i class="fa-brands fa-whatsapp me-2"></i> Consulta Rápida
                    </button>

                    <p class="mb-0 text-center mx-auto titi-microcopy">
                        ¿Necesitas una cotización formal? <br>
                        <a href="{{ route('login') }}" class="titi-microcopy-link">Inicia sesión aquí</a>.
                    </p>
                @endguest

                @auth
                    <button onclick="abrirConsultaRapida({{ $item->id }}, '{{ $item->titulo }}', '{{ $item->nivel_diseno ?? 'Básico' }}', '{{ $item->medida_cm ?? '50 cm' }}', '{{ asset('storage/' . $item->imagen_path) }}', '{{ $item->categoria ?? 'na' }}')" class="btn w-100 rounded-pill shadow-sm btn-titi-action">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Personalizar Modelo
                    </button>
                @endauth
            </div>
        </div>
    </div>
</div>