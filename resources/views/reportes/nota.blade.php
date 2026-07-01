<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Venta - Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #17a2b8; /* Un color más comercial */
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
        }
        .datos-cliente {
            width: 100%;
            margin-bottom: 30px;
        }
        .datos-cliente td {
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .seccion-titulo {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .lista-materiales {
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .totales-caja {
            width: 50%;
            float: right;
            border-collapse: collapse;
        }
        .totales-caja th, .totales-caja td {
            padding: 10px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        .totales-caja th {
            color: #2c3e50;
        }
        .gran-total {
            font-size: 20px;
            font-weight: bold;
            color: #17a2b8;
        }
        .footer {
            clear: both;
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Taller de Bastones</h1>
        <p>Nota de Venta Oficial | Comprobante #{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <table class="datos-cliente">
        <tr>
            <td style="width: 50%;">
                <strong>Cliente / Institución:</strong> <br>
                {{ $pedido->cliente_nombre }}
            </td>
            <td style="width: 50%;">
                <strong>Fecha de Emisión:</strong> <br>
                {{ $pedido->created_at->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Correo Electrónico:</strong> <br>
                {{ $pedido->correo_cliente ?? 'No registrado' }}
            </td>
            <td>
                <strong>Cantidad Total:</strong> <br>
                {{ $pedido->cantidad_total_bastones }} Bastones de Gala
            </td>
        </tr>
    </table>

    <div class="seccion-titulo">Detalle del Diseño de Producción</div>
    <div class="lista-materiales">
        <ul>
            @foreach($pedido->materiales as $mat)
                {{-- Filtramos para NO mostrar insumos internos (cinchos y elásticos) al cliente --}}
                @if(stripos($mat->nombre_material, 'cincho') === false && stripos($mat->nombre_material, 'elástico') === false && stripos($mat->nombre_material, 'elastico') === false)
                    <li>{{ $mat->nombre_material }}</li>
                @endif
            @endforeach
        </ul>
        <p style="font-size: 12px; color: #7f8c8d; margin-left: 20px;">
            * Incluye ensamblaje profesional y materiales estructurales internos.
        </p>
    </div>

    <table class="totales-caja">
        <tr>
            <th>Costo Sugerido por Unidad:</th>
            <td>$ {{ number_format($pedido->costo_unitario, 2) }}</td>
        </tr>
        <tr>
            <th><span class="gran-total">Total a Pagar:</span></th>
            <td><span class="gran-total">$ {{ number_format($pedido->costo_total, 2) }}</span></td>
        </tr>
    </table>

    <div class="footer">
        Gracias por confiar en nuestro trabajo. <br>
        Documento generado automáticamente por el Sistema de Gestión.
    </div>

</body>
</html>