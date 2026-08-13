<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth-neumorphism.css'])
    <!-- Script de Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer></script>
</head>
<body>

    <div class="login-card">

        <h1 class="brand-title">Arte Titi_Val</h1>
        <p class="brand-subtitle">Portal de Clientes y Taller</p>

        <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <input type="hidden" name="recaptcha_token" id="recaptcha_token">

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="usuario@correo.com">
                </div>
                @error('email')
                    <span class="error-message">{!! $message !!}</span>
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

            @error('recaptcha_token')
                <div class="alert alert-danger d-flex align-items-center mb-3 border-0 shadow-sm rounded-3" style="font-size: 0.85rem;" role="alert">
                    <i class="fa-solid fa-robot fs-4 me-3"></i>
                    <div>
                        <strong>¡Alerta de Seguridad!</strong><br>
                        {{ $message }}
                    </div>
                </div>
            @enderror

            <!-- Grupo de acciones: mismo espaciado vertical para todos los botones -->
            <div class="auth-actions">
                <button type="submit" class="btn-submit">
                    Iniciar Sesión <i class="fa-solid fa-arrow-right"></i>
                </button>

                <div class="divider">
                    <span>o ingresa rápidamente con</span>
                </div>

                <a href="{{ route('google.login') }}" class="btn-google">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="m6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    <span>Continuar con Google</span>
                </a>
            </div>

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

    <script>
        document.getElementById('login-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const form = this;
            const submitBtn = form.querySelector('.btn-submit');

            // Prevenir doble clic
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Verificando... <i class="fa-solid fa-spinner fa-spin"></i>';
            }

            const siteKey = "{{ config('services.recaptcha.site_key') }}";

            // Fallback si Google no carga
            if (typeof grecaptcha === 'undefined') {
                form.submit();
                return;
            }

            grecaptcha.ready(function() {
                // Ojo: action ahora es 'login'
                grecaptcha.execute(siteKey, {action: 'login'}).then(function(token) {
                    document.getElementById('recaptcha_token').value = token;
                    form.submit();
                }).catch(function(error) {
                    form.submit();
                });
            });
        });
    </script>

</body>
</html>