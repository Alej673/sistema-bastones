@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="mb-4 d-flex align-items-center">
        <a href="{{ route('catalogo.index') }}" class="btn-volver me-3">
            &larr; Volver al Catálogo
        </a>
        <h1 style="font-family: 'Playfair Display', serif; color: var(--color-lila-fuerte); margin: 0;">
            {{ $tituloCategoria }}
        </h1>
    </div>

    <!-- BARRA DE FILTROS INTELIGENTE -->
    <form action="{{ route('catalogo.categoria', $categoria) }}" method="GET" class="mb-5 p-4 shadow-sm filtro-panel">
        <div class="row g-3 align-items-end justify-content-center">

            <!-- Filtro: Medida (SOLO APARECE PARA BASTONES) -->
            @if($categoria === 'baston' || $categoria === 'bastones')
            <div class="col-md-3">
                <label class="filtro-label d-block">Medida Base</label>
                <select name="medida" class="form-select filtro-select">
                    <option value="">Todas las medidas</option>
                    <option value="45" {{ request('medida') == '45' ? 'selected' : '' }}>45 cm</option>
                    <option value="50" {{ request('medida') == '50' ? 'selected' : '' }}>50 cm</option>
                    <option value="55" {{ request('medida') == '55' ? 'selected' : '' }}>55 cm</option>
                    <option value="60" {{ request('medida') == '60' ? 'selected' : '' }}>60 cm</option>
                </select>
            </div>
            @endif

            <!-- Filtro: Nivel de Diseño (SIEMPRE VISIBLE) -->
            <div class="col-md-3">
                <label class="filtro-label d-block">Nivel de Diseño</label>
                <select name="diseno" class="form-select filtro-select">
                    <option value="">Todos los diseños</option>
                    <option value="basico" {{ request('diseno') == 'basico' ? 'selected' : '' }}>Básico</option>
                    <option value="intermedio" {{ request('diseno') == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                    <option value="premium" {{ request('diseno') == 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
            </div>

            <!-- Filtro: Volumen de Accesorios (NO APARECE PARA LAZOS NI APLIQUES/FLORES) -->
            @if(!in_array(strtolower($categoria), ['lazo', 'lazos', 'aplique', 'apliques', 'flor', 'flores', 'aplique-flor', 'aplique_flor']))
            <div class="col-md-3">
                <label class="filtro-label d-block">Volumen Accesorios</label>
                <select name="accesorios" class="form-select filtro-select">
                    <option value="">Todos los accesorios</option>
                    <option value="estandar" {{ request('accesorios') == 'estandar' ? 'selected' : '' }}>Estándar</option>
                    <option value="detallado" {{ request('accesorios') == 'detallado' ? 'selected' : '' }}>Detallado</option>
                    <option value="Personalizado Pro" {{ request('accesorios') == 'personalizado_pro' ? 'selected' : '' }}>Personalizado Pro</option>
                </select>
            </div>
            @endif

            <!-- Botones de Acción -->
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn shadow-sm btn-filtrar">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
                @if(request()->hasAny(['medida', 'diseno', 'accesorios']))
                    <a href="{{ route('catalogo.categoria', $categoria) }}" class="btn btn-limpiar">
                        Limpiar
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="row g-4">
        @forelse($items as $item)
            <!-- Agregamos 'loopIteration' => $loop->iteration -->
            @include('catalogo.partials.card', ['item' => $item, 'loopIteration' => $loop->iteration])
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-light" style="font-family: 'Inter', sans-serif;">Aún no hay productos en esta categoría.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación de Laravel compatible con Bootstrap 5 -->
    <div class="d-flex justify-content-center mt-5">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection