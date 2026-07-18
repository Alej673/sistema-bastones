<div class="card card-panel shadow-sm mb-4">
    <div class="card-body">
        <form id="form-filtros-historial" class="row g-3 align-items-center">

            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text input-dark border-0">🔍</span>
                    <input type="text" class="form-control input-dark" id="buscar_cliente" placeholder="Buscar cliente o N° Doc...">
                </div>
            </div>

            <div class="col-md-3">
                <input type="date" class="form-control input-dark" id="fecha_filtro">
            </div>

            <div class="col-md-3">
                <select class="form-select input-dark" id="estado_filtro">
                    <option value="">Todos los estados</option>
                    <option value="cotizado">Cotizado (Pendiente)</option>
                    <option value="produccion">En Producción</option>
                    <option value="entregado">Entregado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button type="submit" class="btn fw-bold" style="background-color: #7c3aed; color: #ffffff;">Filtrar</button>
            </div>
        </form>
    </div>
</div>