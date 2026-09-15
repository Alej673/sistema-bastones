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
            // No cerrar el menú móvil si es el toggle de un dropdown
            if (el.matches('[data-bs-toggle="dropdown"]')) return;

            el.addEventListener('click', function () {
                navCollapse.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
            });
        });

        // Asegura que el menú quede visible al agrandar la pantalla
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
// 2. ANIMACIÓN DE APARICIÓN AL HACER SCROLL
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    const observer = new IntersectionObserver((entries) => {
        let delayCounter = 0;

        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('scroll-visible');
                }, delayCounter * 120);

                delayCounter++;
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    });

    document.querySelectorAll('.scroll-hidden').forEach((el) => observer.observe(el));
});

// ============================================================
// 3. COMENTARIOS Y CALIFICACIONES (Estrellas, Formulario y Likes)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    
    // --- 3.1. Estrellas Interactivas ---
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
            if (inputCalificacion) inputCalificacion.value = currentRating;
            pintarEstrellas(currentRating);
        });
    });

    // --- 3.2. Envío del Formulario ---
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

    // --- 3.3. Botón Útil (Like) ---
    document.querySelectorAll('.btn-util').forEach(btn => {
        btn.addEventListener('click', function () {
            const reviewId = this.getAttribute('data-id');
            const icon = this.querySelector('.icon-heart');
            const counter = this.querySelector('.like-counter');

            fetch(`/comentarios/${reviewId}/like`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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

                icon.classList.remove('heart-pop');
                void icon.offsetWidth; 
                icon.classList.add('heart-pop');

                counter.innerText = data.likesCount;
            })
            .catch(error => console.error('Error al procesar el like:', error));
        });
    });

    // --- 3.4. Botón Responder (Admin/Superadmin) ---
    document.addEventListener('click', function (e) {
        const btnResponder = e.target.closest('.btn-responder');
        if (!btnResponder) return;

        e.preventDefault();
        const nombreUsuario = btnResponder.getAttribute('data-nombre');
        const comentarioId = btnResponder.getAttribute('data-id');

        Swal.fire({
            title: `Responder a ${nombreUsuario}`,
            input: 'textarea',
            inputPlaceholder: 'Escribe tu respuesta oficial...',
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

            const formComentario = document.getElementById('form-comentario');
            if (!formComentario) {
                mostrarToast('error', 'No disponible', 'Solo el admin logueado puede responder.');
                return;
            }

            const inputPadre = document.getElementById('review_padre_id_input');
            if (inputPadre) inputPadre.value = comentarioId;

            formComentario.querySelector('textarea[name="contenido"]').value = result.value;

            currentRating = 5;
            if (inputCalificacion) inputCalificacion.value = 5;
            pintarEstrellas(5);

            formComentario.querySelector('button[type="submit"]').requestSubmit
                ? formComentario.querySelector('button[type="submit"]').closest('form').requestSubmit()
                : formComentario.dispatchEvent(new Event('submit', { cancelable: true }));
        });
    });

    // --- 3.5. Cargar más comentarios (AJAX incremental) ---
    const btnCargarMas = document.getElementById('btn-cargar-mas');
    const trackComentarios = document.getElementById('comentarios-grid');

    if (btnCargarMas && trackComentarios) {
        btnCargarMas.addEventListener('click', function () {
            const label = btnCargarMas.querySelector('.btn-cargar-mas-label');
            const spinner = btnCargarMas.querySelector('.btn-cargar-mas-spinner');
            const page = trackComentarios.getAttribute('data-next-page');
            const estrellas = trackComentarios.getAttribute('data-estrellas');

            btnCargarMas.disabled = true;
            label.textContent = 'Cargando...';
            spinner.classList.remove('d-none');

            const url = new URL(CARGAR_MAS_URL); // define esta constante global, ver nota abajo
            url.searchParams.set('page', page);
            if (estrellas) url.searchParams.set('estrellas', estrellas);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                // Guardamos qué elementos ya existían antes de insertar
                const antes = new Set(trackComentarios.querySelectorAll('.review-scroll-item'));

                trackComentarios.insertAdjacentHTML('beforeend', data.html);
                trackComentarios.setAttribute('data-next-page', data.next_page);

                // Las tarjetas nuevas son las que NO estaban en "antes"
                trackComentarios.querySelectorAll('.review-scroll-item').forEach(item => {
                    if (!antes.has(item)) {
                        const card = item.querySelector('.scroll-hidden');
                        if (card) {
                            card.classList.remove('scroll-hidden');
                            card.classList.add('scroll-visible');
                        }
                    }
                });

                if (!data.has_more) {
                    document.getElementById('cargar-mas-wrap')?.remove();
                } else {
                    btnCargarMas.disabled = false;
                    label.textContent = 'Cargar más comentarios';
                    spinner.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error al cargar más comentarios:', error);
                mostrarToast('error', 'Ups...', 'No se pudieron cargar más comentarios.');
                btnCargarMas.disabled = false;
                label.textContent = 'Cargar más comentarios';
                spinner.classList.add('d-none');
            });
        });
    }
});

