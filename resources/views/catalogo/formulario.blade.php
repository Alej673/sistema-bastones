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

    <!-- 1. FORMULARIO DE SUBIDA -->
    <div class="card card-catalogo mb-4 shadow">
        <div class="card-header card-header-purple fw-bold py-3">
            <i class="fa-solid fa-plus-circle me-2"></i> Añadir Nuevo Modelo a la Landing Page
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.catalogo.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4 mb-3">
                    <!-- Título y Foto -->
                    <div class="col-md-6">
                        <label for="titulo" class="form-label text-light">Título del Producto</label>
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

                    <!-- Categoría: única obligatoria -->
                    <div class="col-md-3">
                        <label for="categoria" class="form-label text-light">Categoría</label>
                        <select name="categoria" id="categoria"
                                class="form-select form-control-dark select2-tag"
                                required data-placeholder="Elegir categoría...">
                            <option value="" disabled selected></option>
                            <option value="baston">Bastón Completo</option>
                            <option value="lazo">Lazos / Cintas</option>
                            <option value="aplique">Apliques / Flores</option>
                            <option value="manualidad">Manualidades (Extra)</option>
                        </select>
                        @error('categoria') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Medida Base: opcional -->
                    <div class="col-md-3">
                        <label for="medida_cm" class="form-label text-light">Medida Base</label>
                        <select name="medida_cm" id="medida_cm"
                                class="form-select form-control-dark select2-tag"
                                data-placeholder="Elegir medida...">
                            <option value="" selected disabled></option>
                            <option value="50">50 cm (Estándar)</option>
                            <option value="45">45 cm</option>
                            <option value="55">55 cm</option>
                            <option value="60">60 cm</option>
                        </select>
                        @error('medida_cm') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nivel de Diseño: opcional -->
                    <div class="col-md-3">
                        <label for="nivel_diseno" class="form-label text-light">Nivel de Diseño</label>
                        <select name="nivel_diseno" id="nivel_diseno"
                                class="form-select form-control-dark select2-tag"
                                data-placeholder="Elegir diseño...">
                            <option value="" selected disabled></option>
                            <option value="basico">Básico</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="premium">Premium</option>
                        </select>
                        @error('nivel_diseno') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- Volumen Accesorios: opcional -->
                    <div class="col-md-3">
                        <label for="nivel_accesorios" class="form-label text-light">Volumen Accesorios</label>
                        <select name="nivel_accesorios" id="nivel_accesorios"
                                class="form-select form-control-dark select2-tag"
                                data-placeholder="Elegir accesorios...">
                            <option value="" selected disabled></option>
                            <option value="estandar">Estándar</option>
                            <option value="detallado">Detallado</option>
                            <option value="personalizado_pro">Personalizado</option>
                        </select>
                        @error('nivel_accesorios') <span class="text-danger small">{{ $message }}</span> @enderror
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
        <div class="card-header card-header-purple fw-bold py-3 d-flex justify-content-between align-items-center">
            <div><i class="fa-solid fa-images me-2"></i> Modelos Actuales</div>
            <span class="badge bg-purple">{{ $items->total() }} Registros Totales</span>
        </div>

        <div class="card-body p-4">

            <!-- MOTOR DE BÚSQUEDA Y FILTROS -->
            <form method="GET" action="{{ url()->current() }}" class="mb-4 p-3" style="background: rgba(0,0,0,0.15); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                <div class="row g-2">
                    <!-- Buscador de texto -->
                    <div class="col-md-4">
                        <input type="text" name="buscar" class="form-control form-control-dark" placeholder="Buscar por nombre..." value="{{ request('buscar') }}">
                    </div>

                    <!-- Filtro Categoría -->
                    <div class="col-md-3">
                        <select name="categoria" id="filtro_categoria" class="form-select form-control-dark select2-tag" data-placeholder="Todas las categorías">
                            <option value="todas" {{ request('categoria', 'todas') == 'todas' ? 'selected' : '' }}>Todas las categorías</option>
                            <option value="baston" {{ request('categoria') == 'baston' ? 'selected' : '' }}>Bastones Completos</option>
                            <option value="lazo" {{ request('categoria') == 'lazo' ? 'selected' : '' }}>Lazos / Cintas</option>
                            <option value="aplique" {{ request('categoria') == 'aplique' ? 'selected' : '' }}>Apliques / Flores</option>
                            <option value="manualidad" {{ request('categoria') == 'manualidad' ? 'selected' : '' }}>Manualidades</option>
                        </select>
                    </div>

                    <!-- Filtro Estados -->
                    <div class="col-md-3">
                        <select name="estado" id="filtro_estado" class="form-select form-control-dark select2-tag" data-placeholder="Todos los estados">
                            <option value="todos" {{ request('estado', 'todos') == 'todos' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="destacado" {{ request('estado') == 'destacado' ? 'selected' : '' }}>⭐ Destacados</option>
                            <option value="carrusel" {{ request('estado') == 'carrusel' ? 'selected' : '' }}>🖼️ En Carrusel</option>
                            <option value="oculto" {{ request('estado') == 'oculto' ? 'selected' : '' }}>👁️‍🗨️ Ocultos</option>
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-purple w-100" title="Aplicar filtros"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100" title="Limpiar filtros"><i class="fa-solid fa-eraser"></i></a>
                    </div>
                </div>
            </form>
            <!-- FIN MOTOR DE BÚSQUEDA -->

            <!-- CUADRÍCULA DE TARJETAS -->
            <div class="row g-4">
                @forelse($items as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="item-card-wrapper h-100">

                            <!-- Burbuja de edición (esquina, glassmorphism) -->
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

                            <div class="card item-card h-100 text-white" style="box-shadow: 0 5px 15px rgba(0,0,0,0.3); background-color: var(--card-bg);">

                                <div style="background-color: rgba(0,0,0,0.2); border-radius: 4px 4px 0 0; padding: 10px;">
                                    <img src="{{ asset('storage/' . $item->imagen_path) }}" class="card-img-top" alt="{{ $item->titulo }}" style="height: 220px; width: 100%; object-fit: contain; opacity: 0.95;">
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold">{{ $item->titulo }}</h5>
                                    <!-- INYECCIÓN DE TAGS VISUALES (Este es el que se te borró) -->
                                    <div class="mb-2 mt-1">
                                        <!-- Tag 1: Categoría Principal (Morado claro) -->
                                        <span class="badge" style="background-color: rgba(147, 51, 234, 0.2); color: #d8b4fe; border: 1px solid rgba(147, 51, 234, 0.5); letter-spacing: 0.5px; font-weight: 500;">
                                            {{ strtoupper($item->categoria) }}
                                        </span>
                                        
                                        <!-- Tag 2: Medida Base (Solo se muestra si es un bastón y tiene medida) -->
                                        @if(isset($item->medida_cm) && $item->medida_cm !== 'na' && $item->categoria === 'baston')
                                            <span class="badge ms-1" style="background-color: rgba(255, 255, 255, 0.1); color: #ced4da; border: 1px solid rgba(255, 255, 255, 0.2); font-weight: 500;">
                                                {{ $item->medida_cm }} CM
                                            </span>
                                        @endif
                                    </div>
                                    <p class="card-text small text-light opacity-75 flex-grow-1">{{ $item->descripcion ?? 'Sin descripción.' }}</p>

                                    <div class="d-flex flex-wrap actions-row justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">

                                        <!-- BANDEJA 1: Carrusel + Destacado -->
                                        <div class="actions-tray">
                                            <form action="{{ route('admin.catalogo.carrusel', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action {{ $item->en_carrusel ? 'is-on-info' : '' }}" title="{{ $item->en_carrusel ? 'Quitar del carrusel principal' : 'Mostrar en el carrusel principal' }}">
                                                    <i class="fa-solid fa-images"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.catalogo.destacado', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action {{ $item->es_destacado ? 'is-on-warning' : '' }}" title="{{ $item->es_destacado ? 'Quitar de destacados' : 'Marcar como destacado' }}">
                                                    <i class="fa-solid fa-star"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- BANDEJA 2: Visibilidad + Eliminar -->
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
                        <i class="fa-solid fa-folder-open fa-3x mb-3" style="color: var(--brand-purple-light); opacity: 0.6;"></i>
                        <p class="text-light opacity-75">No se encontraron resultados con estos filtros.</p>
                    </div>
                @endforelse
            </div>

            <!-- MENÚ DE SALTO DE PÁGINA (Paginación) -->
            <div class="d-flex justify-content-center mt-5">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

<!-- MODAL DE EDICIÓN -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #1b0f28; border: 1px solid var(--brand-purple-light);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title text-white"><i class="fa-solid fa-pen-to-square me-2"></i> Editar Modelo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <!-- Fila 1: Título y Foto -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_titulo" class="form-label text-light">Título del Producto</label>
                            <input type="text" name="titulo" id="edit_titulo" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_imagen" class="form-label text-light">Nueva Fotografía (Opcional)</label>
                            <input type="file" name="imagen" id="edit_imagen" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-control form-control-dark">
                            <div class="form-text text-secondary">Si no seleccionas nada, se mantendrá la foto actual.</div>
                        </div>
                    </div>

                    <!-- Fila 2: Categoría obligatoria + 3 opcionales -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="edit_categoria" class="form-label text-light">Categoría</label>
                            <select name="categoria" id="edit_categoria"
                                    class="form-select form-control-dark select2-tag"
                                    required data-placeholder="Elegir categoría...">
                                <option value="" disabled></option>
                                <option value="baston">Bastón Completo</option>
                                <option value="lazo">Lazos / Cintas</option>
                                <option value="aplique">Apliques / Flores</option>
                                <option value="manualidad">Manualidades (Extra)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_medida_cm" class="form-label text-light">Medida Base</label>
                            <select name="medida_cm" id="edit_medida_cm"
                                    class="form-select form-control-dark select2-tag"
                                    data-placeholder="Elegir medida...">
                                <option value=""></option>
                                <option value="50">50 cm (Estándar)</option>
                                <option value="45">45 cm</option>
                                <option value="55">55 cm</option>
                                <option value="60">60 cm</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_nivel_diseno" class="form-label text-light">Nivel Diseño</label>
                            <select name="nivel_diseno" id="edit_nivel_diseno"
                                    class="form-select form-control-dark select2-tag"
                                    data-placeholder="Elegir diseño...">
                                <option value=""></option>
                                <option value="basico">Básico</option>
                                <option value="intermedio">Intermedio</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_nivel_accesorios" class="form-label text-light">Accesorios</label>
                            <select name="nivel_accesorios" id="edit_nivel_accesorios"
                                    class="form-select form-control-dark select2-tag"
                                    data-placeholder="Elegir accesorios...">
                                <option value=""></option>
                                <option value="estandar">Estándar</option>
                                <option value="detallado">Detallado</option>
                                <option value="personalizado_pro">Personalizado Pro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 3: Descripción -->
                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label text-light">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" rows="3" class="form-control form-control-dark"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
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

        // 1. Guardar la posición del scroll justo antes de que la página muera/recargue
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });

        // 2. Restaurar la posición del scroll cuando la página vuelve a nacer
        window.addEventListener('load', function() {
            let scrollPosition = sessionStorage.getItem('scrollPosition');
            
            if (scrollPosition !== null) {
                // Mover la pantalla suavemente a la posición guardada
                window.scrollTo({
                    top: parseInt(scrollPosition),
                    behavior: 'instant' // Usamos instant para que no se vea el "salto", sino que aparezca ahí directamente
                });
                
                // Limpiar la memoria para que si entras otro día, empieces desde arriba
                sessionStorage.removeItem('scrollPosition');
            }
        });

        document.addEventListener('DOMContentLoaded', function () {

            // ===== Select2 para todos los tags FUERA del modal (formulario de subida + filtros) =====
            $('.select2-tag').not('#modalEditar .select2-tag').each(function () {
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).data('placeholder'),
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });
            });

            // ===== Select2 para los tags DENTRO del modal (requiere dropdownParent) =====
            $('#modalEditar .select2-tag').each(function () {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $('#modalEditar'),
                    placeholder: $(this).data('placeholder'),
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });
            });

            // 1. MODAL DE ÉXITO (Al guardar, editar o eliminar)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: '{{ session('success') }}',
                    background: '#1b0f28',
                    color: '#ffffff',
                    confirmButtonColor: '#9333ea',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            // Auto-submit del formulario de filtro al cambiar Categoría o Estado
            // (usa ids únicos: filtro_categoria / filtro_estado, para no chocar
            // con los otros selects "categoria" del formulario de subida y del modal)
            $('#filtro_categoria, #filtro_estado').on('change', function () {
                this.form.submit();
            });

            // 2. MODAL DE CONFIRMACIÓN PARA ELIMINAR
            const botonesEliminar = document.querySelectorAll('.btn-eliminar');

            botonesEliminar.forEach(boton => {
                boton.addEventListener('click', function (e) {
                    e.preventDefault();
                    const formulario = this.closest('.form-eliminar');

                    Swal.fire({
                        title: '¿Eliminar este diseño?',
                        text: "Se borrará del catálogo y de la landing page. Esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        background: '#1b0f28',
                        color: '#ffffff',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            popup: 'border border-secondary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            formulario.submit();
                        }
                    });
                });
            });

        });

        // ===== Cargar datos en el modal de edición =====
        function cargarDatosEditar(id, titulo, descripcion, categoria, medida, diseno, accesorios) {
            let baseUrl = "{{ url('admin/catalogo') }}";
            document.getElementById('formEditar').action = baseUrl + '/' + id;

            document.getElementById('edit_titulo').value = titulo;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_imagen').value = '';

            // Con Select2 hay que usar jQuery .val() + trigger('change')
            $('#edit_categoria').val(categoria).trigger('change');
            $('#edit_medida_cm').val(medida || '').trigger('change');
            $('#edit_nivel_diseno').val(diseno || '').trigger('change');
            $('#edit_nivel_accesorios').val(accesorios || '').trigger('change');
        }
    </script>
@endpush