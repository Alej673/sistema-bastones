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
                    @php
                        // 1. Extraemos un mapa plano de todos los ajustes actuales para poder cruzarlos
                        $mapaAjustes = \App\Models\Ajuste::pluck('valor', 'llave')->toArray();

                        // 2. Diccionario de Traducción 100% Dinámico
                        $traductores = [
                            'pf_lana' => [
                                'divisor' => (float)($mapaAjustes['lana_gramos_madeja'] ?? 90), 
                                'unidad' => 'g'
                            ],
                            'pf_cinta_garza' => ['divisor' => 45.72, 'unidad' => 'm'],
                            'pf_cinta_satin' => ['divisor' => 18.28, 'unidad' => 'm'],
                            'pf_cinta_gross' => ['divisor' => 22.86, 'unidad' => 'm'],
                            'pf_elastico'    => ['divisor' => 10,    'unidad' => 'm'],
                            'pf_cinchos'     => ['divisor' => 100,   'unidad' => 'u'],
                        ];
                    @endphp
        
                    @foreach($grupos as $nombreGrupo => $ajustes)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                             id="content-{{ $nombreGrupo }}" role="tabpanel">
                            
                            <div class="row pt-3">
                                @foreach($ajustes as $ajuste)
                                    @php
                                        // Verificamos si este input necesita traducción comercial
                                        $esTraducido = array_key_exists($ajuste->llave, $traductores);
                                    @endphp

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="form-group">
                                            <label for="{{ $ajuste->llave }}" class="form-label fw-semibold" style="color: var(--text-main); font-size: 0.95rem;">
                                                @if($esTraducido)
                                                    {{ trim(explode('(', $ajuste->descripcion)[0]) }} (Total x {{ $traductores[$ajuste->llave]['divisor'] }}{{ $traductores[$ajuste->llave]['unidad'] }})
                                                @else
                                                    {{ $ajuste->descripcion ?? ucfirst(str_replace('_', ' ', $ajuste->llave)) }}
                                                @endif
                                            </label>
                                            
                                            <div class="input-group neumorphic-input">
                                                <span class="input-group-text">
                                                    {{-- LÓGICA DE ÍCONOS MEJORADA --}}
                                                    @if(str_contains($ajuste->grupo, 'contacto'))
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
                                                    
                                                    {{-- Identificamos métricas de longitud, peso o cantidad (NO dinero) --}}
                                                    @elseif(str_ends_with($ajuste->llave, '_m') || str_contains($ajuste->llave, 'elastico') || $ajuste->grupo == 'recetas' || $ajuste->grupo == 'mayoreo')
                                                        @if(str_ends_with($ajuste->llave, '_m') || str_contains($ajuste->llave, 'elastico'))
                                                            <i class="fa-solid fa-ruler text-secondary"></i> {{-- Regla para metros --}}
                                                        @elseif(str_contains($ajuste->llave, 'gramos') || str_contains($ajuste->llave, 'consumo'))
                                                            <i class="fa-solid fa-weight-scale text-secondary"></i> {{-- Báscula para gramos --}}
                                                        @else
                                                            <i class="fa-solid fa-hashtag text-secondary"></i> {{-- Numeral para cantidades/umbrales --}}
                                                        @endif
                                                    
                                                    {{-- Si no es nada de lo anterior, pero pertenece a finanzas o precios, es dinero --}}
                                                    @elseif(str_contains($ajuste->grupo, 'finanzas') || str_contains($ajuste->grupo, 'precios') || str_contains($ajuste->grupo, 'bases') || str_contains($ajuste->grupo, 'decoracion'))
                                                        <i class="fa-solid fa-dollar-sign"></i>
                                                    @else
                                                        <i class="fa-solid fa-tag"></i>
                                                    @endif
                                                </span>
                                                
                                                @if($esTraducido)
                                                    @php
                                                        $datosTrad = $traductores[$ajuste->llave];
                                                        $precioComercial = round((float)$ajuste->valor * $datosTrad['divisor'], 2);
                                                    @endphp
                                                    
                                                    <!-- Envía el precio comercial directo (Backend hace la división) -->
                                                    <input type="number" step="0.01" 
                                                           class="form-control" 
                                                           id="{{ $ajuste->llave }}" 
                                                           name="{{ $ajuste->llave }}" 
                                                           value="{{ $precioComercial }}">
                                                @else
                                                    <!-- Input estándar -->
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="{{ $ajuste->llave }}" 
                                                           name="{{ $ajuste->llave }}" 
                                                           value="{{ $ajuste->valor }}">
                                                @endif
                                            </div>

                                            {{-- Texto de ayuda (Costo interno actual) solo para los traducidos --}}
                                            @if($esTraducido)
                                                <div class="mt-1 ms-2" style="font-size: 0.8rem; color: var(--text-muted);">
                                                    <i class="fa-solid fa-calculator me-1"></i> Costo interno actual: 
                                                    <strong>${{ number_format((float)$ajuste->valor, 4) }}</strong> / {{ $traductores[$ajuste->llave]['unidad'] }}
                                                </div>
                                            @endif

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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formConfiguraciones');
        const btnGuardar = document.getElementById('btnGuardar');

        form.addEventListener('submit', function (e) {
            e.preventDefault(); 

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
                    
                    let originalText = btnGuardar.innerHTML;
                    btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...';
                    btnGuardar.disabled = true;

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        btnGuardar.innerHTML = originalText;
                        btnGuardar.disabled = false;

                        if (data.success) {
                            // Actualizar la página sutilmente tras guardar para que se recalcule el "$mapaAjustes" visual
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: data.message,
                                background: 'var(--bg-base)',
                                color: 'var(--text-main)',
                                confirmButtonColor: 'var(--accent-purple)'
                            }).then(() => {
                                window.location.reload(); 
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