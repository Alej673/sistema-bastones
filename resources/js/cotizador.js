// =======================================================
// resources/js/cotizador.js
// =======================================================
//
// MAPA DEL ARCHIVO:
//   1. Importaciones de sub-módulos (lana, cortinas, cintas, diseño)
//   2. Puente de datos Laravel → JS (inventario + rutas AJAX)
//   3. Helpers de inventario y carrito (buscarInsumoPorId, agregarAlCarrito)
//   4. mostrarAlerta()        → pinta el modal de validación reutilizable
//   5. $(document).ready()
//      5.1 Constantes de precios/receta
//      5.2 calcularCotizacion()  → motor principal, recalcula tabla + totales
//      5.3 Triggers de recálculo (inputs, selects, switches)
//      5.4 Guardado de cotización (validación → modal cliente → AJAX)
//      5.5 resetearFormulario()
//      5.6 Envío de nota de venta por correo
//
// REGLA DE ORO: este archivo NO decide precios "a ojo": todo sale de
// PRECIOS_FANTASMA / RECETA o del inventario real (INVENTARIO_POR_ID).
// =======================================================

import { inicializarModuloLana }       from './cotizador/moduloLana.js';
import { inicializarModuloCortinas }   from './cotizador/moduloCortinas.js';
import { inicializarModuloDecoracion } from './cotizador/moduloCintas.js';
import { inicializarModuloDiseno }     from './cotizador/moduloDiseno.js';

// =======================================================
// 2. PUENTE DE DATOS (Laravel → JS)
// =======================================================
// El Blade imprime el inventario completo dentro de un <script type="application/json">
// para evitar otra petición HTTP al cargar la pantalla. Aquí solo lo parseamos.
const nodoInventario = document.getElementById('datos-inventario');
const INVENTARIO     = nodoInventario ? JSON.parse(nodoInventario.textContent) : [];
const RUTAS          = window.KardexConfig.rutas;

// Índice de inventario por ID para búsquedas O(1).
// Antes cada INVENTARIO.find(item => item.id == X) recorría todo el arreglo,
// y esto se repetía dentro de cada .each() de colores/cortinas/cintas.
const INVENTARIO_POR_ID = new Map(INVENTARIO.map(item => [String(item.id), item]));

/**
 * Busca un insumo del inventario por su ID (como string o número).
 * @param {string|number} id
 * @returns {object|null} el insumo encontrado, o null si no existe.
 */
function buscarInsumoPorId(id) {
    return INVENTARIO_POR_ID.get(String(id)) || null;
}

// =======================================================
// 4. MODAL DE ALERTA / VALIDACIÓN (reutilizable) - TEMA CLARO / PASTEL
// =======================================================
/**
 * Pinta y muestra el modal #modalValidacion con estilo Soft UI Pastel.
 * Se usa tanto para validaciones de formulario como para errores de red.
 *
 * @param {string|string[]} contenido  Un mensaje único, o un arreglo de
 *                                     campos faltantes (se listan uno por uno).
 * @param {string} titulo              Título del modal.
 * @param {'warning'|'danger'|'info'}  tipo  Define el color/ícono del modal.
 */
function mostrarAlerta(contenido, titulo = 'Faltan campos obligatorios', tipo = 'warning') {

    // Paleta de cada variante del modal adaptada a tonos pastel
    const config = {
        warning: {
            icono: 'fa-triangle-exclamation',
            color: '#d97706', // Ámbar oscuro (legible)
            fondo: '#fef3c7', // Ámbar muy claro (fondo)
            borde: '#fde68a',
            texto: '<i class="fa-solid fa-pen-to-square me-1"></i> Entendido',
        },
        danger: {
            icono: 'fa-circle-xmark',
            color: '#dc2626', // Rojo oscuro
            fondo: '#fee2e2', // Rojo muy claro
            borde: '#fecaca',
            texto: '<i class="fa-solid fa-rotate-right me-1"></i> Cerrar e intentar de nuevo',
        },
        info: {
            icono: 'fa-circle-info',
            color: '#9333ea', // Morado de tu marca
            fondo: '#f3e8ff', // Morado muy claro
            borde: '#d8b4fe',
            texto: '<i class="fa-solid fa-check me-1"></i> Entendido',
        }
    };

    const c = config[tipo] || config.warning;

    // Colores de texto globales basados en tu variables.css
    const textoPrincipal = '#3b0764'; // --text-main
    const textoSecundario = '#7e57c2'; // --text-muted

    // ---- Ícono circular (sin resplandor neón, sombra suave) ----
    const iconoEl = $('#modalValidacion .fa-solid').first();

    iconoEl.attr('class', `fa-solid ${c.icono}`)
        .css({
            color: c.color,
            fontSize: '20px',
            filter: 'none' // Quitamos el resplandor neón
        });

    iconoEl.parent()
        .attr(
            'class',
            'flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle'
        )
        .css({
            width: '48px',
            height: '48px',
            minWidth: '48px',
            background: c.fondo,
            border: `1px solid ${c.borde}`,
            boxShadow: '0 4px 6px rgba(0,0,0,0.05)', // Sombra ligera
            marginTop: '2px'
        });

    // ---- Título y subtítulo ----
    $('#modalValidacionTitulo')
        .text(titulo)
        .css({
            fontSize: '18px',
            fontWeight: '700',
            letterSpacing: '-0.2px',
            color: textoPrincipal
        });

    $('#modalValidacionTitulo').next('p').css('color', textoSecundario);

    // ---- Lista de mensajes (tarjetas elevadas en lugar de hundidas) ----
    const lista = $('#listaValidacion');
    lista.empty();

    const estiloItemLista = `
        background: #ffffff;
        border: 1px solid rgba(199, 186, 219, 0.4);
        box-shadow: 0 2px 4px rgba(59, 7, 100, 0.05);
        color: ${textoPrincipal};
    `;

    if (Array.isArray(contenido)) {
        // Caso: lista de campos faltantes
        lista.append(`
            <div class="small mb-3" style="color: ${textoSecundario};">
                Se encontraron
                <strong style="color: ${c.color};">${contenido.length}</strong>
                campo${contenido.length > 1 ? 's' : ''}
                pendiente${contenido.length > 1 ? 's' : ''}.
            </div>
        `);

        contenido.forEach(function (campo) {
            lista.append(`
                <li class="d-flex align-items-center gap-3 rounded-3 px-3 py-2 mb-2"
                    style="${estiloItemLista}">

                    <div style="
                        width:8px;
                        height:8px;
                        border-radius:50%;
                        background:${c.color};
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        flex-shrink:0;
                    "></div>

                    <span style="font-size:13px; font-weight: 500;">
                        ${campo}
                    </span>
                </li>
            `);
        });

    } else {
        // Caso: mensaje único
        lista.append(`
            <li class="d-flex align-items-start gap-3 rounded-3 px-3 py-3"
                style="${estiloItemLista}">

                <i class="fa-solid fa-circle-info"
                   style="
                        color:${c.color};
                        margin-top:3px;
                   ">
                </i>

                <span style="font-size:13px; line-height: 1.5; font-weight: 500;">
                    ${contenido}
                </span>
            </li>
        `);
    }

    // ---- Botón de cierre ----
    $('#modalValidacion .modal-footer .btn, #modalValidacion .px-4.pb-4 .btn')
        .attr('class', 'btn w-100 rounded-3')
        .css({
            background: c.fondo,
            color: c.color,
            border: `1px solid ${c.borde}`,
            fontSize: '13px',
            fontWeight: '600',
            padding: '10px',
            boxShadow: '0 2px 4px rgba(0,0,0,0.05)', // Sombra tradicional
            transition: 'all 0.2s ease'
        })
        .html(c.texto)
        // Hover manual para elevar el botón suavemente
        .on('mouseenter', function() {
            $(this).css({
                transform: 'translateY(-1px)',
                boxShadow: '0 4px 8px rgba(0,0,0,0.1)'
            });
        })
        .on('mouseleave', function() {
            $(this).css({
                transform: 'translateY(0)',
                boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
            });
        });

    // ---- Fondo del modal (Blanco limpio en lugar de cristal oscuro) ----
    $('#modalValidacion .modal-content').css({
        background: '#ffffff',
        border: '1px solid rgba(199, 186, 219, 0.4)',
        borderRadius: '16px',
        overflow: 'hidden',
        boxShadow: '0 20px 45px rgba(59, 7, 100, 0.15)', // Sombra morada difuminada
        backdropFilter: 'none' // Ya no necesitamos el blur intenso del modo oscuro
    });

    // ---- Mostrar ----
    const modal = new bootstrap.Modal(
        document.getElementById('modalValidacion')
    );
    modal.show();
}

