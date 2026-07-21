<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arte Titi_Val - Catálogo Oficial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/welcome.css'])
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('home') }}" class="nav-brand">
            <i class="fa-solid fa-leaf"></i> Arte Titi_Vals
        </a>

        <div class="nav-menu">
            <a href="#">Inicio</a>
            <a href="#catalogo">Catálogo</a>
            <a href="#">Nosotros</a>
            <a href="#">Contacto</a>
        </div>

        <div class="nav-actions">
            @guest
                <a href="{{ route('login') }}" class="btn btn-outline">Ingresar</a>
                <a href="{{ route('register') }}" class="btn btn-solid">Registrarse</a>
            @endguest

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="btn btn-solid">Panel Taller</a>
                @else
                    <a href="{{ route('cliente.dashboard') }}" class="btn btn-outline">Mis Pedidos</a>
                @endif

                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="padding: 10px 15px;"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                </form>
            @endauth
        </div>
    </nav>

    <header class="hero-section">
        <h1>Diseños únicos para brillar en la pista</h1>
        <p>Descubre nuestra línea exclusiva de bastones para bastoneras. Confeccionados a mano, con acabados en plata y oro, y personalizados con los colores de tu institución.</p>

        <div class="carousel-placeholder">
            <div style="text-align: center;">
                <i class="fa-regular fa-images fa-3x" style="margin-bottom: 15px; color: var(--color-oro);"></i>
                <p>Aquí irá el Carrusel de Imágenes Promocionales</p>
            </div>
        </div>
    </header>

    <section id="catalogo">
        <h2 class="section-title">Nuestro Catálogo</h2>
        <main class="catalog-container">

            @forelse($bastones as $baston)
                <!-- TARJETA GENERADA DINÁMICAMENTE -->
                <article class="product-card">
                    
                    <!-- FIX: Fondo suave y object-fit: contain -->
                    <div class="product-image" style="background-color: #f8f9fa; text-align: center; border-radius: 8px 8px 0 0;">
                        <!-- Aquí Laravel coloca la foto real -->
                        <img src="{{ asset('storage/' . $baston->imagen_path) }}" alt="{{ $baston->titulo }}" style="width: 100%; height: 250px; object-fit: contain; padding: 15px;">
                    </div>
                    
                    <div class="product-info">
                        <!-- Título y Descripción de la BD -->
                        <h2 class="product-title">{{ $baston->titulo }}</h2>
                        <p class="product-desc">{{ $baston->descripcion }}</p>

                        @guest
                            <a href="{{ route('register') }}" class="btn btn-outline btn-full">
                                <i class="fa-solid fa-lock"></i> Regístrate para Cotizar
                            </a>
                        @endguest

                        @auth
                            <!-- ESTE ES EL BOTÓN QUE ABRIRÁ TU COTIZADOR -->
                            <button onclick="abrirCotizador('{{ $baston->titulo }}')" class="btn btn-solid btn-full">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Personalizar Modelo
                            </button>
                        @endauth
                    </div>
                </article>
            @empty
                <!-- Mensaje por si aún no se ha subido ninguna foto -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fa-solid fa-wand-magic-sparkles fa-3x" style="color: var(--color-oro); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--color-lila-fuerte); font-size: 24px;">¡Nuevos diseños en camino!</h3>
                    <p style="color: var(--color-texto-mutado);">Estamos preparando el catálogo de esta temporada. Vuelve pronto.</p>
                </div>
            @endforelse

        </main>
    </section>

</body>
</html>