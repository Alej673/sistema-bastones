<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
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
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="toggle-icon"></i>
                    </button>
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="remember-group">
                <div class="checkbox-wrapper">
                    <input type="checkbox" id="remember_me" name="remember">
                    <label class="remember-label" for="remember_me">Recordarme</label>
                </div>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">
                        ¿Olvidaste tu clave?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-submit">
                Iniciar Sesión <i class="fa-solid fa-arrow-right"></i>
            </button>

            <div class="auth-prompt">
                ¿No tienes una cuenta para cotizar? <br>
                <a href="{{ route('register') }}">Crea una cuenta aquí</a>
            </div>

        </form>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggle-icon');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>

</body>
</html>