// =======================================================
// 4.1 VALIDACIÓN: MATERIAL NUEVO SIN TIPO DE CINTA
// =======================================================
/**
 * Revisa los selects de decoración que aceptan "Cotizar nuevo material"
 * (Lazo Simple, Flores, Lazo con Nombre). Si el usuario escribió un
 * material nuevo (no existe en el inventario) y no incluyó si es
 * Satín / Gross / Garza, bloquea el guardado hasta que lo aclare.
 *
 * Se llama justo antes de abrir el modal de datos del cliente / antes
 * de enviar el pedido, para no dejar pasar cintas ambiguas al inventario.
 *
 * @returns {boolean} true si todo está en orden, false si hay que detener el flujo.
 */
function validarCintasNuevas() {
    // Recolectamos los selects de decoración que usan cintas y que están activos.
    const selectsDecoracion = [];

    if ($('#swLazoSimple').is(':checked')) {
        selectsDecoracion.push($('select[name="cinta_lazo_simple"]').select2('data'));
    }

    if ($('#swLazoFlor').is(':checked')) {
        $('#contenedorFlores select').each(function () {
            selectsDecoracion.push($(this).select2('data'));
        });
    }

    if ($('#swLazoNombre').is(':checked')) {
        selectsDecoracion.push($('select[name="cinta_lazo_nombre"]').select2('data'));
    }

    let errorEncontrado = false;

    selectsDecoracion.forEach(dataArray => {
        if (dataArray && dataArray.length > 0) {
            const seleccion = dataArray[0];
            if (!seleccion || seleccion.id === '') return;

            // Verificamos si es un Tag Nuevo (escrito a mano, sin ID de BD)
            const esTagNuevo = seleccion.newTag || isNaN(seleccion.id);

            if (esTagNuevo) {
                const nombreLazo = seleccion.text.toLowerCase();

                const tieneSatin = nombreLazo.includes('satin') || nombreLazo.includes('satín');
                const tieneGross = nombreLazo.includes('gross');
                const tieneGarza = nombreLazo.includes('garza');

                if (!tieneSatin && !tieneGross && !tieneGarza) {
                    errorEncontrado = true;
                }
            }
        }
    });

    if (errorEncontrado) {
        mostrarAlerta(
            'Para cotizar un material nuevo, por favor incluye si la cinta es <b>Satín</b>, <b>Gross</b> o <b>Garza</b>.<br><br><small class="text-muted">Ejemplo: "Cinta roja gross"</small>',
            'Falta el tipo de cinta',
            'warning'
        );
        return false; // Bloquea la ejecución del guardado/cálculo
    }

    return true; // Todo está en orden, el cálculo puede continuar
}


