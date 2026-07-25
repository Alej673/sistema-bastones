/**
 * catalogo.js
 * ---------------------------------------------------------------------
 * Lógica de front-end de la página de catálogo — Taller Arte Titi_Val.
 * Extraído de los <script> que estaban embebidos en el archivo .blade.php.
 *
 * ⚠️ IMPORTANTE (Blade -> JS puro):
 * Este archivo ya NO pasa por el motor de Blade, así que expresiones como
 * {{ csrf_token() }} o {{ url('/') }} NO se compilan aquí (se enviarían
 * como texto literal). Para conservar el mismo comportamiento, ahora esos
 * valores se leen desde atributos data-* en el <body>.
 *
 * En tu vista Blade, agrega esto al <body>:
 *
 *   <body data-csrf="{{ csrf_token() }}" data-home-url="{{ url('/') }}" data-telefono="{{ $telefonoTaller }}">
 *
 * Y luego incluye este archivo con:
 *   <script src="{{ asset('js/catalogo.js') }}" defer></script>
 * ---------------------------------------------------------------------
 */

// ============================================================
// CONFIG — valores que antes venían inyectados por Blade
// ============================================================
const CSRF_TOKEN = document.body.dataset.csrf;
const HOME_URL = document.body.dataset.homeUrl;

// Número de WhatsApp del taller (antes repetido dos veces en el código,
// y una tercera vez quemado como "593900000000" en el bloque de subida
// de imagen — ya unificado, ver sección 6)
const TELEFONO_TALLER = document.body.dataset.telefono; // TODO: reemplazar con el número real de producción


// ============================================================
// HELPER GLOBAL — Toast compacto con SweetAlert2
// ------------------------------------------------------------
// Antes vivía metido dentro del DOMContentLoaded de la sección 4
// (comentarios), así que solo esa sección podía usarlo. Lo subo al
// scope global del archivo para poder reutilizarlo también en la
// sección 6 (envío del formulario de cotización con imagen).
// ============================================================
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


// ============================================================
// 1. MENÚ DE NAVEGACIÓN MÓVIL
// ============================================================
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


// ============================================================
// 2. ANIMACIÓN DE APARICIÓN AL HACER SCROLL (IntersectionObserver)
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
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


