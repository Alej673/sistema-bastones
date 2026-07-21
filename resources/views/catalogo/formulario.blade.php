@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['resources/css/formulario.css'])
    <style>
        /* ===== Burbuja flotante de edición ===== */
        .item-card-wrapper {
            position: relative;
        }

        .btn-edit-bubble {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 5;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(27, 15, 40, 0.75);
            color: #ffffff;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35);
            transition: transform 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-edit-bubble:hover {
            background: var(--brand-purple, #9333ea);
            border-color: var(--brand-purple, #9333ea);
            transform: scale(1.08);
            box-shadow: 0 6px 14px rgba(147, 51, 234, 0.45);
            color: #fff;
        }

        .btn-edit-bubble i {
            font-size: 14px;
        }

        /* ===== Modal estilizado ===== */
        #modalEditar .modal-content {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(147, 51, 234, 0.35);
            background: linear-gradient(180deg, #1f1230 0%, #170c26 100%);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.5);
        }

        #modalEditar .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.5rem 1.75rem 1.25rem;
            align-items: flex-start;
        }

        #modalEditar .modal-header-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 12px;
            background: rgba(147, 51, 234, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-purple-light, #c084fc);
            margin-right: 0.85rem;
            font-size: 18px;
        }

        #modalEditar .modal-title-group .modal-title {
            font-size: 1.15rem;
            margin-bottom: 2px;
        }

        #modalEditar .modal-title-group small {
            color: rgba(255, 255, 255, 0.55);
        }

        #modalEditar .modal-body {
            padding: 1.75rem;
        }

        #modalEditar .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            margin-bottom: 0.4rem;
        }

        #modalEditar .form-control-dark {
            background-color: rgba(0, 0, 0, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            padding: 0.65rem 0.9rem;
        }

        #modalEditar .form-control-dark:focus {
            border-color: var(--brand-purple, #9333ea);
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.25);
            background-color: rgba(0, 0, 0, 0.35);
        }

        #modalEditar .form-text {
            font-size: 0.78rem;
        }

        #modalEditar .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.25rem 1.75rem;
        }

        #modalEditar .btn-purple {
            border-radius: 10px;
            padding: 0.55rem 1.4rem;
        }

        #modalEditar .btn-secondary {
            border-radius: 10px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        #modalEditar .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
        }
    </style>
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
                        <div class="item-card-wrapper h-100">

                            <!-- Burbuja de edición flotante -->
                            <button type="button" class="btn-edit-bubble"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar"
                                    title="Editar modelo"
                                    onclick="cargarDatosEditar({{ $item->id }}, '{{ addslashes($item->titulo) }}', '{{ addslashes($item->descripcion ?? '') }}')">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <div class="card item-card h-100 text-white" style="box-shadow: 0 5px 15px rgba(0,0,0,0.3); background-color: var(--card-bg);">

                                <!-- FIX DE IMAGEN: Usando object-fit: contain y un fondo sutil -->
                                <div style="background-color: rgba(0,0,0,0.2); border-radius: 4px 4px 0 0; padding: 10px;">
                                    <img src="{{ asset('storage/' . $item->imagen_path) }}" class="card-img-top" alt="{{ $item->titulo }}" style="height: 220px; width: 100%; object-fit: contain; opacity: 0.95;">
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold">{{ $item->titulo }}</h5>
                                    <p class="card-text small text-light opacity-75 flex-grow-1">{{ $item->descripcion ?? 'Sin descripción.' }}</p>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">

                                        <!-- Botón Ocultar/Mostrar -->
                                        <form action="{{ route('admin.catalogo.toggle', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $item->activo ? 'btn-outline-success' : 'btn-outline-secondary' }}">
                                                <i class="fa-solid {{ $item->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i> 
                                                {{ $item->activo ? 'Visible' : 'Oculto' }}
                                            </button>
                                        </form>

                                        <!-- Botón Eliminar (Modificado para SweetAlert2) -->
                                        <form action="{{ route('admin.catalogo.destroy', $item->id) }}" method="POST" class="form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar">
                                                <i class="fa-solid fa-trash-can"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
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

<!-- MODAL DE EDICIÓN -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex">
                    <div class="modal-header-icon">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div class="modal-title-group">
                        <h5 class="modal-title text-white">Editar Modelo</h5>
                        <small>Actualiza los datos de este bastón</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- El action se llenará dinámicamente con JS -->
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_titulo" class="form-label text-light">Título del Bastón</label>
                        <input type="text" name="titulo" id="edit_titulo" class="form-control form-control-dark" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label text-light">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" rows="3" class="form-control form-control-dark"></textarea>
                    </div>

                    <div class="mb-1">
                        <label for="edit_imagen" class="form-label text-light">Nueva Fotografía (Opcional)</label>
                        <input type="file" name="imagen" id="edit_imagen" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-control form-control-dark">
                        <div class="form-text text-secondary">Si no seleccionas nada, se mantendrá la foto actual.</div>
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
    <!-- Librería SweetAlert2 (por si no la tienes en tu layout base) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. MODAL DE ÉXITO (Al guardar, editar o eliminar)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: '{{ session('success') }}',
                    background: '#1b0f28', // Color oscuro de tu panel
                    color: '#ffffff',
                    confirmButtonColor: '#9333ea', // Morado de tu marca
                    timer: 3000, // Se cierra solo en 3 segundos
                    timerProgressBar: true
                });
            @endif

            // 2. MODAL DE CONFIRMACIÓN PARA ELIMINAR
            const botonesEliminar = document.querySelectorAll('.btn-eliminar');

            botonesEliminar.forEach(boton => {
                boton.addEventListener('click', function (e) {
                    e.preventDefault(); // Detiene cualquier acción por defecto
                    const formulario = this.closest('.form-eliminar'); // Busca el form padre

                    Swal.fire({
                        title: '¿Eliminar este diseño?',
                        text: "Se borrará del catálogo y de la landing page. Esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        background: '#1b0f28', // Color oscuro de tu panel
                        color: '#ffffff',
                        confirmButtonColor: '#dc3545', // Rojo para peligro
                        cancelButtonColor: '#6c757d', // Gris para cancelar
                        confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            popup: 'border border-secondary'
                        }
                    }).then((result) => {
                        // Si el usuario hace clic en "Sí, eliminar"
                        if (result.isConfirmed) {
                            formulario.submit(); // Envía el formulario al backend
                        }
                    });
                });
            });

        });
    </script>

<script>
    function cargarDatosEditar(id, titulo, descripcion) {
        // 1. Apuntar el formulario a la ruta correcta de actualización
        let baseUrl = "{{ url('admin/catalogo') }}";
        document.getElementById('formEditar').action = baseUrl + '/' + id;

        // 2. Llenar los campos con los datos actuales
        document.getElementById('edit_titulo').value = titulo;
        document.getElementById('edit_descripcion').value = descripcion;
        
        // 3. Limpiar el input de archivo por si había algo seleccionado antes
        document.getElementById('edit_imagen').value = '';
    }
</script>
@endpush