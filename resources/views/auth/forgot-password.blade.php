<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acceso - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Enlace a tu CSS unificado -->
    @vite(['resources/css/auth-neumorphism.css'])
</head>
<body>

    <div class="login-card">
        
        <h1 class="brand-title">Arte Titi_Val</h1>
        <p class="brand-subtitle">Recuperación de Contraseña</p>

        <div style="text-align: center; margin-bottom: 1.5rem; color: #666; font-size: 0.9rem;">
            ¿Olvidaste tu contraseña? Ingresa el correo con el que te registraste y te enviaremos un enlace para crear una nueva.
        </div>

        <!-- Mensaje de éxito nativo de Laravel -->
        @if (session('status'))
            <div style="background-color: #d1e7dd; color: #0f5132; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 0.85rem;">
                {{ session('status') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="usuario@correo.com">
                </div>
                @error('email')
                    <span class="error-message" style="color: #dc3545; font-size: 0.8rem; margin-top: 5px; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 1.5rem;">
                Enviar enlace <i class="fa-solid fa-paper-plane ms-2"></i>
            </button>
            
            <div class="register-prompt" style="margin-top: 1.5rem;">
                <a href="{{ route('login') }}" style="text-decoration: none; font-weight: 500;">
                    <i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión
                </a>
            </div>

        </form>
    </div>

</body>
</html>