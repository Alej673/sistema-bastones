<div class="col-md-4">
    <!-- Contenedor principal claro -->
    <div class="card h-100 border-0 shadow-sm titi-card scroll-hidden" style="border-radius: 16px; overflow: hidden; background-color: #f6e8ff;">
        
        <!-- Contenedor de la Imagen -->
        <div class="text-center pt-4 pb-3 titi-card-img-container" style="background-color: #eaddff;">
            <!-- object-fit: contain para que la imagen se vea completa como en tu captura -->
            <img src="{{ asset('storage/' . $item->imagen_path) }}" class="img-fluid shadow-sm titi-card-img" alt="{{ $item->titulo }}" style="height: 220px; width: 100%; object-fit: contain; padding: 15px;">
        </div>
        
        <!-- Línea Dorada Separadora original -->
        <hr style="margin: 0; border-top: 2px solid #d4af37; opacity: 1;">

        <!-- Cuerpo de la Tarjeta (Fondo blanco original) -->
        <div class="card-body d-flex flex-column text-center" style="background-color: #ffffff;">
            <h5 class="card-title mb-2" style="font-family: 'Playfair Display', serif; color: #4a148c; font-weight: bold; font-size: 1.3rem;">
                {{ $item->titulo }}
            </h5>
            
            @if($item->descripcion)
                <p class="card-text text-muted mb-3" style="font-family: 'Inter', sans-serif; font-size: 0.85rem;">
                    {{ Str::limit($item->descripcion, 50) }}
                </p>
            @endif

            <!-- Etiquetas (Tags) adaptadas al fondo blanco -->
            <div class="mb-4 d-flex justify-content-center flex-wrap gap-1">
                @if($item->medida_cm !== 'na')
                    <span class="badge" style="background-color: #8a2be2; color: #fff;">{{ $item->medida_cm }} cm</span>
                @endif
                @if($item->nivel_diseno !== 'na')
                    <span class="badge" style="background-color: #d4af37; color: #1b0f28;">{{ ucfirst($item->nivel_diseno) }}</span>
                @endif
            </div>

            <!-- Botones con validación de sesión -->
            <div class="mt-auto">
                @guest
                    <!-- Invitado: Directo a WhatsApp -->
                    <button onclick="enviarWhatsAppDirecto('{{ $item->titulo }}')" class="btn w-100 rounded-pill shadow-sm btn-titi-action" style="font-weight: 600; font-family: 'Inter', sans-serif; padding: 10px 0;">
                        <i class="fa-brands fa-whatsapp me-2"></i> Consultar Modelo
                    </button>
                @endguest

                @auth
                    <!-- Logueado: Abre el Modal de Consulta Rápida -->
                    <button onclick="abrirConsultaRapida('{{ $item->titulo }}', '{{ $item->nivel_diseno ?? 'Básico' }}', '{{ $item->medida_cm ?? '50 cm' }}', '{{ asset('storage/' . $item->imagen_path) }}', '{{ $item->categoria ?? 'na' }}')" class="btn w-100 rounded-pill shadow-sm btn-titi-action" style="font-weight: 600; font-family: 'Inter', sans-serif; padding: 10px 0;">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Personalizar Modelo
                    </button>
                @endauth
            </div>
        </div>
    </div>
</div>