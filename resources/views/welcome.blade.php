<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arte Titi_Val - Catálogo Oficial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

    <style>
        /* CONFIGURACIÓN BASE - PALETA MÁS LILA Y CÁLIDA */
        :root {
            --color-fondo-claro: #FBF6FF;   /* fondo general, lila casi blanco */
            --color-lila-pastel: #EAD9FF;   /* watercolor de fondo, más presente */
            --color-lila-suave: #D9BFFA;    /* detalles, bordes, sombras */
            --color-lila-medio: #9D5CE0;    /* alas de la mariposa, más saturado */
            --color-lila-fuerte: #6B2FA3;   /* textos con contraste, títulos */
            --color-lila-oscuro: #34164F;   /* NUEVO: morado profundo para el header */
            --color-oro: #C9A052;
            --color-oro-claro: #E8D099;
            --color-texto-principal: #3B2D4A;
            --color-texto-mutado: #7A698A;
            --color-texto-sobre-oscuro: #F4EAFF; /* texto legible sobre el header oscuro */
        }

        body {
            background-color: var(--color-fondo-claro);
            font-family: 'Inter', sans-serif;
            margin: 0;
            color: var(--color-texto-principal);
            overflow-x: hidden;
        }

        /* NAVEGACIÓN — AHORA OSCURA Y CÁLIDA */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background: linear-gradient(135deg, var(--color-lila-oscuro), #4A1F6E);
            border-bottom: 2px solid var(--color-oro);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 25px rgba(52, 22, 79, 0.35);
        }

        .nav-brand {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--color-oro-claro);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            display: flex;
            gap: 25px;
        }

        .nav-menu a {
            color: var(--color-texto-sobre-oscuro);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover { color: var(--color-oro-claro); }

        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* BOTONES */
        .btn {
            display: inline-block;
            text-align: center;
            padding: 10px 22px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--color-oro);
            color: var(--color-oro-claro);
        }
        .btn-outline:hover { background: rgba(201, 160, 82, 0.15); }

        .btn-solid {
            background: linear-gradient(135deg, var(--color-lila-medio), var(--color-lila-fuerte));
            color: white;
            box-shadow: 0 4px 15px rgba(107, 47, 163, 0.4);
        }
        .btn-solid:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 47, 163, 0.5);
        }

        /* HERO — FONDO OSCURO CON BLOBS DIFUMINADOS (GLASSMORPHISM) */
        .hero-section {
            position: relative;
            overflow: hidden;
            padding: 80px 5% 70px;
            text-align: center;
            background:
                radial-gradient(circle at 15% 20%, rgba(157, 92, 224, 0.55) 0%, transparent 45%),
                radial-gradient(circle at 85% 15%, rgba(201, 160, 82, 0.25) 0%, transparent 40%),
                radial-gradient(circle at 70% 85%, rgba(107, 47, 163, 0.5) 0%, transparent 50%),
                linear-gradient(180deg, #24123A 0%, #3D1B63 55%, var(--color-fondo-claro) 100%);
        }

        /* Blobs difuminados extra, como en la referencia */
        .hero-section::before,
        .hero-section::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            z-index: 0;
            pointer-events: none;
        }
        .hero-section::before {
            width: 320px;
            height: 320px;
            background: var(--color-lila-medio);
            opacity: 0.55;
            top: -60px;
            left: -60px;
        }
        .hero-section::after {
            width: 260px;
            height: 260px;
            background: var(--color-oro-claro);
            opacity: 0.35;
            bottom: 40px;
            right: 8%;
        }

        .hero-section > * {
            position: relative;
            z-index: 1;
        }

        .hero-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 46px;
            color: #FFFFFF;
            text-shadow: 0 2px 20px rgba(52, 22, 79, 0.4);
            margin-bottom: 15px;
        }

        .hero-section p {
            color: #DFC9F5;
            font-size: 17px;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        /* Placeholder del carrusel como tarjeta de cristal (glassmorphism) */
        .carousel-placeholder {
            width: 100%;
            max-width: 1000px;
            height: 400px;
            margin: 0 auto 10px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-texto-sobre-oscuro);
            font-size: 18px;
            font-weight: 500;
            box-shadow: 0 20px 45px rgba(20, 8, 35, 0.35);
        }

        /* GRID DEL CATÁLOGO */
        .section-title {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--color-lila-fuerte);
            margin-bottom: 40px;
        }
        .section-title::before,
        .section-title::after {
            content: "❧";
            color: var(--color-oro);
            font-size: 20px;
            margin: 0 15px;
        }

        .catalog-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 40px;
            padding: 0 5% 80px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(107, 47, 163, 0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--color-lila-suave);
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-8px);
            border-color: var(--color-oro-claro);
            box-shadow: 0 16px 35px rgba(107, 47, 163, 0.18);
        }

        .product-image {
            width: 100%;
            height: 280px;
            background: linear-gradient(160deg, var(--color-lila-pastel), var(--color-fondo-claro));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-lila-fuerte);
            border-bottom: 3px solid var(--color-oro);
        }

        .product-info {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            margin: 0 0 10px;
            color: var(--color-lila-fuerte);
        }

        .product-desc {
            font-size: 14px;
            color: var(--color-texto-mutado);
            margin: 0 0 25px;
            line-height: 1.6;
            flex-grow: 1;
        }

        .btn-full { width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('home') }}" class="nav-brand">
            <i class="fa-solid fa-leaf"></i> Arte Titi_Val
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
                    <div class="product-image">
                        <!-- Aquí Laravel coloca la foto real subida por tu mamá -->
                        <img src="{{ asset('storage/' . $baston->imagen_path) }}" alt="{{ $baston->titulo }}" style="width: 100%; height: 100%; object-fit: cover;">
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
                            <!-- ESTE ES EL BOTÓN QUE PRONTO ABRIRÁ TU COTIZADOR -->
                            <button onclick="abrirCotizador('{{ $baston->titulo }}')" class="btn btn-solid btn-full">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Personalizar Modelo
                            </button>
                        @endauth
                    </div>
                </article>
            @empty
                <!-- Mensaje por si tu mamá aún no ha subido ninguna foto -->
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