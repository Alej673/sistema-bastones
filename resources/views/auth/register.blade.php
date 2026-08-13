<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth-neumorphism.css'])
    <!-- Script de Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer></script>
</head>
<body>

    <div class="login-card">
        
        <h1 class="brand-title">Crear Cuenta</h1>
        <p class="brand-subtitle">Únete para cotizar tus diseños</p>
        
        <form method="POST" action="{{ route('register') }}" id="register-form">
            @csrf
            
            <!-- Input oculto para el token de reCAPTCHA -->
            <input type="hidden" name="recaptcha_token" id="recaptcha_token">

            <!-- ... Tus campos normales (Nombre, Correo, Contraseña, etc.) ... -->
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

            <!-- Alerta de error de reCAPTCHA -->
            @error('recaptcha_token')
                <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm rounded-3" style="font-size: 0.85rem;" role="alert">
                    <i class="fa-solid fa-robot fs-4 me-3"></i>
                    <div>
                        <strong>¡Alerta de Seguridad!</strong><br>
                        {{ $message }}
                    </div>
                </div>
            @enderror

            <button type="submit" class="btn-submit">
                Registrarse <i class="fa-solid fa-arrow-right"></i>
            </button>
            
            <div class="login-prompt">
                ¿Ya tienes una cuenta? <br>
                <a href="{{ route('login') }}">Inicia sesión aquí</a>
            </div>

        </form>
    </div>

    <script>
    document.getElementById('register-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');

        // Desactivar el botón para prevenir el doble envío accidental
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        const siteKey = "{{ config('services.recaptcha.site_key') }}";

        // Salvavidas: si la CDN de Google no ha cargado o falla, se envía al backend
        if (typeof grecaptcha === 'undefined') {
            console.warn('reCAPTCHA no cargó a tiempo. Procediendo al envío.');
            form.submit();
            return;
        }

        grecaptcha.ready(function() {
            grecaptcha.execute(siteKey, {action: 'register'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                form.submit();
            }).catch(function(error) {
                console.error('Error al ejecutar reCAPTCHA:', error);
                // Si la API falla, enviamos el formulario para evaluación del backend
                form.submit();
            });
        });
    });
    </script>
</body>
</html>