@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/formulario.css', 'resources/css/variables.css'])
@endpush

@section('titulo', 'Gestión de Catálogo')

@section('contenido')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- Eliminado text-white -->
        <h2 class="fw-bold brand-glow-purple" style="color: var(--text-main);">
            <i class="fa-solid fa-images me-2" style="color: var(--accent-purple);"></i> Gestión de Catálogo
        </h2>
    </div>

    <!-- 1. FORMULARIO DE SUBIDA -->
    <div class="card card-catalogo mb-4 shadow-sm">
        <div class="card-header card-header-purple fw-bold py-3">
            <i class="fa-solid fa-plus-circle me-2"></i> Añadir Nuevo Modelo a la Landing Page
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

                    <!-- Medida Base: opcional -->
                    <div class="col-md-3">
                        <label for="medida_cm" class="form-label" style="color: var(--text-main);">Medida Base</label>
                        <select name="medida_cm" id="medida_cm"
                                class="form-select form-control-dark select2-form"
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
                        <label for="nivel_diseno" class="form-label" style="color: var(--text-main);">Nivel de Diseño</label>
                        <select name="nivel_diseno" id="nivel_diseno"
                                class="form-select form-control-dark select2-form"
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
                        <label for="nivel_accesorios" class="form-label" style="color: var(--text-main);">Volumen Accesorios</label>
                        <select name="nivel_accesorios" id="nivel_accesorios"
                                class="form-select form-control-dark select2-form"
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
            <!-- Cambiamos el fondo oscuro por un inset neumórfico suave -->
            <form id="form-filtros" method="GET" action="{{ url()->current() }}" class="mb-4 p-3" 
                  style="background: var(--bg-elevated); border-radius: 12px; border: 1px solid rgba(199, 186, 219, 0.4); box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px #ffffff;">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="buscar" id="filtro_buscar" class="form-control form-control-dark" placeholder="Buscar por nombre..." value="{{ request('buscar') }}">
                    </div>

                    <div class="col-md-3">
                        <select name="categoria" id="filtro_categoria" class="form-select form-control-dark select2-filtro">
                            <option value="todas" {{ request('categoria', 'todas') == 'todas' ? 'selected' : '' }}>Todas las categorías</option>
                            <option value="baston" {{ request('categoria') == 'baston' ? 'selected' : '' }}>Bastones Completos</option>
                            <option value="lazo" {{ request('categoria') == 'lazo' ? 'selected' : '' }}>Lazos / Cintas</option>
                            <option value="aplique" {{ request('categoria') == 'aplique' ? 'selected' : '' }}>Apliques / Flores</option>
                            <option value="manualidad" {{ request('categoria') == 'manualidad' ? 'selected' : '' }}>Manualidades</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="estado" id="filtro_estado" class="form-select form-control-dark select2-filtro">
                            <option value="todos" {{ request('estado', 'todos') == 'todos' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="destacado" {{ request('estado') == 'destacado' ? 'selected' : '' }}>Destacados</option>
                            <option value="carrusel" {{ request('estado') == 'carrusel' ? 'selected' : '' }}>En Carrusel</option>
                            <option value="oculto" {{ request('estado') == 'oculto' ? 'selected' : '' }}>Ocultos</option>
                        </select>
                    </div>

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
                            <!-- Quitamos text-white para que herede el color de .item-card -->
                            <div class="card item-card h-100">

                                <div class="card-image-wrapper">
                                    <div class="bg-blur" style="background-image: url('{{ asset('storage/' . $item->imagen_path) }}');"></div>
                                    <div class="bg-overlay"></div>
                                    <img src="{{ asset('storage/' . $item->imagen_path) }}" class="card-img-top" alt="{{ $item->titulo }}">
                                </div>

                                <div class="card-body d-flex flex-column p-4">
                                    <h5 class="card-title mb-3">{{ $item->titulo }}</h5>
                                    
                                    <div class="mb-3 mt-1">
                                        <!-- Tag 1: Categoría Principal (Pastel Morado) -->
                                        <span class="badge" style="background-color: #f3e8ff; color: var(--accent-purple); border: 1px solid #d8b4fe; letter-spacing: 0.5px; font-weight: 600; border-radius: 8px; padding: 0.5em 0.8em;">
                                            {{ strtoupper($item->categoria) }}
                                        </span>
                                        
                                        <!-- Tag 2: Medida Base (Pastel Azul) -->
                                        @if(isset($item->medida_cm) && $item->medida_cm !== 'na' && $item->categoria === 'baston')
                                            <span class="badge ms-1" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600; border-radius: 8px; padding: 0.5em 0.8em;">
                                                {{ $item->medida_cm }} CM
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <p class="card-text small flex-grow-1" style="color: var(--text-muted); line-height: 1.5;">{{ $item->descripcion ?? 'Sin descripción.' }}</p>

                                    <!-- Bandejas de acciones -->
                                    <!-- Cambiamos el color del borde superior -->
                                    <div class="d-flex flex-wrap actions-row justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid rgba(199, 186, 219, 0.4);">

                                        <!-- BANDEJA 1 -->
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

                                        <!-- BANDEJA 2 -->
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
<!-- Quitamos estilos en línea oscuros -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <!-- Quitamos text-white y arreglamos icono -->
                <h5 class="modal-title" style="color: var(--text-main);"><i class="fa-solid fa-pen-to-square me-2" style="color: var(--accent-purple);"></i> Editar Modelo</h5>
                <!-- Quitamos btn-close-white -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <!-- Quitamos text-light -->
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
                        <div class="col-md-3">
                            <label for="edit_medida_cm" class="form-label">Medida Base</label>
                            <select name="medida_cm" id="edit_medida_cm" class="form-select form-control-dark select2-modal" data-placeholder="Elegir medida...">
                                <option value=""></option>
                                <option value="50">50 cm (Estándar)</option>
                                <option value="45">45 cm</option>
                                <option value="55">55 cm</option>
                                <option value="60">60 cm</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_nivel_diseno" class="form-label">Nivel Diseño</label>
                            <select name="nivel_diseno" id="edit_nivel_diseno" class="form-select form-control-dark select2-modal" data-placeholder="Elegir diseño...">
                                <option value=""></option>
                                <option value="basico">Básico</option>
                                <option value="intermedio">Intermedio</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_nivel_accesorios" class="form-label">Accesorios</label>
                            <select name="nivel_accesorios" id="edit_nivel_accesorios" class="form-select form-control-dark select2-modal" data-placeholder="Elegir accesorios...">
                                <option value=""></option>
                                <option value="estandar">Estándar</option>
                                <option value="detallado">Detallado</option>
                                <option value="personalizado_pro">Personalizado Pro</option>
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
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });

        window.addEventListener('load', function() {
            let scrollPosition = sessionStorage.getItem('scrollPosition');
            if (scrollPosition !== null) {
                window.scrollTo({
                    top: parseInt(scrollPosition),
                    behavior: 'instant'
                });
                sessionStorage.removeItem('scrollPosition');
            }
        });

        // ===== LÍMITES DE CARRUSEL Y DESTACADOS =====
        // Estos totales son globales (idealmente vienen del controller vía
        // $totalEnCarrusel / $totalEnDestacados). Como los botones hacen un
        // submit normal (recarga completa), el valor siempre se refresca
        // con el dato real del servidor en cada carga de página.
        const LIMITES = {
            carrusel:  { max: {{ $LIMITE_CARRUSEL }}, actual: {{ $totalEnCarrusel }} },
            destacado: { max: {{ $LIMITE_DESTACADOS }}, actual: {{ $totalEnDestacados }} }
        };

        function mostrarAlertaLimite(tipo) {
            const mensajes = {
                carrusel: {
                    titulo: 'Límite del carrusel alcanzado',
                    texto: `Ya tienes ${LIMITES.carrusel.max} diseños en el carrusel principal. Quita uno antes de añadir otro para no saturar la landing.`
                },
                destacado: {
                    titulo: 'Límite de destacados alcanzado',
                    texto: `Ya tienes ${LIMITES.destacado.max} diseños marcados como destacados. Quita uno antes de añadir otro.`
                }
            };

            Swal.fire({
                icon: 'warning',
                title: mensajes[tipo].titulo,
                text: mensajes[tipo].texto,
                background: '#ffffff',
                color: 'var(--text-main)',
                confirmButtonColor: 'var(--accent-purple)',
                confirmButtonText: 'Entendido'
            });
        }

        // Marca visualmente (opacidad reducida) los botones que ya no se
        // pueden activar porque se llegó al límite, sin deshabilitarlos:
        // así el clic sigue disparando la alerta explicativa.
        function aplicarEstadoLimiteVisual() {
            document.querySelectorAll('.form-carrusel-toggle button').forEach(boton => {
                const activo = boton.classList.contains('is-on-info');
                const limiteAlcanzado = LIMITES.carrusel.actual >= LIMITES.carrusel.max;
                boton.classList.toggle('btn-limite-alcanzado', !activo && limiteAlcanzado);
            });

            document.querySelectorAll('.form-destacado-toggle button').forEach(boton => {
                const activo = boton.classList.contains('is-on-warning');
                const limiteAlcanzado = LIMITES.destacado.actual >= LIMITES.destacado.max;
                boton.classList.toggle('btn-limite-alcanzado', !activo && limiteAlcanzado);
            });
        }

        function bindLimitesToggle() {
            document.querySelectorAll('.form-carrusel-toggle').forEach(form => {
                form.addEventListener('submit', function (e) {
                    const boton = form.querySelector('button');
                    const estaActivo = boton.classList.contains('is-on-info');
                    if (!estaActivo && LIMITES.carrusel.actual >= LIMITES.carrusel.max) {
                        e.preventDefault();
                        mostrarAlertaLimite('carrusel');
                    }
                });
            });

            document.querySelectorAll('.form-destacado-toggle').forEach(form => {
                form.addEventListener('submit', function (e) {
                    const boton = form.querySelector('button');
                    const estaActivo = boton.classList.contains('is-on-warning');
                    if (!estaActivo && LIMITES.destacado.actual >= LIMITES.destacado.max) {
                        e.preventDefault();
                        mostrarAlertaLimite('destacado');
                    }
                });
            });

            aplicarEstadoLimiteVisual();
        }

        document.addEventListener('DOMContentLoaded', function () {

            function initSelect2() {
                $('.select2-form').select2({
                    width: '100%',
                    placeholder: function(){ $(this).data('placeholder'); },
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });

                $('.select2-filtro').select2({
                    width: '100%',
                    minimumResultsForSearch: Infinity
                });

                $('.select2-modal').select2({
                    width: '100%',
                    dropdownParent: $('#modalEditar'),
                    placeholder: function(){ $(this).data('placeholder'); },
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });
            }

            initSelect2();

            // Mensajes de éxito - ACTUALIZADO A TEMA CLARO
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: '{{ session('success') }}',
                    background: '#ffffff',
                    color: 'var(--text-main)',
                    confirmButtonColor: 'var(--accent-purple)',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            // Mensaje de error (por ejemplo, cuando el controller rechace
            // por límite de carrusel/destacados alcanzado)
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo completar la acción',
                    text: '{{ session('error') }}',
                    background: '#ffffff',
                    color: 'var(--text-main)',
                    confirmButtonColor: 'var(--accent-purple)',
                });
            @endif

            bindLimitesToggle();

            // Lógica AJAX
            $('#filtro_categoria, #filtro_estado').on('change', function () {
                ejecutarFiltroAjax();
            });

            $('#form-filtros').on('submit', function (e) {
                e.preventDefault();
                ejecutarFiltroAjax();
            });
            
            $('#btn-limpiar-filtros').on('click', function (e) {
                e.preventDefault();
                $('#filtro_buscar').val('');
                $('#filtro_categoria').val('todas').trigger('change.select2');
                $('#filtro_estado').val('todos').trigger('change.select2');
                ejecutarFiltroAjax();
            });

            $(document).on('click', '#contenedor-resultados .pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                ejecutarFiltroAjax(url);
            });

            function ejecutarFiltroAjax(urlPaginacion = null) {
                let form = $('#form-filtros');
                let urlDestino = urlPaginacion ? urlPaginacion : (form.attr('action') + '?' + form.serialize());

                $('#contenedor-resultados').css('opacity', '0.5');

                $.ajax({
                    url: urlDestino,
                    type: 'GET',
                    success: function(response) {
                        let nuevoContenido = $(response).find('#contenedor-resultados').html();
                        $('#contenedor-resultados').html(nuevoContenido).css('opacity', '1');
                        window.history.pushState(null, '', urlDestino);
                        bindSweetAlertEliminar();
                        bindLimitesToggle();
                    },
                    error: function() {
                        $('#contenedor-resultados').css('opacity', '1');
                        console.error('Error al cargar los datos.');
                    }
                });
            }

            // CONFIRMACIÓN DE ELIMINAR - ACTUALIZADO A TEMA CLARO
            function bindSweetAlertEliminar() {
                const botonesEliminar = document.querySelectorAll('.btn-eliminar');
                botonesEliminar.forEach(boton => {
                    boton.replaceWith(boton.cloneNode(true)); 
                });
                
                document.querySelectorAll('.btn-eliminar').forEach(boton => {
                    boton.addEventListener('click', function (e) {
                        e.preventDefault();
                        const formulario = this.closest('.form-eliminar');

                        Swal.fire({
                            title: '¿Eliminar este diseño?',
                            text: "Se borrará del catálogo y de la landing page. Esta acción no se puede deshacer.",
                            icon: 'warning',
                            showCancelButton: true,
                            background: '#ffffff',
                            color: 'var(--text-main)',
                            confirmButtonColor: 'var(--color-error)',
                            cancelButtonColor: '#9ca3af',
                            confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Sí, eliminar',
                            cancelButtonText: 'Cancelar',
                            customClass: { popup: 'border border-light' }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                formulario.submit();
                            }
                        });
                    });
                });
            }

            bindSweetAlertEliminar();
        });

        // Cargar datos en el modal
        function cargarDatosEditar(id, titulo, descripcion, categoria, medida, diseno, accesorios) {
            let baseUrl = "{{ url('admin/catalogo') }}";
            document.getElementById('formEditar').action = baseUrl + '/' + id;

            document.getElementById('edit_titulo').value = titulo;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_imagen').value = '';

            $('#edit_categoria').val(categoria).trigger('change');
            $('#edit_medida_cm').val(medida || '').trigger('change');
            $('#edit_nivel_diseno').val(diseno || '').trigger('change');
            $('#edit_nivel_accesorios').val(accesorios || '').trigger('change');
        }
    </script>

    <style>
        .btn-limite-alcanzado {
            opacity: 0.45;
        }
    </style>
@endpush