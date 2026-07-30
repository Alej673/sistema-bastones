/**
 * catalogo.js
 */

// ============================================================
// CONFIG — valores que antes venían inyectados por Blade
// ============================================================
const CSRF_TOKEN = document.body.dataset.csrf;
const HOME_URL = document.body.dataset.homeUrl;

// Número de WhatsApp del taller
const TELEFONO_TALLER = document.body.dataset.telefono; 


// ============================================================
// HELPER GLOBAL — Toast compacto con SweetAlert2
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
// ============================================================

// Variable privada del módulo: guarda la instancia del modal de Bootstrap
let modalConsulta = null;

// Se expone a window porque se invoca desde un onclick="" en el HTML de Blade
window.abrirConsultaRapida = function (titulo, nivel, tamano, imagen, categoria) { // <- 1. NUEVO PARÁMETRO AQUÍ
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
    document.getElementById('mc-producto-titulo').value = titulo;
    document.getElementById('mc-producto-imagen').value = imagen;

    // 2. NUEVO: Inyectar la categoría en el campo oculto del modal
    const inputCategoria = document.getElementById('mc-producto-categoria');
    if (inputCategoria) {
        // Si por alguna razón llega vacío, le ponemos 'na' por defecto
        inputCategoria.value = categoria || 'na'; 
    }

    // Resetear formulario y guardar referencia del producto actual
    const form = document.getElementById('formConsultaRapida');
    if (form) {
        form.reset();
        form.dataset.productoActual = titulo;
    }

    modalConsulta.show();
};

// Función para usuarios NO logueados (Directo a WhatsApp sin modal)
window.enviarWhatsAppDirecto = function(titulo, imagenUrl) {
    const textoWhatsapp = 
        `👋 Hola Taller Arte Titi_Val.%0A%0A` +
        `Me interesa el modelo: *${titulo}*.%0A` +
        `🔗 Link de referencia: ${imagenUrl}%0A%0A` +
        `¿Me podrían dar más información o ayudarme a cotizar este diseño?`;

    window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');
};

