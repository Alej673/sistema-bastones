<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Enlace al nuevo CSS unificado para Login/Register -->
    @vite(['resources/css/auth-neumorphism.css'])
</head>
<body>

    <div class="login-card">
        
        <h1 class="brand-title">Arte Titi_Val</h1>
        <p class="brand-subtitle">Portal de Clientes y Taller</p>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="usuario@correo.com">
                </div>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••">
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="remember-group">
                <input type="checkbox" id="remember_me" name="remember">
                <label class="remember-label" for="remember_me">Recordar mis credenciales</label>
            </div>

            <button type="submit" class="btn-submit">
                Iniciar Sesión <i class="fa-solid fa-arrow-right"></i>
            </button>
            
            <div class="register-prompt">
                ¿No tienes una cuenta para cotizar? <br>
                <a href="{{ route('register') }}">Crea una cuenta aquí</a>
            </div>

        </form>
    </div>

</body>
</html>