// ============================================================
// 3. ESTRELLAS DE CALIFICACIÓN (versión simple / básica)
// ------------------------------------------------------------
// ⚠️ NOTA: en el blade original este bloque estaba DUPLICADO por otro
// listener casi idéntico más abajo (sección 4.1), que además añade hover
// y guarda "currentRating". Ambos quedan aquí IGUAL que en el original
// para no cambiar el comportamiento, pero significa que un click en una
// estrella dispara los dos listeners (redundante, aunque el resultado
// visual final es el mismo). Si en algún momento quieres que lo limpie
// y deje solo una versión, dímelo y lo hacemos en un paso aparte.
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('.star-btn');
    const inputCalificacion = document.getElementById('calificacion_input');

    stars.forEach(star => {
        star.addEventListener('click', function () {
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


// ============================================================
// 4. COMENTARIOS: estrellas (hover+click), envío del formulario,
//    botón "útil" (like) y botón "responder" (solo admin)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // --- 4.1. ESTRELLAS INTERACTIVAS (hover + click) ---
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

    // --- 4.2. ENVÍO DEL FORMULARIO DE COMENTARIO ---
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
                        // Antes: {{ url('/') }} — ahora tomado del data-home-url del <body>
                        window.location.href = `${HOME_URL}#comentarios`;
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

    // --- 4.3. BOTÓN "ÚTIL" (LIKE) ---
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
                    // Antes: {{ csrf_token() }} — ahora tomado del data-csrf del <body>
                    'X-CSRF-TOKEN': CSRF_TOKEN,
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

    // --- 4.4. BOTÓN RESPONDER (EXCLUSIVO ADMIN, CON MODAL) ---
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


// ============================================================
// 5. MODAL DE CONSULTA RÁPIDA DEL CATÁLOGO
// ------------------------------------------------------------
// FIX (2): antes había DOS bloques que creaban CADA UNO su propia
// instancia de "new bootstrap.Modal(...)" sobre el mismo <div>. Dos
// instancias controlando el mismo modal es lo que rompía el backdrop
// ("Cannot read properties of undefined (reading 'backdrop')").
// Ahora hay UNA sola instancia, creada de forma "lazy" (solo la primera
// vez que se necesita, dentro de abrirConsultaRapida).
//
// FIX (3): con Vite, catalogo.js se carga como módulo ES, así que las
// funciones ya no quedan colgadas en `window` automáticamente. Como el
// HTML llama a la función vía onclick="abrirConsultaRapida(...)",
// necesita existir en el scope global -> se expone con
// "window.abrirConsultaRapida = function (...) {...}".
//
// Si tu botón real en el blade usa otro nombre (ej. "abrirCotizador"),
// solo cambia el nombre después de "window." en la línea de abajo para
// que coincida EXACTO con el que aparece en el onclick="" del HTML.
// ============================================================

// Variable privada del módulo: guarda la instancia del modal de Bootstrap
let modalConsulta = null;

// Se expone a window porque se invoca desde un onclick="" en el HTML de Blade
window.abrirConsultaRapida = function (titulo, nivel, tamano, imagen) {
    const modalElement = document.getElementById('modalConsultaCat');

    // Validación de seguridad por si el HTML del modal no está en esta página
    if (!modalElement) {
        console.error('El HTML del modal #modalConsultaCat no se encuentra en el DOM.');
        return;
    }

    // Inicialización lazy: solo se crea la instancia una vez
    if (!modalConsulta) {
        modalConsulta = new bootstrap.Modal(modalElement);
    }

    // Inyectar datos en el modal
    document.getElementById('mc-nombre').innerText = titulo;
    document.getElementById('mc-nivel').innerText = nivel;
    document.getElementById('mc-tamano').innerText = tamano;
    document.getElementById('mc-imagen').src = imagen;

    // Resetear formulario y guardar referencia del producto actual
    const form = document.getElementById('formConsultaRapida');
    if (form) {
        form.reset();
        form.dataset.productoActual = titulo;
    }

    modalConsulta.show();
};

// Función para usuarios NO logueados (Directo a WhatsApp sin modal)
window.enviarWhatsAppDirecto = function(titulo) {
    const textoWhatsapp = `Hola Taller Arte Titi_Val, me interesa el modelo *${titulo}*. ¿Me podrían dar más información?`;
    window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');
};

// Botones internos del modal: sí pueden enlazarse normalmente en DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {

    // Acción: Enviar a WhatsApp
    document.getElementById('btnConsultarWhatsapp')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('clienteNombre').value.trim();
        const mensaje = document.getElementById('clienteMensaje').value.trim();
        const producto = document.getElementById('formConsultaRapida').dataset.productoActual;

        if (!nombreCliente || !mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor ingresa tu nombre y lo que deseas consultar.',
                confirmButtonColor: '#9D5CE0'
            });
            return;
        }

        const textoWhatsapp = `Hola Taller Arte Titi_Val, soy ${nombreCliente}. Me interesa personalizar el modelo *${producto}*.%0A%0AMi consulta es: ${mensaje}`;

        window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');
        if (modalConsulta) modalConsulta.hide();
    });

    // Acción: Guardar en el Sistema (Mockup para la Fase Actual)
    document.getElementById('btnGuardarSistema')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('clienteNombre').value.trim();
        const mensaje = document.getElementById('clienteMensaje').value.trim();

        if (!nombreCliente || !mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor ingresa tu nombre y lo que deseas consultar.',
                confirmButtonColor: '#9D5CE0'
            });
            return;
        }

        // Aquí a futuro harás un fetch() o $.ajax() a tu Controlador.
        // Por ahora, cerramos el modal y mostramos la alerta de éxito.
        if (modalConsulta) modalConsulta.hide();

        Swal.fire({
            icon: 'success',
            title: '¡Consulta Registrada!',
            html: 'Hemos guardado tu solicitud en nuestro sistema.<br>En esta fase de pruebas, te recomendamos usar también el botón de WhatsApp.',
            confirmButtonColor: '#6B2FA3',
            background: '#FBF6FF',
            color: '#3B2D4A'
        });
    });

});


