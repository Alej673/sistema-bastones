<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #9D5CE0; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #6B2FA3; }
        .content { margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        .table th { background-color: #f4f4f4; color: #6B2FA3; }
        
        /* Estilos para la sección de la imagen */
        .imagen-container { margin-top: 20px; text-align: center; padding: 15px; border: 1px dashed #9D5CE0; border-radius: 8px; background-color: #fafafa; }
        .imagen-container h4 { color: #6B2FA3; margin-top: 0; }
        .imagen-referencia { max-width: 250px; max-height: 250px; object-fit: contain; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
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
                <th>Modelo / Colores</th>
            </tr>
            <tr>
                <td>{{ $cotizacion->cantidad }}</td>
                <td>{{ $cotizacion->medida_cm }} cm</td>
                <td>{{ $cotizacion->acabado }}</td>
                <td>{{ $cotizacion->colores }}</td>
            </tr>
        </table>

        @if($cotizacion->descripcion_diseno_especial)
            <div style="margin-top: 15px;">
                <h4 style="color: #6B2FA3; margin-bottom: 5px;">Detalles Adicionales:</h4>
                <p style="margin-top: 0; background-color: #f9f9f9; padding: 10px; border-left: 3px solid #9D5CE0;">
                    {{ $cotizacion->descripcion_diseno_especial }}
                </p>
            </div>
        @endif

        <!-- Bloque de la Imagen de Referencia -->
        @if($cotizacion->imagen_path)
            <div class="imagen-container">
                <h4>Imagen de Referencia</h4>
                <!-- Usamos public_path() para que DomPDF encuentre el archivo físico en el servidor -->
                <img src="{{ public_path('storage/' . $cotizacion->imagen_path) }}" alt="Referencia Visual" class="imagen-referencia">
            </div>
        @endif
    </div>
</body>
</html>