/**
 * gestion-usuarios.js
 * Filtrado asíncrono para la vista "Gestión de Usuarios".
 *
 * Requiere en el Blade:
 *   <form id="gu-filtros" data-endpoint="{{ route('super.usuarios.index') }}">
 *   <div id="gu-tabla-container"> ... @include('super.usuarios._tabla') ... </div>
 *
 * IMPORTANTE (fix #1): el botón [data-gu-clear-filters] debe existir SIEMPRE
 * en el HTML (con el atributo `hidden` puesto por defecto). No lo envuelvas
 * en un @if de Blade: como el AJAX solo reemplaza #gu-tabla-container, el
 * <form> nunca se vuelve a renderizar por el servidor, así que si el botón
 * no estaba en el HTML inicial, este script jamás podrá mostrarlo hasta
 * que el usuario recargue la página a mano.
 *
 * El servidor debe responder SOLO el HTML del partial (_tabla.blade.php)
 * cuando la petición llega por AJAX. Ver nota al final de este archivo.
 */

const FORM_SELECTOR = "#gu-filtros";
const CONTAINER_SELECTOR = "#gu-tabla-container";
const DEBOUNCE_MS = 400;

let abortController = null;
let debounceTimer = null;

// Fix #2: un único dropdown "abierto" a la vez, registrado a nivel global.
// Antes cada enhanceSelect() añadía sus propios listeners de click/keydown
// al document; si el contenedor se reemplazaba (AJAX) mientras un dropdown
// seguía abierto, esos listeners quedaban huérfanos para siempre y se iban
// acumulando cada vez que se filtraba con un select abierto, degradando el
// comportamiento de la página con el uso. Centralizarlo aquí elimina la fuga
// por completo: no hay nada que se pueda quedar "pegado".
let openDropdownWrapper = null;

function getContainer() {
    return document.querySelector(CONTAINER_SELECTOR);
}

function closeOpenDropdown() {
    if (!openDropdownWrapper) return;
    openDropdownWrapper.classList.remove("is-open");
    const trigger = openDropdownWrapper.querySelector(".gu-dropdown__trigger");
    if (trigger) trigger.setAttribute("aria-expanded", "false");
    openDropdownWrapper = null;
}

function showLoading(container) {
    let overlay = container.querySelector(".gu-loading-overlay");
    if (!overlay) {
        overlay = document.createElement("div");
        overlay.className = "gu-loading-overlay";
        overlay.innerHTML = '<div class="gu-spinner" role="status" aria-label="Cargando"></div>';
        container.style.position = "relative";
        container.prepend(overlay);
    }
    overlay.classList.add("is-active");
}

function hideLoading(container) {
    const overlay = container.querySelector(".gu-loading-overlay");
    if (overlay) overlay.classList.remove("is-active");
}

async function fetchUsuarios(url) {
    const container = getContainer();
    if (!container) return;

    // Cancela una petición anterior si el usuario sigue escribiendo/filtrando
    if (abortController) abortController.abort();
    abortController = new AbortController();

    // Si había un select personalizado abierto en la tabla vieja, ciérralo
    // antes de destruir su DOM (ver fix #2 arriba).
    closeOpenDropdown();

    showLoading(container);

    try {
        const response = await fetch(url, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "text/html",
            },
            signal: abortController.signal,
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const html = await response.text();

        // Si había un modal de confirmación abierto, ciérralo antes de borrar
        // su HTML del DOM: si no, Bootstrap deja el fondo oscuro (backdrop)
        // pegado al <body> para siempre, aunque el modal ya no exista.
        const openModal = container.querySelector(".modal.show");
        if (openModal && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getInstance(openModal)?.hide();
        }
        document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
        document.body.classList.remove("modal-open");
        document.body.style.removeProperty("overflow");
        document.body.style.removeProperty("padding-right");

        container.innerHTML = html;

        // Los selects de "Rol Asignado" son nuevos en cada respuesta: hay que
        // volver a convertirlos en dropdown personalizado.
        enhanceAllSelects(container);

        // Actualiza la URL sin recargar, para que se pueda compartir/recargar el filtro
        window.history.pushState({ gu: true }, "", url);

        // El form vive fuera de #gu-tabla-container, pero su estado de
        // "hay filtros activos" puede haber cambiado (por ejemplo tras
        // usar paginación con filtros en la URL), así que lo resincronizamos.
        const form = document.querySelector(FORM_SELECTOR);
        if (form) updateClearButtonVisibility(form);
    } catch (error) {
        if (error.name === "AbortError") return; // Petición cancelada intencionalmente
        console.error("Error al filtrar usuarios:", error);
        container.innerHTML = `
            <div class="gu-empty">
                <i class="fa-solid fa-triangle-exclamation"></i>
                No se pudo cargar la lista de usuarios. Intenta de nuevo.
            </div>`;
    } finally {
        hideLoading(getContainer() ?? container);
    }
}

