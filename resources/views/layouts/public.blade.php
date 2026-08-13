<!-- resources/views/layouts/public.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arte Titi_Val - Catálogo Oficial')</title>
    
    <!-- Fuentes e Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tu CSS Personalizado -->
    @vite(['resources/css/welcome.css'])
    @stack('css')
</head>
<body data-csrf="{{ csrf_token() }}" 
      data-home-url="{{ url('/') }}" 
      data-telefono="{{ $ajustesTaller['telefono_whatsapp'] ?? '593999856725' }}">

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="{{ route('home') }}" class="nav-brand">
            <i class="fa-solid fa-leaf"></i> Arte Titi_Vals
        </a>

        <button type="button" class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="navCollapse">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="nav-collapse" id="navCollapse">
            <div class="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarCatalogo" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Catálogo <i class="fa-solid fa-chevron-down nav-caret"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarCatalogo" style="background-color: #1b0f28; border-color: #d4af37;">
                            <li><a class="dropdown-item" href="{{ route('catalogo.index') }}" style="color: #d4af37;">Ver Todo</a></li>
                            <li><hr class="dropdown-divider" style="border-color: rgba(212, 175, 55, 0.3);"></li>
                            <li><a class="dropdown-item text-light" href="{{ route('catalogo.categoria', 'baston') }}">Bastones</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('catalogo.categoria', 'lazo') }}">Lazos</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('catalogo.categoria', 'manualidad') }}">Manualidades</a></li>
                            <li><a class="dropdown-item text-light" href="{{ route('catalogo.categoria', 'aplique') }}">Apliques / Flores</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Nosotros</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Contacto</a>
                    </li>
                </ul>
            </div>

            <div class="nav-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline">Ingresar</a>
                    <a href="{{ route('register') }}" class="btn btn-solid">Registrarse</a>
                @endguest

                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('dashboard') }}" class="btn btn-solid">Panel Taller</a>
                    @else
                        <a href="{{ route('cliente.dashboard') }}" class="btn btn-outline">Mis Pedidos</a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline nav-logout-btn" style="padding: 10px 15px;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="footer-section" style="background-color: #1b0f28; color: #f8f9fa; padding: 60px 0 20px; border-top: 3px solid var(--color-oro);">
        <div class="container">
            <div class="row gy-4">
                
                <!-- Columna 1: Marca y Descripción -->
                <div class="col-lg-4 col-md-6">
                    <h4 style="color: var(--color-oro); font-family: 'Playfair Display', serif; margin-bottom: 20px;">
                        <i class="fa-solid fa-leaf"></i> Arte Titi_Val
                    </h4>
                    <p style="color: #adb5bd; font-size: 0.95rem; line-height: 1.6;">
                        Confección artesanal de bastones especializados para bastoneras. Diseños únicos, elaborados con dedicación y adaptados a los colores de tu institución.
                    </p>
                </div>

                <!-- Columna 2: Enlaces Rápidos -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-4">Enlaces Rápidos</h5>
                    <ul class="list-unstyled" style="line-height: 2.2;">
                        <li><a href="#" class="text-decoration-none" style="color: #adb5bd; transition: 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#adb5bd'">Inicio</a></li>
                        <li><a href="#destacados" class="text-decoration-none" style="color: #adb5bd; transition: 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#adb5bd'">Opciones Destacadas</a></li>
                        <li><a href="{{ url('/catalogo') }}" class="text-decoration-none" style="color: #adb5bd; transition: 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#adb5bd'">Catálogo Completo</a></li>
                        <li><a href="{{ route('login') }}" class="text-decoration-none" style="color: #adb5bd; transition: 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#adb5bd'">Acceso Clientes</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Contacto y Redes -->
                <div class="col-lg-4 col-md-12">
                    <h5 class="text-white mb-4">Contáctanos</h5>
                    <ul class="list-unstyled" style="line-height: 2; color: #adb5bd;">
                        <li>
                            <i class="fa-solid fa-location-dot me-2" style="color: var(--color-lila-fuerte);"></i> Quito, Ecuador
                        </li>
                        <li>
                            <i class="fa-brands fa-whatsapp me-2" style="color: #25D366; font-size: 1.1rem;"></i> 
                            
                            @php
                                // 1. Extraemos el número bruto del arreglo o usamos el valor por defecto
                                $numeroBruto = $ajustesTaller['telefono_whatsapp'] ?? '593999856725';
                                
                                // 2. Limpiamos espacios y guiones, y quitamos el '0' o '+' al inicio si existe
                                $numeroLimpio = preg_replace('/[^0-9]/', '', $numeroBruto);
                                if (str_starts_with($numeroLimpio, '0')) {
                                    $numeroLimpio = '593' . substr($numeroLimpio, 1);
                                }
                            @endphp

                            <!-- El enlace wa.me abre directamente WhatsApp con el número estandarizado -->
                            <a href="https://wa.me/{{ $numeroLimpio }}" 
                            target="_blank" 
                            class="text-decoration-none" 
                            style="color: #adb5bd; transition: 0.3s;" 
                            onmouseover="this.style.color='#25D366'" 
                            onmouseout="this.style.color='#adb5bd'">
                                {{ $ajustesTaller['telefono_whatsapp'] ?? '099 985 6725' }}
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-4">
                        <!-- Botón de TikTok -->
                        <a href="https://www.tiktok.com/@titi_val_0905?lang=es-419" target="_blank" class="btn btn-outline-light rounded-circle me-2 d-inline-flex justify-content-center align-items-center" style="width: 45px; height: 45px; border-color: rgba(255,255,255,0.2);">
                            <i class="fa-brands fa-tiktok fs-5"></i>
                        </a>
                        <!-- Botón de Facebook (Opcional, puedes borrarlo si no tiene) -->
                        <a href="#" target="_blank" class="btn btn-outline-light rounded-circle me-2 d-inline-flex justify-content-center align-items-center" style="width: 45px; height: 45px; border-color: rgba(255,255,255,0.2);">
                            <i class="fa-brands fa-facebook-f fs-5"></i>
                        </a>
                        <!-- Botón de Instagram (Opcional) -->
                        <a href="#" target="_blank" class="btn btn-outline-light rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 45px; height: 45px; border-color: rgba(255,255,255,0.2);">
                            <i class="fa-brands fa-instagram fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <hr class="mt-5 mb-4" style="border-color: rgba(255,255,255,0.1);">

            <!-- Copyright y Créditos -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p style="color: #6c757d; font-size: 0.85rem;">&copy; {{ date('Y') }} Arte Titi_Val. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p style="color: #6c757d; font-size: 0.85rem;">
                        Desarrollado por <span style="color: var(--color-oro);">Alejandro Larco</span>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts de Bootstrap y tuyos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Importamos SweetAlert2 si no estaba en public.blade.php -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>

    @vite(['resources/js/catalogo.js'])
    @stack('js')

@include('catalogo.partials.modal-consulta')

<!-- Notificaciones Flash del Sistema Interno (Laravel Session) -->
@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '¡Completado!',
            text: '{{ session('success') }}',
            confirmButtonColor: 'var(--color-lila-fuerte)',
            background: 'var(--color-fondo-claro)',
            color: 'var(--color-texto-principal)',
            customClass: { popup: 'titi-modal-alert' }
        });
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Algo salió mal',
            text: '{{ session('error') }}',
            confirmButtonColor: '#c0392b',
            background: 'var(--color-fondo-claro)',
            color: 'var(--color-texto-principal)'
        });
    });
</script>
@endif

<!-- Errores de Validación de Laravel ($errors) -->
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Revisa tus datos',
            html: `
                <ul class="text-start mb-0" style="color: var(--color-texto-mutado); font-size: 0.9rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            `,
            confirmButtonColor: 'var(--color-lila-fuerte)',
            background: 'var(--color-fondo-claro)',
            color: 'var(--color-texto-principal)'
        });
    });
</script>
@endif
</body>
</html>