// ============================================================
// 6. FORMULARIO "DISEÑA TU BASTÓN DESDE CERO"
//    Dropzone de imagen + compresión (DataTransfer trick)
//    Flujos duales: WhatsApp (Fetch) y Sistema Kardex (Submit nativo)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formNuevoBaston');
    if (!form) return;

    const inputImagen = document.getElementById('imagenReferencia');
    const dropzoneLabel = document.getElementById('titiDropzoneLabel');
    const dropzoneEmpty = document.getElementById('titiDropzoneEmpty');
    const dropzonePreview = document.getElementById('titiDropzonePreview');
    const previewImg = document.getElementById('titiPreviewImg');
    const btnQuitarImagen = document.getElementById('titiRemoveImg');

    // Botones de acción
    const btnWhatsapp = document.getElementById('btnCotizarWhatsapp');
    const btnInterno = document.getElementById('btnGuardarInterno');

    // --- 6.1. Comprimir e INYECTAR archivo al input ---
    function procesarArchivo(file) {
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            mostrarToast('warning', 'Formato no válido', 'Solo se aceptan imágenes JPG, PNG o WEBP.');
            return;
        }

        new Compressor(file, {
            quality: 0.6,
            maxWidth: 1200,
            success(result) {
                // TRUCO MAESTRO: Inyectar la imagen optimizada al input HTML.
                // Así, tanto FormData (WhatsApp) como form.submit() (Kardex)
                // tomarán la versión de 200kb y pasarán la validación de Laravel sin colapsar el servidor.
                const dataTransfer = new DataTransfer();
                const fileComprimido = new File([result], file.name, { type: result.type });
                dataTransfer.items.add(fileComprimido);
                inputImagen.files = dataTransfer.files;

                previewImg.src = URL.createObjectURL(result);
                dropzoneEmpty.classList.add('d-none');
                dropzonePreview.classList.remove('d-none');
            },
            error(err) {
                console.error('Error al comprimir:', err.message);
                mostrarToast('error', 'Ups...', 'No se pudo procesar la imagen.');
            },
        });
    }

    inputImagen?.addEventListener('change', e => procesarArchivo(e.target.files[0]));

    // Drag & drop
    ['dragover', 'dragleave', 'drop'].forEach(evento => {
        dropzoneLabel?.addEventListener(evento, e => {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    dropzoneLabel?.addEventListener('dragover', () => dropzoneLabel.classList.add('titi-dropzone-dragover'));
    dropzoneLabel?.addEventListener('dragleave', () => dropzoneLabel.classList.remove('titi-dropzone-dragover'));
    dropzoneLabel?.addEventListener('drop', function (e) {
        dropzoneLabel.classList.remove('titi-dropzone-dragover');
        procesarArchivo(e.dataTransfer.files[0]);
    });

    // Quitar imagen
    btnQuitarImagen?.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        inputImagen.value = ''; // Limpia el input real
        previewImg.src = '';
        dropzonePreview.classList.add('d-none');
        dropzoneEmpty.classList.remove('d-none');
    });

    // --- 6.2. Acción 1: Flujo Ágil (WhatsApp) ---
    btnWhatsapp?.addEventListener('click', function (e) {
        e.preventDefault();

        // Validar que al menos haya puesto teléfono (requerido en HTML)
        if(!form.reportValidity()) return; 

        const textoOriginal = btnWhatsapp.innerHTML;
        btnWhatsapp.innerHTML = '<i class="bi bi-arrow-repeat fa-spin me-2"></i> Procesando...';
        btnWhatsapp.disabled = true;

        // Como inyectamos la imagen con DataTransfer, FormData ya la tiene lista
        const formData = new FormData(form);

        fetch('/cotizacion/whatsapp', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(data => {
            if (!data.success) return;

            const cantidad = form.querySelector('#cantidad')?.value || '1';
            let mensaje = `Hola Taller Arte Titi_Val, quiero cotizar *${cantidad} bastón/es* para bastoneras. `;
            if (data.datos.colores) mensaje += `Colores: ${data.datos.colores}. `;
            if (data.datos.detalles) mensaje += `Detalles: ${data.datos.detalles}. `;
            if (data.url_imagen) mensaje += `Mi referencia visual: ${data.url_imagen}`;

            const urlWhatsApp = `https://wa.me/${TELEFONO_TALLER}?text=${encodeURIComponent(mensaje)}`;
            window.open(urlWhatsApp, '_blank');

            mostrarToast('success', '¡Genial!', 'Te redirigimos a WhatsApp.');
            
            // Si quieres limpiar el form después de mandarlo a WP, descomenta esto:
            // form.reset();
            // btnQuitarImagen.click();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('error', 'Ups...', 'Hubo un problema al procesar la cotización.');
        })
        .finally(() => {
            btnWhatsapp.innerHTML = textoOriginal;
            btnWhatsapp.disabled = false;
        });
    });

    // --- 6.3. Acción 2: Sistema Interno (Kardex) ---
    btnInterno?.addEventListener('click', function (e) {
        e.preventDefault();
        
        // Ejecutamos las validaciones nativas de HTML antes de enviar
        if(form.reportValidity()) {
            // Disparamos el submit al QuoteRequestController. 
            // La foto ya va comprimida en el input gracias al DataTransfer!
            form.submit();
        }
    });
});