// =======================================================
// BLOQUE PRINCIPAL — Un solo $(document).ready()
// =======================================================
$(document).ready(function () {

    inicializarModuloLana(RUTAS);
    inicializarModuloCortinas(RUTAS);
    inicializarModuloDecoracion(RUTAS);
    inicializarModuloDiseno();

    // Convierte los selects ESTÁTICOS (sin búsqueda AJAX) en Select2
    // también, para que hereden el mismo look "dark glass" en su lista.
    $('#selectTamano, #selectAcabado, #selectCantidadColores, #selectCantCortinaLana, #selectCantCortinaFiesta, #selectCantFlores, #selectNivelDiseno').select2({
        theme: 'bootstrap-5',
        width: '100%',
        minimumResultsForSearch: Infinity, // oculta el buscador: son listas cortas y fijas
    });

    // =======================================================
    // 5.1 Inicialización de Componentes y Select2
    // =======================================================

    // Inicializamos el selector de solicitudes web
    $('#selectSolicitudWeb').select2({
        dropdownParent: $('#modalDatosCliente'), // Obligatorio para que Select2 funcione dentro de un Modal de Bootstrap
        placeholder: '-- Cliente Presencial (Ninguna) --',
        allowClear: true,
        ajax: {
            url: window.KardexConfig.rutas.buscarSolicitudes, // Usamos la ruta que agregamos al puente
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term // Término de búsqueda escrito por tu mamá
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    })
                };
            }
        }
    });

    // Evento opcional de UX: Si tu mamá selecciona una solicitud web, autocompletamos
    // el nombre del cliente y el correo si existen en la solicitud
    $('#selectSolicitudWeb').on('select2:select', function (e) {
        const data = e.params.data;
        if (data) {
            // Hacemos una petición rápida o usamos la info cargada para autocompletar inputs
            fetch(`${window.KardexConfig.rutas.buscarSolicitudes}?id=${data.id}`)
                .then(res => res.json())
                .then(solicitud => {
                    if (solicitud && solicitud.user) {
                        $('#inputNombreCliente').val(solicitud.user.name);
                        $('#inputCorreoCliente').val(solicitud.user.email || '');
                    }
                });
        }
    });


    // =======================================================
    // 5.2 CONSTANTES Y CONFIGURACIÓN BASE
    // =======================================================
    // "Precio fantasma" = precio de referencia que se usa SOLO cuando el
    // cliente pide un material nuevo que todavía no existe en el inventario
    // (Select2 "Cotizar nuevo material"). Sirve para no dejar el cálculo en
    // $0 mientras bodega registra el insumo real.
    const PRECIOS_FANTASMA = {
        lana:                 0.0127,  // $1.15 / 90g
        cinta_garza:          0.11,    // $5.00 / 45.72m
        cinta_satin:          0.16,    // $3.00 / 18.28m
        cinta_gross:          0.15,    // $3.50 / 22.86m
        elastico:             0.09,    // $0.90 / 10m
        cinchos:              0.02,    // $2.00 / 100u
        cortina_fiesta_menor: 1.00,    // pedido < 12 bastones
        cortina_fiesta_mayor: 0.50,    // pedido >= 12 bastones
    };

    // Cantidades fijas de insumos de ensamblaje por cada bastón producido.
    const RECETA = {
        cinchos_por_baston:  3,     // 3 cinchos por unidad
        elastico_por_baston: 0.40,  // 0.40 m (40 cm) por unidad
    };


    // =======================================================
    // 5.3 FUNCIÓN MAESTRA — Recalcula la tabla y los totales
    // =======================================================

    /**
     * Agrega una línea al carrito global (window.carritoInsumos), que luego
     * se envía como JSON al backend al guardar la cotización.
     */
    function agregarAlCarrito({ insumo_id = null, nombre_material, cantidad_requerida, subtotal_calculado }) {
        window.carritoInsumos.push({
            insumo_id: insumo_id ?? null,
            nombre_material: nombre_material || '',
            cantidad_requerida: parseFloat(cantidad_requerida) || 0,
            subtotal_calculado: parseFloat(subtotal_calculado) || 0,
        });
    }

    /**
     * Motor de cálculo del cotizador. Lee TODO el estado actual del formulario,
     * recalcula el costo de cada fase (base, ensamblaje, lana, cortinas,
     * decoración, diseño), repinta la tabla de impacto en inventario y
     * actualiza el panel de totales.
     *
     * Se dispara con debounce cada vez que el usuario cambia algún campo
     * (ver sección 5.4).
     */
    function calcularCotizacion() {

        window.carritoInsumos = [];
        const tabla = $('#cuerpoTablaImpacto');

        // Acumulamos el HTML de todas las filas y lo pintamos una sola vez
        // al final con tabla.html(...). Antes cada tabla.append(...) forzaba
        // un reflow individual del navegador por cada fila.
        const filasHtml = [];

        let costoTotalMateriales = 0;
        let costoTotalManoObra   = 0;

        const cantidadBastones = parseInt($('#inputCantidad').val()) || 0;
        const colorBase        = $('#selectAcabado').val();
        const tamanoBase       = $('#selectTamano').val();

        // ---- Guarda de formulario: sin estos 3 campos no hay nada que calcular ----
        if (cantidadBastones <= 0 || !colorBase || !tamanoBase) {
            tabla.html(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fa-solid fa-calculator fa-2x mb-2 text-light"></i><br>
                        Esperando configuración del pedido...
                    </td>
                </tr>
            `);
            $('#txtCostoMateriales, #txtCostoManoObra, #txtCostoTotal').text('$ 0.00');
            $('#txtCostoUnitario').text('$ 0.00 c/u');
            $('#lblResumenBastones').text('0 Bastones');
            $('#btnGuardarCotizacion').prop('disabled', true);
            return;
        }

        $('#lblResumenBastones').text(`${cantidadBastones} Bastones`);

        // ---------------------------------------------------
        // FASE 1: BASE DEL BASTÓN
        // Precio unitario depende del acabado (plata/dorado) y de si el
        // pedido alcanza el volumen de mayoreo (>= 12 unidades).
        // ---------------------------------------------------
        let precioBaseFantasma = 0;
        let nombreAcabado      = '';

        // 1. Definir el precio de emergencia (fantasma / mayoreo) por si no existe en BD
        if (colorBase === 'dorado') {
            nombreAcabado      = 'Dorado';
            precioBaseFantasma = (cantidadBastones >= 12) ? 5.00 : 5.50;
        } else {
            nombreAcabado      = 'Plata';
            precioBaseFantasma = (cantidadBastones >= 12) ? 4.50 : 5.00;
        }

        let esGrande     = (tamanoBase === '55' || tamanoBase === '60');
        let tamanoVisual = tamanoBase + ' cm';

        // 2. Buscamos en el inventario real la base que coincide en color + tamaño
        let baseEnKardex = INVENTARIO.find(item => {
            if (item.categoria !== 'base_baston' || !item.nombre) return false;
            let nombreBD      = item.nombre.toLowerCase();
            let colorBuscado  = colorBase.toLowerCase();
            let tamanoBuscado = tamanoBase.replace(/\D/g, ''); 
            
            return nombreBD.includes(colorBuscado) && nombreBD.includes(tamanoBuscado);
        });

        // 3. LA MAGIA: Si existe en Kardex forzamos su precio real a número. Si no, usamos el fantasma.
        let precioBaseUnitario = baseEnKardex ? parseFloat(baseEnKardex.costo_unitario) : precioBaseFantasma;

        // 4. Ahora sí, hacemos las matemáticas con el precio correcto
        let costoTotalBases = precioBaseUnitario * cantidadBastones;
        costoTotalMateriales += costoTotalBases;

        let alertaBase = (baseEnKardex && baseEnKardex.stock_actual >= cantidadBastones)
            ? `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`
            : `<span class="text-danger fw-bold">- ${cantidadBastones} u. (Falta Comprar)</span>`;

        filasHtml.push(`
            <tr>
                <td class="fw-bold text-dark">Base ${nombreAcabado} (${tamanoVisual})</td>
                <td class="text-muted small">${cantidadBastones} u. &times; $${precioBaseUnitario.toFixed(2)} c/u</td>
                <td class="fw-bold text-muted">$${costoTotalBases.toFixed(2)}</td>
                <td class="text-end">${alertaBase}</td>
            </tr>
        `);

        // Extraemos el ID sin operadores opcionales para evitar crashes
        let idBase = baseEnKardex ? baseEnKardex.id : null;

        agregarAlCarrito({
            insumo_id: idBase,
            nombre_material: `Base ${nombreAcabado} (${tamanoVisual})`,
            cantidad_requerida: cantidadBastones,
            subtotal_calculado: costoTotalBases,
        });

        // ---------------------------------------------------
        // FASE 2: INSUMOS FIJOS DE ENSAMBLAJE (cinchos + elástico)
        // Estos NO dependen del diseño elegido, solo de la cantidad de
        // bastones, según la RECETA fija definida arriba.
        // ---------------------------------------------------

        const totalCinchos  = RECETA.cinchos_por_baston  * cantidadBastones;
        const totalElastico = RECETA.elastico_por_baston * cantidadBastones;

        // BUSCAR PRECIOS REALES EN EL KARDEX:
        const insumoCinchos  = INVENTARIO.find(item => item.categoria === 'cinchos');
        const insumoElastico = INVENTARIO.find(item => item.categoria === 'elastico');

        // Si existe en BD usa su precio real y lo forzamos a número (parseFloat), si no, usa el fantasma
        const precioCinchosReal  = insumoCinchos  ? parseFloat(insumoCinchos.costo_unitario)  : PRECIOS_FANTASMA.cinchos;
        const precioElasticoReal = insumoElastico ? parseFloat(insumoElastico.costo_unitario) : PRECIOS_FANTASMA.elastico;

        const costoCinchos  = totalCinchos  * precioCinchosReal;
        const costoElastico = totalElastico * precioElasticoReal;

        costoTotalMateriales += costoCinchos + costoElastico;

        filasHtml.push(`
            <tr>
                <td class="fw-bold text-dark">Insumos de Ensamblaje</td>
                <td class="text-muted small">${totalCinchos} cinchos &middot; ${totalElastico.toFixed(2)}m elástico</td>
                <td class="fw-bold text-muted">$${(costoCinchos + costoElastico).toFixed(2)}</td>
                <td class="text-end text-muted">&mdash;</td>
            </tr>
            <tr>
                <td class="text-dark ps-3">&#x21B3; Cinchos</td>
                <td class="text-muted small">${totalCinchos} u. &times; $${precioCinchosReal.toFixed(2)}/u</td>
                <td class="text-muted small">$${costoCinchos.toFixed(2)}</td>
                <td class="text-end text-muted">&mdash;</td>
            </tr>
            <tr>
                <td class="text-dark ps-3">&#x21B3; Elástico</td>
                <td class="text-muted small">${totalElastico.toFixed(2)}m &times; $${precioElasticoReal.toFixed(2)}/m</td>
                <td class="text-muted small">$${costoElastico.toFixed(2)}</td>
                <td class="text-end text-muted">&mdash;</td>
            </tr>
        `);

        // Extraemos los IDs de forma clásica, sin símbolos raros para evitar crashes
        let idCinchos = insumoCinchos ? insumoCinchos.id : null;
        let idElastico = insumoElastico ? insumoElastico.id : null;

        agregarAlCarrito({ 
            insumo_id: idCinchos, 
            nombre_material: 'Cinchos', 
            cantidad_requerida: totalCinchos, 
            subtotal_calculado: costoCinchos 
        });
        
        agregarAlCarrito({ 
            insumo_id: idElastico, 
            nombre_material: 'Elástico', 
            cantidad_requerida: totalElastico, 
            subtotal_calculado: costoElastico 
        });

        // ---------------------------------------------------
        // FASE 3: CUERPO (LANA)
        // El consumo total de lana se reparte en partes iguales entre
        // los colores que el usuario haya seleccionado (1, 2 o 3 colores).
        // ---------------------------------------------------
        const consumoLana_g = esGrande ? 150 : 135;

        // Una sola pasada por los selects: recogemos las selecciones válidas
        // y de una vez sabemos cuántas hay (antes se recorría el mismo
        // .each() dos veces: una para contar, otra para calcular).
        const seleccionesLana = [];
        $('#contenedorColoresLana .select2-ajax').each(function () {
            const data = $(this).select2('data');
            if (data && data.length > 0 && data[0].id !== '') seleccionesLana.push(data[0]);
        });

        const coloresActivos = seleccionesLana.length || 1;
        const gramosPorColorTotal = (consumoLana_g / coloresActivos) * cantidadBastones;

        seleccionesLana.forEach(function (seleccion) {
            let nombreLana   = seleccion.text;
            // "Tag nuevo" = el usuario escribió un color que no existe en el
            // inventario todavía (Select2 lo deja crear on-the-fly).
            const esTagNuevo = seleccion.newTag || isNaN(seleccion.id);
            let costoLana    = 0;
            let stockActual  = 0;
            let insumoBD     = null;

            if (!esTagNuevo) {
                insumoBD = buscarInsumoPorId(seleccion.id);
                if (insumoBD) {
                    costoLana   = gramosPorColorTotal * insumoBD.costo_unitario;
                    stockActual = insumoBD.stock_actual;
                }
            } else {
                costoLana  = gramosPorColorTotal * PRECIOS_FANTASMA.lana;
                nombreLana = nombreLana.replace(' (Cotizar nuevo material)', '');
            }

            costoTotalMateriales += costoLana;
            const madejasNecesarias = Math.ceil(gramosPorColorTotal / 90);
            const stockSuficiente   = !esTagNuevo && stockActual >= gramosPorColorTotal;

            const textoStock = stockSuficiente
                ? `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`
                : `<span class="text-danger fw-bold">- ${gramosPorColorTotal.toFixed(1)}g (${madejasNecesarias} Madejas)</span>`;

            filasHtml.push(`
                <tr>
                    <td class="fw-bold text-dark">Cuerpo: ${nombreLana}</td>
                    <td class="text-muted small">${gramosPorColorTotal.toFixed(1)}g calculados</td>
                    <td class="text-muted fw-bold">$${costoLana.toFixed(2)}</td>
                    <td class="text-end">${textoStock}</td>
                </tr>
            `);

            agregarAlCarrito({
                insumo_id: insumoBD?.id ?? null,
                nombre_material: nombreLana,
                cantidad_requerida: gramosPorColorTotal,
                subtotal_calculado: costoLana,
            });
        });


        // ---------------------------------------------------
        // FASE 4: CORTINAS (LANA Y FIESTA)
        // ---------------------------------------------------

        // 4.1 Cortinas de Lana — mismo patrón que el cuerpo, pero con un
        // consumo fijo de 30g por color (no se reparte entre colores).
        if ($('#swCortinaLana').is(':checked')) {
            let gramosPorCortinaLana = 30 * cantidadBastones;

            $('#contenedorCortinasLana select').each(function () {
                let dataSelect = $(this).select2('data');

                if (dataSelect && dataSelect.length > 0 && dataSelect[0].id !== '') {
                    let seleccion      = dataSelect[0];
                    let nombreMaterial = seleccion.text.replace(' (Cotizar nuevo material)', '');
                    let idMaterial     = seleccion.id;
                    let esTagNuevo     = seleccion.newTag || isNaN(idMaterial);

                    let costoCalculado  = 0;
                    let stockDisponible = 0;
                    let textoAlerta     = '';
                    let insumoBD        = null;

                    if (!esTagNuevo) {
                        insumoBD = buscarInsumoPorId(idMaterial);
                        if (insumoBD) {
                            costoCalculado  = gramosPorCortinaLana * insumoBD.costo_unitario;
                            stockDisponible = insumoBD.stock_actual;
                        }
                    } else {
                        costoCalculado = gramosPorCortinaLana * PRECIOS_FANTASMA.lana;
                    }

                    costoTotalMateriales += costoCalculado;
                    let madejasNecesarias = Math.ceil(gramosPorCortinaLana / 90);

                    if (esTagNuevo || stockDisponible < gramosPorCortinaLana) {
                        textoAlerta = `<span class="text-danger fw-bold">- ${gramosPorCortinaLana.toFixed(1)}g (${madejasNecesarias} Madejas)</span>`;
                    } else {
                        textoAlerta = `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`;
                    }

                    filasHtml.push(`
                        <tr>
                            <td class="fw-bold text-dark">Cortina Lana: ${nombreMaterial}</td>
                            <td class="text-muted small">${gramosPorCortinaLana.toFixed(1)}g calculados</td>
                            <td class="text-muted fw-bold">$${costoCalculado.toFixed(2)}</td>
                            <td class="text-end">${textoAlerta}</td>
                        </tr>
                    `);

                    agregarAlCarrito({
                        insumo_id: insumoBD?.id ?? null,
                        nombre_material: 'Cortina de Lana: ' + nombreMaterial,
                        cantidad_requerida: gramosPorCortinaLana,
                        subtotal_calculado: costoCalculado,
                    });
                }
            });
        }

        // 4.2 Cortinas de Fiesta — se venden por paquete (4 unidades), y el
        // precio "fantasma" depende del volumen total de cortinas del pedido.
        if ($('#swCortinaFiesta').is(':checked')) {

            // Igual que en lana: una sola pasada para recoger las selecciones
            // válidas, en vez de recorrer el select dos veces.
            const seleccionesFiesta = [];
            $('#contenedorCortinasFiesta select').each(function () {
                const data = $(this).select2('data');
                if (data && data.length > 0 && data[0].id !== '') seleccionesFiesta.push(data[0]);
            });

            if (seleccionesFiesta.length > 0) {
                let totalCortinasFisicas = cantidadBastones * seleccionesFiesta.length;
                let precioFantasmaFiesta = (totalCortinasFisicas >= 12)
                    ? PRECIOS_FANTASMA.cortina_fiesta_mayor
                    : PRECIOS_FANTASMA.cortina_fiesta_menor;

                let cortinasPorColor = cantidadBastones;
                let paquetesPorColor = cortinasPorColor / 4;

                seleccionesFiesta.forEach(function (seleccion) {
                    let nombreMaterial = seleccion.text.replace(' (Cotizar nuevo material)', '');
                    let idMaterial     = seleccion.id;
                    let esTagNuevo     = seleccion.newTag || isNaN(idMaterial);

                    let costoCalculado = 0;
                    let faltaStock     = false;
                    let textoAlerta    = '';
                    let insumoBD       = null;

                    if (!esTagNuevo) {
                        insumoBD = buscarInsumoPorId(idMaterial);
                        if (insumoBD) {
                            costoCalculado = cortinasPorColor * insumoBD.costo_unitario;
                            let stockEnUnidades = insumoBD.stock_actual;
                            if (stockEnUnidades < cortinasPorColor) {
                                faltaStock = true;
                            }
                        }
                    } else {
                        costoCalculado = paquetesPorColor * precioFantasmaFiesta;
                        faltaStock     = true;
                    }

                    costoTotalMateriales += costoCalculado;

                    if (faltaStock) {
                        let paquetesFisicosComprar = Math.ceil(paquetesPorColor);
                        textoAlerta = `<span class="text-danger fw-bold">- ${cortinasPorColor} cortinas (Comprar ${paquetesFisicosComprar} paq.)</span>`;
                    } else {
                        textoAlerta = `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`;
                    }

                    filasHtml.push(`
                        <tr>
                            <td class="fw-bold text-dark">Cortina Fiesta: ${nombreMaterial}</td>
                            <td class="text-muted small">${cortinasPorColor} cortinas calculadas</td>
                            <td class="fw-bold text-muted">$${costoCalculado.toFixed(2)}</td>
                            <td class="text-end">${textoAlerta}</td>
                        </tr>
                    `);

                    agregarAlCarrito({
                        insumo_id: insumoBD?.id ?? null,
                        nombre_material: 'Cortina de Fiesta: ' + nombreMaterial, // <-- AQUÍ ESTÁ EL CAMBIO
                        cantidad_requerida: gramosPorCortinaLana,
                        subtotal_calculado: costoCalculado,
                    });
                });
            }
        }


        // =======================================================
        // FASE 5: DECORACIÓN Y APLIQUES
        // =======================================================

        /**
         * Procesa un select de cinta (Select2 con búsqueda AJAX) y agrega
         * su fila + su costo al cálculo general.
         *
         * @param {string|HTMLElement} idSelect     Selector jQuery o elemento del <select>.
         * @param {string} nombreFila                Etiqueta a mostrar en la tabla (ej. "Lazo Simple").
         * @param {number} metrosPorUnidad            Metros de cinta que consume 1 bastón.
         * @param {number} recargoFijo                Extra de mano de obra por bastón (ej. $0.70 del nombre bordado).
         */
        function procesarCinta(idSelect, nombreFila, metrosPorUnidad, recargoFijo = 0) {
            let dataSelect = $(idSelect).select2('data');
            if (dataSelect && dataSelect.length > 0 && dataSelect[0].id !== '') {
                let seleccion      = dataSelect[0];
                let nombreMaterial = seleccion.text.replace(' (Cotizar nuevo material)', '');
                let idMaterial     = seleccion.id;
                let esTagNuevo     = seleccion.newTag || isNaN(idMaterial);

                let metrosTotales   = metrosPorUnidad * cantidadBastones;
                let costoCalculado  = 0;
                let stockDisponible = 0;
                let textoAlerta     = '';
                let insumoBD        = null;

                if (!esTagNuevo) {
                    insumoBD = buscarInsumoPorId(idMaterial);
                    if (insumoBD) {
                        costoCalculado  = metrosTotales * insumoBD.costo_unitario;
                        stockDisponible = insumoBD.stock_actual; // en metros
                    }
                } else {
                    // Sin insumo real: adivinamos el tipo de cinta por el nombre
                    // para usar un precio fantasma más realista que el genérico.
                    let precioFantasma  = PRECIOS_FANTASMA.cinta_gross; // Default
                    let textoMinuscula  = nombreMaterial.toLowerCase();

                    if (textoMinuscula.includes('garza'))                          precioFantasma = PRECIOS_FANTASMA.cinta_garza;
                    else if (textoMinuscula.includes('satin') ||
                             textoMinuscula.includes('satín'))                     precioFantasma = PRECIOS_FANTASMA.cinta_satin;

                    costoCalculado = metrosTotales * precioFantasma;
                }

                costoTotalMateriales += costoCalculado;

                // El recargo (ej. bordado de nombre) es mano de obra, no material.
                let totalRecargo = recargoFijo * cantidadBastones;
                if (totalRecargo > 0) {
                    costoTotalManoObra += totalRecargo;
                }

                if (esTagNuevo || stockDisponible < metrosTotales) {
                    textoAlerta = `<span class="text-danger fw-bold">- ${metrosTotales.toFixed(2)}m (Falta Comprar)</span>`;
                } else {
                    textoAlerta = `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> Stock Suficiente</span>`;
                }

                let subtotalVisual = (costoCalculado + totalRecargo).toFixed(2);
                let detalleRecargo = totalRecargo > 0
                    ? ` <br><small class="text-muted">(Incluye $${totalRecargo.toFixed(2)} extra)</small>`
                    : '';

                filasHtml.push(`
                    <tr>
                        <td class="fw-bold text-dark">${nombreFila}: ${nombreMaterial}</td>
                        <td class="text-muted small">${metrosTotales.toFixed(2)}m calculados</td>
                        <td class="fw-bold text-muted">$${subtotalVisual}${detalleRecargo}</td>
                        <td class="text-end">${textoAlerta}</td>
                    </tr>
                `);

                agregarAlCarrito({
                    insumo_id: insumoBD?.id ?? null,
                    nombre_material: `${nombreFila}: ${nombreMaterial}`,
                    cantidad_requerida: metrosTotales,
                    subtotal_calculado: costoCalculado + totalRecargo,
                });
            }
        }

        // 5.1 Lazo Simple (1.5m, sin recargo)
        if ($('#swLazoSimple').is(':checked')) procesarCinta('select[name="cinta_lazo_simple"]', 'Lazo Simple', 1.5, 0);

        // 5.2 Flores Dinámicas (1.0m cada una, cantidad variable definida por el usuario)
        if ($('#swLazoFlor').is(':checked')) {
            let numeroFlor = 1;
            $('#contenedorFlores select').each(function () {
                procesarCinta(this, `Flor ${numeroFlor}`, 1.0, 0);
                numeroFlor++;
            });
        }

        // 5.3 Lazo con Nombre (1.0m + $0.70 de mano de obra por el bordado)
        if ($('#swLazoNombre').is(':checked')) procesarCinta('select[name="cinta_lazo_nombre"]', 'Lazo c/ Nombre', 1.0, 0.70);

        // 5.4 Apliques Manuales — no vienen de inventario, es un costo fijo por unidad ($0.50 c/u).
        if ($('#swApliques').is(':checked')) {
            let cantApliques  = parseInt($('#cantApliques').val()) || 1;
            let totalApliques = cantApliques * cantidadBastones;
            let costoApliques = totalApliques * 0.50;

            costoTotalManoObra += costoApliques;

            filasHtml.push(`
                <tr>
                    <td class="fw-bold text-dark">Detalles: Apliques</td>
                    <td class="text-muted small">${totalApliques} u. totales × $0.50</td>
                    <td class="fw-bold text-muted">$${costoApliques.toFixed(2)}</td>
                    <td class="text-end"><span class="text-muted">Extra Fijo</span></td>
                </tr>
            `);

            agregarAlCarrito({
                insumo_id: null,
                nombre_material: 'Apliques',
                cantidad_requerida: totalApliques,
                subtotal_calculado: costoApliques,
            });
        }


        // =======================================================
        // FASE 6: DISEÑOS PERSONALIZADOS (MANO DE OBRA EXTRA)
        // =======================================================
        if ($('#swDisenoPersonalizado').is(':checked')) {
            let tarifaDisenoUnidad = parseFloat($('#selectNivelDiseno').val()) || 0;
            let costoTotalDiseno   = tarifaDisenoUnidad * cantidadBastones;

            costoTotalManoObra += costoTotalDiseno;

            let nombreNivel = $('#selectNivelDiseno option:selected').text().split(' ')[0];

            filasHtml.push(`
                <tr class="bg-warning bg-opacity-10">
                    <td class="fw-bold text-dark"><i class="fa-solid fa-star text-warning"></i> Diseño Especial: ${nombreNivel}</td>
                    <td class="text-muted small">${cantidadBastones} u. × $${tarifaDisenoUnidad.toFixed(2)}</td>
                    <td class="fw-bold text-dark">$${costoTotalDiseno.toFixed(2)}</td>
                    <td class="text-end"><span class="text-muted">Mano de Obra</span></td>
                </tr>
            `);

            agregarAlCarrito({
                insumo_id: null,
                nombre_material: `Diseño Especial: ${nombreNivel}`,
                cantidad_requerida: cantidadBastones,
                subtotal_calculado: costoTotalDiseno,
            });
        }

        // Una sola escritura al DOM con todas las filas acumuladas.
        tabla.html(filasHtml.join(''));

        // =======================================================
        // ACTUALIZACIÓN DEL PANEL FINANCIERO VISUAL (MARGEN 60%)
        // =======================================================
        
        // 1. Calculamos la ganancia base dinámica (60% del costo de los insumos físicos)
        let porcentajeGanancia = 0.60;
        let gananciaBase       = costoTotalMateriales * porcentajeGanancia;

        // 2. El gran total es la suma de las 3 partes separadas: 
        // Materiales + Extras de Diseño + Ganancia Base (60%)
        let granTotal     = costoTotalMateriales + costoTotalManoObra + gananciaBase;
        let costoUnitario = granTotal / cantidadBastones;

        // 3. Reflejamos en la interfaz respetando cada línea de tu HTML
        $('#txtCostoMateriales').text(`$ ${costoTotalMateriales.toFixed(2)}`);
        $('#txtCostoManoObra').text(`$ ${costoTotalManoObra.toFixed(2)}`); 
        $('#txtGananciaFija').text(`$ ${gananciaBase.toFixed(2)} (60%)`);

        // Totales
        $('#txtCostoTotal').text(`$ ${granTotal.toFixed(2)}`);
        $('#txtCostoUnitario').text(`$ ${costoUnitario.toFixed(2)} c/u`);

        $('#btnGuardarCotizacion').prop('disabled', false);
        $('#inputCostoInsumos').val(costoTotalMateriales.toFixed(2));

    }

    // =======================================================
    // 5.4 TRIGGERS — Eventos que disparan el recálculo
    // =======================================================

    // Debounce corto: agrupa cambios que llegan casi al mismo tiempo (por
    // ejemplo, Select2 puede disparar varios 'change' en cadena), así
    // calcularCotizacion() no se ejecuta 3-4 veces por un solo clic.
    let temporizadorRecalculo = null;
    function recalcularConDebounce() {
        clearTimeout(temporizadorRecalculo);
        temporizadorRecalculo = setTimeout(calcularCotizacion, 80);
    }

    // Controles simples que NO son <select> (inputs de texto/número y
    // switches). Los <select> se manejan aparte, en un único listener
    // delegado más abajo (para que también funcione con los selects que
    // los módulos crean dinámicamente, ej. al añadir más colores).
    const controlesSimples = [
        '#inputCantidad',
        '#swCortinaLana', '#swCortinaFiesta',
        '#swLazoSimple', '#swLazoFlor', '#swLazoNombre',
        '#swApliques', '#cantApliques', '#swDisenoPersonalizado',
    ].join(', ');

    $(controlesSimples).on('input change', recalcularConDebounce);
    $('body').on('change', 'select', recalcularConDebounce);

    // Primer cálculo al cargar la página (deja la tabla en su estado "vacío").
    calcularCotizacion();


    // =======================================================
    // 5.5 GUARDAR COTIZACIÓN — AJAX + modal de validación
    // =======================================================

    // ---- PASO 1: Validación (botón verde de la pantalla principal) ----
    $('#btnGuardarCotizacion').on('click', function (e) {
        e.preventDefault();

        // Bloqueo preventivo: si hay una cinta nueva sin tipo (Satín/Gross/Garza),
        // detenemos todo el flujo antes de siquiera revisar los demás campos.
        if (!validarCintasNuevas()) {
            return;
        }

        const cantidad = parseInt($('#inputCantidad').val()) || 0;
        const tamano   = $('#selectTamano').val();
        const acabado  = $('#selectAcabado').val();

        let tieneColorLana = false;
        $('#contenedorColoresLana .select2-ajax').each(function () {
            const data = $(this).select2('data');
            if (data && data.length > 0 && data[0].id !== '') tieneColorLana = true;
        });

        const faltantes = [];
        if (cantidad <= 0)   faltantes.push('Cantidad de bastones (Módulo 1)');
        if (!tamano)         faltantes.push('Tamaño del bastón (Módulo 1)');
        if (!acabado)        faltantes.push('Acabado dorado / plata (Módulo 1)');
        if (!tieneColorLana) faltantes.push('Al menos un color de lana (Módulo 2)');

        // ---- Módulo 3: Cortinas (solo si el switch respectivo está activo) ----
        if ($('#swCortinaLana').is(':checked')) {
            let tieneCortinaLana = false;
            $('#contenedorCortinasLana select').each(function () {
                const data = $(this).select2('data');
                if (data && data.length > 0 && data[0].id !== '') tieneCortinaLana = true;
            });
            if (!tieneCortinaLana) faltantes.push('Color de Cortina de Lana (Módulo 3)');
        }

        if ($('#swCortinaFiesta').is(':checked')) {
            let tieneCortinaFiesta = false;
            $('#contenedorCortinasFiesta select').each(function () {
                const data = $(this).select2('data');
                if (data && data.length > 0 && data[0].id !== '') tieneCortinaFiesta = true;
            });
            if (!tieneCortinaFiesta) faltantes.push('Color de Cortina de Fiesta (Módulo 3)');
        }

        // ---- Módulo 4: Decoración y Apliques ----
        if ($('#swLazoSimple').is(':checked')) {
            const data = $('select[name="cinta_lazo_simple"]').select2('data');
            if (!data || data.length === 0 || data[0].id === '') faltantes.push('Cinta para Lazo Simple (Módulo 4)');
        }

        if ($('#swLazoFlor').is(':checked')) {
            let tieneFlores = false;
            $('#contenedorFlores select').each(function () {
                const data = $(this).select2('data');
                if (data && data.length > 0 && data[0].id !== '') tieneFlores = true;
            });
            if (!tieneFlores) faltantes.push('Cinta para Flores (Módulo 4)');
        }

        if ($('#swLazoNombre').is(':checked')) {
            const data = $('select[name="cinta_lazo_nombre"]').select2('data');
            if (!data || data.length === 0 || data[0].id === '') faltantes.push('Cinta para Lazo con Nombre (Módulo 4)');
        }

        if ($('#swApliques').is(':checked')) {
            const cantApliques = parseInt($('#cantApliques').val()) || 0;
            if (cantApliques <= 0) faltantes.push('Cantidad de Apliques (Módulo 4)');
        }

        // ---- Módulo 5: Diseño Personalizado ----
        if ($('#swDisenoPersonalizado').is(':checked')) {
            const nivelDiseno = $('#selectNivelDiseno').val();
            if (!nivelDiseno) faltantes.push('Nivel de Diseño Personalizado (Módulo 5)');
        }

        if (faltantes.length > 0) {
            mostrarAlerta(faltantes, 'Faltan campos obligatorios', 'warning');
            return;
        }

        // Todo bien: mostramos el modal para pedir los datos del cliente.
        const modalCliente = new bootstrap.Modal(document.getElementById('modalDatosCliente'));
        modalCliente.show();
    });

    // ---- PASO 2: Guardar cotización (botón dentro del modal Cliente) ----
    $('#btnConfirmarPedidoModal').on('click', function (e) {
        e.preventDefault();

        const nombreCliente = $('#inputNombreCliente').val();
        const correoCliente = $('#inputCorreoCliente').val();
        const quoteRequestId = $('#selectSolicitudWeb').val();

        if (nombreCliente.trim() === '') {
            mostrarAlerta('Por favor ingresa el nombre del cliente.', 'Falta información', 'warning');
            return;
        }

        const btnGuardar    = $(this);
        const textoOriginal = btnGuardar.html();
        btnGuardar.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando Pedido...');

        // Armamos el payload: todos los campos del form + datos del cliente
        // + el resumen de costos ya formateado + el detalle del carrito.
        let datosFormulario = $('#formCotizador').serializeArray();
        // INYECTAMOS EL ID EN EL PAYLOAD (NUEVO)
        if (quoteRequestId) {
            datosFormulario.push({ name: 'quote_request_id', value: quoteRequestId });
        }
        datosFormulario.push({ name: 'nombre_cliente', value: nombreCliente });
        datosFormulario.push({ name: 'correo_cliente', value: correoCliente });
        datosFormulario.push({ name: 'costo_materiales', value: $('#txtCostoMateriales').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'costo_extras',     value: $('#txtCostoManoObra').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'ganancia_fija',    value: $('#txtGananciaFija').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'costo_total',      value: $('#txtCostoTotal').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'costo_unitario',   value: $('#txtCostoUnitario').text().replace('$', '').replace('c/u', '').trim() });
        datosFormulario.push({ name: 'materiales', value: JSON.stringify(window.carritoInsumos) });

        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url:  '/cotizaciones/guardar',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: datosFormulario,
            success: function (response) {
                // A. Ocultamos el modal de captura de cliente.
                $('#modalDatosCliente').modal('hide');

                // B. Imprimimos el ID del pedido en los modales de éxito.
                $('#modalNumCotizacion').text(response.id);
                $('#txtNumeroCotizacionExport').text(response.id);

                // C. Inyectamos el ID en los enlaces de descarga de PDF.
                $('#btnPdfReceta').attr('href', '/pedidos/' + response.id + '/pdf-receta');
                $('#btnPdfNota').attr('href', '/pedidos/' + response.id + '/pdf-nota');

                // D. Mostramos el modal de éxito.
                $('#modalExito').modal('show');

                // E. Bloqueamos el botón principal (el que abre el modal) para que no
                // se pueda reabrir y generar la misma cotización dos veces.
                // Guardamos su texto original para restaurarlo solo en el reseteo.
                const btnPrincipal = $('#btnGuardarCotizacion');
                if (!btnPrincipal.data('texto-original')) {
                    btnPrincipal.data('texto-original', btnPrincipal.html());
                }
                btnPrincipal.prop('disabled', true).html('<i class="fa-solid fa-check"></i> Cotización guardada');

                setTimeout(function () {
                    if (document.activeElement) {
                        document.activeElement.blur();
                    }

                    $('#modalExito').modal('hide');
                    $('#panelExportar').removeClass('d-none').hide().slideDown();

                    // NOTA: btnGuardar (el del modal) y btnGuardarCotizacion se quedan
                    // deshabilitados a propósito. Solo resetearFormulario() los libera.
                }, 1800);
            },
            error: function (xhr) {
                console.error('Fallo al guardar:', xhr.responseText);
                mostrarAlerta('No se pudo conectar con el servidor. Revisa tu conexión.', 'Error al guardar el pedido', 'danger');
                btnGuardar.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // ---- PASO 3: Cerrar flujo y limpiar pantalla (delegado porque el
    // botón vive dentro de #panelExportar, que se muestra dinámicamente) ----
    $(document).on('click', '#btnCerrarFlujoExportacion', function (e) {
        e.preventDefault();

        $('#inputNombreCliente').val('');
        $('#inputCorreoCliente').val('');
        //LIMPIAMOS EL SELECTOR DE LA SOLICITUD WEB (NUEVO)
        if ($('#selectSolicitudWeb').hasClass('select2-hidden-accessible')) {
            $('#selectSolicitudWeb').val(null).trigger('change');
        }

        resetearFormulario();
    });

    // =======================================================
    // 5.6 FUNCIÓN DE RESETEO
    // Deja el formulario listo para cotizar un pedido nuevo desde cero.
    // =======================================================
    function resetearFormulario() {
        // Reactivamos los botones de guardado que quedaron bloqueados
        // tras la cotización anterior.
        const btnPrincipal = $('#btnGuardarCotizacion');
        btnPrincipal.prop('disabled', false).html(btnPrincipal.data('texto-original') || btnPrincipal.html());

        $('#inputCantidad').val('');
        $('#selectTamano').val('').trigger('change');
        $('#selectCantidadColores').val('1').trigger('change');

        $('#contenedorColoresLana .select2-ajax').each(function () {
            $(this).val(null).trigger('change');
        });

        const switches = [
            '#swCortinaLana', '#swCortinaFiesta',
            '#swLazoSimple',  '#swLazoFlor', '#swLazoNombre',
            '#swApliques',    '#swDisenoPersonalizado',
        ];

        switches.forEach(function (id) {
            if ($(id).is(':checked')) {
                $(id).prop('checked', false).trigger('change');
            }
        });

        $('#contenedorCortinasLana select, #contenedorCortinasFiesta select, #contenedorFlores select')
            .each(function () {
                $(this).val(null).trigger('change');
            });

        $('#panelExportar').slideUp(function () {
            $(this).addClass('d-none');
        });

        $('#txtGananciaFija').text('$ 0.00');
        calcularCotizacion();

        $('#selectSolicitudWeb').val(null).trigger('change');
    }

    // =======================================================
    // 5.6 ENVÍO DE NOTA DE VENTA POR CORREO
    // =======================================================
    $(document).on('click', '#btnConfirmarEnvio', function (e) {
        e.preventDefault();

        let btn = $(this);
        let textoOriginal = btn.html();
        let emailDestino = $('#emailCliente').val();
        let pedidoId = $('#txtNumeroCotizacionExport').text();

        if (!emailDestino) {
            alert("Por favor, ingresa un correo electrónico.");
            return;
        }

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: '/pedidos/enviar-correo',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                pedido_id: pedidoId,
                email: emailDestino
            },
            success: function (response) {
                console.log("¡Servidor respondió con éxito!", response);

                if (response.success) {
                    // Cerramos el modal de captura de correo y limpiamos el input.
                    $('#modalEnviarCorreo').modal('hide');
                    $('#emailCliente').val('');

                    // Mostramos el modal de confirmación de envío.
                    $('#modalCorreoExito').modal('show');
                }
            },
            error: function (xhr) {
                alert("Hubo un error al enviar el correo. Revisa la consola.");
                console.error(xhr.responseText);
            },
            complete: function () {
                // Restauramos el botón a su estado original, haya éxito o error.
                btn.prop('disabled', false).html(textoOriginal);
            }
        });
    });

});