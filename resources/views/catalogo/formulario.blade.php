@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/formulario.css', 'resources/css/variables.css'])
    <style>
    .btn-limite-alcanzado {
        opacity: 0.45;
    }
    </style>
@endpush

@section('titulo', 'Gestión de Catálogo')

@section('contenido')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold brand-glow-purple" style="color: var(--text-main);">
            <i class="fa-solid fa-images me-2" style="color: var(--accent-purple);"></i> Gestión de Catálogo
        </h2>
    </div>

    <!-- 1. FORMULARIO DE SUBIDA -->
    <div class="card card-catalogo mb-4 shadow-sm">
        <div class="card-header card-header-purple fw-bold py-3">
            <i class="fa-solid fa-plus-circle me-2"></i> Añadir Nuevo Modelo al Catálogo
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.catalogo.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4 mb-3">
                    <!-- Título y Foto -->
                    <div class="col-md-6">
                        <label for="titulo" class="form-label" style="color: var(--text-main);">Título del Producto</label>
                        <input type="text" name="titulo" id="titulo" required
                               class="form-control form-control-dark"
                               placeholder="Ej: Bastón Premium Oro">
                        @error('titulo') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="imagen" class="form-label" style="color: var(--text-main);">Fotografía del Producto</label>
                        <input type="file" name="imagen" id="imagen" required accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="form-control form-control-dark">
                        <div class="form-text" style="color: var(--text-muted);">Formatos: JPG, PNG, WEBP. Máx: 2MB.</div>
                        @error('imagen') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Categoría: única obligatoria -->
                    <div class="col-md-3">
                        <label for="categoria" class="form-label" style="color: var(--text-main);">Categoría</label>
                        <select name="categoria" id="categoria"
                                class="form-select form-control-dark select2-form"
                                required data-placeholder="Elegir categoría...">
                            <option value="" disabled selected></option>
                            <option value="baston">Bastón Completo</option>
                            <option value="lazo">Lazos / Cintas</option>
                            <option value="aplique">Apliques / Flores</option>
                            <option value="manualidad">Manualidades (Extra)</option>
                        </select>
                        @error('categoria') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Medida Base -->
                    <div class="col-md-3" id="wrapper_medida">
                        <label for="medida_cm" class="form-label" style="color: var(--text-main);">Medida Base</label>
                        <select name="medida_cm" id="medida_cm"
                                class="form-select form-control-dark select2-form"
                                data-placeholder="Elegir medida...">
                            <option value="" selected disabled></option>
                            <option value="50">50 cm (Estándar)</option>
                            <option value="45">45 cm</option>
                            <option value="55">55 cm</option>
                            <option value="60">60 cm</option>
                            <option value="na" class="d-none">N/A</option>
                        </select>
                        @error('medida_cm') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nivel de Diseño -->
                    <div class="col-md-3" id="wrapper_diseno">
                        <label for="nivel_diseno" class="form-label" style="color: var(--text-main);">Nivel de Diseño</label>
                        <select name="nivel_diseno" id="nivel_diseno"
                                class="form-select form-control-dark select2-form"
                                data-placeholder="Elegir diseño...">
                            <option value="" selected disabled></option>
                            <option value="basico">Básico</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="premium">Premium</option>
                            <option value="na" class="d-none">N/A</option>
                        </select>
                        @error('nivel_diseno') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Volumen Accesorios -->
                    <div class="col-md-3" id="wrapper_accesorios">
                        <label for="nivel_accesorios" class="form-label" style="color: var(--text-main);">Volumen Accesorios</label>
                        <select name="nivel_accesorios" id="nivel_accesorios"
                                class="form-select form-control-dark select2-form"
                                data-placeholder="Elegir accesorios...">
                            <option value="" selected disabled></option>
                            <option value="estandar">Estándar</option>
                            <option value="detallado">Detallado</option>
                            <option value="personalizado_pro">Personalizado</option>
                            <option value="na" class="d-none">N/A</option>
                        </select>
                        @error('nivel_accesorios') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                <div class="mb-4">
                    <label for="descripcion" class="form-label" style="color: var(--text-main);">Descripción de acabados y detalles (Opcional)</label>
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
    <div class="card card-catalogo shadow-sm">
        <div class="card-header card-header-purple fw-bold py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div><i class="fa-solid fa-images me-2"></i> Modelos Actuales</div>

            <div class="d-flex gap-2 flex-wrap" id="contador-limites">
                <span class="badge" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600; border-radius: 8px; padding: 0.5em 0.8em;">
                    <i class="fa-solid fa-images me-1"></i> Carrusel: <span id="badge-carrusel-actual">{{ $totalEnCarrusel }}</span>/{{ $LIMITE_CARRUSEL }}
                </span>
                <span class="badge" style="background-color: #fef9c3; color: #a16207; border: 1px solid #fde68a; font-weight: 600; border-radius: 8px; padding: 0.5em 0.8em;">
                    <i class="fa-solid fa-star me-1"></i> Destacados: <span id="badge-destacados-actual">{{ $totalEnDestacados }}</span>/{{ $LIMITE_DESTACADOS }}
                </span>
                <span class="badge" style="background-color: var(--accent-purple); color: #fff;">{{ $items->total() }} Registros Totales</span>
            </div>
        </div>

        <div class="card-body p-4">

            <!-- MOTOR DE BÚSQUEDA Y FILTROS -->
            <form id="form-filtros" method="GET" action="{{ url()->current() }}" class="mb-4 p-3" 
                  style="background: var(--bg-elevated); border-radius: 12px; border: 1px solid rgba(199, 186, 219, 0.4); box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px #ffffff;">
                <div class="row g-2">
                    <!-- Reduje a col-md-3 para dar espacio al nuevo filtro -->
                    <div class="col-md-3">
                        <input type="text" name="buscar" id="filtro_buscar" class="form-control form-control-dark" placeholder="Buscar por nombre..." value="{{ request('buscar') }}">
                    </div>

                    <!-- Reduje a col-md-2 -->
                    <div class="col-md-2">
                        <select name="categoria" id="filtro_categoria" class="form-select form-control-dark select2-filtro">
                            <option value="todas" {{ request('categoria', 'todas') == 'todas' ? 'selected' : '' }}>Todas las categorías</option>
                            <option value="baston" {{ request('categoria') == 'baston' ? 'selected' : '' }}>Bastones Completos</option>
                            <option value="lazo" {{ request('categoria') == 'lazo' ? 'selected' : '' }}>Lazos / Cintas</option>
                            <option value="aplique" {{ request('categoria') == 'aplique' ? 'selected' : '' }}>Apliques / Flores</option>
                            <option value="manualidad" {{ request('categoria') == 'manualidad' ? 'selected' : '' }}>Manualidades</option>
                        </select>
                    </div>

                    <!-- Reduje a col-md-2 -->
                    <div class="col-md-2">
                        <select name="estado" id="filtro_estado" class="form-select form-control-dark select2-filtro">
                            <option value="todos" {{ request('estado', 'todos') == 'todos' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="destacado" {{ request('estado') == 'destacado' ? 'selected' : '' }}>Destacados</option>
                            <option value="carrusel" {{ request('estado') == 'carrusel' ? 'selected' : '' }}>En Carrusel</option>
                            <option value="oculto" {{ request('estado') == 'oculto' ? 'selected' : '' }}>Ocultos</option>
                        </select>
                    </div>

                    <!-- NUEVO FILTRO DE FECHA (col-md-3) -->
                    <div class="col-md-3">
                        <select name="fecha" id="filtro_fecha" class="form-select form-control-dark select2-filtro">
                            <option value="recientes" {{ request('fecha', 'recientes') == 'recientes' ? 'selected' : '' }}>Más recientes</option>
                            <option value="antiguos" {{ request('fecha') == 'antiguos' ? 'selected' : '' }}>Más antiguos</option>
                            <option value="ultimos_7_dias" {{ request('fecha') == 'ultimos_7_dias' ? 'selected' : '' }}>Últimos 7 días</option>
                        </select>
                    </div>

                    <!-- Botones (col-md-2 se mantiene igual) -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-purple w-100" title="Aplicar filtros"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <a href="{{ url()->current() }}" id="btn-limpiar-filtros" class="btn btn-outline-secondary w-100" title="Limpiar filtros" style="background: #fff; border-color: rgba(199, 186, 219, 0.8);"><i class="fa-solid fa-eraser"></i></a>
                    </div>
                </div>
            </form>

            <!-- CONTENEDOR DINÁMICO (Para AJAX) -->
            <div id="contenedor-resultados">
            <!-- CUADRÍCULA DE TARJETAS -->
            <div class="row g-4">
                @forelse($items as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="item-card-wrapper h-100 position-relative">

                            <!-- Burbuja de edición -->
                            <button type="button" class="btn-edit-bubble"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"
                                    title="Editar modelo"
                                    onclick="cargarDatosEditar(
                                        {{ $item->id }},
                                        '{{ addslashes($item->titulo) }}',
                                        '{{ addslashes($item->descripcion ?? '') }}',
                                        '{{ $item->categoria }}',
                                        '{{ $item->medida_cm }}',
                                        '{{ $item->nivel_diseno }}',
                                        '{{ $item->nivel_accesorios }}'
                                    )">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <!-- TARJETA SOFT UI -->
                            <div class="card item-card h-100">

                                <div class="card-image-wrapper">
                                    <div class="bg-blur" style="background-image: url('{{ asset('storage/' . $item->imagen_path) }}');"></div>
                                    <div class="bg-overlay"></div>
                                    <img src="{{ asset('storage/' . $item->imagen_path) }}" class="card-img-top" alt="{{ $item->titulo }}">
                                </div>

                                <div class="card-body d-flex flex-column p-4">
                                    <h5 class="card-title mb-3">{{ $item->titulo }}</h5>
                                    
                                    <div class="mb-3 mt-1">
                                        <span class="badge" style="background-color: #f3e8ff; color: var(--accent-purple); border: 1px solid #d8b4fe; letter-spacing: 0.5px; font-weight: 600; border-radius: 8px; padding: 0.5em 0.8em;">
                                            {{ strtoupper($item->categoria) }}
                                        </span>
                                        
                                        @if(isset($item->medida_cm) && $item->medida_cm !== 'na' && $item->categoria === 'baston')
                                            <span class="badge ms-1" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600; border-radius: 8px; padding: 0.5em 0.8em;">
                                                {{ $item->medida_cm }} CM
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <p class="card-text small flex-grow-1" style="color: var(--text-muted); line-height: 1.5;">{{ $item->descripcion ?? 'Sin descripción.' }}</p>

                                    <div class="d-flex flex-wrap actions-row justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid rgba(199, 186, 219, 0.4);">

                                        <div class="actions-tray">
                                            <form action="{{ route('admin.catalogo.carrusel', $item->id) }}" method="POST" class="d-inline form-carrusel-toggle">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action {{ $item->en_carrusel ? 'is-on-info' : '' }}" title="{{ $item->en_carrusel ? 'Quitar del carrusel principal' : 'Mostrar en el carrusel principal' }}">
                                                    <i class="fa-solid fa-images"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.catalogo.destacado', $item->id) }}" method="POST" class="d-inline form-destacado-toggle">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action {{ $item->es_destacado ? 'is-on-warning' : '' }}" title="{{ $item->es_destacado ? 'Quitar de destacados' : 'Marcar como destacado' }}">
                                                    <i class="fa-solid fa-star"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="actions-tray">
                                            <form action="{{ route('admin.catalogo.toggle', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action {{ $item->activo ? 'is-on-success' : '' }}" title="{{ $item->activo ? 'Visible en la landing (clic para ocultar)' : 'Oculto (clic para mostrar)' }}">
                                                    <i class="fa-solid {{ $item->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.catalogo.destroy', $item->id) }}" method="POST" class="form-eliminar d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-action is-danger btn-eliminar" title="Eliminar modelo">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: var(--accent-purple); opacity: 0.5;"></i>
                        <p style="color: var(--text-muted);">No se encontraron resultados con estos filtros.</p>
                    </div>
                @endforelse
            </div>

            <!-- MENÚ DE SALTO DE PÁGINA -->
            <div class="d-flex justify-content-center mt-5">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
            </div> <!-- FIN CONTENEDOR DINÁMICO -->

        </div>
    </div>
</div>

<!-- MODAL DE EDICIÓN -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: var(--text-main);"><i class="fa-solid fa-pen-to-square me-2" style="color: var(--accent-purple);"></i> Editar Modelo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_titulo" class="form-label">Título del Producto</label>
                            <input type="text" name="titulo" id="edit_titulo" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_imagen" class="form-label">Nueva Fotografía (Opcional)</label>
                            <input type="file" name="imagen" id="edit_imagen" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-control form-control-dark">
                            <div class="form-text">Si no seleccionas nada, se mantendrá la foto actual.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="edit_categoria" class="form-label">Categoría</label>
                            <select name="categoria" id="edit_categoria" class="form-select form-control-dark select2-modal" required data-placeholder="Elegir categoría...">
                                <option value="" disabled></option>
                                <option value="baston">Bastón Completo</option>
                                <option value="lazo">Lazos / Cintas</option>
                                <option value="aplique">Apliques / Flores</option>
                                <option value="manualidad">Manualidades (Extra)</option>
                            </select>
                        </div>

                        <!-- ID añadido: edit_wrapper_medida -->
                        <div class="col-md-3" id="edit_wrapper_medida">
                            <label for="edit_medida_cm" class="form-label">Medida Base</label>
                            <select name="medida_cm" id="edit_medida_cm" class="form-select form-control-dark select2-modal" data-placeholder="Elegir medida...">
                                <option value=""></option>
                                <option value="50">50 cm (Estándar)</option>
                                <option value="45">45 cm</option>
                                <option value="55">55 cm</option>
                                <option value="60">60 cm</option>
                                <option value="na" class="d-none">N/A</option>
                            </select>
                        </div>

                        <!-- ID añadido: edit_wrapper_diseno -->
                        <div class="col-md-3" id="edit_wrapper_diseno">
                            <label for="edit_nivel_diseno" class="form-label">Nivel Diseño</label>
                            <select name="nivel_diseno" id="edit_nivel_diseno" class="form-select form-control-dark select2-modal" data-placeholder="Elegir diseño...">
                                <option value=""></option>
                                <option value="basico">Básico</option>
                                <option value="intermedio">Intermedio</option>
                                <option value="premium">Premium</option>
                                <option value="na" class="d-none">N/A</option>
                            </select>
                        </div>

                        <!-- ID añadido: edit_wrapper_accesorios -->
                        <div class="col-md-3" id="edit_wrapper_accesorios">
                            <label for="edit_nivel_accesorios" class="form-label">Accesorios</label>
                            <select name="nivel_accesorios" id="edit_nivel_accesorios" class="form-select form-control-dark select2-modal" data-placeholder="Elegir accesorios...">
                                <option value=""></option>
                                <option value="estandar">Estándar</option>
                                <option value="detallado">Detallado</option>
                                <option value="personalizado_pro">Personalizado Pro</option>
                                <option value="na" class="d-none">N/A</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" rows="3" class="form-control form-control-dark"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-purple">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    window.CatalogoConfig = {
        limites: {
            carrusel: { max: {{ $LIMITE_CARRUSEL ?? 3 }}, actual: {{ $totalEnCarrusel ?? 0 }} },
            destacado: { max: {{ $LIMITE_DESTACADOS ?? 6 }}, actual: {{ $totalEnDestacados ?? 0 }} }
        },
        baseUrl: "{{ url('admin/catalogo') }}",
        session: {
            success: {!! json_encode(session('success')) !!},
            error: {!! json_encode(session('error')) !!}
        }
    };
</script>

@vite(['resources/js/formulario.js'])

@endpush