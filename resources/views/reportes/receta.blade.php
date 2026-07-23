<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta de Producción - Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .encabezado {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .encabezado h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .datos-pedido {
            width: 100%;
            margin-bottom: 20px;
        }
        .datos-pedido td {
            padding: 5px 0;
        }
        .tabla-materiales {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabla-materiales th, .tabla-materiales td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }
        .tabla-materiales th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .badge-traduccion {
            font-weight: bold;
            color: #000;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="encabezado">
        <h1>Receta de Producción (Bodega)</h1>
        <p style="margin-top: 5px; font-size: 16px;"><strong>Orden de Ensamblaje #{{ $pedido->id }}</strong></p>
    </div>

    <table class="tabla-materiales" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f3f4f6; text-align: left;">
                <th style="width: 30%; padding: 8px; border: 1px solid #ddd;">Material Requerido</th>
                <th style="width: 15%; padding: 8px; border: 1px solid #ddd;">Costo Unit.</th>
                <th style="width: 15%; padding: 8px; border: 1px solid #ddd;">Subtotal</th>
                <th style="width: 15%; padding: 8px; border: 1px solid #ddd;">Cant. Neta</th>
                <th style="width: 10%; padding: 8px; border: 1px solid #ddd;">Stock</th>
                <th style="width: 15%; padding: 8px; border: 1px solid #ddd;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->materiales as $mat)
                @if($mat->es_diseno)
                    @continue
                @endif
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $mat->nombre_material }}</td>
                <!-- Nuevas columnas financieras -->
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $mat->precio_unitario_visual }}</td>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">{{ $mat->subtotal_visual }}</td>
                
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $mat->requerido_visual }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $mat->stock_visual }}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">
                    @if($mat->falta_comprar_num > 0)
                        <strong style="color: red;">¡Faltan {{ $mat->falta_visual }}! (Comprar)</strong>
                    @else
                        <strong style="color: green;">Stock Completo</strong>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Nuevo Footer con Resumen Financiero Completo -->
    <div style="margin-top: 25px; border-top: 2px solid #ddd; padding-top: 15px; text-align: right;">
        
        <p style="font-size: 14px; color: #555; margin-bottom: 5px;">
            Costo Base Insumos + Extras: <strong>${{ number_format($costoTotalMateriales, 2) }}</strong>
        </p>

        <p style="font-size: 14px; color: #555; margin-bottom: 5px;">
            Mano de Obra (Ganancia Base): <strong>${{ number_format($costoManoObra, 2) }}</strong>
        </p>

        @if($costoDisenoPersonalizado > 0)
        <p style="font-size: 14px; color: #555; margin-bottom: 15px;">
            Diseño Personalizado (Extra): <strong>${{ number_format($costoDisenoPersonalizado, 2) }}</strong>
        </p>
        @endif

        <h3 style="margin: 0; font-size: 20px; color: #16a34a;">
            COSTO TOTAL PRODUCCIÓN: ${{ number_format($costoTotalProduccion, 2) }}
        </h3>
        
    </div>

    <div class="footer">
        Documento de uso interno del Taller de Bastones. Los detalles financieros han sido omitidos por seguridad.
    </div>

</body>
</html>