<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #1b0f28; padding: 30px 20px; text-align: center; border-bottom: 4px solid #d4af37; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: normal; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .data-box { background-color: #f6e8ff; border-left: 4px solid #9d5ce0; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
        .data-row { margin-bottom: 10px; font-size: 14px; }
        .data-row strong { color: #4a148c; display: inline-block; width: 100px; }
        .message-box { background-color: #ffffff; border: 1px solid #e0e0e0; padding: 20px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap; font-style: italic; color: #555; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #888888; background-color: #f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nuevo Mensaje del Buzón</h1>
        </div>
        
        <div class="content">
            <p>Hola, tienes una nueva notificación desde la página web de <strong>Arte Titi_Val</strong>.</p>
            
            <div class="data-box">
                <div class="data-row"><strong>Remitente:</strong> {{ $datos['nombre'] }}</div>
                <div class="data-row"><strong>Correo:</strong> <a href="mailto:{{ $datos['correo'] }}">{{ $datos['correo'] }}</a></div>
                <div class="data-row"><strong>Teléfono:</strong> {{ $datos['telefono'] ?? 'No proporcionado' }}</div>
                <div class="data-row"><strong>Asunto:</strong> {{ $datos['asunto'] }}</div>
            </div>

            <h3 style="color: #1b0f28; margin-bottom: 5px;">Detalle del Mensaje:</h3>
            <div class="message-box">{{ $datos['mensaje'] }}</div>
        </div>

        <div class="footer">
            Este es un correo automático generado por el sistema de Arte Titi_Val.<br>
            Por favor, utiliza los datos de contacto proporcionados arriba para responder al cliente.
        </div>
    </div>
</body>
</html>