<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual - {{ $nombreMes }} {{ $anio }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; }
        .encabezado { text-align: center; border-bottom: 2px solid #9d5ce0; padding-bottom: 10px; margin-bottom: 20px; }
        .encabezado h1 { margin: 0; font-size: 20px; text-transform: uppercase; color: #4a148c; }
        
        /* Flexbox no funciona bien en DomPDF, usamos tablas para estructurar */
        .grid-container { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .grid-container td { padding: 5px; vertical-align: top; }
        
        .caja-info { background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center; }
        .caja-info h4 { margin: 0 0 5px 0; font-size: 11px; color: #666; text-transform: uppercase; }
        .caja-info.finanzas .valor { font-size: 18px; font-weight: bold; color: #4a148c; }
        .caja-info.operativa .valor { font-size: 16px; font-weight: bold; color: #333; }
        
        .tabla-datos { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tabla-datos th, .tabla-datos td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
        .tabla-datos th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 10px; color: #444; }
        
        .alerta-roja { background-color: #ffe6e6; color: #cc0000; font-weight: bold; }
        .alerta-naranja { background-color: #fff3cd; color: #856404; }
        .titulo-seccion { color: #1b0f28; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 25px; margin-bottom: 10px; }
        
        /* Salto de página */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- ==============================================
         PÁGINA 1: FINANZAS Y OPERATIVA
         ============================================== -->
    <div class="encabezado">
        <h1>Reporte Gerencial y Auditoría</h1>
        <p style="margin-top: 5px;"><strong>Periodo: {{ $nombreMes }} {{ $anio }}</strong></p>
    </div>

    <h2 class="titulo-seccion">1. Resumen Financiero (Pedidos Completados)</h2>
    <table class="grid-container">
        <tr>
            <td width="33%">
                <div class="caja-info finanzas">
                    <h4>Ingresos Brutos</h4>
                    <div class="valor">${{ number_format($ingresosTotales, 2) }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="caja-info finanzas">
                    <h4>Costo Insumos</h4>
                    <div class="valor" style="color: #dc3545;">${{ number_format($costoInsumosTotal, 2) }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="caja-info finanzas">
                    <h4>Margen / Mano Obra</h4>
                    <div class="valor" style="color: #198754;">${{ number_format($manoObraTotal, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <h2 class="titulo-seccion">2. Embudo Operativo del Mes</h2>
    <table class="grid-container">
        <tr>
            <td width="25%"><div class="caja-info operativa"><h4>Realizados</h4><div class="valor" style="color: green;">{{ $estadosCount['realizado'] }}</div></div></td>
            <td width="25%"><div class="caja-info operativa"><h4>En Producción</h4><div class="valor" style="color: blue;">{{ $estadosCount['en_produccion'] }}</div></div></td>
            <td width="25%"><div class="caja-info operativa"><h4>Pendientes</h4><div class="valor" style="color: orange;">{{ $estadosCount['pendiente'] }}</div></div></td>
            <td width="25%"><div class="caja-info operativa"><h4>Cancelados</h4><div class="valor" style="color: red;">{{ $estadosCount['cancelado'] }}</div></div></td>
        </tr>
    </table>

    <h2 class="titulo-seccion">3. Modelos Más Populares (Catálogo Web)</h2>
    <table class="tabla-datos">
        <thead><tr><th width="70%">Nombre del Diseño</th><th width="30%">Consultas / Favoritos</th></tr></thead>
        <tbody>
            @foreach($topProductos as $producto)
            <tr><td>{{ $producto->titulo }}</td><td>{{ $producto->contador_consultas }} interacciones</td></tr>
            @endforeach
        </tbody>
    </table>

    <!-- Salto de Página -->
    <div class="page-break"></div>

    <!-- ==============================================
         PÁGINA 2: AUDITORÍA DE INVENTARIO
         ============================================== -->
    <div class="encabezado">
        <h1>Auditoría de Kardex y Bodega</h1>
        <p style="margin-top: 5px;"><strong>Periodo: {{ $nombreMes }} {{ $anio }}</strong></p>
    </div>

    <h2 class="titulo-seccion">4. Alertas Críticas: Stock en Negativo (Por Comprar)</h2>
    @if($stockNegativo->count() > 0)
        <table class="tabla-datos">
            <thead><tr class="alerta-roja"><th width="60%">Material</th><th width="40%">Déficit Actual</th></tr></thead>
            <tbody>
                @foreach($stockNegativo as $insumo)
                <tr>
                    <td>{{ $insumo->nombre }}</td>
                    <td style="color: red; font-weight: bold;">{{ $insumo->stock_actual }} (Faltante)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: green; font-weight: bold;">Excelente: No hay materiales con stock en negativo.</p>
    @endif

    <h2 class="titulo-seccion">5. Materiales Fantasma (No descontados en BD)</h2>
    <p style="font-size: 11px; color: #666; margin-top: -5px;">Estos materiales se solicitaron en pedidos este mes, pero el sistema no encontró su par en el Kardex para descontarlos.</p>
    @if(count($materialesFantasmas) > 0)
        <table class="tabla-datos">
            <thead><tr class="alerta-naranja"><th width="50%">Material Escrito</th><th width="25%">Pedido N°</th><th width="25%">Cant. Usada</th></tr></thead>
            <tbody>
                @foreach($materialesFantasmas as $fantasma)
                <tr>
                    <td>{{ $fantasma['nombre'] }}</td>
                    <td>#{{ str_pad($fantasma['pedido'], 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $fantasma['cantidad'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: green; font-weight: bold;">Perfecto: Todos los materiales de este mes fueron enlazados y descontados del Kardex.</p>
    @endif

    <h2 class="titulo-seccion">6. Top 5: Materiales Más Consumidos</h2>
    <table class="tabla-datos">
        <thead><tr><th width="70%">Material</th><th width="30%">Cantidad Total Usada</th></tr></thead>
        <tbody>
            @forelse($topInsumos as $nombre => $cantidad)
            <tr><td>{{ $nombre }}</td><td>{{ $cantidad }} unidades/metros</td></tr>
            @empty
            <tr><td colspan="2" style="text-align: center;">No hay consumo registrado.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>