function buildUrlFromForm(form) {
    const endpoint = form.dataset.endpoint || form.action;
    const params = new URLSearchParams(new FormData(form));
    // Limpia parámetros vacíos para no ensuciar la URL
    [...params.keys()].forEach((key) => {
        if (!params.get(key)) params.delete(key);
    });
    const query = params.toString();
    return query ? `${endpoint}?${query}` : endpoint;
}

function submitFiltersNow(form) {
    fetchUsuarios(buildUrlFromForm(form));
}

function submitFiltersDebounced(form) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => submitFiltersNow(form), DEBOUNCE_MS);
}

function updateClearButtonVisibility(form) {
    const clearBtn = form.querySelector("[data-gu-clear-filters]");
    if (!clearBtn) return;

    const formData = new FormData(form);
    const hasActiveFilter = [...formData.entries()].some(([key, value]) => {
        if (!value) return false;
        // "recientes" es el valor por defecto de "orden": no cuenta como filtro activo
        if (key === "orden") return value !== "recientes";
        return true;
    });

    clearBtn.hidden = !hasActiveFilter;
}

function attachFormListeners(form) {
    // Envío manual (botón "Filtrar")
    form.addEventListener("submit", (event) => {
        event.preventDefault();
        clearTimeout(debounceTimer);
        submitFiltersNow(form);
    });

    // Búsqueda con debounce mientras el usuario escribe
    const searchInput = form.querySelector('[name="buscar"]');
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            updateClearButtonVisibility(form);
            submitFiltersDebounced(form);
        });
    }

    // Los selects filtran de inmediato al cambiar
    form.querySelectorAll("select").forEach((select) => {
        select.addEventListener("change", () => {
            updateClearButtonVisibility(form);
            submitFiltersNow(form);
        });
    });

    // Botón "borrador": limpia el input y todos los selects (incluido su
    // dropdown personalizado, vía el evento "change" que dispara enhanceSelect)
    const clearBtn = form.querySelector("[data-gu-clear-filters]");
    if (clearBtn) {
        clearBtn.addEventListener("click", (event) => {
            event.preventDefault();
            clearTimeout(debounceTimer);

            if (searchInput) searchInput.value = "";

            form.querySelectorAll("select").forEach((select) => {
                select.selectedIndex = 0;
                select.dispatchEvent(new Event("change", { bubbles: true }));
            });

            updateClearButtonVisibility(form);
            fetchUsuarios(form.dataset.endpoint || form.action);
        });
    }
}

/**
 * Delegación de eventos sobre el contenedor de la tabla, porque su contenido
 * se reemplaza en cada fetch: cubre paginación, cambio de rol y ban/restaurar.
 */
function attachContainerDelegation(container) {
    container.addEventListener("click", (event) => {
        const pageLink = event.target.closest(".gu-pagination-wrap a.page-link");
        if (pageLink && pageLink.href) {
            event.preventDefault();
            fetchUsuarios(pageLink.href);
        }
    });

    // Los clics de paginación son la única delegación que necesita vivir
    // aquí, porque la paginación se regenera en cada respuesta AJAX.
}

/* ============================================================
 * Dropdown personalizado
 * Envuelve cada <select class="gu-select"> con un botón + lista
 * estilizados. El <select> original se mantiene oculto pero
 * funcional, así el formulario se sigue enviando igual y el
 * listener de "change" que ya existe sigue disparando el filtro.
 * ============================================================ */
