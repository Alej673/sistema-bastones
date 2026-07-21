@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/formulario.css'])
@endpush

@section('titulo', 'Gestión de Catálogo')

@section('contenido')


<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold brand-glow-purple">
            <i class="fa-solid fa-images me-2" style="color: var(--brand-purple-light);"></i> Gestión de Catálogo
        </h2>
    </div>

    <!-- Alertas de Éxito -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="background: rgba(25, 135, 84, 0.15); border: 1px solid #198754; color: #a3cfbb;" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. FORMULARIO DE SUBIDA -->
    <div class="card card-catalogo mb-4 shadow">
        <div class="card-header card-header-purple fw-bold py-3">
            <i class="fa-solid fa-plus-circle me-2"></i> Añadir Nuevo Modelo a la Landing Page
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.catalogo.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4 mb-3">
                    <div class="col-md-6">
                        <label for="titulo" class="form-label text-light">Título del Bastón</label>
                        <input type="text" name="titulo" id="titulo" required 
                               class="form-control form-control-dark" 
                               placeholder="Ej: Bastón Premium Oro">
                        @error('titulo') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="imagen" class="form-label text-light">Fotografía del Producto</label>
                        <input type="file" name="imagen" id="imagen" required accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="form-control form-control-dark">
                        <div class="form-text text-secondary">Formatos: JPG, PNG, WEBP. Máx: 2MB.</div>
                        @error('imagen') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="descripcion" class="form-label text-light">Descripción de acabados y detalles (Opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="3" 
                              class="form-control form-control-dark" 
                              placeholder="Menciona si incluye lazos especiales..."></textarea>
                    @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-purple fw-bold px-4">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Subir y Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. GALERÍA DE MODELOS PUBLICADOS -->
    <div class="card card-catalogo shadow">
        <div class="card-header card-header-purple fw-bold py-3">
            <i class="fa-solid fa-images me-2"></i> Modelos Actuales
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                @forelse($items as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="card item-card h-100 text-white" style="box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                            <img src="{{ asset('storage/' . $item->imagen_path) }}" class="card-img-top" alt="{{ $item->titulo }}" style="height: 220px; object-fit: cover; opacity: 0.9;">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold">{{ $item->titulo }}</h5>
                                <p class="card-text small text-light opacity-75 flex-grow-1">{{ $item->descripcion ?? 'Sin descripción.' }}</p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid var(--card-border);">
                                    
                                    <!-- Botón Ocultar/Mostrar -->
                                    <form action="{{ route('admin.catalogo.toggle', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $item->activo ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                                            <i class="fa-solid {{ $item->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i> 
                                            {{ $item->activo ? 'Visible' : 'Oculto' }}
                                        </button>
                                    </form>

                                    <!-- Botón Eliminar -->
                                    <form action="{{ route('admin.catalogo.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este diseño del catálogo definitivamente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: var(--brand-purple-light); opacity: 0.6;"></i>
                        <p class="text-light opacity-75">No hay bastones registrados en el catálogo aún.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection