<div class="card border-0 shadow-sm card-panel">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0" style="color: #f5eaff;">
                <thead style="border-bottom: 2px solid #5b21b6;">
                    <tr>
                        <th class="ps-4 pb-3 pt-3">N° Doc</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Resumen de Pedido</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="pe-4 text-end">Documentos</th>
                    </tr>
                </thead>
                <tbody id="tbody-historial">
                    @forelse ($pedidos as $pedido)
                        <!-- Aquí inyectamos el archivo de la fila por cada pedido encontrado -->
                        @include('Ventas.partials._fila-pedido', ['pedido' => $pedido])
                    @empty
                        <!-- Esto se mostrará si no hay registros -->
                        <tr>
                            <td colspan="7" class="text-center py-4 text-lavanda">
                                No hay pedidos registrados todavía o que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- =========================================
            PAGINACIÓN
            ========================================= -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-lavanda small">
                Mostrando {{ $pedidos->firstItem() ?? 0 }} a {{ $pedidos->lastItem() ?? 0 }} de {{ $pedidos->total() }} pedidos
            </div>
            <div>
                <!-- Inyectamos los botones y mantenemos los filtros activos en la URL -->
                {{ $pedidos->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>