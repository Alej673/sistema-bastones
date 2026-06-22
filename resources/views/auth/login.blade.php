<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro - Sistema de Gestión y Kardex</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* 1. CONFIGURACIÓN BASE Y FONDO ULTRA-MINIMALISTA */
        :root {
            --color-amatista: #6e3553; /* Púrpura base aclarado */
            --color-amatista-pupa: #C8A9B9; /* Púrpura pálido para bordes */
            --color-dorado: #d0a976; /* Dorado base aclarado */
            --color-fondo: #d5d5d5; /* Fondo blanco roto */
        }

        body {
            background: linear-gradient(135deg, var(--color-fondo) 0%, #FFFFFF 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif; /* Usar una fuente sans-serif limpia */
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Toque sutil de polvo dorado en el fondo */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(#C5A57A 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.15;
            z-index: 1;
        }

        /* 2. TARJETA DE FORMULARIO NÍTIDA Y CENTRADA */
        .login-card {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); /* Sombra ultra sutil */
            width: 100%;
            max-width: 380px;
            padding: 40px;
            z-index: 2;
            text-align: center;
        }

        /* 3. TIPOGRAFÍA DE MARCA ELEGANTE (PERO SUTIL) */
        .brand-title {
            font-family: 'Playfair Display', serif; /* Fuente serif elegante y sutil */
            font-size: 24px;
            color: var(--color-amatista);
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .brand-subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--color-amatista);
            opacity: 0.8;
            margin: 0 0 30px 0;
        }

        /* 4. CAMPOS DE ENTRADA CON ACENTOS SUTILES */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 12px;
            color: var(--color-amatista);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            font-size: 14px;
            color: var(--color-dorado);
            opacity: 0.7;
        }

        .form-control {
            width: 100%;
            padding: 10px 10px 10px 36px;
            font-size: 14px;
            border: 1px solid var(--color-amatista-pupa);
            border-radius: 6px;
            background: #FAFAFC;
            color: #333333;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-amatista);
            box-shadow: 0 0 0 2px rgba(166, 124, 146, 0.1);
        }

        /* 5. BOTÓN DE ENVÍO SÓLIDO Y LIMPIO */
        .btn-submit {
            background: var(--color-amatista);
            color: #FFFFFF;
            border: none;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background-color: #936A7D;
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .btn-icon {
            font-size: 12px;
            color: var(--color-dorado);
        }

        /* 6. ENLACES SECUNDARIOS SÚPER SUTILES EN EL PIE */
        .auth-footer {
            margin-top: 25px;
            font-size: 12px;
            color: #999999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .auth-link {
            color: var(--color-amatista);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        .auth-link:hover {
            color: #c2779a;
        }

        .secondary-link {
            font-weight: 400;
            opacity: 0.7;
        }

        /* Checkbox simple */
        .remember-group {
            margin-bottom: 25px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-label {
            font-size: 12px;
            color: #757575;
            font-weight: 400;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="login-card">
        
        <h1 class="brand-title">Sistema de Gestión y Kardex</h1>
        <p class="brand-subtitle">Acceso Seguro</p>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="email@dominio.com">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                </div>
            </div>

            <div class="remember-group">
                <input type="checkbox" id="remember_me" name="remember">
                <label class="remember-label" for="remember_me">Mantener sesión iniciada</label>
            </div>

            <button type="submit" class="btn-submit">
                Ingresar al Sistema <i class="fa-solid fa-arrow-right-to-bracket btn-icon"></i>
            </button>
            
        </form>
    </div>

</body>
</html>