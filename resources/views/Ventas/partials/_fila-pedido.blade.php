<tr style="border-bottom: 1px solid #1b0f28;">
    <td class="ps-4 fw-bold text-accent">#{{ str_pad($pedido->id, 4, '0', STR_PAD_LEFT) }}</td>
    
    <td class="text-lavanda">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
    
    <!-- 1. CORRECCIÓN: variable cliente_nombre -->
    <td class="fw-bold">{{ $pedido->cliente_nombre }}</td>
    
    <!-- 2. CORRECCIÓN: variable cantidad_total_bastones -->
    <td class="text-lavanda">{{ $pedido->cantidad_total_bastones }} Bastones</td>
    
    <!-- 3. CORRECCIÓN: variable costo_total -->
    <td class="fw-bold" style="color: #4ade80;">$ {{ number_format($pedido->costo_total, 2) }}</td>
    
    <td>
        <div class="dropdown">
            <!-- 4. CORRECCIÓN: La clase del badge ahora lee exactamente el ENUM -->
            <button class="btn btn-sm dropdown-toggle badge-estado {{ $pedido->estado }}" 
                    type="button" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false" 
                    data-bs-boundary="window">
                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-dark shadow" style="background-color: #2c1548; border-color: #5b21b6;">
                <li><a class="dropdown-item cambiar-estado" href="#" data-id="{{ $pedido->id }}" data-estado="pendiente">Cotizado (Pendiente)</a></li>
                <li><a class="dropdown-item cambiar-estado" href="#" data-id="{{ $pedido->id }}" data-estado="en_produccion">En Producción</a></li>
                <li><a class="dropdown-item cambiar-estado text-success" href="#" data-id="{{ $pedido->id }}" data-estado="realizado">Marcar Realizado</a></li>
                <li><hr class="dropdown-divider" style="border-color: #5b21b6;"></li>
                <li><a class="dropdown-item cambiar-estado text-danger" href="#" data-id="{{ $pedido->id }}" data-estado="cancelado">Cancelar Pedido</a></li>
            </ul>
        </div>
    </td>
    <td class="pe-4 text-end">
        <button class="btn btn-sm btn-accion-secundaria" title="Nota de Venta" onclick="window.open('{{ route('pedidos.pdf_nota', $pedido->id) }}', '_blank')">📄</button>
        <button class="btn btn-sm btn-accion" title="Receta Interna" onclick="window.open('{{ route('pedidos.pdf_receta', $pedido->id) }}', '_blank')">📋</button>
    </td>
</tr>