// ============================================================
// 4. FAVORITOS (Botón Corazón del Catálogo)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const botonesFavoritos = document.querySelectorAll('.btn-favorito');

    botonesFavoritos.forEach(boton => {
        boton.addEventListener('click', function() {
            const modeloId = this.getAttribute('data-id');
            const iconoCorazon = this.querySelector('i');
            
            fetch('/favoritos/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ modelo_id: modeloId })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Alternamos las clases mágicamente
                    iconoCorazon.classList.toggle('fa-regular');
                    iconoCorazon.classList.toggle('fa-solid');
                }
            })
            .catch(error => console.error('Error al actualizar favorito:', error));
        });
    });
});


// ============================================================
// 5. MODAL DE CONSULTA RÁPIDA DEL CATÁLOGO
// ============================================================

// Variable privada del módulo: guarda la instancia del modal de Bootstrap
let modalConsulta = null;

// Función auxiliar segura para obtener el token CSRF
function obtenerTokenCSRF() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    console.error('Falta la etiqueta <meta name="csrf-token" content="..."> en el <head> de esta vista.');
    return '';
}

window.abrirConsultaRapida = function (id, titulo, nivel, tamano, imagen, categoria) { 
    const modalElement = document.getElementById('modalConsultaCat');

    if (!modalElement) {
        console.error('El HTML del modal #modalConsultaCat no se encuentra en el DOM.');
        return;
    }

    // Petición silenciosa protegida
    if (id) {
        const token = obtenerTokenCSRF();
        fetch(`/productos/${id}/consultar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) console.log(`Popularidad sumada. Total: ${data.total}`);
        })
        .catch(err => console.error('Error sumando métrica:', err));
    }

    if (!modalConsulta) {
        modalConsulta = new bootstrap.Modal(modalElement);
    }

    document.getElementById('mc-nombre').innerText = titulo;
    document.getElementById('mc-nivel').innerText = nivel;
    document.getElementById('mc-tamano').innerText = tamano;
    document.getElementById('mc-imagen').src = imagen;
    document.getElementById('mc-producto-titulo').value = titulo;
    document.getElementById('mc-producto-imagen').value = imagen;

    const inputCategoria = document.getElementById('mc-producto-categoria');
    if (inputCategoria) {
        inputCategoria.value = categoria || 'na'; 
    }

    const form = document.getElementById('formConsultaRapida');
    if (form) {
        form.reset();
        form.dataset.productoActual = titulo;
    }

    modalConsulta.show();
};

window.enviarWhatsAppDirecto = function(id, titulo, imagenUrl) {
    if (id) {
        const token = obtenerTokenCSRF();
        fetch(`/productos/${id}/consultar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).catch(err => console.error('Error sumando métrica:', err));
    }

    const textoWhatsapp = 
        `👋 Hola Taller Arte Titi_Val.%0A%0A` +
        `Me interesa el modelo: *${titulo}*.%0A` +
        `🔗 Link de referencia: ${imagenUrl}%0A%0A` +
        `¿Me podrían dar más información o ayudarme a cotizar este diseño?`;

    window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');
};

