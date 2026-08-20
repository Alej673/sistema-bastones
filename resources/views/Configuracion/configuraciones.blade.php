@extends('layouts.admin')

@section('title', 'Ajustes del Sistema')

@push('css')
    <!-- Aseguramos que tus estilos neumórficos carguen aquí -->
    @vite(['resources/css/variables.css', 'resources/css/reglas.css'])
@endpush

@section('contenido')
<div class="container-fluid py-4 neumorphic-container">
    
    <div class="card card-neumorphic mb-5">
        <div class="card-header">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-sliders me-2"></i> Panel de Reglas y Configuraciones Globales</h4>
            <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">
                Modifica los precios fantasma, márgenes y enlaces del sistema. Los cambios se aplicarán en tiempo real al cotizador.
            </p>
        </div>
        
        <div class="card-body">
            <!-- Formulario Asíncrono -->
            <form id="formConfiguraciones" action="{{ route('configuraciones.update') }}" method="POST">
                @csrf
                
                <!-- Pestañas Neumórficas -->
                <ul class="nav nav-tabs neumorphic-tabs mb-4" id="configTabs" role="tablist">
                    @foreach($grupos as $nombreGrupo => $ajustes)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }} text-capitalize" 
                                    id="tab-{{ $nombreGrupo }}" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#content-{{ $nombreGrupo }}" 
                                    type="button" role="tab">
                                {{ str_replace('_', ' ', $nombreGrupo) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <!-- Contenido de las Pestañas -->
                <div class="tab-content" id="configTabsContent">
                    @foreach($grupos as $nombreGrupo => $ajustes)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                             id="content-{{ $nombreGrupo }}" role="tabpanel">
                            
                            <div class="row pt-3">
                                @foreach($ajustes as $ajuste)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="form-group">
                                            <label for="{{ $ajuste->llave }}" class="form-label fw-semibold" style="color: var(--text-main); font-size: 0.95rem;">
                                                {{ $ajuste->descripcion ?? ucfirst(str_replace('_', ' ', $ajuste->llave)) }}
                                            </label>
                                            
                                            <div class="input-group neumorphic-input">
                                                <span class="input-group-text">
                                                    @if(str_contains($ajuste->grupo, 'finanzas') || str_contains($ajuste->grupo, 'precios') || str_contains($ajuste->grupo, 'bases') || str_contains($ajuste->grupo, 'decoracion'))
                                                        <i class="fa-solid fa-dollar-sign"></i>
                                                    @elseif(str_contains($ajuste->grupo, 'contacto'))
                                                        @if(str_contains($ajuste->llave, 'whatsapp'))
                                                            <i class="fa-brands fa-whatsapp text-success"></i>
                                                        @elseif(str_contains($ajuste->llave, 'facebook'))
                                                            <i class="fa-brands fa-facebook text-primary"></i>
                                                        @elseif(str_contains($ajuste->llave, 'instagram'))
                                                            <i class="fa-brands fa-instagram text-danger"></i>
                                                        @elseif(str_contains($ajuste->llave, 'tiktok'))
                                                            <i class="fa-brands fa-tiktok text-dark"></i>
                                                        @else
                                                            <i class="fa-solid fa-phone"></i>
                                                        @endif
                                                    @elseif(str_contains($ajuste->grupo, 'sistema'))
                                                        <i class="fa-solid fa-globe"></i>
                                                    @else
                                                        <i class="fa-solid fa-tag"></i>
                                                    @endif
                                                </span>
                                                
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="{{ $ajuste->llave }}" 
                                                       name="{{ $ajuste->llave }}" 
                                                       value="{{ $ajuste->valor }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 d-flex justify-content-end">
                    <button type="submit" class="btn-neumorphic-success" id="btnGuardar">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<!-- SweetAlert2 (Por si no está global en tu panel) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formConfiguraciones');
        const btnGuardar = document.getElementById('btnGuardar');

        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Evitamos que la página se recargue

            // 1. Modal de Confirmación
            Swal.fire({
                title: '¿Confirmar actualización?',
                text: "Los nuevos valores afectarán inmediatamente las cotizaciones en curso.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--color-success)',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar cambios',
                cancelButtonText: 'Cancelar',
                background: 'var(--bg-base)',
                color: 'var(--text-main)'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    // Cambiamos el estado del botón mientras carga
                    let originalText = btnGuardar.innerHTML;
                    btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...';
                    btnGuardar.disabled = true;

                    // 2. Petición Asíncrona (AJAX / Fetch)
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest' // Le dice a Laravel que es AJAX
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Restauramos el botón
                        btnGuardar.innerHTML = originalText;
                        btnGuardar.disabled = false;

                        if (data.success) {
                            // 3. Alerta de Éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: data.message,
                                background: 'var(--bg-base)',
                                color: 'var(--text-main)',
                                confirmButtonColor: 'var(--accent-purple)'
                            });
                        } else {
                            throw new Error(data.message || 'Error desconocido');
                        }
                    })
                    .catch(error => {
                        btnGuardar.innerHTML = originalText;
                        btnGuardar.disabled = false;
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Hubo un error al guardar: ' + error.message,
                            background: 'var(--bg-base)',
                            color: 'var(--text-main)'
                        });
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection