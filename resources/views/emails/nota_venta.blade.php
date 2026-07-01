<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .contenedor { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 8px; }
        .header { background-color: #17a2b8; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .contenido { padding: 20px; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 20px; border-top: 1px solid #eaeaea; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="header">
            <h2>Taller de Bastones</h2>
        </div>
        
        <div class="contenido">
            <p>Hola, <strong>{{ $pedido->cliente_nombre }}</strong>,</p>
            
            <p>Gracias por tu preferencia y por confiar en nuestro trabajo. Adjunto a este correo encontrarás la Nota de Venta correspondiente a tu orden de producción <strong>#{{ $pedido->id }}</strong> por la cantidad de {{ $pedido->cantidad_total_bastones }} bastones.</p>
            
            <p>El total registrado en tu nota de venta es de: <strong>${{ number_format($pedido->costo_total, 2) }}</strong>.</p>
            
            <p>Por favor, revisa el documento adjunto (PDF) para ver los detalles de los materiales y el diseño de tu pedido.</p>
            
            <p>Saludos cordiales,<br>El equipo de Producción</p>
        </div>

        <div class="footer">
            Este es un correo generado automáticamente, por favor no respondas a esta dirección.
        </div>
    </div>
</body>
</html>