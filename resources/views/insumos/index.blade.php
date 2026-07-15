@extends('layouts.admin')

@section('titulo', 'Inventario y Movimientos')

{{-- Inyectamos el CSS exclusivo de esta vista --}}
@push('css')
    @vite(['resources/css/inventario.css'])
@endpush

@section('contenido')
<div class="dark-glass-kardex pb-5">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom" style="border-color: rgba(232,121,249,0.14) !important;">
        <h2 class="h3">Gestión de Inventario y Kardex</h2>
        <div>
            <button class="btn glass-btn-primary fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoInsumo">
                ➕ Nuevo Insumo
            </button>
        </div>
    </div>

    {{-- TABLA DE STOCK ACTUAL --}}
    <div class="card glass-card mb-4 border-0">
        <div class="card-header glass-card-header d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
            <span class="fw-bold fs-5" style="color: #f5eaff;">Stock Actual de Materia Prima</span>
            <div style="flex-grow: 1; max-width: 350px; display: flex; justify-content: flex-end;">
                <input type="text" id="buscadorInventario" class="form-control form-control-sm glass-input buscador-animado" placeholder="🔍 Buscar material...">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                <table class="table glass-table align-middle text-nowrap" id="tablaInventario">
                    <thead class="sticky-top">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Artículo y Categoría</th>
                            <th>Costo Unit. (BD)</th>
                            <th>Stock Físico Estimado</th>
                            <th>Estado</th>
                            <th class="text-center pe-4">Acciones Rápidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($insumos as $insumo)
                        <tr class="fila-insumo">
                            <td class="ps-4 text-muted small">INS-00{{ $insumo->id }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $insumo->nombre }}</span><br>
                                <span class="badge glass-badge-light mt-1">
                                    {{ strtoupper($insumo->categoria) }}
                                </span>
                            </td>
                            <td>
                                @if($insumo->categoria == 'lana')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 90, 2) }}</span>
                                    <span class="text-muted small">/ Madeja</span>
                                @elseif($insumo->categoria == 'cinta_garza')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 45.72, 2) }}</span>
                                    <span class="text-muted small">/ Rollo (50 yardas)</span>
                                @elseif($insumo->categoria == 'cinta_satin')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 18.28, 2) }}</span>
                                    <span class="text-muted small">/ Rollo (20 yardas)</span>
                                @elseif($insumo->categoria == 'cinta_gross')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 22.86, 2) }}</span>
                                    <span class="text-muted small">/ Rollo (25 yardas)</span>
                                @elseif($insumo->categoria == 'cortina_fiesta')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 4, 2) }}</span>
                                    <span class="text-muted small">/ Paquete</span>
                                @elseif($insumo->categoria == 'elastico')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 10, 2) }}</span>
                                    <span class="text-muted small">/ Pieza (10m)</span>
                                @elseif($insumo->categoria == 'cinchos')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 100, 2) }}</span>
                                    <span class="text-muted small">/ Paquete (100u)</span>
                                @else
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario, 2) }}</span>
                                    <span class="text-muted small">/ {{ $insumo->unidad_medida }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold fs-6 text-dark">{{ $insumo->stock_actual }} {{ $insumo->unidad_medida }}</span><br>
                                @if($insumo->categoria == 'lana')
                                    <small class="text-muted">({{ ceil($insumo->stock_actual / 90) }} Madejas aprox.)</small>
                                @elseif($insumo->categoria == 'cortina_fiesta')
                                    <small class="text-muted">({{ ceil($insumo->stock_actual / 4) }} Paquetes aprox.)</small>
                                @endif
                            </td>
                            <td>
                                @if($insumo->stock_actual <= 0)
                                    <span class="badge glass-badge-danger">Agotado</span>
                                @elseif($insumo->stock_actual <= $insumo->stock_minimo)
                                    <span class="badge glass-badge-warning">Stock Bajo</span>
                                @else
                                    <span class="badge glass-badge-success">Adecuado</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <button class="btn btn-sm glass-btn-action text-success me-1 btn-ajuste" data-bs-toggle="modal" data-bs-target="#modalAjusteStock" data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}" data-categoria="{{ $insumo->categoria }}" data-operacion="entrada">➕ Entrada</button>
                                <button class="btn btn-sm glass-btn-action text-danger me-1 btn-ajuste" data-bs-toggle="modal" data-bs-target="#modalAjusteStock" data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}" data-categoria="{{ $insumo->categoria }}" data-operacion="salida">➖ Salida</button>
                                <button class="btn btn-sm glass-btn-action text-secondary me-1 btn-editar" data-bs-toggle="modal" data-bs-target="#modalEditarInsumo" data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}">✏️</button>
                                <button type="button" class="btn btn-sm glass-btn-action text-danger btn-confirmar-borrado" data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}" data-action="{{ route('insumos.destroy', $insumo->id) }}">🗑️</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($insumos->isEmpty())
                <div class="alert text-center m-4 text-muted" style="background: rgba(168,85,247,0.04); border: 1px dashed rgba(232,121,249,0.15);">
                    Aún no hay insumos registrados en el taller. Haz clic en "Nuevo Insumo" para empezar.
                </div>
            @endif
        </div>
    </div>

    {{-- TABLA DE ÚLTIMOS MOVIMIENTOS --}}
    <div class="card glass-card mb-4 border-0">
        <div class="card-header glass-card-header py-3 fw-bold" style="color: #f5eaff !important;">
            Últimos 15 Movimientos Registrados
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-borderless glass-table align-middle small text-nowrap">
                    <thead class="sticky-top">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Tipo de Movimiento</th>
                            <th>Artículo</th>
                            <th>Cantidad</th>
                            <th class="pe-4">Detalle / Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($mov->tipo_movimiento == 'entrada' || $mov->tipo_movimiento == 'ingreso_nuevo')
                                    <span class="badge glass-badge-success">➕ Entrada / Nuevo</span>
                                @elseif($mov->tipo_movimiento == 'eliminado')
                                    <span class="badge glass-badge-light">❌ Anulado</span>
                                @else
                                    <span class="badge glass-badge-danger">➖ Salida</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">{{ $mov->insumo->nombre ?? 'Insumo Eliminado' }}</td>
                            <td class="fw-bold fs-6 text-dark">
                                {{ floatval($mov->cantidad) }}
                                <span class="text-muted small">{{ $mov->insumo->unidad_medida ?? '' }}</span>
                            </td>
                            <td class="text-muted small pe-4">{{ $mov->detalle }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert text-center m-3 text-muted" style="background: rgba(168,85,247,0.04);">Aún no hay movimientos recientes.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL: NUEVO INSUMO --}}
    <div class="modal fade" id="modalNuevoInsumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card" style="background: rgba(38, 18, 56, 0.96) !important;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">📦 Ingreso de Compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('insumos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold mb-1">Tipo de Material Comprado</label>
                            <div class="custom-select" id="customCategoria">
                                <button type="button" class="custom-select-trigger glass-select">
                                    <span class="custom-select-value placeholder">Selecciona una opción...</span>
                                    <span class="custom-select-arrow">▾</span>
                                </button>
                                <ul class="custom-select-options" role="listbox">
                                    <li data-value="lana">Lana (Madejas de 90g)</li>
                                    <li data-value="cinta_garza">Cinta Garza (Rollos de 50 yardas)</li>
                                    <li data-value="cinta_satin">Cinta Satín (Rollos de 20 yardas)</li>
                                    <li data-value="cinta_gross">Cinta Gross (Rollos de 25 yardas)</li>
                                    <li data-value="cortina_fiesta">Cortina de Fiesta (Paquetes de 4 u.)</li>
                                    <li data-value="elastico">Elástico (Piezas de 10m)</li>
                                    <li data-value="cinchos">Cinchos (Paquetes de 100 u.)</li>
                                    <li data-value="base_baston">Bases de Bastón</li>
                                    <li data-value="unidad_simple">Apliques, Taipe, Silicona (Unidad suelta)</li>
                                </ul>
                                <input type="hidden" name="categoria" id="selectorCategoria">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold mb-1">Nombre Comercial</label>
                            <input type="text" name="nombre" id="inputNombreInsumo" class="form-control glass-input" placeholder="Ej. Lana Roja Neón" required>
                        </div>

                        <div id="opcionesBaston" class="row g-2 mb-3 d-none p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(232, 121, 249, 0.35);">
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold mb-1">Color del Bastón</label>
                                <div class="custom-select" id="customColorBaston">
                                    <button type="button" class="custom-select-trigger glass-select">
                                        <span class="custom-select-value">Color Plata</span>
                                        <span class="custom-select-arrow">▾</span>
                                    </button>
                                    <ul class="custom-select-options" role="listbox">
                                        <li data-value="Plata" class="seleccionada">Color Plata</li>
                                        <li data-value="Dorado">Color Dorado</li>
                                    </ul>
                                    <input type="hidden" id="selectorColorBaston" value="Plata">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold mb-1">Tamaño (cm)</label>
                                <div class="custom-select" id="customTamanoBaston">
                                    <button type="button" class="custom-select-trigger glass-select">
                                        <span class="custom-select-value">45 cm</span>
                                        <span class="custom-select-arrow">▾</span>
                                    </button>
                                    <ul class="custom-select-options" role="listbox">
                                        <li data-value="45cm" class="seleccionada">45 cm</li>
                                        <li data-value="50cm">50 cm</li>
                                        <li data-value="55cm">55 cm</li>
                                        <li data-value="60cm">60 cm</li>
                                    </ul>
                                    <input type="hidden" id="selectorTamanoBaston" value="45cm">
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <small class="fw-bold" style="color: var(--color-morado-claro);">El nombre se generará automáticamente arriba.</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label id="labelCantidad" class="form-label text-secondary small fw-bold mb-1">Cant. del Insumo</label>
                                <input type="number" name="cantidad_comprada" class="form-control glass-input" placeholder="Ej. 5" step="0.01" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold mb-1">Precio Unitario ($)</label>
                                <input type="number" name="precio_unitario" class="form-control glass-input" placeholder="Ej. 1.15" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label id="labelAlerta" class="form-label text-secondary small fw-bold mb-1">Alerta de Stock Mínimo</label>
                            <input type="number" name="alerta_minima" class="form-control glass-input" placeholder="Ej. 2" step="0.01" required>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn glass-btn-action px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn glass-btn-primary fw-bold px-4">Guardar en Inventario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: AJUSTE DE STOCK --}}
    <div class="modal fade" id="modalAjusteStock" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content glass-card" style="background: rgba(38, 18, 56, 0.96) !important;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-white" id="tituloAjuste">Ajustar Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formAjusteStock" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <p class="text-muted small mb-3" id="subtituloAjuste">Material: ...</p>
                        <div class="mb-2">
                            <label id="labelMover" class="form-label text-secondary small fw-bold mb-1">Cantidad comercial</label>
                            <input type="number" name="cantidad_movimiento" class="form-control glass-input fw-bold text-center fs-5 text-white" placeholder="Ej. 1" min="1" step="1" required>
                        </div>
                        <input type="hidden" name="tipo_movimiento" id="tipoMovimiento">
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn glass-btn-action btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnGuardarAjuste" class="btn glass-btn-primary btn-sm fw-bold px-4">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: EDITAR NOMBRE --}}
    <div class="modal fade" id="modalEditarInsumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card" style="background: rgba(38, 18, 56, 0.96) !important;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-white">✏️ Corregir Nombre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formEditarInsumo" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label text-secondary small fw-bold mb-1">Nuevo Nombre Comercial</label>
                            <input type="text" name="nombre" id="inputEditarNombre" class="form-control glass-input" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn glass-btn-action px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn glass-btn-primary fw-bold px-4">Actualizar Nombre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- OVERLAY: CONFIRMAR BORRADO --}}
    <div id="overlayConfirmarBorrado" role="dialog" aria-modal="true" aria-labelledby="tituloBorrado">
        <div id="tarjetaConfirmarBorrado" class="glass-card" style="background: rgba(38, 18, 56, 0.96) !important;">
            <div class="text-center mb-3">
                <div style="font-size: 2.5rem; line-height: 1;">🗑️</div>
                <h5 class="fw-bold mt-2 mb-0 text-white" id="tituloBorrado">Eliminar insumo</h5>
            </div>
            <p class="text-center text-muted small mb-4" id="mensajeBorrado">
                ¿Seguro que deseas eliminar <strong id="nombreInsumoABorrar" class="text-white"></strong>?
                <br>Esta acción no se puede deshacer.
            </p>
            <div class="d-flex gap-2">
                <button id="btnCancelarBorrado" class="btn glass-btn-action fw-bold flex-fill">Cancelar</button>
                <form id="formConfirmarBorrado" method="POST" class="flex-fill">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn fw-bold w-100 text-white" style="background: rgba(248, 113, 113, 0.2); border: 1px solid rgba(248, 113, 113, 0.4); box-shadow: inset 2px 2px 5px rgba(0,0,0,0.3);">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- Inyectamos el JS exclusivo de esta vista --}}
@push('js')
    @vite(['resources/js/inventario.js'])
@endpush