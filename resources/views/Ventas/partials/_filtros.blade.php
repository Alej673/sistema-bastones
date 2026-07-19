<div class="card border-0 shadow-sm mb-4 card-panel">
    <div class="card-body">
        <!-- 1. Agregamos action apuntando a la ruta y method GET -->
        <form id="form-filtros-historial" action="{{ route('ventas.index') }}" method="GET" class="row g-3 align-items-center">
            
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text border-0 input-dark">🔍</span>
                    <!-- Transformamos el input a select para Select2 -->
                    <select name="buscar" id="buscar_cliente_ajax" class="form-select border-0 input-dark">
                        @if(request('buscar'))
                            <option value="{{ request('buscar') }}" selected>{{ request('buscar') }}</option>
                        @else
                            <!-- IMPORTANTE: Debe tener value="" y estar vacío por dentro -->
                            <option value=""></option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <!-- 3. name="fecha" -->
                <input type="date" name="fecha" class="form-control border-0 input-dark" value="{{ request('fecha') }}">
            </div>

            <div class="col-md-3">
                <!-- 4. name="estado" y lógica para mantener seleccionada la opción -->
                <select name="estado" class="form-select border-0 input-dark">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Cotizado (Pendiente)</option>
                    <option value="en_produccion" {{ request('estado') == 'en_produccion' ? 'selected' : '' }}>En Producción</option>
                    <option value="realizado" {{ request('estado') == 'realizado' ? 'selected' : '' }}>Realizado</option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn fw-bold flex-grow-1" style="background-color: var(--color-violeta-boton); color: #ffffff;">Filtrar</button>
                
                <!-- 5. Botón para limpiar filtros (solo aparece si hay filtros aplicados) -->
                @if(request()->hasAny(['buscar', 'fecha', 'estado']))
                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary fw-bold border-0" style="color: var(--color-texto-mutado);" title="Limpiar filtros">✖</a>
                @endif
            </div>
        </form>
    </div>
</div>