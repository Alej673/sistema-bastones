@extends('layouts.admin')

@section('titulo', 'Ajustes del Sistema')

@push('css')
    @vite(['resources/css/variables.css', 'resources/css/reglas.css'])
    <style>
        /* ============ LAYOUT: contenido + sidebar derecho ============ */
        .config-layout {
            display: flex;
            align-items: flex-start;
            gap: 1.75rem;
        }
        .config-main { flex: 1 1 auto; min-width: 0; }

        .config-sidebar {
            flex: 0 0 270px;
            position: sticky;
            top: 1rem;
            border-radius: 18px;
            padding: 1.1rem .9rem;
            background: var(--bg-base);
            box-shadow: 6px 6px 14px rgba(0, 0, 0, .12), -6px -6px 14px rgba(255, 255, 255, .08);
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }
        .config-sidebar .sidebar-titulo {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 .6rem .6rem;
        }
        .config-sidebar .nav { flex-direction: column; flex-wrap: nowrap; gap: .45rem; border: 0; }
        .config-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            width: 100%;
            text-align: left;
            border: 0;
            border-radius: 12px;
            padding: .65rem .8rem;
            font-weight: 600;
            font-size: .92rem;
            color: var(--text-muted);
            background: transparent;
            transition: all .2s ease;
        }
        .config-sidebar .nav-link i { width: 1.25rem; text-align: center; }
        .config-sidebar .nav-link:hover { color: var(--text-main); }
        .config-sidebar .nav-link.active {
            color: var(--accent-purple, #7c3aed);
            background: var(--bg-base);
            box-shadow: inset 3px 3px 7px rgba(0, 0, 0, .13), inset -3px -3px 7px rgba(255, 255, 255, .08);
        }

        /* Barra superior móvil + backdrop (solo < lg) */
        .config-mobile-bar { display: none; }
        .config-backdrop { display: none; }

        @media (max-width: 991.98px) {
            .config-layout { display: block; }

            .config-mobile-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                position: sticky;
                top: 0;
                z-index: 20;
                margin-bottom: 1.25rem;
                padding: .6rem .8rem;
                border-radius: 14px;
                background: var(--bg-base);
                box-shadow: 4px 4px 10px rgba(0, 0, 0, .12), -4px -4px 10px rgba(255, 255, 255, .08);
            }
            .config-mobile-bar .titulo-actual {
                font-weight: 700;
                color: var(--text-main);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .config-mobile-bar button {
                border: 0;
                border-radius: 10px;
                padding: .5rem .85rem;
                font-weight: 600;
                font-size: .85rem;
                white-space: nowrap;
                color: var(--accent-purple, #7c3aed);
                background: var(--bg-base);
                box-shadow: 3px 3px 7px rgba(0, 0, 0, .12), -3px -3px 7px rgba(255, 255, 255, .08);
            }

            .config-sidebar {
                position: fixed;
                top: 0;
                right: 0;
                height: 100%;
                max-height: none;
                width: min(85vw, 320px);
                border-radius: 18px 0 0 18px;
                z-index: 1060;
                transform: translateX(105%);
                transition: transform .28s ease;
                padding-top: 1.25rem;
            }
            .config-sidebar.abierto { transform: translateX(0); }

            .config-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 1055;
                background: rgba(30, 20, 60, .45);
                opacity: 0;
                pointer-events: none;
                transition: opacity .28s ease;
            }
            .config-backdrop.visible { opacity: 1; pointer-events: auto; }
        }

        /* ============ ENCABEZADO DE CADA PANEL ============ */
        .pane-header {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
        }
        .pane-header i { color: var(--accent-purple, #7c3aed); }

        .subseccion-titulo {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid rgba(128, 128, 128, .25);
            padding-bottom: .35rem;
            margin: 0 0 1.1rem;
        }
        .subseccion-bloque { margin-bottom: 2rem; }

        /* ============ CUADRÍCULA NIVELADA DE CAMPOS ============ */
        .campos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.4rem 1.5rem;
            align-items: start;
        }
        .campos-grid .form-label {
            display: flex;
            align-items: flex-end;
            margin-bottom: .5rem;
        }
        @media (min-width: 768px) {
            /* Reserva 2 líneas de etiqueta para que todos los inputs queden a la misma altura */
            .campos-grid .form-label { min-height: 2.7em; }
        }
        .campo-ayuda { font-size: .78rem; color: var(--text-muted); margin: .35rem 0 0 .5rem; }

        .panel-referencia {
            border-radius: 14px;
            padding: 1rem 1.25rem;
            background: var(--bg-base);
            box-shadow: inset 4px 4px 8px rgba(0, 0, 0, .12), inset -4px -4px 8px rgba(255, 255, 255, .06);
            color: var(--text-main);
            font-size: .9rem;
        }
        .panel-referencia .fila { display: flex; justify-content: space-between; gap: 1rem; padding: .2rem 0; }
        .panel-referencia .valor { font-weight: 700; }

        .barra-guardar { display: flex; justify-content: flex-end; margin-top: 2rem; }
        @media (max-width: 575.98px) {
            .barra-guardar .btn-neumorphic-success { width: 100%; justify-content: center; }
        }
    </style>
@endpush

@section('contenido')
<div class="container-fluid py-4 neumorphic-container">

    <div class="card card-neumorphic mb-5">
        <div class="card-header">
            <h4 class="mb-0 fw-bold"><i class="fa-solid fa-sliders me-2"></i> Panel de Reglas y Configuraciones Globales</h4>
            <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">
                Modifica los precios fantasma, presentaciones de insumos, recetas, márgenes y enlaces del sistema. Los cambios se aplicarán en tiempo real al cotizador y al Kardex.
            </p>
        </div>

        <div class="card-body">
            @php
                $titulosPestanas = [
                    'finanzas'         => ['titulo' => 'Finanzas y Margen',    'icono' => 'fa-chart-line'],
                    'precios_fantasma' => ['titulo' => 'Precios Fantasma',     'icono' => 'fa-ghost'],
                    'insumos'          => ['titulo' => 'Presentación Insumos', 'icono' => 'fa-boxes-stacked'],
                    'recetas'          => ['titulo' => 'Recetas y Consumos',   'icono' => 'fa-flask'],
                    'mayoreo'          => ['titulo' => 'Umbrales Mayoreo',     'icono' => 'fa-layer-group'],
                    'bases'            => ['titulo' => 'Precios de Bases',     'icono' => 'fa-wand-magic-sparkles'],
                    'decoracion'       => ['titulo' => 'Decoración y Extras',  'icono' => 'fa-ribbon'],
                    'contacto'         => ['titulo' => 'Redes y Contacto',     'icono' => 'fa-address-book'],
                    'sistema'          => ['titulo' => 'Estado del Taller',    'icono' => 'fa-power-off'],
                ];

                $llavesEnteras = [
                    'receta_cinchos',
                    'cortina_unidades_paquete',
                    'insumo_paquete_cinchos_u',
                    'base_umbral_mayoreo',
                    'cortina_fiesta_umbral',
                ];

                // El orden de este arreglo define también el orden de las sub-secciones y campos
                $subsecciones = [
                    // Recetas
                    'lana_gramos_madeja'       => 'Lana',
                    'lana_consumo_normal'      => 'Lana',
                    'lana_consumo_grande'      => 'Lana',
                    'receta_cinchos'           => 'Bastón: accesorios',
                    'receta_elastico'          => 'Bastón: accesorios',
                    'cortina_lana_gramos'      => 'Cortina de fiesta',
                    'cortina_unidades_paquete' => 'Cortina de fiesta',
                    // Precios fantasma
                    'pf_lana'          => 'Materiales (precio comercial)',
                    'pf_cinta_garza'   => 'Materiales (precio comercial)',
                    'pf_cinta_satin'   => 'Materiales (precio comercial)',
                    'pf_cinta_gross'   => 'Materiales (precio comercial)',
                    'pf_elastico'      => 'Materiales (precio comercial)',
                    'pf_cinchos'       => 'Materiales (precio comercial)',
                    'pf_cortina_menor' => 'Cortina de fiesta (precio de venta)',
                    'pf_cortina_mayor' => 'Cortina de fiesta (precio de venta)',
                    // Bases
                    'base_dorado_normal'  => 'Base dorada',
                    'base_dorado_mayoreo' => 'Base dorada',
                    'base_plata_normal'   => 'Base plata',
                    'base_plata_mayoreo'  => 'Base plata',
                    // Decoración
                    'deco_lazo_simple_m'   => 'Consumo de cinta',
                    'deco_flor_m'          => 'Consumo de cinta',
                    'deco_lazo_nombre_m'   => 'Consumo de cinta',
                    'deco_lazo_nombre_mo'  => 'Mano de obra y apliques',
                    'deco_apliques_precio' => 'Mano de obra y apliques',
                    'deco_diseno_basico'     => 'Diseños personalizados',
                    'deco_diseno_intermedio' => 'Diseños personalizados',
                    'deco_diseno_premium'    => 'Diseños personalizados',
                ];

                $unidades = [
                    'receta_cinchos'           => 'u / bastón',
                    'receta_elastico'          => 'm / bastón',
                    'lana_consumo_grande'      => 'g / bastón',
                    'lana_consumo_normal'      => 'g / bastón',
                    'lana_gramos_madeja'       => 'g / madeja',
                    'cortina_lana_gramos'      => 'g / cortina',
                    'cortina_unidades_paquete' => 'cortinas / paquete',
                    'base_umbral_mayoreo'      => 'unidades',
                    'cortina_fiesta_umbral'    => 'unidades',
                    'deco_lazo_simple_m'       => 'm',
                    'deco_flor_m'              => 'm',
                    'deco_lazo_nombre_m'       => 'm',
                    'insumo_rollo_garza_m'     => 'm / rollo',
                    'insumo_rollo_satin_m'     => 'm / rollo',
                    'insumo_rollo_gross_m'     => 'm / rollo',
                    'insumo_paquete_elastico_m'=> 'm / paquete',
                    'insumo_paquete_cinchos_u' => 'u / paquete',
                ];

                $ayudas = [
                    'lana_gramos_madeja'       => 'También define el divisor del precio fantasma de la lana.',
                    'lana_consumo_grande'      => 'Se usa para tamaños de 55 a 60 cm.',
                    'cortina_unidades_paquete' => 'Cuántas cortinas salen de un paquete.',
                    'receta_cinchos'           => 'Cinchos que consume cada bastón.',
                    'receta_elastico'          => 'Metros de elástico que consume cada bastón.',
                ];

                // Pestañas en el orden definido arriba (lo que no esté en la lista va al final)
                $ordenGrupos = array_flip(array_keys($titulosPestanas));
                $grupos = $grupos->sortBy(fn ($a, $k) => $ordenGrupos[$k] ?? 999);
                $ordenLlaves = array_flip(array_keys($subsecciones));
            @endphp

            <form id="formConfiguraciones" action="{{ route('configuraciones.update') }}" method="POST">
                @csrf

                <div class="config-layout">

                    {{-- ===================== CONTENIDO ===================== --}}
                    <div class="config-main">

                        {{-- Barra móvil: sección actual + botón para abrir el cajón --}}
                        <div class="config-mobile-bar">
                            <span class="titulo-actual" id="tituloSeccionActual">
                                @php $primero = $grupos->keys()->first(); @endphp
                                {{ $titulosPestanas[$primero]['titulo'] ?? ucfirst(str_replace('_', ' ', (string) $primero)) }}
                            </span>
                            <button type="button" id="btnAbrirSecciones" aria-controls="sidebarSecciones" aria-expanded="false">
                                <i class="fa-solid fa-bars-staggered me-1"></i> Secciones
                            </button>
                        </div>

                        <div class="tab-content" id="configTabsContent">
                            @foreach($grupos as $nombreGrupo => $ajustes)
                                @php
                                    $metaTab = $titulosPestanas[$nombreGrupo] ?? [
                                        'titulo' => ucfirst(str_replace('_', ' ', $nombreGrupo)),
                                        'icono'  => 'fa-sliders'
                                    ];
                                    $ajustesOrdenados = $ajustes->sortBy(fn ($a) => $ordenLlaves[$a->llave] ?? 999)->values();
                                    $porSubseccion = $ajustesOrdenados->groupBy(fn ($a) => $subsecciones[$a->llave] ?? 'General');
                                    $mostrarSubtitulos = $porSubseccion->count() > 1;
                                @endphp

                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                     id="content-{{ $nombreGrupo }}" role="tabpanel">

                                    <div class="pane-header">
                                        <i class="fa-solid {{ $metaTab['icono'] }}"></i> {{ $metaTab['titulo'] }}
                                    </div>

                                    @foreach($porSubseccion as $nombreSub => $ajustesSub)
                                        <div class="subseccion-bloque">
                                            @if($mostrarSubtitulos)
                                                <div class="subseccion-titulo">{{ $nombreSub }}</div>
                                            @endif

                                            <div class="campos-grid">
                                                @foreach($ajustesSub as $ajuste)
                                                    @php
                                                        $esTraducido = array_key_exists($ajuste->llave, $traductores);

                                                        $esBooleano = $ajuste->grupo === 'sistema' && in_array($ajuste->valor, ['0', '1', 'abierto', 'cerrado'], true);
                                                        $usaTextoAbierto = in_array($ajuste->valor, ['abierto', 'cerrado'], true);
                                                        $estaActivo = in_array($ajuste->valor, ['1', 'abierto'], true);

                                                        $tienePorcentajeFijo = (bool) preg_match('/\(\s*\d+(\.\d+)?\s*%\s*\)/', $ajuste->descripcion ?? '');

                                                        $esContacto = str_contains($ajuste->grupo, 'contacto');
                                                        $esWhatsapp = str_contains($ajuste->llave, 'whatsapp');
                                                        $esRedSocial = $esContacto && !$esWhatsapp;

                                                        $esEntero = in_array($ajuste->llave, $llavesEnteras, true);
                                                        $unidadTexto = $unidades[$ajuste->llave] ?? null;
                                                    @endphp

                                                    <div class="form-group">
                                                        {{-- ETIQUETA --}}
                                                        <label for="{{ $ajuste->llave }}" class="form-label fw-semibold" style="color: var(--text-main); font-size: 0.95rem;">
                                                            <span>
                                                            @if($esTraducido)
                                                                {{ trim(explode('(', $ajuste->descripcion)[0]) }}
                                                                <span class="text-muted fw-normal" id="label-divisor-{{ $ajuste->llave }}">
                                                                    (Total x {{ floatval($traductores[$ajuste->llave]['divisor']) }}{{ $traductores[$ajuste->llave]['unidad'] }})
                                                                </span>
                                                            @elseif($tienePorcentajeFijo)
                                                                @php
                                                                    $descripcionSinPorcentaje = trim(preg_replace('/\(\s*\d+(\.\d+)?\s*%\s*\)/', '', $ajuste->descripcion));
                                                                    $valorPorcentaje = rtrim(rtrim(number_format((float)$ajuste->valor * 100, 2), '0'), '.');
                                                                @endphp
                                                                {{ $descripcionSinPorcentaje }} <span id="label-porcentaje-{{ $ajuste->llave }}">({{ $valorPorcentaje }}%)</span>
                                                            @else
                                                                {{ $ajuste->descripcion ?? ucfirst(str_replace('_', ' ', $ajuste->llave)) }}
                                                            @endif
                                                            </span>
                                                        </label>

                                                        {{-- CONTROL --}}
                                                        @if($esBooleano)
                                                            <div class="form-check form-switch neumorphic-switch">
                                                                <input type="hidden" name="{{ $ajuste->llave }}" value="{{ $usaTextoAbierto ? 'cerrado' : '0' }}">
                                                                <input class="form-check-input" type="checkbox" role="switch"
                                                                       id="{{ $ajuste->llave }}"
                                                                       name="{{ $ajuste->llave }}"
                                                                       value="{{ $usaTextoAbierto ? 'abierto' : '1' }}"
                                                                       data-texto-on="{{ $usaTextoAbierto ? 'Abierto (Recibiendo pedidos)' : 'Activado' }}"
                                                                       data-texto-off="{{ $usaTextoAbierto ? 'Cerrado (Temporalmente)' : 'Desactivado' }}"
                                                                       {{ $estaActivo ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="{{ $ajuste->llave }}">
                                                                    {{ $estaActivo ? ($usaTextoAbierto ? 'Abierto (Recibiendo pedidos)' : 'Activado') : ($usaTextoAbierto ? 'Cerrado (Temporalmente)' : 'Desactivado') }}
                                                                </label>
                                                            </div>
                                                        @else
                                                            <div class="input-group neumorphic-input">
                                                                <span class="input-group-text">
                                                                    @if($esContacto)
                                                                        @if($esWhatsapp)
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
                                                                    @elseif($ajuste->llave === 'margen_ganancia')
                                                                        <i class="fa-solid fa-percent text-secondary"></i>
                                                                    @elseif(!$esTraducido && (
                                                                        str_ends_with($ajuste->llave, '_m') ||
                                                                        str_ends_with($ajuste->llave, '_u') ||
                                                                        in_array($ajuste->grupo, ['recetas', 'insumos', 'mayoreo'])
                                                                    ))
                                                                        @if(str_ends_with($ajuste->llave, '_m') || str_contains($ajuste->llave, 'elastico'))
                                                                            <i class="fa-solid fa-ruler text-secondary"></i>
                                                                        @elseif(str_contains($ajuste->llave, 'gramos') || str_contains($ajuste->llave, 'consumo'))
                                                                            <i class="fa-solid fa-weight-scale text-secondary"></i>
                                                                        @else
                                                                            <i class="fa-solid fa-hashtag text-secondary"></i>
                                                                        @endif
                                                                    @elseif(in_array($ajuste->grupo, ['finanzas', 'precios_fantasma', 'bases', 'decoracion']))
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
                                                                    <input type="number" step="0.01" min="0"
                                                                           class="form-control input-precio-traducido"
                                                                           id="{{ $ajuste->llave }}"
                                                                           name="{{ $ajuste->llave }}"
                                                                           data-divisor="{{ $datosTrad['divisor'] }}"
                                                                           data-unidad="{{ $datosTrad['unidad'] }}"
                                                                           value="{{ number_format($precioComercial, 2, '.', '') }}"
                                                                           required>
                                                                @elseif($esContacto)
                                                                    <input type="{{ $esRedSocial ? 'url' : 'text' }}"
                                                                           class="form-control"
                                                                           id="{{ $ajuste->llave }}"
                                                                           name="{{ $ajuste->llave }}"
                                                                           value="{{ $ajuste->valor }}"
                                                                           placeholder="{{ $esRedSocial ? 'https://... (Opcional)' : 'Ej. 98 432 2541' }}"
                                                                           @if($esWhatsapp) data-whatsapp-format="1" inputmode="numeric" maxlength="15" required @endif>
                                                                @else
                                                                    <input type="number"
                                                                           step="{{ $esEntero ? '1' : '0.01' }}"
                                                                           min="0"
                                                                           class="form-control"
                                                                           id="{{ $ajuste->llave }}"
                                                                           name="{{ $ajuste->llave }}"
                                                                           value="{{ $esEntero ? intval($ajuste->valor) : floatval($ajuste->valor) }}"
                                                                           required>
                                                                @endif

                                                                @if($unidadTexto && !$esTraducido)
                                                                    <span class="input-group-text" style="font-size: .8rem;">{{ $unidadTexto }}</span>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @if($esTraducido)
                                                            <div class="campo-ayuda">
                                                                <i class="fa-solid fa-calculator me-1"></i> Costo interno por unidad mínima:
                                                                <strong id="preview-interno-{{ $ajuste->llave }}">${{ number_format((float)$ajuste->valor, 4) }}</strong> / {{ $traductores[$ajuste->llave]['unidad'] }}
                                                            </div>
                                                        @elseif(isset($ayudas[$ajuste->llave]))
                                                            <div class="campo-ayuda">
                                                                <i class="fa-solid fa-circle-info me-1"></i>{{ $ayudas[$ajuste->llave] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- PANEL DE REFERENCIA SOLO EN RECETAS --}}
                                    @if($nombreGrupo === 'recetas')
                                        <div class="subseccion-titulo">Costo de referencia (en vivo)</div>
                                        <div class="panel-referencia" id="panelReferenciaRecetas">
                                            <div class="text-muted mb-2" style="font-size:.8rem;">
                                                Solo lana, cinchos y elástico (sin cintas ni decoración), calculado con los valores que tienes en pantalla.
                                            </div>
                                            <div class="fila"><span>Bastón normal · costo materiales</span><span class="valor" id="ref-baston-normal">$0.00</span></div>
                                            <div class="fila"><span>Bastón grande · costo materiales</span><span class="valor" id="ref-baston-grande">$0.00</span></div>
                                            <div class="fila"><span>Cortina de fiesta · costo por unidad</span><span class="valor" id="ref-cortina-unidad">$0.00</span></div>
                                            <div class="fila"><span>Cortina de fiesta · costo por paquete</span><span class="valor" id="ref-cortina-paquete">$0.00</span></div>
                                            <hr class="my-2">
                                            <div class="fila"><span>Bastón normal · con margen</span><span class="valor" id="ref-baston-normal-m">$0.00</span></div>
                                            <div class="fila"><span>Bastón grande · con margen</span><span class="valor" id="ref-baston-grande-m">$0.00</span></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="barra-guardar">
                            <button type="submit" class="btn-neumorphic-success" id="btnGuardar">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>

                    {{-- ===================== SIDEBAR DERECHO ===================== --}}
                    <aside class="config-sidebar" id="sidebarSecciones" aria-label="Secciones de configuración">
                        <div class="sidebar-titulo">Secciones</div>
                        <ul class="nav neumorphic-tabs" id="configTabs" role="tablist">
                            @foreach($grupos as $nombreGrupo => $ajustes)
                                @php
                                    $metaTab = $titulosPestanas[$nombreGrupo] ?? [
                                        'titulo' => ucfirst(str_replace('_', ' ', $nombreGrupo)),
                                        'icono'  => 'fa-sliders'
                                    ];
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="tab-{{ $nombreGrupo }}"
                                            data-bs-toggle="tab"
                                            data-bs-target="#content-{{ $nombreGrupo }}"
                                            data-titulo="{{ $metaTab['titulo'] }}"
                                            type="button" role="tab">
                                        <i class="fa-solid {{ $metaTab['icono'] }}"></i>
                                        <span>{{ $metaTab['titulo'] }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </aside>

                    <div class="config-backdrop" id="backdropSecciones"></div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formConfiguraciones');
        const btnGuardar = document.getElementById('btnGuardar');

        // 0. Sidebar: cajón en móvil / lateral fijo en escritorio
        const sidebar = document.getElementById('sidebarSecciones');
        const backdrop = document.getElementById('backdropSecciones');
        const btnAbrir = document.getElementById('btnAbrirSecciones');
        const tituloActual = document.getElementById('tituloSeccionActual');

        function abrirSidebar() {
            sidebar.classList.add('abierto');
            backdrop.classList.add('visible');
            btnAbrir.setAttribute('aria-expanded', 'true');
        }
        function cerrarSidebar() {
            sidebar.classList.remove('abierto');
            backdrop.classList.remove('visible');
            btnAbrir.setAttribute('aria-expanded', 'false');
        }
        btnAbrir.addEventListener('click', abrirSidebar);
        backdrop.addEventListener('click', cerrarSidebar);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarSidebar(); });

        // Al cambiar de sección: actualizar el título móvil, cerrar el cajón y subir al inicio
        document.querySelectorAll('#configTabs [data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function () {
                if (tituloActual) tituloActual.textContent = this.dataset.titulo || '';
                cerrarSidebar();
                if (window.innerWidth < 992) {
                    document.querySelector('.config-main').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // 1. Switches
        document.querySelectorAll('.neumorphic-switch .form-check-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const label = this.closest('.neumorphic-switch').querySelector('.form-check-label');
                label.textContent = this.checked
                    ? (this.dataset.textoOn || 'Activado')
                    : (this.dataset.textoOff || 'Desactivado');
            });
        });

        // 2. Vínculos Presentación Insumos / Recetas -> Precios Fantasma
        const mapaVinculos = {
            'lana_gramos_madeja':        { target: 'pf_lana',        unidad: 'g' },
            'insumo_rollo_garza_m':      { target: 'pf_cinta_garza', unidad: 'm' },
            'insumo_rollo_satin_m':      { target: 'pf_cinta_satin', unidad: 'm' },
            'insumo_rollo_gross_m':      { target: 'pf_cinta_gross', unidad: 'm' },
            'insumo_paquete_elastico_m': { target: 'pf_elastico',    unidad: 'm' },
            'insumo_paquete_cinchos_u':  { target: 'pf_cinchos',     unidad: 'u' }
        };

        function num(id) {
            const el = document.getElementById(id);
            return el ? (parseFloat(el.value) || 0) : 0;
        }

        function costoInterno(idPrecio) {
            const el = document.getElementById(idPrecio);
            if (!el) return 0;
            const divisor = parseFloat(el.dataset.divisor) || 1;
            return divisor > 0 ? (parseFloat(el.value) || 0) / divisor : 0;
        }

        const money = (n) => '$' + n.toFixed(2);
        const setTxt = (id, txt) => { const e = document.getElementById(id); if (e) e.textContent = txt; };

        // 3. Panel de referencia de recetas
        function recalcularReferencia() {
            if (!document.getElementById('panelReferenciaRecetas')) return;

            const lanaG  = costoInterno('pf_lana');
            const cincho = costoInterno('pf_cinchos');
            const elast  = costoInterno('pf_elastico');
            const accesorios = num('receta_cinchos') * cincho + num('receta_elastico') * elast;

            const normal = num('lana_consumo_normal') * lanaG + accesorios;
            const grande = num('lana_consumo_grande') * lanaG + accesorios;
            const cortinaU = num('cortina_lana_gramos') * lanaG;
            const cortinaP = cortinaU * num('cortina_unidades_paquete');
            const margen = num('margen_ganancia');

            setTxt('ref-baston-normal', money(normal));
            setTxt('ref-baston-grande', money(grande));
            setTxt('ref-cortina-unidad', money(cortinaU));
            setTxt('ref-cortina-paquete', money(cortinaP));
            setTxt('ref-baston-normal-m', money(normal * (1 + margen)));
            setTxt('ref-baston-grande-m', money(grande * (1 + margen)));
        }

        function recalcularPreviewInterno(inputPrecio) {
            const divisor = parseFloat(inputPrecio.dataset.divisor) || 1;
            const precioComercial = parseFloat(inputPrecio.value) || 0;
            const costo = divisor > 0 ? (precioComercial / divisor) : 0;
            const previewEl = document.getElementById('preview-interno-' + inputPrecio.id);
            if (previewEl) previewEl.textContent = '$' + costo.toFixed(4);
            recalcularReferencia();
        }

        document.querySelectorAll('.input-precio-traducido').forEach(function (inputPrecio) {
            inputPrecio.addEventListener('input', function () { recalcularPreviewInterno(this); });
        });

        Object.keys(mapaVinculos).forEach(function (llaveInsumo) {
            const inputInsumo = document.getElementById(llaveInsumo);
            const info = mapaVinculos[llaveInsumo];
            const inputPrecio = document.getElementById(info.target);
            const labelDivisor = document.getElementById('label-divisor-' + info.target);

            if (inputInsumo && inputPrecio) {
                inputInsumo.addEventListener('input', function () {
                    const nuevoDivisor = parseFloat(this.value) || 1;
                    inputPrecio.dataset.divisor = nuevoDivisor;
                    if (labelDivisor) labelDivisor.textContent = `(Total x ${nuevoDivisor}${info.unidad})`;
                    recalcularPreviewInterno(inputPrecio);
                });
            }
        });

        ['receta_cinchos', 'receta_elastico', 'lana_consumo_normal', 'lana_consumo_grande',
         'cortina_lana_gramos', 'cortina_unidades_paquete', 'margen_ganancia'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', recalcularReferencia);
        });
        recalcularReferencia();

        // 4. Porcentaje de margen en vivo
        const inputMargen = document.getElementById('margen_ganancia');
        const labelMargen = document.getElementById('label-porcentaje-margen_ganancia');
        if (inputMargen && labelMargen) {
            inputMargen.addEventListener('input', function () {
                const porc = ((parseFloat(this.value) || 0) * 100).toFixed(2).replace(/\.?0+$/, '');
                labelMargen.textContent = `(${porc}%)`;
            });
        }

        // 5. Formato WhatsApp
        function formatearTelefonoVisual(valor) {
            let digitos = valor.replace(/\D/g, '');
            if (digitos.startsWith('593')) digitos = digitos.substring(3);
            if (digitos.startsWith('0')) digitos = digitos.substring(1);
            digitos = digitos.substring(0, 9);

            if (digitos.length > 2 && digitos.length <= 5) {
                return `${digitos.slice(0, 2)} ${digitos.slice(2)}`;
            } else if (digitos.length > 5) {
                return `${digitos.slice(0, 2)} ${digitos.slice(2, 5)} ${digitos.slice(5)}`;
            }
            return digitos;
        }

        document.querySelectorAll('[data-whatsapp-format="1"]').forEach(function (inputWhatsapp) {
            inputWhatsapp.value = formatearTelefonoVisual(inputWhatsapp.value);
            inputWhatsapp.addEventListener('input', function (e) {
                e.target.value = formatearTelefonoVisual(e.target.value);
            });
        });

        // 6. Envío asíncrono
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Confirmar actualización?',
                text: "Los nuevos valores afectarán inmediatamente las cotizaciones y el Kardex.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--color-success)',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar cambios',
                cancelButtonText: 'Cancelar',
                background: 'var(--bg-base)',
                color: 'var(--text-main)'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const originalText = btnGuardar.innerHTML;
                btnGuardar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Guardando...';
                btnGuardar.disabled = true;

                const formData = new FormData(form);
                document.querySelectorAll('[data-whatsapp-format="1"]').forEach(function (inputWhatsapp) {
                    const soloDigitos = inputWhatsapp.value.replace(/\D/g, '');
                    if (soloDigitos) formData.set(inputWhatsapp.name, '+593' + soloDigitos);
                });

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    btnGuardar.innerHTML = originalText;
                    btnGuardar.disabled = false;

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: data.message,
                            background: 'var(--bg-base)',
                            color: 'var(--text-main)',
                            confirmButtonColor: 'var(--accent-purple)'
                        }).then(() => window.location.reload());
                    } else {
                        throw new Error(data.message || 'Error desconocido');
                    }
                })
                .catch(error => {
                    btnGuardar.innerHTML = originalText;
                    btnGuardar.disabled = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Validación',
                        text: error.message,
                        background: 'var(--bg-base)',
                        color: 'var(--text-main)'
                    });
                });
            });
        });
    });
</script>
@endpush
@endsection