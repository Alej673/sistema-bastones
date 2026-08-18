@extends('layouts.public')

@section('title', 'Nosotros - Arte Titi_Val')

@push('css')
<style>
    /* Estilos exclusivos para el Hero con Video */
    .hero-video-container {
        position: relative;
        height: 60vh;
        min-height: 400px;
        overflow: hidden;
        background-color: #1b0f28; /* Color de respaldo por si el video tarda en cargar */
    }
    .hero-video {
        position: absolute;
        top: 50%;
        left: 50%;
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        transform: translateX(-50%) translateY(-50%);
        object-fit: cover;
        z-index: 0;
        opacity: 0.5; /* Lo oscurecemos al 50% para que las letras blancas resalten perfectamente */
    }
    .hero-content {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: white;
        padding: 0 20px;
    }
    /* Tarjetas de valores */
    .value-card {
        border-radius: 15px;
        background-color: #f8f9fa;
        border: 1px solid rgba(157, 92, 224, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .value-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .icon-circle {
        width: 70px;
        height: 70px;
        background-color: #eaddff;
        color: var(--color-lila-fuerte);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 20px;
    }
</style>
@endpush

@section('content')
<!-- 1. HERO CON VIDEO -->
<section class="hero-video-container">
    <!-- El atributo 'playsinline' es clave para que se reproduzca automático en celulares -->
    <!-- Reemplaza 'intro-taller.mp4' con el archivo real cuando lo tengas -->
    <video class="hero-video" autoplay muted loop playsinline>
        <source src="{{ asset('image/Video.mp4') }}" type="video/mp4">
    </video>
    
    <div class="hero-content">
        <h1 class="display-4 fw-bold" style="font-family: 'Playfair Display', serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
            El Arte Detrás de Cada Presentación
        </h1>
        <p class="lead mt-3" style="max-width: 700px; text-shadow: 1px 1px 3px rgba(0,0,0,0.7);">
            Confección artesanal de bastones personalizados. Pasión, precisión y orgullo en cada detalle para que tu equipo brille en la pista.
        </p>
    </div>
</section>

<!-- 2. HISTORIA Y ESENCIA (Estilo Landing Page) -->
<section class="container py-5 mt-4">
    <div class="row align-items-center mb-5">
        <div class="col-md-6 mb-4 mb-md-0 scroll-hidden">
            <h2 class="fw-bold mb-4" style="color: var(--color-lila-fuerte); font-family: 'Playfair Display', serif;">
                Nuestra Esencia
            </h2>
            <p class="text-muted" style="line-height: 1.8;">
                Entendemos que un bastón no es solo un accesorio; es una extensión del talento y la energía de quien lo porta. Cada pieza que sale de nuestro taller está elaborada a mano con meticulosa atención al detalle, asegurando el equilibrio perfecto y una estética impecable.
            </p>
            <p class="text-muted" style="line-height: 1.8;">
                Nos enorgullece ser parte de incontables desfiles, adaptando nuestros diseños a los colores e identidad de cada institución, para que cada coreografía cuente con la calidad que merece.
            </p>
        </div>
        <div class="col-md-6 text-center scroll-hidden">
            <!-- Imagen de respaldo (idealmente la administradora trabajando o un detalle de los materiales) -->
            <img src="{{ asset('image/Logotipo.png') }}" alt="Trabajo artesanal en el taller" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: cover; border: 3px solid var(--color-oro-claro);">
        </div>
    </div>

    <!-- 3. PILARES DEL TALLER (Tarjetas de propuesta de valor) -->
    <div class="row g-4 mt-2 text-center">
        <div class="col-md-4 scroll-hidden">
            <div class="card value-card h-100 p-4 shadow-sm">
                <div class="icon-circle">
                    <i class="fa-solid fa-hands-holding-circle"></i>
                </div>
                <h5 class="fw-bold" style="color: #4a148c;">Hecho a Mano</h5>
                <p class="text-muted small mt-2 mb-0">Cada diseño es confeccionado de forma 100% artesanal, garantizando acabados únicos y atención personalizada.</p>
            </div>
        </div>
        <div class="col-md-4 scroll-hidden">
            <div class="card value-card h-100 p-4 shadow-sm">
                <div class="icon-circle">
                    <i class="fa-solid fa-palette"></i>
                </div>
                <h5 class="fw-bold" style="color: #4a148c;">Personalización Total</h5>
                <p class="text-muted small mt-2 mb-0">Adaptamos los tonos, adornos y medidas exactamente a la identidad visual de tu colegio o agrupación.</p>
            </div>
        </div>
        <div class="col-md-4 scroll-hidden">
            <div class="card value-card h-100 p-4 shadow-sm">
                <div class="icon-circle">
                    <i class="fa-solid fa-medal"></i>
                </div>
                <h5 class="fw-bold" style="color: #4a148c;">Alta Durabilidad</h5>
                <p class="text-muted small mt-2 mb-0">Utilizamos insumos seleccionados para que los bastones resistan largas jornadas de ensayo y presentaciones.</p>
            </div>
        </div>
    </div>
</section>
@endsection