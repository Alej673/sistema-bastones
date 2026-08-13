<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Correo - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Enlace a tu CSS unificado -->
    @vite(['resources/css/auth-neumorphism.css'])
</head>
<body>

    <div class="login-card" style="max-width: 500px;">
        
        <h1 class="brand-title">Arte Titi_Val</h1>
        <p class="brand-subtitle">Verifica tu correo electrónico</p>

        <div style="text-align: center; margin-bottom: 1.5rem; color: #666; font-size: 0.95rem; line-height: 1.5;">
            ¡Gracias por registrarte! Antes de comenzar a cotizar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el correo, con gusto te enviaremos otro.
        </div>

        <!-- Mensaje de éxito al reenviar el correo -->
        @if (session('status') == 'verification-link-sent')
            <div style="background-color: #d1e7dd; color: #0f5132; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; border: 1px solid #badbcc;">
                <i class="fa-solid fa-circle-check me-1"></i> Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste.
            </div>
        @endif
        
        <div style="display: flex; flex-direction: column; gap: 1rem; align-items: center; margin-top: 1.5rem;">
            
            <!-- Botón de Reenviar -->
            <form method="POST" action="{{ route('verification.send') }}" style="width: 100%;">
                @csrf
                <button type="submit" class="btn-submit" style="margin: 0; width: 100%;">
                    Reenviar correo de verificación <i class="fa-solid fa-paper-plane ms-2"></i>
                </button>
            </form>

            <!-- Botón de Cerrar Sesión -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: transparent; border: none; color: #6a1b9a; font-weight: 600; font-size: 0.9rem; cursor: pointer; text-decoration: underline; padding: 5px;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                    Cerrar sesión
                </button>
            </form>

        </div>
    </div>

</body>
</html>