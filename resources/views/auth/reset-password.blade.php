<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/auth-neumorphism.css'])
</head>
<body>

    <div class="login-card">

        <h1 class="brand-title">Arte Titi_Val</h1>
        <p class="brand-subtitle">Crea tu nueva contraseña</p>

        <p class="reset-context">
            <i class="fa-solid fa-circle-info"></i>
            <span>Estás restableciendo el acceso para <strong>{{ $request->email }}</strong>. Elige una contraseña nueva para continuar.</span>
        </p>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $request->email) }}" required autofocus readonly>
                </div>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggle-icon-1')">
                        <i class="fa-solid fa-eye" id="toggle-icon-1"></i>
                    </button>
                </div>
                <span class="field-hint">Mínimo 8 caracteres.</span>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                    <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', 'toggle-icon-2')">
                        <i class="fa-solid fa-eye" id="toggle-icon-2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit btn-submit--spaced">
                Guardar Contraseña <i class="fa-solid fa-floppy-disk"></i>
            </button>

            <div class="auth-prompt">
                <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Volver a Iniciar Sesión</a>
            </div>

        </form>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>

</body>
</html>