@extends('layouts.admin')

@section('titulo', 'Ajustes del Sistema')

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
                <div class="tabs-scroll-wrapper mb-4">
                    <ul class="nav nav-tabs neumorphic-tabs" id="configTabs" role="tablist">
                        @foreach($grupos as $nombreGrupo => $ajustes)
                            @continue($nombreGrupo === 'sistema')
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
                </div>

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
                        @continue($nombreGrupo === 'sistema')
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                             id="content-{{ $nombreGrupo }}" role="tabpanel">
                            
                            <div class="row pt-3">
                                @foreach($ajustes as $ajuste)
                                    @php
                                        // Verificamos si este input necesita traducción comercial
                                        $esTraducido = array_key_exists($ajuste->llave, $traductores);

                                        // Verificamos si es un ajuste de encendido/apagado (grupo sistema, valor 0 o 1)
                                        $esBooleano = $ajuste->grupo === 'sistema' && in_array($ajuste->valor, ['0', '1'], true);

                                        // Verificamos si la descripción trae un porcentaje escrito a mano, ej: "...(60%)"
                                        // para reemplazarlo por el valor real guardado, en vez de mostrar el texto congelado.
                                        $tienePorcentajeFijo = (bool) preg_match('/\(\s*\d+(\.\d+)?\s*%\s*\)/', $ajuste->descripcion ?? '');

                                        // Identificamos si este input es el del teléfono de WhatsApp,
                                        // para engancharle el auto-formateo visual desde el JS de abajo.
                                        $esWhatsapp = str_contains($ajuste->llave, 'whatsapp');
                                    @endphp

                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="form-group">
                                            <label for="{{ $ajuste->llave }}" class="form-label fw-semibold" style="color: var(--text-main); font-size: 0.95rem;">
                                                @if($esTraducido)
                                                    {{ trim(explode('(', $ajuste->descripcion)[0]) }} (Total x {{ $traductores[$ajuste->llave]['divisor'] }}{{ $traductores[$ajuste->llave]['unidad'] }})
                                                @elseif($tienePorcentajeFijo)
                                                    @php
                                                        $descripcionSinPorcentaje = trim(preg_replace('/\(\s*\d+(\.\d+)?\s*%\s*\)/', '', $ajuste->descripcion));
                                                        // El valor se guarda como decimal (0.60), lo pasamos a porcentaje (60) solo para mostrarlo
                                                        $valorPorcentaje = rtrim(rtrim(number_format((float)$ajuste->valor * 100, 2), '0'), '.');
                                                    @endphp
                                                    {{ $descripcionSinPorcentaje }} ({{ $valorPorcentaje }}%)
                                                @else
                                                    {{ $ajuste->descripcion ?? ucfirst(str_replace('_', ' ', $ajuste->llave)) }}
                                                @endif
                                            </label>
                                            
                                            @if($esBooleano)
                                                <!-- Switch neumórfico Activado/Desactivado -->
                                                <div class="form-check form-switch neumorphic-switch">
                                                    <input type="hidden" name="{{ $ajuste->llave }}" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           id="{{ $ajuste->llave }}"
                                                           name="{{ $ajuste->llave }}"
                                                           value="1"
                                                           {{ $ajuste->valor === '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $ajuste->llave }}">
                                                        {{ $ajuste->valor === '1' ? 'Activado' : 'Desactivado' }}
                                                    </label>
                                                </div>
                                            @else
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
                                                    <!-- Input estándar (agregamos data-whatsapp-format en el campo del teléfono) -->
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="{{ $ajuste->llave }}" 
                                                           name="{{ $ajuste->llave }}" 
                                                           value="{{ $ajuste->valor }}"
                                                           @if($esWhatsapp) data-whatsapp-format="1" inputmode="numeric" maxlength="12" @endif>
                                                @endif
                                            </div>
                                            @endif

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

        // Actualiza el texto del switch (Activado/Desactivado) al cambiarlo
        document.querySelectorAll('.neumorphic-switch .form-check-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const label = this.closest('.neumorphic-switch').querySelector('.form-check-label');
                label.textContent = this.checked ? 'Activado' : 'Desactivado';
            });
        });

        // ============================================================
        // AUTO-FORMATEO DEL NÚMERO DE WHATSAPP
        // Aplica a cualquier input marcado con data-whatsapp-format="1"
        // (hoy solo "contacto_whatsapp", pero queda listo si en el futuro
        // agregas otro campo de teléfono en el grupo "contacto").
        // ============================================================
        document.querySelectorAll('[data-whatsapp-format="1"]').forEach(function (inputWhatsapp) {

            // Mientras el usuario escribe: mostramos "98 432 2541"
            inputWhatsapp.addEventListener('input', function (e) {
                // 1. Nos quedamos solo con dígitos
                let digitos = e.target.value.replace(/\D/g, '');

                // 2. Si viene con el '0' inicial típico de Ecuador, lo quitamos para el formateo visual
                if (digitos.startsWith('0')) {
                    digitos = digitos.substring(1);
                }

                // 3. Límite razonable (9 dígitos sin el 0, estándar de celular EC)
                digitos = digitos.substring(0, 9);

                // 4. Agrupamos visualmente: XX XXX XXXX
                let formateado = digitos;
                if (digitos.length > 2 && digitos.length <= 5) {
                    formateado = `${digitos.slice(0, 2)} ${digitos.slice(2)}`;
                } else if (digitos.length > 5) {
                    formateado = `${digitos.slice(0, 2)} ${digitos.slice(2, 5)} ${digitos.slice(5)}`;
                }

                e.target.value = formateado;
            });

            // Al enviar el formulario: reconstruimos el formato limpio "593XXXXXXXXX"
            // que espera wa.me, para que se guarde en BD ya listo para usar.
            inputWhatsapp.closest('form').addEventListener('submit', function () {
                const soloDigitos = inputWhatsapp.value.replace(/\D/g, '');
                if (soloDigitos) {
                    inputWhatsapp.value = '593' + soloDigitos;
                }
            });
        });

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