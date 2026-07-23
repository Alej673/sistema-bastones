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

    <!-- Tu CSS Personalizado -->
    @vite(['resources/css/welcome.css'])
    @stack('css')
</head>
<body>

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

    <!-- EL HUECO MÁGICO: Aquí se inyectará el contenido de cada página -->
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
                            <!-- El enlace wa.me abre directamente WhatsApp -->
                            <a href="https://wa.me/593999856725" target="_blank" class="text-decoration-none" style="color: #adb5bd; transition: 0.3s;" onmouseover="this.style.color='#25D366'" onmouseout="this.style.color='#adb5bd'">
                                099 985 6725
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-4">
                        <!-- Botón de TikTok -->
                        <a href="https://www.tiktok.com/@USUARIO_DE_TU_MAMA" target="_blank" class="btn btn-outline-light rounded-circle me-2 d-inline-flex justify-content-center align-items-center" style="width: 45px; height: 45px; border-color: rgba(255,255,255,0.2);">
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

<script>
    // Menú de navegación móvil
    document.addEventListener('DOMContentLoaded', function () {
        const navToggle = document.getElementById('navToggle');
        const navCollapse = document.getElementById('navCollapse');

        if (navToggle && navCollapse) {
            navToggle.addEventListener('click', function () {
                const isOpen = navCollapse.classList.toggle('is-open');
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                navToggle.innerHTML = isOpen
                    ? '<i class="fa-solid fa-xmark"></i>'
                    : '<i class="fa-solid fa-bars"></i>';
            });

            navCollapse.querySelectorAll('a, button[type="submit"]').forEach(function (el) {
                // No cerrar el menú móvil si es el toggle de un dropdown (ej. "Catálogo")
                if (el.matches('[data-bs-toggle="dropdown"]')) return;

                el.addEventListener('click', function () {
                    navCollapse.classList.remove('is-open');
                    navToggle.setAttribute('aria-expanded', 'false');
                    navToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
                });
            });

            // Si la pantalla vuelve a ser grande, asegura que el menú quede visible/normal
            window.addEventListener('resize', function () {
                if (window.innerWidth > 860) {
                    navCollapse.classList.remove('is-open');
                    navToggle.setAttribute('aria-expanded', 'false');
                    navToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
                }
            });
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Configuramos el observador de intersección
        const observer = new IntersectionObserver((entries) => {
            let delayCounter = 0; // Para el efecto cascada
            
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    // Si entra en pantalla, le damos un ligero delay matemático
                    setTimeout(() => {
                        entry.target.classList.add('scroll-visible');
                    }, delayCounter * 120); // 120ms entre cada tarjeta
                    
                    delayCounter++;
                    observer.unobserve(entry.target); // Dejamos de observarlo para que no se repita al subir
                }
            });
        }, { 
            threshold: 0.15, // Se activa cuando el 15% de la tarjeta asoma
            rootMargin: "0px 0px -50px 0px" // Un pequeño margen para que se note al hacer scroll
        });

        // Buscamos todo lo que tenga la clase scroll-hidden y lo observamos
        document.querySelectorAll('.scroll-hidden').forEach((el) => observer.observe(el));
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-btn');
    const inputCalificacion = document.getElementById('calificacion_input');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            let rating = this.getAttribute('data-value');
            inputCalificacion.value = rating; // Guardamos el valor en el input oculto

            // Repintamos las estrellas según el clic
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= rating) {
                    s.classList.remove('fa-regular');
                    s.classList.add('fa-solid'); // Pintada
                } else {
                    s.classList.remove('fa-solid');
                    s.classList.add('fa-regular'); // Vacía
                }
            });
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // --- Helper de alertas compactas (toast) ---
    function mostrarToast(icon, titulo, texto = '') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: titulo,
            text: texto,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: { popup: 'titi-toast' },
            background: 'var(--color-fondo-claro)',
            color: 'var(--color-texto-principal)',
            iconColor: icon === 'success' ? 'var(--color-oro)' : '#ff10f0'
        });
    }

    // --- 1. ESTRELLAS INTERACTIVAS (hover + click) ---
    let currentRating = 0;
    const stars = document.querySelectorAll('.star-btn');
    const inputCalificacion = document.getElementById('calificacion_input');

    function pintarEstrellas(rating) {
        stars.forEach(s => {
            const activa = s.getAttribute('data-value') <= rating;
            s.classList.toggle('fa-solid', activa);
            s.classList.toggle('fa-regular', !activa);
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', () => pintarEstrellas(star.getAttribute('data-value')));
        star.addEventListener('mouseout', () => pintarEstrellas(currentRating));
        star.addEventListener('click', function () {
            currentRating = this.getAttribute('data-value');
            inputCalificacion.value = currentRating;
            pintarEstrellas(currentRating);
        });
    });

    // --- 2. ENVÍO DEL FORMULARIO DE COMENTARIO ---
    const formComentario = document.getElementById('form-comentario');

    if (formComentario) {
        formComentario.addEventListener('submit', function (e) {
            e.preventDefault();

            if (currentRating == 0) {
                mostrarToast('warning', 'Faltan estrellas', 'Selecciona una calificación.');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const btnOriginalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Publicando...';
            submitBtn.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw data;
                return data;
            })
            .then(data => {
                if (data.success) {
                    mostrarToast('success', '¡Aporte recibido!', 'Gracias por tu opinión.');
                    formComentario.reset();
                    currentRating = 0;
                    inputCalificacion.value = 0;
                    pintarEstrellas(0);

                    setTimeout(() => {
                        window.location.href = "{{ url('/') }}#comentarios";
                    }, 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const msg = error?.message || 'Hubo un problema al enviar tu comentario.';
                mostrarToast('error', 'Ups...', msg);
            })
            .finally(() => {
                submitBtn.innerHTML = btnOriginalText;
                submitBtn.disabled = false;
            });
        });
    }

    // --- 3. BOTÓN "ÚTIL" (LIKE) ---
    document.querySelectorAll('.btn-util').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!document.getElementById('form-comentario') && !document.body.classList.contains('user-logged')) {
                // Si no hay forma de comentar visible, asumimos visitante sin sesión
            }

            const reviewId = this.getAttribute('data-id');
            const icon = this.querySelector('.icon-heart');
            const counter = this.querySelector('.like-counter');

            fetch(`/comentarios/${reviewId}/like`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 401 || response.status === 419) {
                    mostrarToast('info', 'Inicia sesión', 'Necesitas una cuenta para marcar útil.');
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data || !data.success) return;

                this.classList.toggle('is-liked', data.isLiked);
                counter.classList.toggle('is-liked', data.isLiked);
                icon.classList.toggle('fa-solid', data.isLiked);
                icon.classList.toggle('fa-regular', !data.isLiked);

                // Animación de "pop"
                icon.classList.remove('heart-pop');
                void icon.offsetWidth; // fuerza reflow para reiniciar la animación
                icon.classList.add('heart-pop');

                counter.innerText = data.likesCount;
            })
            .catch(error => console.error('Error al procesar el like:', error));
        });
    });

    // --- 4. BOTÓN RESPONDER (EXCLUSIVO ADMIN, CON MODAL) ---
    document.addEventListener('click', function (e) {
        const btnResponder = e.target.closest('.btn-responder');
        if (!btnResponder) return;

        e.preventDefault();
        const nombreUsuario = btnResponder.getAttribute('data-nombre');

        Swal.fire({
            title: `Responder a ${nombreUsuario}`,
            input: 'textarea',
            inputPlaceholder: 'Escribe tu respuesta oficial del Taller Arte Titi_Val...',
            background: 'var(--color-fondo-claro)',
            color: 'var(--color-texto-principal)',
            confirmButtonColor: 'var(--color-lila-fuerte)',
            cancelButtonColor: 'var(--color-texto-mutado)',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> Publicar Respuesta',
            customClass: { popup: 'titi-reply-modal' },
            inputValidator: (value) => {
                if (!value) return 'Necesitas escribir algo para responder.';
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            const respuestaText = `@${nombreUsuario} - ${result.value}`;
            const formComentario = document.getElementById('form-comentario');

            if (!formComentario) {
                mostrarToast('error', 'No disponible', 'Solo el admin logueado puede responder.');
                return;
            }

            formComentario.querySelector('textarea[name="contenido"]').value = respuestaText;

            currentRating = 5;
            const inputCalif = document.getElementById('calificacion_input');
            if (inputCalif) inputCalif.value = 5;
            pintarEstrellas(5);

            formComentario.querySelector('button[type="submit"]').requestSubmit
                ? formComentario.querySelector('button[type="submit"]').closest('form').requestSubmit()
                : formComentario.dispatchEvent(new Event('submit', { cancelable: true }));
        });
    });

});
</script>
    @stack('scripts')
</body>
</html>