function enhanceSelect(select) {
    if (select.dataset.guEnhanced) return;
    select.dataset.guEnhanced = "true";

    const wrapper = document.createElement("div");
    wrapper.className = "gu-dropdown";
    if (select.disabled) wrapper.classList.add("is-disabled");
    if (select.classList.contains("gu-role-select")) wrapper.classList.add("gu-dropdown--role");

    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "gu-dropdown__trigger";
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-expanded", "false");
    if (select.disabled) trigger.disabled = true;

    const label = document.createElement("span");
    const chevron = document.createElement("i");
    chevron.className = "fa-solid fa-chevron-down gu-dropdown__chevron";
    trigger.append(label, chevron);

    const menu = document.createElement("ul");
    menu.className = "gu-dropdown__menu";
    menu.setAttribute("role", "listbox");

    function syncLabel() {
        const selectedOption = select.options[select.selectedIndex];
        label.textContent = selectedOption ? selectedOption.textContent : "";
    }

    function buildOptions() {
        menu.innerHTML = "";
        [...select.options].forEach((option) => {
            const item = document.createElement("li");
            item.className = "gu-dropdown__option";
            item.setAttribute("role", "option");
            item.dataset.value = option.value;
            if (option.value === select.value) item.classList.add("is-selected");

            const text = document.createElement("span");
            text.textContent = option.textContent;
            item.appendChild(text);

            if (option.value === select.value) {
                const check = document.createElement("i");
                check.className = "fa-solid fa-check";
                item.appendChild(check);
            }

            item.addEventListener("click", () => {
                select.value = option.value;
                select.dispatchEvent(new Event("change", { bubbles: true }));
                closeOpenDropdown();
            });

            menu.appendChild(item);
        });
    }

    trigger.addEventListener("click", () => {
        if (select.disabled) return;

        const isOpen = wrapper.classList.contains("is-open");
        // Cierra cualquier otro dropdown que haya quedado abierto antes de
        // abrir (o simplemente cerrar) este.
        closeOpenDropdown();

        if (!isOpen) {
            wrapper.classList.add("is-open");
            trigger.setAttribute("aria-expanded", "true");
            openDropdownWrapper = wrapper;
        }
    });

    // Mantiene el botón/lista sincronizados si el <select> cambia por código
    // externo (form.reset(), el botón de limpiar filtros, etc.), no solo por clic.
    select.addEventListener("change", () => {
        syncLabel();
        buildOptions();
    });

    select.parentNode.insertBefore(wrapper, select);
    wrapper.append(trigger, menu);
    wrapper.appendChild(select);

    syncLabel();
    buildOptions();
}

function enhanceAllSelects(scope) {
    scope.querySelectorAll("select.gu-select, select.gu-role-select").forEach(enhanceSelect);
}

function initGestionUsuarios() {
    // Evita doble inicialización si el script se llegara a cargar dos veces.
    if (document.body.dataset.guInitialized) return;
    document.body.dataset.guInitialized = "true";

    const form = document.querySelector(FORM_SELECTOR);
    const container = getContainer();
    if (!form || !container) return;

    attachFormListeners(form);
    attachContainerDelegation(container);
    enhanceAllSelects(form);
    enhanceAllSelects(container);
    updateClearButtonVisibility(form);

    // Cierre global de cualquier dropdown personalizado abierto: un solo
    // listener para toda la página en vez de uno por select (fix #2).
    document.addEventListener("click", (event) => {
        if (openDropdownWrapper && !openDropdownWrapper.contains(event.target)) {
            closeOpenDropdown();
        }
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && openDropdownWrapper) {
            const trigger = openDropdownWrapper.querySelector(".gu-dropdown__trigger");
            closeOpenDropdown();
            trigger?.focus();
        }
    });

    // Soporta el botón "atrás/adelante" del navegador
    window.addEventListener("popstate", () => {
        fetchUsuarios(window.location.href);
    });
}

document.addEventListener("DOMContentLoaded", initGestionUsuarios);