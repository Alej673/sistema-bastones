@extends('layouts.public')

@section('title', 'Mis Favoritos - Arte Titi_Val')

@section('content')
<div class="container py-5" style="min-height: 70vh;">
    
    <!-- Cabecera de la sección -->
    <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: var(--color-lila-fuerte); font-family: 'Playfair Display', serif;">
            <i class="fa-solid fa-heart text-danger me-2"></i> Mis Modelos Favoritos
        </h2>
        <p class="text-muted">Aquí están los diseños de Arte Titi_Val que has guardado para tus futuras presentaciones.</p>
    </div>

    <!-- Verificación de favoritos vacíos -->
    @if($favoritos->isEmpty())
        <div class="alert text-center shadow-sm p-5 mx-auto" style="max-width: 600px; background-color: #f6e8ff; border: 1px dashed var(--color-lila-fuerte); border-radius: 15px;">
            <i class="fa-regular fa-folder-open mb-3" style="font-size: 3rem; color: #b39ddb;"></i>
            <h5 class="fw-bold" style="color: #4a148c;">Aún no tienes favoritos</h5>
            <p class="text-muted mb-4">Explora nuestro catálogo y presiona el corazón en los modelos que más te gusten.</p>
            <a href="{{ route('home') }}" class="btn rounded-pill px-4" style="background-color: var(--color-lila-fuerte); color: white; font-weight: 600;">
                <i class="fa-solid fa-magnifying-glass me-2"></i> Explorar Catálogo
            </a>
        </div>
    @else
        <!-- Cuadrícula de Favoritos reutilizando tu Partial -->
        <div class="row g-4 justify-content-center">
            @foreach($favoritos as $item)
                <!-- Llamamos a tu tarjeta y le pasamos la variable $item -->
                @include('catalogo.partials.card', ['item' => $item])
            @endforeach
        </div>

        <!-- Paginación con estilo Bootstrap -->
        <div class="d-flex justify-content-center mt-5">
            {{ $favoritos->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>
@endsection