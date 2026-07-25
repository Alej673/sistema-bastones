<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #9D5CE0; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #6B2FA3; }
        .content { margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; color: #6B2FA3; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Taller Arte Titi_Val</div>
        <p>Documento Oficial de Cotización Interna</p>
    </div>

    <div class="content">
        <h3>Datos del Cliente</h3>
        <p><strong>Nombre:</strong> {{ $cotizacion->nombre }}</p>
        <p><strong>Teléfono:</strong> {{ $cotizacion->telefono }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($cotizacion->estado) }}</p>

        <h3>Detalles de Fabricación</h3>
        <table class="table">
            <tr>
                <th>Cantidad</th>
                <th>Medida</th>
                <th>Acabado</th>
                <th>Gama de Colores</th>
            </tr>
            <tr>
                <td>{{ $cotizacion->cantidad }}</td>
                <td>{{ $cotizacion->medida_cm }} cm</td>
                <td>{{ $cotizacion->acabado }}</td>
                <td>{{ $cotizacion->colores }}</td>
            </tr>
        </table>

        @if($cotizacion->descripcion_diseno_especial)
            <h4>Detalles Adicionales:</h4>
            <p>{{ $cotizacion->descripcion_diseno_especial }}</p>
        @endif
    </div>
</body>
</html>