// ============================================================
// FUNCIONES DEL NUEVO MODAL: COTIZACIÓN EXACTA
// ============================================================
window.abrirCotizacionExacta = function (id, titulo, nivel, tamano, imagenUrl, categoria) {
    // 1. Llenar los datos visuales del resumen
    document.getElementById('ce-imagen').src = imagenUrl;
    document.getElementById('ce-nombre').textContent = titulo;
    document.getElementById('ce-nivel').textContent = nivel;
    document.getElementById('ce-tamano').textContent = (tamano && tamano !== 'na') ? tamano + ' cm' : 'N/A';

    // 2. Llenar los campos ocultos para el formulario
    document.getElementById('ce-producto-titulo').value = titulo;
    document.getElementById('ce-producto-imagen').value = imagenUrl;

    // Creamos/actualizamos un campo oculto para la categoría real (fix: ya no se manda "baston" quemado)
    let catInput = document.getElementById('ce-producto-categoria');
    if (!catInput) {
        catInput = document.createElement('input');
        catInput.type = 'hidden';
        catInput.id = 'ce-producto-categoria';
        document.body.appendChild(catInput);
    }
    catInput.value = categoria || 'na';

    // 3. Abrir el modal usando Bootstrap 5
    var modalExacto = new bootstrap.Modal(document.getElementById('modalCotizacionExacta'));
    modalExacto.show();
};

