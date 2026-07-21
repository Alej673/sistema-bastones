<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Arte Titi_Val</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* 1. CONFIGURACIÓN BASE - PALETA DE COLORES */
        :root {
            --color-morado: #a855f7;
            --color-morado-claro: #c084fc;
            --color-morado-oscuro: #5b21b6;
            --color-violeta-boton: #7c3aed;
            --color-fucsia: #e879f9;
            --color-fucsia-oscuro: #86198f;
            
            --color-fondo-medio: #2c1548;
            --color-fondo-base: #1b0f28;
            
            --color-texto: #f5eaff;
            --color-texto-mutado: #b9a8c9;
            --color-error: #f87171;
            --color-exito: #4ade80;
        }

        body {
            background: linear-gradient(135deg, var(--color-fondo-base) 0%, var(--color-fondo-medio) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
            position: relative;
            padding: 20px 0; /* Un poco de padding vertical por si la pantalla es pequeña */
        }

        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            z-index: 1;
            filter: blur(80px);
        }

        body::before {
            width: 450px;
            height: 450px;
            background: rgba(168, 85, 247, 0.25);
            top: -10%;
            left: -10%;
        }

        body::after {
            width: 350px;
            height: 350px;
            background: rgba(134, 25, 143, 0.3);
            bottom: -5%;
            right: -5%;
        }

        /* 2. TARJETA DARK GLASSMORPHISM */
        .login-card {
            background: rgba(44, 21, 72, 0.4); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(192, 132, 252, 0.15);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
            width: 100%;
            max-width: 420px;
            padding: 40px 45px;
            z-index: 2;
            text-align: center;
        }

        /* 3. TIPOGRAFÍA */
        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--color-texto);
            margin: 0 0 8px 0;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
        }

        .brand-subtitle {
            font-size: 12px;
            color: var(--color-texto-mutado);
            margin: 0 0 30px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* 4. CAMPOS DE ENTRADA */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 12px;
            color: var(--color-texto-mutado);
            margin-bottom: 8px;
            font-weight: 500;
            padding-left: 4px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            font-size: 14px;
            color: var(--color-texto-mutado);
            transition: color 0.3s ease;
            opacity: 0.7;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 44px;
            font-size: 14px;
            border: 1px solid rgba(192, 132, 252, 0.05);
            border-radius: 12px;
            background: rgba(27, 15, 40, 0.7); 
            box-shadow: inset 5px 5px 10px rgba(0, 0, 0, 0.6), 
                        inset -3px -3px 8px rgba(192, 132, 252, 0.03);
            color: var(--color-texto);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control::placeholder {
            color: rgba(185, 168, 201, 0.4);
        }

        .form-control:focus {
            outline: none;
            background: rgba(27, 15, 40, 0.9);
            border-color: var(--color-morado);
            box-shadow: inset 6px 6px 12px rgba(0, 0, 0, 0.8), 
                        inset -2px -2px 6px rgba(192, 132, 252, 0.08);
        }

        .form-control:focus + .input-icon,
        .input-group:focus-within .input-icon {
            color: var(--color-fucsia);
            opacity: 1;
        }

        .form-control.is-invalid {
            border-color: rgba(248, 113, 113, 0.5);
            box-shadow: inset 5px 5px 10px rgba(0, 0, 0, 0.6), 
                        inset -3px -3px 8px rgba(248, 113, 113, 0.05);
            background: rgba(248, 113, 113, 0.05);
        }

        .error-message {
            color: var(--color-error);
            font-size: 11px;
            margin-top: 6px;
            display: block;
            font-weight: 500;
            padding-left: 4px;
        }

        /* 5. BOTONES Y ENLACES */
        .btn-submit {
            background: var(--color-violeta-boton);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5), 
                        -2px -2px 8px rgba(192, 132, 252, 0.1);
        }

        .btn-submit:hover {
            background-color: var(--color-morado-oscuro);
        }

        .btn-submit:active {
            box-shadow: inset 4px 4px 10px rgba(0, 0, 0, 0.6), 
                        inset -2px -2px 6px rgba(0, 0, 0, 0.2);
            transform: translateY(2px);
        }

        .login-prompt {
            margin-top: 25px;
            font-size: 13px;
            color: var(--color-texto-mutado);
            border-top: 1px solid rgba(192, 132, 252, 0.1);
            padding-top: 20px;
        }

        .login-prompt a {
            color: var(--color-fucsia);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-prompt a:hover {
            color: var(--color-morado-claro);
            text-decoration: underline;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
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