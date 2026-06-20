@extends('layouts.admin')

@section('titulo', 'Inventario y Movimientos')

@section('contenido')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
        <h2 class="h3">Gestión de Inventario y Kardex</h2>
        <div>
            <button class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoInsumo">➕ Nuevo Insumo</button>
        </div>
    </div>

    <style>
        .buscador-animado {
            width: 140px; /* Tamaño discreto por defecto */
            transition: width 0.4s ease-in-out; /* Animación suave */
        }
        .buscador-animado:focus {
            width: 100%; /* Al tocarlo, se expande al máximo disponible */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); /* Resplandor azul Bootstrap */
        }
        /* Ajuste para pantallas más grandes (computadora) */
        @media (min-width: 768px) {
            .buscador-animado { width: 220px; }
        }
    </style>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
            <span class="text-primary fw-bold">Stock Actual de Materia Prima</span>
            
            <div style="flex-grow: 1; max-width: 350px; display: flex; justify-content: flex-end;">
                <input type="text" id="buscadorInventario" class="form-control form-control-sm buscador-animado" placeholder="🔍 Buscar material...">
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 text-nowrap" id="tablaInventario">
                    
                    <thead class="text-secondary sticky-top" style="background-color: #f8f9fa; z-index: 1;">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Artículo y Categoría</th>
                            <th>Costo Unit. (BD)</th>
                            <th>Stock Físico Estimado</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones Rápidas</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @foreach($insumos as $insumo)
                        <tr class="fila-insumo">
                            <td class="ps-4 text-muted small">INS-00{{ $insumo->id }}</td>
                            
                            <td>
                                <span class="fw-bold text-dark">{{ $insumo->nombre }}</span><br>
                                <span class="badge bg-light text-secondary border border-secondary-subtle">{{ strtoupper($insumo->categoria) }}</span>
                            </td>
                            
                            <td>
                                @if($insumo->categoria == 'lana')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 90, 2) }}</span> <span class="text-muted small">/ Madeja</span>
                                @elseif($insumo->categoria == 'cinta_garza')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 45.72, 2) }}</span> <span class="text-muted small">/ Rollo (50 yardas)</span>
                                @elseif($insumo->categoria == 'cinta_satin')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 18.28, 2) }}</span> <span class="text-muted small">/ Rollo (20 yardas)</span>
                                @elseif($insumo->categoria == 'cortina_fiesta')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 4, 2) }}</span> <span class="text-muted small">/ Paquete</span>
                                @elseif($insumo->categoria == 'elastico')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 10, 2) }}</span> <span class="text-muted small">/ Pieza (10m)</span>
                                @elseif($insumo->categoria == 'cinchos')
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario * 100, 2) }}</span> <span class="text-muted small">/ Paquete (100u)</span>
                                @else
                                    <span class="text-dark fw-bold">$ {{ number_format($insumo->costo_unitario, 2) }}</span> <span class="text-muted small">/ {{ $insumo->unidad_medida }}</span>
                                @endif
                            </td>
                            
                            <td>
                                <span class="fw-bold fs-6">{{ $insumo->stock_actual }} {{ $insumo->unidad_medida }}</span><br>
                                
                                @if($insumo->categoria == 'lana')
                                    <small class="text-muted">({{ ceil($insumo->stock_actual / 90) }} Madejas aprox.)</small>
                                @elseif($insumo->categoria == 'cortina_fiesta')
                                    <small class="text-muted">({{ ceil($insumo->stock_actual / 4) }} Paquetes aprox.)</small>
                                @endif
                            </td>
                            
                            <td>
                                @if($insumo->stock_actual <= 0)
                                    <span class="badge bg-danger">Agotado</span>
                                @elseif($insumo->stock_actual <= $insumo->stock_minimo)
                                    <span class="badge bg-warning text-dark">Stock Bajo</span>
                                @else
                                    <span class="badge bg-success">Adecuado</span>
                                @endif
                            </td>
                            
                        <td class="text-center">
                                <button class="btn btn-sm btn-light border fw-bold text-success me-1 btn-ajuste" 
                                        data-bs-toggle="modal" data-bs-target="#modalAjusteStock" 
                                        data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}" 
                                        data-categoria="{{ $insumo->categoria }}" data-operacion="entrada">
                                    ➕ Entrada
                                </button>
                                
                                <button class="btn btn-sm btn-light border fw-bold text-danger me-1 btn-ajuste" 
                                        data-bs-toggle="modal" data-bs-target="#modalAjusteStock" 
                                        data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}" 
                                        data-categoria="{{ $insumo->categoria }}" data-operacion="salida">
                                    ➖ Salida
                                </button>
                                
                                <button class="btn btn-sm btn-light border text-secondary me-1 btn-editar" 
                                        data-bs-toggle="modal" data-bs-target="#modalEditarInsumo" 
                                        data-id="{{ $insumo->id }}" data-nombre="{{ $insumo->nombre }}">
                                    ✏️
                                </button>

                                <form action="{{ route('insumos.destroy', $insumo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar completamente este artículo del inventario? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($insumos->isEmpty())
                <div class="alert alert-light text-center m-4 text-muted border border-dashed">
                    Aún no hay insumos registrados en el taller. Haz clic en "Nuevo Insumo" para empezar.
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white text-secondary py-3 fw-bold">
            Últimos 15 Movimientos Registrados
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-borderless table-striped align-middle mb-0 small text-nowrap">
                    <thead class="border-bottom sticky-top bg-white"> <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Tipo de Movimiento</th>
                            <th>Artículo</th>
                            <th>Cantidad</th>
                            <th>Detalle / Referencia</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($mov->tipo_movimiento == 'entrada' || $mov->tipo_movimiento == 'ingreso_nuevo')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">➕ Entrada / Nuevo</span>
                                @elseif($mov->tipo_movimiento == 'eliminado')
                                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark-subtle">❌ Anulado</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">➖ Salida</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $mov->insumo->nombre ?? 'Insumo Eliminado' }}</td>
                            <td class="fw-bold fs-6">
                                {{ floatval($mov->cantidad) }} <span class="text-muted small">{{ $mov->insumo->unidad_medida ?? '' }}</span>
                            </td>
                            <td class="text-muted small">{{ $mov->detalle }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-light text-center m-3 text-muted">Aún no hay movimientos recientes.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevoInsumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">📦 Ingreso de Compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                
                <form action="{{ route('insumos.store') }}" method="POST">
                    @csrf 
                    <div class="modal-body">
                        
                        <!-- Modal con un menu desplegable para seleccionar el insumo -->
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold mb-1">Tipo de Material Comprado</label>
                            <select name="categoria" id="selectorCategoria" class="form-select bg-light" required>
                                <option value="" selected disabled>Selecciona una opción...</option>
                                <option value="lana">Lana (Madejas de 90g)</option>
                                <option value="cinta_garza">Cinta Garza (Rollos de 50 yardas)</option>
                                <option value="cinta_satin">Cinta Satín (Rollos de 20 yardas)</option>
                                <option value="cortina_fiesta">Cortina de Fiesta (Paquetes de 4 u.)</option>
                                <option value="elastico">Elástico (Piezas de 10m)</option>
                                <option value="cinchos">Cinchos (Paquetes de 100 u.)</option>
                                <option value="base_baston">Bases de Bastón</option> 
                                <option value="unidad_simple">Apliques, Taipe (Unidad suelta)</option>
                            </select>
                        </div>

                        <!-- seccion para ingresar el nombre del insumo -->
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold mb-1">Nombre Comercial</label>
                            <input type="text" name="nombre" id="inputNombreInsumo" class="form-control bg-light" placeholder="Ej. Lana Roja Neón" required>
                        </div>

                        <div id="opcionesBaston" class="row g-2 mb-3 d-none p-3 border border-primary border-opacity-25 rounded bg-white shadow-sm">
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold mb-1">Color del Bastón</label>
                                <select id="selectorColorBaston" class="form-select">
                                    <option value="Plata">Color Plata</option>
                                    <option value="Dorado">Color Dorado</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold mb-1">Tamaño (cm)</label>
                                <select id="selectorTamanoBaston" class="form-select">
                                    <option value="45cm">45 cm</option>
                                    <option value="50cm">50 cm</option>
                                    <option value="55cm">55 cm</option>
                                    <option value="60cm">60 cm</option>
                                </select>
                            </div>
                            <div class="col-12 mt-1">
                                <small class="fw-bold text-primary">El nombre se generará automáticamente arriba.</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label id="labelCantidad" class="form-label text-secondary small fw-bold mb-1">Cant. de Madejas</label>
                                <input type="number" name="cantidad_comprada" class="form-control bg-light" placeholder="Ej. 5" step="0.01" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small fw-bold mb-1">Precio Unitario ($)</label>
                                <input type="number" name="precio_unitario" class="form-control bg-light" placeholder="Ej. 1.15" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label id="labelAlerta" class="form-label text-secondary small fw-bold mb-1">Alerta de Stock Mínimo</label>
                            <input type="number" name="alerta_minima" class="form-control bg-light" placeholder="Ej. 2" step="0.01" required>
                        </div>

                    </div>
                    
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark fw-bold px-4">Guardar en Inventario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ModalAjusteStock -->

    <div class="modal fade" id="modalAjusteStock" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm"> <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="tituloAjuste">Ajustar Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                
                <form id="formAjusteStock" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <p class="text-muted small mb-3" id="subtituloAjuste">Material: ...</p>
                        
                        <div class="mb-2">
                            <label id="labelMover" class="form-label text-secondary small fw-bold mb-1">Cantidad comercial</label>
                            <input type="number" name="cantidad_movimiento" class="form-control bg-light fw-bold text-center fs-5" placeholder="Ej. 1" min="1" step="1" required>
                        </div>
                        
                        <input type="hidden" name="tipo_movimiento" id="tipoMovimiento">
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnGuardarAjuste" class="btn btn-dark btn-sm fw-bold px-3">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarInsumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">✏️ Corregir Nombre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                
                <form id="formEditarInsumo" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label text-secondary small fw-bold mb-1">Nuevo Nombre Comercial</label>
                            <input type="text" name="nombre" id="inputEditarNombre" class="form-control bg-light" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark fw-bold">Actualizar Nombre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --------------------------------------------------------
        // 1. BUSCADOR RÁPIDO DE LA TABLA
        // --------------------------------------------------------
        document.getElementById('buscadorInventario').addEventListener('keyup', function() {
            let textoBusqueda = this.value.toLowerCase();
            let filas = document.querySelectorAll('.fila-insumo');
            filas.forEach(function(fila) {
                let nombreInsumo = fila.cells[1].textContent.toLowerCase();
                fila.style.display = nombreInsumo.includes(textoBusqueda) ? '' : 'none';
            });
        });

        // --------------------------------------------------------
        // 2. LÓGICA DEL MODAL: "NUEVO INSUMO" (Ingresos)
        // --------------------------------------------------------
        const selectorNuevo = document.getElementById('selectorCategoria');
        const inputNombre = document.getElementById('inputNombreInsumo');
        const opcionesBaston = document.getElementById('opcionesBaston');
        const selectorColor = document.getElementById('selectorColorBaston');
        const selectorTamano = document.getElementById('selectorTamanoBaston');
        const labelCant = document.getElementById('labelCantidad');
        const labelAlert = document.getElementById('labelAlerta');

        const textosNuevoInsumo = {
            'lana': { cant: 'Cant. de Madejas compradas', alert: 'Avisar cuando queden (Madejas):' },
            'cinta_garza': { cant: 'Cant. de Rollos comprados', alert: 'Avisar cuando queden (Rollos):' },
            'cinta_satin': { cant: 'Cant. de Rollos comprados', alert: 'Avisar cuando queden (Rollos):' },
            'cortina_fiesta': { cant: 'Cant. de Paquetes comprados', alert: 'Avisar cuando queden (Paquetes):' },
            'elastico': { cant: 'Cant. de Piezas (10m) compradas', alert: 'Avisar cuando queden (Piezas):' },
            'cinchos': { cant: 'Cant. de Paquetes (100u) compr.', alert: 'Avisar cuando queden (Paquetes):' },
            'base_baston': { cant: 'Cant. de Bases compradas', alert: 'Avisar cuando queden (Bases):' },
            'unidad_simple': { cant: 'Cant. de Unidades compradas', alert: 'Avisar cuando queden (Unidades):' }
        };

        // Autocompletar el nombre del bastón
        function actualizarNombreBaston() {
            if(selectorNuevo.value === 'base_baston') {
                inputNombre.value = `Base ${selectorColor.value} ${selectorTamano.value}`;
            }
        }

        selectorNuevo.addEventListener('change', function() {
            let cat = this.value;
            
            // Textos de las etiquetas
            labelCant.textContent = textosNuevoInsumo[cat].cant;
            labelAlert.textContent = textosNuevoInsumo[cat].alert;

            // Mostrar/Ocultar y bloquear nombres si es Bastón
            if (cat === 'base_baston') {
                opcionesBaston.classList.remove('d-none');
                inputNombre.readOnly = true; 
                inputNombre.classList.add('bg-secondary', 'bg-opacity-10', 'fw-bold', 'text-secondary');
                actualizarNombreBaston(); 
            } else {
                opcionesBaston.classList.add('d-none');
                inputNombre.readOnly = false;
                inputNombre.classList.remove('bg-secondary', 'bg-opacity-10', 'fw-bold', 'text-primary');
                inputNombre.value = ''; 
            }
        });

        selectorColor.addEventListener('change', actualizarNombreBaston);
        selectorTamano.addEventListener('change', actualizarNombreBaston);


        // --------------------------------------------------------
        // 3. LÓGICA DEL MODAL: "AJUSTE DE STOCK" (Entradas/Salidas)
        // --------------------------------------------------------
        const textosEntrada = {
            'lana': '¿Cuántas MADEJAS vas a ingresar?:',
            'cinta_garza': '¿Cuántos ROLLOS (50yd) vas a ingresar?:',
            'cinta_satin': '¿Cuántos ROLLOS (20yd) vas a ingresar?:',
            'cortina_fiesta': '¿Cuántos PAQUETES (4u) vas a ingresar?:',
            'elastico': '¿Cuántas PIEZAS (10m) vas a ingresar?:',
            'cinchos': '¿Cuántos PAQUETES (100u) vas a ingresar?:',
            'base_baston': '¿Cuántas BASES vas a ingresar?:',
            'unidad_simple': '¿Cuántas UNIDADES vas a ingresar?:'
        };

        const textosSalida = {
            'lana': '¿Cuántos GRAMOS vas a retirar (Desperdicio)?:',
            'cinta_garza': '¿Cuántos METROS vas a retirar (Desperdicio)?:',
            'cinta_satin': '¿Cuántos METROS vas a retirar (Desperdicio)?:',
            'cortina_fiesta': '¿Cuántas CORTINAS SUELTAS vas a retirar?:',
            'elastico': '¿Cuántos METROS vas a retirar?:',
            'cinchos': '¿Cuántos CINCHOS SUELTOS vas a retirar?:',
            'base_baston': '¿Cuántas BASES vas a retirar?:',
            'unidad_simple': '¿Cuántas UNIDADES vas a retirar?:'
        };

        document.querySelectorAll('.btn-ajuste').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const categoria = this.getAttribute('data-categoria');
                const operacion = this.getAttribute('data-operacion');
                
                document.getElementById('formAjusteStock').action = `/insumos/${id}/ajustar`;
                document.getElementById('tipoMovimiento').value = operacion;
                document.getElementById('subtituloAjuste').innerHTML = `Material: <strong>${nombre}</strong>`;
                
                if(operacion === 'entrada') {
                    document.getElementById('tituloAjuste').innerText = '➕ Registrar Entrada';
                    document.getElementById('labelMover').innerText = textosEntrada[categoria] || 'Cantidad:';
                    document.getElementById('btnGuardarAjuste').className = 'btn btn-success btn-sm fw-bold px-3';
                } else {
                    document.getElementById('tituloAjuste').innerText = '➖ Registrar Salida';
                    document.getElementById('labelMover').innerText = textosSalida[categoria] || 'Cantidad:';
                    document.getElementById('btnGuardarAjuste').className = 'btn btn-danger btn-sm fw-bold px-3';
                }
            });
        });

        // --------------------------------------------------------
        // 4. LÓGICA DEL MODAL: "EDITAR NOMBRE"
        // --------------------------------------------------------
        document.querySelectorAll('.btn-editar').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                
                document.getElementById('formEditarInsumo').action = `/insumos/${id}`;
                document.getElementById('inputEditarNombre').value = nombre;
            });
        });
    </script>
@endsection
