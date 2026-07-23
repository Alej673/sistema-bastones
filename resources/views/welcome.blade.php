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
                                <div class="carousel-caption hero-slide-caption d-flex flex-column align-items-start justify-content-end text-start p-4">
                                    <span class="hero-eyebrow mb-2">
                                        <i class="fa-solid fa-sparkles"></i> Elección del Editor
                                    </span>
                                    <h3 class="hero-title mb-1">{{ $item->titulo }}</h3>
                                    @if($item->descripcion)
                                        <p class="hero-desc mb-0">{{ Str::limit($item->descripcion, 90) }}</p>
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
        <h2 class="section-title text-center mb-5" style="font-family: 'Playfair Display', serif; color: #d4af37;">
            <i class="fa-solid fa-star" style="color: #d4af37;"></i> Opciones Destacadas
        </h2>
        
        <div class="container">
            <!-- Usamos la grilla para que las tarjetas neón se acomoden bien -->
            <div class="row g-4 justify-content-center">
                @forelse($destacados as $destacado)
                    <!-- Llamamos a la tarjeta, pasándole $destacado con el nombre $item -->
                    @include('catalogo.partials.card', ['item' => $destacado])
                @empty
                    <!-- Mensaje por si no hay destacados activos -->
                    <div class="col-12 text-center" style="padding: 60px 20px;">
                        <i class="fa-solid fa-star fa-3x mb-3" style="color: #d4af37; opacity: 0.5;"></i>
                        <h3 style="color: #bc13fe; font-family: 'Playfair Display', serif; font-size: 24px;">Selección Exclusiva en Camino</h3>
                        <p style="color: #eaddff; font-family: 'Inter', sans-serif;">Estamos seleccionando los mejores modelos de bastones para destacar esta temporada. Vuelve pronto.</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- CALL TO ACTION: Botón para ir a la pestaña del catálogo completo -->
        <div class="text-center" style="margin-top: 50px;">
            <a href="{{ url('/catalogo') }}" class="btn rounded-pill shadow-sm btn-titi-action" style="font-size: 1.1rem; padding: 12px 35px; border-width: 2px;">
                <i class="fa-solid fa-images me-2"></i> Explorar Catálogo Completo
            </a>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- SECCIÓN: AGREGADOS RECIENTEMENTE -->
    <!-- ============================================== -->
    <section id="recientes" style="padding: 60px 0; background-color: #fcf9ff;">
        <h2 class="section-title text-center mb-5" style="font-family: 'Playfair Display', serif; color: #4a148c;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #ff10f0;"></i> Novedades en el Taller
        </h2>
        
        <div class="container">
            <div class="row g-4 justify-content-center">
                @forelse($recientes as $reciente)
                    <!-- Reutilizamos la misma tarjeta inteligente -->
                    @include('catalogo.partials.card', ['item' => $reciente])
                @empty
                    <div class="col-12 text-center" style="padding: 40px 20px;">
                        <p style="color: #4a148c; font-family: 'Inter', sans-serif;">Nuevos diseños se publicarán muy pronto.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- SECCIÓN: SISTEMA DE COMENTARIOS -->
    <!-- ============================================== -->
    <section id="comentarios" style="padding: 80px 0; background-color: #ffffff;">
        <div class="container">

            <!-- Filtros -->
            <h2 class="section-title text-center mb-5" style="font-family: 'Playfair Display', serif; color: var(--color-lila-fuerte);">
                <i class="fa-solid fa-comments" style="color: var(--color-oro);"></i> Lo que dicen en la Pista
            </h2>

            <!-- Filtros -->
            <div class="d-flex justify-content-center flex-wrap gap-2 mb-4 filtros-comentarios">
                <a href="{{ request()->fullUrlWithQuery(['estrellas' => '']) }}#comentarios"
                class="btn btn-sm filtro-chip rounded-pill px-3 {{ !request('estrellas') ? 'activo' : '' }}">Todos</a>
                @foreach([5, 4, 3, 2, 1] as $n)
                    <a href="{{ request()->fullUrlWithQuery(['estrellas' => $n]) }}#comentarios"
                    class="btn btn-sm filtro-chip rounded-pill px-3 {{ request('estrellas') == $n ? 'activo' : '' }}">
                        {{ $n }} <i class="fa-solid fa-star"></i>
                    </a>
                @endforeach
            </div>

            <!-- Lista de comentarios -->
            @forelse($comentarios as $comentario)
                <div class="col-md-4">
                    <div class="card review-card h-100 shadow-sm text-center scroll-hidden d-flex flex-column {{ $comentario->user->role === 'admin' ? 'review-card-admin' : '' }}">
                        <div class="card-body p-4">

                            @if($comentario->user->role === 'admin')
                                <span class="badge-taller">
                                    <i class="fa-solid fa-scissors"></i> Equipo Arte Titi_Val
                                </span>
                            @else
                                <div class="review-stars mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $comentario->calificacion ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                    @endfor
                                </div>
                            @endif

                            <p class="review-texto">"{{ $comentario->contenido }}"</p>
                            <h6 class="fw-bold review-autor">— {{ $comentario->user->name }}</h6>
                        </div>

                        <div class="card-footer bg-transparent border-0 d-flex justify-content-around pb-3">
                            <button type="button"
                                    class="btn btn-sm btn-link text-decoration-none btn-util {{ $comentario->isLikedByAuthUser() ? 'is-liked' : '' }}"
                                    data-id="{{ $comentario->id }}">
                                <i class="{{ $comentario->isLikedByAuthUser() ? 'fa-solid' : 'fa-regular' }} fa-heart me-1 icon-heart"></i> Útil
                                <span class="badge ms-1 like-counter {{ $comentario->isLikedByAuthUser() ? 'is-liked' : '' }}">
                                    {{ $comentario->likes->count() }}
                                </span>
                            </button>

                            @if(auth()->check() && auth()->user()->role === 'admin')
                                <button type="button" class="btn btn-sm btn-link text-decoration-none btn-responder"
                                        data-nombre="{{ $comentario->user->name }}">
                                    <i class="fa-solid fa-reply me-1"></i> Responder
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                
                @empty
                    <div id="sin-comentarios" class="col-12 text-center p-5 scroll-hidden review-empty">
                        <i class="fa-regular fa-comment-dots fa-3x mb-3" style="color: var(--color-lila-fuerte); opacity: 0.5;"></i>
                        <h4 style="color: var(--color-texto-principal); font-family: 'Playfair Display', serif;">Aún no hay opiniones</h4>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mb-5">
                {{ $comentarios->links('pagination::bootstrap-5') }}
            </div>

            <!-- Caja de escritura -->
            <div class="row justify-content-center scroll-hidden">
                <div class="col-md-8 text-center p-5 shadow-sm review-write-box">

                    @auth
                        <h4 class="mb-3" style="color: var(--color-texto-principal); font-family: 'Playfair Display', serif;">
                            Deja tu comentario
                        </h4>

                        <form id="form-comentario" action="{{ route('comentarios.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="calificacion" id="calificacion_input" value="0">

                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold" style="color: var(--color-lila-fuerte); font-size: 0.85rem;">
                                    Tu Experiencia
                                </label>
                                <textarea name="contenido" class="form-control border-0 shadow-sm" rows="3"
                                        placeholder="Escribe aquí tu opinión sobre los bastones y accesorios..." required></textarea>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label fw-bold me-3" style="color: var(--color-lila-fuerte); font-size: 0.85rem;">
                                    Calificación:
                                </label>
                                <span id="star-container">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star star-btn" data-value="{{ $i }}"></i>
                                    @endfor
                                </span>
                            </div>

                            <button type="submit" class="btn btn-titi-action w-100 rounded-pill shadow-sm" style="padding: 12px; font-weight: bold;">
                                <i class="fa-solid fa-paper-plane me-2"></i> Publicar Comentario
                            </button>
                        </form>
                    @endauth

                    @guest
                        <i class="fa-solid fa-lock fa-2x mb-3" style="color: var(--color-oro);"></i>
                        <h4 style="color: var(--color-texto-principal); font-family: 'Playfair Display', serif;">¿Quieres dejar tu opinión?</h4>
                        <p class="mb-4" style="color: var(--color-texto-mutado);">
                            Para mantener la calidad de nuestra comunidad, necesitas una cuenta para comentar o calificar.
                        </p>
                        <a href="{{ route('login') }}" class="btn btn-titi-action rounded-pill shadow-sm px-5" style="padding: 12px; font-weight: bold;">
                            <i class="fa-solid fa-user-plus me-2"></i> Regístrate para Comentar
                        </a>
                    @endguest

                </div>
            </div>
        </div>
    </section>

@endsection