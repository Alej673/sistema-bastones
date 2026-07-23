@extends('layouts.public')

@section('content')
<div class="container py-5">
    <h1 class="text-center mb-5" style="font-family: 'Playfair Display', serif; color: var(--color-lila-fuerte);">
        Catálogo Arte Titi_Val
    </h1>

    @php
        $secciones = [
            ['titulo' => 'Bastones para Bastoneras', 'items' => $bastones, 'slug' => 'baston'],
            ['titulo' => 'Lazos', 'items' => $lazos, 'slug' => 'lazo'],
            ['titulo' => 'Manualidades y Apliques', 'items' => $manualidades, 'slug' => 'manualidad'],
            ['titulo' => 'Apliques y Flores', 'items' => $apliques, 'slug' => 'aplique'],
        ];
    @endphp

    @foreach($secciones as $index => $seccion)
        <div class="mb-5 {{ $index > 0 ? 'mt-4' : '' }}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 style="font-family: 'Playfair Display', serif; color: var(--color-texto-principal);">
                    {{ $seccion['titulo'] }}
                </h3>
                <a href="{{ route('catalogo.categoria', $seccion['slug']) }}" class="btn btn-sm btn-ver-mas">
                    Ver Más &rarr;
                </a>
            </div>
            <div class="row g-4">
                @foreach($seccion['items'] as $item)
                    @include('catalogo.partials.card', ['item' => $item])
                @endforeach
            </div>
        </div>

        @if(!$loop->last)
            <hr style="background-color: var(--color-oro-claro); opacity: 0.4; height: 2px; border: none;">
        @endif
    @endforeach
</div>
@endsection