// Botones internos del modal: sí pueden enlazarse normalmente en DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {

    // --- ACCIÓN: ENVIAR A WHATSAPP (CONSULTA RÁPIDA) ---
    document.getElementById('btnConsultarWhatsapp')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('clienteNombre').value.trim();
        const mensaje = document.getElementById('clienteMensaje').value.trim();

        const producto = document.getElementById('mc-producto-titulo').value
            || document.getElementById('formConsultaRapida').dataset.productoActual;
        const urlImagen = document.getElementById('mc-producto-imagen').value;

        if (!nombreCliente || !mensaje) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor ingresa tu nombre y lo que deseas consultar para escribirte por WhatsApp.',
                confirmButtonColor: '#25D366'
            });
            return;
        }

        const textoWhatsapp =
            `👋 Hola Taller Arte Titi_Val, soy *${nombreCliente}*.%0A%0A` +
            `Me interesa personalizar este modelo de su catálogo:%0A` +
            `*${producto}*%0A` +
            `🔗 Link de referencia: ${urlImagen}%0A%0A` +
            `📝 *Mi consulta es:*%0A` +
            `${mensaje}`;

        window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');

        if (typeof modalConsulta !== 'undefined' && modalConsulta) {
            modalConsulta.hide();
        }
    });

    // --- ACCIÓN: ENVIAR AL SISTEMA (CONSULTA RÁPIDA / PERSONALIZADA) ---
    document.getElementById('btnGuardarSistema')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('clienteNombre').value.trim();
        const telefonoCliente = document.getElementById('clienteTelefono').value.trim();
        const mensaje = document.getElementById('clienteMensaje').value.trim();
        const producto = document.getElementById('mc-producto-titulo').value;
        const urlImagenCatalogo = document.getElementById('mc-producto-imagen').value;

        const categoriaProducto = document.getElementById('mc-producto-categoria')?.value || 'na';

        if (!nombreCliente || !telefonoCliente || !mensaje) {
            mostrarToast('warning', 'Campos incompletos', 'Por favor llena tu nombre, teléfono y consulta.');
            return;
        }

        const btn = this;
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Enviando...';
        btn.disabled = true;

        try {
            const badgeTamano = document.getElementById('mc-tamano');
            let medidaBadge = badgeTamano ? badgeTamano.innerText.trim() : 'na';
            if (medidaBadge === '' || medidaBadge === 'N/A') medidaBadge = 'na';

            const esBaston = (categoriaProducto.toLowerCase() === 'baston' || categoriaProducto.toLowerCase() === 'bastones');

            const formData = new FormData();
            formData.append('nombre', nombreCliente);
            formData.append('telefono', telefonoCliente);
            formData.append('cantidad', 1);
            formData.append('medida_cm', medidaBadge);
            formData.append('acabado', esBaston ? 'Plata' : 'na');
            formData.append('colores', `Basado en modelo: ${producto}`);
            formData.append('descripcion_diseno_especial', mensaje);
            formData.append('categoria', categoriaProducto);

            if (urlImagenCatalogo) {
                formData.append('imagen_catalogo_url', urlImagenCatalogo);
            }

            fetch('/cotizar', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw data;
                return data;
            })
            .then(data => {
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
                console.error('Error al guardar consulta en el servidor:', error);
                if (error.errors) console.table(error.errors);
                mostrarToast('error', 'Ups...', 'No se pudo registrar la consulta. Revisa los datos.');
            })
            .finally(() => {
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
            });

        } catch (err) {
            console.error('Error interno de JavaScript:', err);
            mostrarToast('error', 'Error', 'Ocurrió un problema procesando el formulario.');
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }
    });

    // --- ACCIÓN: ENVIAR A WHATSAPP (COTIZACIÓN EXACTA) ---
    document.getElementById('btnConsultarWhatsappExacto')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('ceClienteNombre').value.trim();
        const producto = document.getElementById('ce-producto-titulo').value;
        const urlImagen = document.getElementById('ce-producto-imagen').value;

        if (!nombreCliente) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta tu nombre',
                text: 'Por favor ingresa tu nombre para saber con quién hablamos en WhatsApp.',
                confirmButtonColor: '#25D366'
            });
            return;
        }

        const textoWhatsapp =
            `👋 Hola Taller Arte Titi_Val, soy *${nombreCliente}*.%0A%0A` +
            `Me interesa adquirir este modelo exacto de su catálogo:%0A` +
            `*${producto}*%0A` +
            `🔗 Link de referencia: ${urlImagen}%0A%0A` +
            `¿Me podrían ayudar con el precio y tiempo de entrega?`;

        window.open(`https://wa.me/${TELEFONO_TALLER}?text=${textoWhatsapp}`, '_blank');

        const modalEl = document.getElementById('modalCotizacionExacta');
        const modalInst = bootstrap.Modal.getInstance(modalEl);
        if (modalInst) modalInst.hide();
    });

    // --- ACCIÓN: ENVIAR AL SISTEMA WEB (COTIZACIÓN EXACTA) ---
    document.getElementById('btnGuardarCotizacionExacta')?.addEventListener('click', function () {
        const nombreCliente = document.getElementById('ceClienteNombre').value.trim();
        const telefonoCliente = document.getElementById('ceClienteTelefono').value.trim();
        const producto = document.getElementById('ce-producto-titulo').value;
        const urlImagenCatalogo = document.getElementById('ce-producto-imagen').value;

        // Recuperamos la categoría real que dejó abrirCotizacionExacta() (fix: ya no va "baston" quemado)
        const categoriaProducto = document.getElementById('ce-producto-categoria')?.value || 'na';

        if (!nombreCliente || !telefonoCliente) {
            mostrarToast('warning', 'Campos incompletos', 'Por favor llena tu nombre y teléfono.');
            return;
        }

        const btn = this;
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando...';
        btn.disabled = true;

        try {
            const badgeTamano = document.getElementById('ce-tamano');
            let medidaBadge = badgeTamano ? badgeTamano.innerText.trim().replace(' cm', '') : 'na';
            if (medidaBadge === 'N/A' || medidaBadge === '') medidaBadge = 'na';

            const esBaston = (categoriaProducto.toLowerCase() === 'baston' || categoriaProducto.toLowerCase() === 'bastones');

            const formData = new FormData();
            formData.append('nombre', nombreCliente);
            formData.append('telefono', telefonoCliente);
            formData.append('cantidad', 1);
            formData.append('medida_cm', medidaBadge);
            formData.append('acabado', esBaston ? 'Plata' : 'na');
            formData.append('colores', `Diseño estándar del modelo: ${producto}`);
            formData.append('descripcion_diseno_especial', 'Cotización de modelo exacto de catálogo sin modificaciones extras.');
            formData.append('categoria', categoriaProducto);
            if (urlImagenCatalogo) formData.append('imagen_catalogo_url', urlImagenCatalogo);

            fetch('/cotizar', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw data;
                return data;
            })
            .then(data => {
                const modalEl = document.getElementById('modalCotizacionExacta');
                const modalInst = bootstrap.Modal.getInstance(modalEl);
                if (modalInst) modalInst.hide();

                Swal.fire({
                    icon: 'success',
                    title: '¡Cotización Generada!',
                    text: 'Tu solicitud de este modelo ha sido guardada en el sistema.',
                    confirmButtonColor: 'var(--color-lila-fuerte)',
                    background: 'var(--color-fondo-claro)',
                    color: 'var(--color-texto-principal)'
                });
            })
            .catch(error => {
                console.error('Error al guardar:', error);
                if (error.errors) console.table(error.errors);
                mostrarToast('error', 'Ups...', 'No se pudo generar la cotización.');
            })
            .finally(() => {
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
            });
        } catch (err) {
            console.error('Error JS:', err);
            mostrarToast('error', 'Error', 'Ocurrió un problema procesando la solicitud.');
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