// Botones internos del modal: sí pueden enlazarse normalmente en DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {

    // Acción: Enviar a WhatsApp
    document.getElementById('btnConsultarWhatsapp')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('clienteNombre').value.trim();
        const mensaje = document.getElementById('clienteMensaje').value.trim();
        
        // Capturamos el producto y la URL de la imagen de los inputs ocultos que creamos antes
        const producto = document.getElementById('mc-producto-titulo').value || document.getElementById('formConsultaRapida').dataset.productoActual;
        const urlImagen = document.getElementById('mc-producto-imagen').value;

        if (!nombreCliente || !mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor ingresa tu nombre y lo que deseas consultar para escribirte por WhatsApp.',
                confirmButtonColor: '#25D366' // Color de WhatsApp para este botón
            });
            return;
        }

        // Construimos un mensaje formateado con saltos de línea (%0A) y negritas de WhatsApp (*)
        const textoWhatsapp = 
            `👋 Hola Taller Arte Titi_Val, soy *${nombreCliente}*.%0A%0A` +
            `Me interesa personalizar este modelo de su catálogo:%0A` +
            `*${producto}*%0A` +
            `🔗 Link de referencia: ${urlImagen}%0A%0A` +
            `📝 *Mi consulta es:*%0A` +
            `${mensaje}`;

        // Abrimos WhatsApp en una nueva pestaña
        window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');
        
        // Ocultamos el modal para limpiar la pantalla
        if (typeof modalConsulta !== 'undefined' && modalConsulta) {
            modalConsulta.hide();
        }
    });

    // --- ACCIÓN: ENVIAR AL SISTEMA DESDE EL MODAL DEL CATÁLOGO ---
        document.getElementById('btnGuardarSistema')?.addEventListener('click', function () {
            // 1. Capturamos los datos básicos
            const nombreCliente = document.getElementById('clienteNombre').value.trim();
            const telefonoCliente = document.getElementById('clienteTelefono').value.trim();
            const mensaje = document.getElementById('clienteMensaje').value.trim();
            const producto = document.getElementById('mc-producto-titulo').value;
            const urlImagenCatalogo = document.getElementById('mc-producto-imagen').value;
            
            // NUEVO 1: Capturamos la categoría desde el input oculto que creamos
            const categoriaProducto = document.getElementById('mc-producto-categoria')?.value || 'na';

            // 2. Validación rápida
            if (!nombreCliente || !telefonoCliente || !mensaje) {
                mostrarToast('warning', 'Campos incompletos', 'Por favor llena tu nombre, teléfono y consulta.');
                return;
            }

            // 3. Cambiamos el botón a estado "Cargando"
            const btn = this;
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enviando...';
            btn.disabled = true;

            // Usamos un try-catch para que, si algo falla internamente, el botón se destrabe
            try {
                // Leemos los badges visuales (con protección por si acaso no existen)
                const badgeTamano = document.getElementById('mc-tamano');
                const badgeNivel = document.getElementById('mc-nivel');
                
                const medidaBadge = badgeTamano ? badgeTamano.innerText.trim() : 'na';
                const nivelBadge = badgeNivel ? badgeNivel.innerText.trim() : 'na';
                const esBaston = (categoriaProducto.toLowerCase() === 'baston' || categoriaProducto.toLowerCase() === 'bastones');

                // 4. Armamos el paquete de datos para Laravel
                const formData = new FormData();
                formData.append('nombre', nombreCliente);
                formData.append('telefono', telefonoCliente);
                formData.append('cantidad', 1);
                formData.append('medida_cm', medidaBadge);
                formData.append('acabado', esBaston ? 'Plata' : 'na');
                formData.append('colores', `Basado en modelo: ${producto}`);
                formData.append('descripcion_diseno_especial', mensaje);
                
                // NUEVO 2: Adjuntamos la categoría al paquete que viaja a Laravel
                formData.append('categoria', categoriaProducto);

                // Si hay URL de imagen, la adjuntamos
                if (urlImagenCatalogo) {
                    formData.append('imagen_catalogo_url', urlImagenCatalogo);
                }

                // 5. Enviamos a la ruta /cotizar
                fetch('/cotizar', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // Fundamental para que Laravel devuelva JSON
                    }
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then(data => {
                    // Éxito: Ocultamos el modal y mostramos la alerta
                    if (typeof modalConsulta !== 'undefined' && modalConsulta) {
                        modalConsulta.hide();
                    }
                    Swal.fire({
                        icon: 'success',
                        title: '¡Consulta Registrada!',
                        text: 'Tu solicitud de personalización ha sido enviada al taller.',
                        confirmButtonColor: 'var(--color-lila-fuerte)',
                        background: 'var(--color-fondo-claro)',
                        color: 'var(--color-texto-principal)'
                    });
                })
                .catch(error => {
                    // Error de servidor o validación
                    console.error('Error al guardar consulta en el servidor:', error);
                    mostrarToast('error', 'Ups...', 'No se pudo registrar la consulta. Revisa los datos.');
                })
                .finally(() => {
                    // 6. PASE LO QUE PASE, devolvemos el botón a la normalidad
                    btn.innerHTML = textoOriginal;
                    btn.disabled = false;
                });

            } catch (err) {
                // Si hay un error de sintaxis en el JS, lo atrapamos aquí para que no se congele
                console.error('Error interno de JavaScript:', err);
                mostrarToast('error', 'Error', 'Ocurrió un problema procesando el formulario.');
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
            }
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
.       then(data => {
            if (!data.success) return;

            // 1. Capturamos todos los valores directamente desde el formulario HTML
            const nombre = form.querySelector('[name="nombre"]')?.value.trim() || 'Cliente';
            const cantidad = form.querySelector('#cantidad')?.value || '1';
            const medida = form.querySelector('[name="medida_cm"]')?.value;
            const acabado = form.querySelector('[name="acabado"]')?.value;
            const colores = form.querySelector('[name="colores"]')?.value.trim();
            const detalles = form.querySelector('[name="descripcion_diseno_especial"]')?.value.trim();
            
            // 2. Lógica para el plural
            const textoCantidad = cantidad === '1' ? '1 bastón' : `${cantidad} bastones`;
            
            // 3. Construimos bloques individuales con emojis y saltos de línea (%0A)
            const medidaTexto = medida ? `📏 *Medida:* ${medida} cm%0A` : '';
            const acabadoTexto = acabado ? `✨ *Acabado del tubo:* ${acabado}%0A` : '';
            const coloresTexto = colores ? `🎨 *Colores:* ${colores}%0A` : '';
            const detallesTexto = detalles ? `📝 *Detalles extra:* ${detalles}%0A` : '';
            // La imagen es lo único que sí leemos del servidor (data.url_imagen)
            const imagenTexto = data.url_imagen ? `%0A🔗 *Referencia visual:* ${data.url_imagen}%0A` : '';

            // 4. Unimos todo con el formato limpio
            const textoWhatsapp = 
                `👋 Hola Taller Arte Titi_Val, soy *${nombre}*.%0A%0A` +
                `Quiero cotizar *${textoCantidad}* diseñado desde cero con estas características:%0A%0A` +
                medidaTexto +
                acabadoTexto +
                coloresTexto +
                detallesTexto +
                imagenTexto;

            // 5. Enviamos a WhatsApp
            const urlWhatsApp = `https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp.trim()}`;
            window.open(urlWhatsApp, '_blank');

            mostrarToast('success', '¡Genial!', 'Te redirigimos a WhatsApp.');
            
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
