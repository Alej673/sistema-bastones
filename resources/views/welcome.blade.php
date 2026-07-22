@extends('layouts.public')

@section('title', 'Inicio - Arte Titi_Val')

@section('content')

    <header class="hero-section">
        <h1 style="margin-bottom: 10px;">Diseños únicos para brillar en la pista</h1>
        <p style="margin-bottom: 30px;">Descubre nuestra línea exclusiva de bastones para bastoneras. Confeccionados a mano, con acabados en plata y oro, y personalizados con los colores de tu institución.</p>

        <!-- INYECCIÓN DEL CARRUSEL DINÁMICO -->
        @if($carruselItems->count() > 0)
            <div id="heroCarousel" class="hero-carousel carousel slide carousel-fade mx-auto" data-bs-ride="carousel">

                <div class="carousel-inner">
                    @foreach($carruselItems as $index => $item)
                        <!-- data-bs-interval determina los milisegundos que dura cada foto -->
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" data-bs-interval="5000">
                            <div class="hero-slide">
                                <!-- Fondo desenfocado: rellena el marco sin importar la orientación de la foto -->
                                <div class="hero-slide-bg" style="background-image: url('{{ asset('storage/' . $item->imagen_path) }}');"></div>

                                <!-- Foto real, completa, sin recortes -->
                                <img src="{{ asset('storage/' . $item->imagen_path) }}" alt="{{ $item->titulo }}" class="hero-slide-img">

                                <!-- Degradado para que el texto siempre se lea bien -->
                                <div class="hero-slide-scrim"></div>

                                <!-- Textos sobre la imagen -->
                                <div class="carousel-caption hero-slide-caption d-none d-md-block">
                                    <span class="hero-eyebrow"><i class="fa-solid fa-sparkles"></i> Eleccion del Editor</span>
                                    <h3>{{ $item->titulo }}</h3>
                                    @if($item->descripcion)
                                        <p>{{ Str::limit($item->descripcion, 90) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Controles e indicadores solo si hay más de 1 imagen -->
                @if($carruselItems->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="hero-control-icon">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="hero-control-icon">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>

                    <div class="carousel-indicators hero-indicators">
                        @foreach($carruselItems as $index => $item)
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}"
                                    class="{{ $index === 0 ? 'active' : '' }}"
                                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="Ir a la foto {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <!-- Placeholder si tu mamá apaga todas las estrellas del carrusel -->
            <div class="carousel-placeholder" style="max-width: 900px; margin: 0 auto;">
                <div style="text-align: center; padding: 60px 20px; background: rgba(0,0,0,0.2); border-radius: 12px;">
                    <i class="fa-regular fa-images fa-3x" style="margin-bottom: 15px; color: var(--color-oro);"></i>
                    <p>Las mejores colecciones están por llegar...</p>
                </div>
            </div>
        @endif
    </header>

    <section id="destacados" style="padding: 60px 0;">
        <h2 class="section-title text-center mb-5">
            <i class="fa-solid fa-star" style="color: var(--color-oro);"></i> Opciones Destacadas
        </h2>
        <main class="catalog-container">

            @forelse($destacados as $destacado)
                <!-- TARJETA GENERADA DINÁMICAMENTE -->
                <article class="product-card shadow-sm">
                    
                    <div class="product-image" style="background-color: #f8f9fa; text-align: center; border-radius: 8px 8px 0 0;">
                        <img src="{{ asset('storage/' . $destacado->imagen_path) }}" alt="{{ $destacado->titulo }}" style="width: 100%; height: 250px; object-fit: contain; padding: 15px;">
                    </div>
                    
                    <div class="product-info">
                        <h2 class="product-title">{{ $destacado->titulo }}</h2>
                        <p class="product-desc">{{ $destacado->descripcion }}</p>

                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline btn-full">
                                <i class="fa-solid fa-lock"></i> Regístrate para Cotizar
                            </a>
                        @endguest

                        @auth
                            <button onclick="abrirCotizador('{{ $destacado->titulo }}')" class="btn btn-solid btn-full">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Personalizar Modelo
                            </button>
                        @endauth
                    </div>
                </article>
            @empty
                <!-- Mensaje por si no hay destacados activos -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fa-solid fa-star fa-3x" style="color: var(--color-oro); opacity: 0.5; margin-bottom: 20px;"></i>
                    <h3 style="color: var(--color-lila-fuerte); font-size: 24px;">Selección Exclusiva en Camino</h3>
                    <p style="color: var(--color-texto-mutado);">Estamos seleccionando los mejores modelos de bastones para destacar esta temporada. Vuelve pronto.</p>
                </div>
            @endforelse

        </main>
        
        <!-- CALL TO ACTION: Botón para ir a la pestaña del catálogo completo -->
        <div class="text-center" style="margin-top: 50px;">
            <a href="{{ url('/catalogo') }}" class="btn btn-outline" style="font-size: 1.1rem; padding: 12px 35px; border-width: 2px;">
                <i class="fa-solid fa-images me-2"></i> Explorar Catálogo Completo
            </a>
        </div>
    </section>

@endsection