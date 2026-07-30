<tr>
    <td class="ps-4 fw-bold text-accent">#{{ str_pad($pedido->id, 4, '0', STR_PAD_LEFT) }}</td>
    
    <td class="text-lavanda">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
    
    <!-- 1. CORRECCIÓN: variable cliente_nombre -->
    <td class="fw-bold">{{ $pedido->cliente_nombre }}</td>
    
    <!-- 2. RESUMEN DE PEDIDO (Lógica de Detección Dual) -->
    <td class="text-lavanda">
        @php
            // Verificamos si es una cotización rápida buscando nuestro tag oculto
            $esRapida = $pedido->materiales->count() === 1 && str_starts_with($pedido->materiales->first()->nombre_material, '[COTI-RÁPIDA]');
        @endphp

        @if($esRapida)
            @php
                // Limpiamos el texto para mostrar solo el nombre (Ej. "Fufucha de Primera Comunión")
                $nombreProducto = str_replace('[COTI-RÁPIDA] ', '', $pedido->materiales->first()->nombre_material);
            @endphp
            <span class="badge" style="background-color: var(--accent-purple); color: white; font-weight: 600;">
                <i class="fa-solid fa-star me-1" style="font-size: 0.75rem;"></i> Manualidad
            </span>
            <div class="text-muted mt-1" style="font-size: 0.75rem; line-height: 1.1; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $nombreProducto }}">
                {{ $nombreProducto }}
            </div>
        @else
            <span class="fw-semibold">
                <i class="fa-solid fa-wand-magic-sparkles me-1 text-muted" style="font-size: 0.8rem;"></i>
                {{ $pedido->cantidad_total_bastones }} Bastones
            </span>
        @endif
    </td>
    
    <!-- 3. CORRECCIÓN: variable costo_total -->
    <td class="fw-bold" style="color: #16a34a;">$ {{ number_format($pedido->costo_total, 2) }}</td>
    
    <td>
        <div class="dropdown">
            <!-- 4. CORRECCIÓN: La clase del badge ahora lee exactamente el ENUM -->
            <button class="btn btn-sm dropdown-toggle badge-estado {{ $pedido->estado }}" 
                    type="button" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false" 
                    data-bs-boundary="window"
                    {{ $pedido->estado == 'realizado' ? 'disabled' : '' }}>
                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-dark shadow">
                <li><a class="dropdown-item cambiar-estado" href="#" data-id="{{ $pedido->id }}" data-estado="pendiente">Cotizado (Pendiente)</a></li>
                <li><a class="dropdown-item cambiar-estado" href="#" data-id="{{ $pedido->id }}" data-estado="en_produccion">En Producción</a></li>
                <li><a class="dropdown-item cambiar-estado text-success" href="#" data-id="{{ $pedido->id }}" data-estado="realizado">Marcar Realizado</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item cambiar-estado text-danger" href="#" data-id="{{ $pedido->id }}" data-estado="cancelado">Cancelar Pedido</a></li>
            </ul>
        </div>
    </td>
    <td class="pe-4 text-end">
        <button type="button" 
                class="btn btn-sm btn-vincular-action ms-1 btn-vincular" 
                data-id="{{ $pedido->id }}" 
                data-estado="{{ $pedido->estado }}"
                title="Vincular a cliente web o enviar por correo">
            <i class="fa-solid fa-link"></i>
        </button>
        
        <!-- Ocultamos el botón de Receta Interna (Materiales) si es una manualidad rápida, ya que no usa el Kardex -->
        @if(!$esRapida)
            <button class="btn btn-sm btn-ver-detalle me-1" title="Ver Materiales" data-id="{{ $pedido->id }}">👁️</button>
            <button class="btn btn-sm btn-accion" title="Receta Interna" onclick="window.open('{{ route('pedidos.pdf_receta', $pedido->id) }}', '_blank')">📋</button>
        @endif
        
        <button class="btn btn-sm btn-accion-secundaria" title="Nota de Venta" onclick="window.open('{{ route('pedidos.pdf_nota', $pedido->id) }}', '_blank')">📄</button>
    </td>
</tr>