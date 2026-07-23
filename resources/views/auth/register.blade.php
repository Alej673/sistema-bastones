<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth-neumorphism.css'])
</head>
<body>

    <div class="login-card">
        
        <h1 class="brand-title">Crear Cuenta</h1>
        <p class="brand-subtitle">Únete para cotizar tus diseños</p>
        
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Campo Nombre -->
            <div class="form-group">
                <label for="name" class="form-label">Nombre Completo</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Tu nombre">
                </div>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campo Correo -->
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

            <!-- Campo Contraseña -->
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

            <!-- Campo Confirmar Contraseña -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Registrarse <i class="fa-solid fa-user-plus"></i>
            </button>
            
            <div class="login-prompt">
                ¿Ya tienes una cuenta? <br>
                <a href="{{ route('login') }}">Inicia sesión aquí</a>
            </div>

        </form>
    </div>

</body>
</html>