// =======================================================
// resources/js/cotizador.js
// =======================================================

// — IMPORTACIONES DE MÓDULOS —
import { inicializarModuloLana }       from './cotizador/moduloLana.js';
import { inicializarModuloCortinas }   from './cotizador/moduloCortinas.js';
import { inicializarModuloDecoracion } from './cotizador/moduloCintas.js';
import { inicializarModuloDiseno }     from './cotizador/moduloDiseno.js';

// — ISLA DE DATOS (Laravel → JS) —
const nodoInventario = document.getElementById('datos-inventario');
const INVENTARIO     = nodoInventario ? JSON.parse(nodoInventario.textContent) : [];
const RUTAS          = window.KardexConfig.rutas;

// ✅ Índice de inventario por ID para búsquedas O(1). Antes cada
// INVENTARIO.find(item => item.id == X) recorría todo el arreglo,
// y esto se repetía dentro de cada .each() de colores/cortinas/cintas.
const INVENTARIO_POR_ID = new Map(INVENTARIO.map(item => [String(item.id), item]));

function buscarInsumoPorId(id) {
    return INVENTARIO_POR_ID.get(String(id)) || null;
}


function mostrarAlerta(contenido, titulo = 'Faltan campos obligatorios', tipo = 'warning') {

    const config = {
        warning: {
            icono: 'fa-triangle-exclamation',
            color: '#BA7517',
            fondo: '#FAEEDA',
            texto: '<i class="fa-solid fa-pen-to-square me-1"></i> Entendido',
        },
        danger: {
            icono: 'fa-circle-xmark',
            color: '#A32D2D',
            fondo: '#FCEBEB',
            texto: '<i class="fa-solid fa-rotate-right me-1"></i> Cerrar e intentar de nuevo',
        },
        info: {
            icono: 'fa-circle-info',
            color: '#185FA5',
            fondo: '#E6F1FB',
            texto: '<i class="fa-solid fa-check me-1"></i> Entendido',
        }
    };

    const c = config[tipo] || config.warning;

    // ==========================
    // ICONO
    // ==========================
    const iconoEl = $('#modalValidacion .fa-solid').first();

    iconoEl.attr('class', `fa-solid ${c.icono}`)
        .css({
            color: c.color,
            fontSize: '20px'
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
            boxShadow: '0 4px 12px rgba(0,0,0,.08)',
            marginTop: '2px'
        });

    // ==========================
    // TITULO
    // ==========================
    $('#modalValidacionTitulo')
        .text(titulo)
        .css({
            fontSize: '18px',
            fontWeight: '600',
            letterSpacing: '-0.2px'
        });

    // ==========================
    // LISTA DE MENSAJES
    // ==========================
    const lista = $('#listaValidacion');
    lista.empty();

    if (Array.isArray(contenido)) {

        lista.append(`
            <div class="small text-muted mb-3">
                Se encontraron
                <strong>${contenido.length}</strong>
                campo${contenido.length > 1 ? 's' : ''}
                pendiente${contenido.length > 1 ? 's' : ''}.
            </div>
        `);

        contenido.forEach(function (campo) {

            lista.append(`
                <li class="d-flex align-items-center gap-2 rounded-3 px-3 py-2 mb-2"
                    style="
                        background:#fff;
                        border:1px solid #ececec;
                        box-shadow:0 2px 8px rgba(0,0,0,.03);
                    ">
                    
                    <div style="
                        width:7px;
                        height:7px;
                        border-radius:50%;
                        background:${c.color};
                        flex-shrink:0;
                    "></div>

                    <span style="
                        font-size:13px;
                        color:#343a40;
                    ">
                        ${campo}
                    </span>
                </li>
            `);

        });

    } else {

        lista.append(`
            <li class="d-flex align-items-start gap-2 rounded-3 px-3 py-3"
                style="
                    background:#fff;
                    border:1px solid #ececec;
                    box-shadow:0 2px 8px rgba(0,0,0,.03);
                ">

                <i class="fa-solid fa-circle-info"
                   style="
                        color:${c.color};
                        margin-top:3px;
                   ">
                </i>

                <span style="
                    font-size:13px;
                    color:#343a40;
                ">
                    ${contenido}
                </span>

            </li>
        `);

    }

    // ==========================
    // BOTON
    // ==========================
    $('#modalValidacion .modal-footer .btn')
        .attr('class', 'btn w-100 rounded-3')
        .css({
            background: c.fondo,
            color: c.color,
            border: 'none',
            fontSize: '13px',
            fontWeight: '600',
            padding: '10px',
            boxShadow: '0 2px 10px rgba(0,0,0,.05)'
        })
        .html(c.texto);

    // ==========================
    // MODAL
    // ==========================
    $('#modalValidacion .modal-content').css({
        border: 'none',
        borderRadius: '18px',
        overflow: 'hidden',
        boxShadow: '0 20px 40px rgba(0,0,0,.12)'
    });

    // ==========================
    // MOSTRAR
    // ==========================
    const modal = new bootstrap.Modal(
        document.getElementById('modalValidacion')
    );

    modal.show();
}


// =======================================================
// BLOQUE PRINCIPAL — Un solo $(document).ready()
// =======================================================
$(document).ready(function () {

    // — Inicializar módulos de secciones del formulario —
    inicializarModuloLana(RUTAS);
    inicializarModuloCortinas(RUTAS);
    inicializarModuloDecoracion(RUTAS);
    inicializarModuloDiseno();


    // {{-- ==========================================
    //   MOTOR DE CÁLCULO — COTIZADOR AUTOMÁTICO
    //   Recalcula todo cada vez que el usuario cambia
    //   cualquier campo del formulario.
    // ========================================== --}}


    // =======================================================
    // 1. CONSTANTES Y CONFIGURACIÓN BASE
    // =======================================================

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

    const RECETA = {
        cinchos_por_baston:  3,     // 3 cinchos por unidad
        elastico_por_baston: 0.40,  // 0.40 m (40 cm) por unidad
    };


    // =======================================================
    // 2. FUNCIÓN MAESTRA — Recalcula la tabla y los totales
    // =======================================================
    function agregarAlCarrito({ insumo_id = null, nombre_material, cantidad_requerida, subtotal_calculado }) {
        window.carritoInsumos.push({
            insumo_id: insumo_id ?? null,
            nombre_material: nombre_material || '',
            cantidad_requerida: parseFloat(cantidad_requerida) || 0,
            subtotal_calculado: parseFloat(subtotal_calculado) || 0,
        });
    }

    function calcularCotizacion() {

        window.carritoInsumos = [];
        const tabla = $('#cuerpoTablaImpacto');

        // ✅ Acumulamos el HTML de todas las filas y lo pintamos una sola
        // vez al final con tabla.html(...). Antes cada tabla.append(...)
        // forzaba un reflow individual del navegador.
        const filasHtml = [];

        let costoTotalMateriales = 0;
        let costoTotalManoObra   = 0;

        const cantidadBastones = parseInt($('#inputCantidad').val()) || 0;
        const colorBase        = $('#selectAcabado').val();
        const tamanoBase       = $('#selectTamano').val();

        // --- GUARDA DEL FORMULARIO ---
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
        // ---------------------------------------------------
        let precioBaseUnitario = 0;
        let nombreAcabado      = '';

        if (colorBase === 'dorado') {
            nombreAcabado      = 'Dorado';
            precioBaseUnitario = (cantidadBastones >= 12) ? 5.00 : 5.50;
        } else {
            nombreAcabado      = 'Plata';
            precioBaseUnitario = (cantidadBastones >= 12) ? 4.50 : 5.00;
        }

        let costoTotalBases = precioBaseUnitario * cantidadBastones;
        costoTotalMateriales += costoTotalBases;

        let esGrande     = (tamanoBase === '55' || tamanoBase === '60');
        let tamanoVisual = esGrande ? '55-60 cm' : '45-50 cm';

        let baseEnKardex = INVENTARIO.find(item => {
            if (item.categoria !== 'base_baston' || !item.nombre) return false;
            let nombreBD     = item.nombre.toLowerCase();
            let colorBuscado  = colorBase.toLowerCase();
            let tamanoBuscado = tamanoBase + 'cm';
            return nombreBD.includes(colorBuscado) && nombreBD.includes(tamanoBuscado);
        });

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

        agregarAlCarrito({
            insumo_id: baseEnKardex?.id ?? null,
            nombre_material: `Base ${nombreAcabado} (${tamanoVisual})`,
            cantidad_requerida: cantidadBastones,
            subtotal_calculado: costoTotalBases,
        });

        // ---------------------------------------------------
        // FASE 2: INSUMOS FIJOS DE ENSAMBLAJE
        // ---------------------------------------------------
        const totalCinchos  = RECETA.cinchos_por_baston  * cantidadBastones;
        const totalElastico = RECETA.elastico_por_baston * cantidadBastones;

        const costoCinchos  = totalCinchos  * PRECIOS_FANTASMA.cinchos;
        const costoElastico = totalElastico * PRECIOS_FANTASMA.elastico;

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
                <td class="text-muted small">${totalCinchos} u. &times; $${PRECIOS_FANTASMA.cinchos.toFixed(2)}/u</td>
                <td class="text-muted small">$${costoCinchos.toFixed(2)}</td>
                <td class="text-end text-muted">&mdash;</td>
            </tr>
            <tr>
                <td class="text-dark ps-3">&#x21B3; Elástico</td>
                <td class="text-muted small">${totalElastico.toFixed(2)}m &times; $${PRECIOS_FANTASMA.elastico.toFixed(2)}/m</td>
                <td class="text-muted small">$${costoElastico.toFixed(2)}</td>
                <td class="text-end text-muted">&mdash;</td>
            </tr>
        `);

        agregarAlCarrito({ insumo_id: null, nombre_material: 'Cinchos', cantidad_requerida: totalCinchos, subtotal_calculado: costoCinchos });
        agregarAlCarrito({ insumo_id: null, nombre_material: 'Elástico', cantidad_requerida: totalElastico, subtotal_calculado: costoElastico });


        // ---------------------------------------------------
        // FASE 3: CUERPO (LANA)
        // ---------------------------------------------------
        const consumoLana_g = esGrande ? 150 : 135;

        // ✅ Una sola pasada por los selects: recogemos las selecciones
        // válidas y de una vez sabemos cuántas hay (antes se recorría
        // el mismo .each() dos veces: una para contar, otra para calcular).
        const seleccionesLana = [];
        $('#contenedorColoresLana .select2-ajax').each(function () {
            const data = $(this).select2('data');
            if (data && data.length > 0 && data[0].id !== '') seleccionesLana.push(data[0]);
        });

        const coloresActivos = seleccionesLana.length || 1;
        const gramosPorColorTotal = (consumoLana_g / coloresActivos) * cantidadBastones;

        seleccionesLana.forEach(function (seleccion) {
            let nombreLana   = seleccion.text;
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

        // 4.1 Cortinas de Lana
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
                        nombre_material: nombreMaterial,
                        cantidad_requerida: gramosPorCortinaLana,
                        subtotal_calculado: costoCalculado,
                    });
                }
            });
        }

        // 4.2 Cortinas de Fiesta
        if ($('#swCortinaFiesta').is(':checked')) {

            // ✅ Igual que en lana: una sola pasada para recoger las
            // selecciones válidas, en vez de recorrer el select dos veces.
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
                            <td class="text-muted fw-bold">$${costoCalculado.toFixed(2)}</td>
                            <td class="text-end">${textoAlerta}</td>
                        </tr>
                    `);

                    agregarAlCarrito({
                        insumo_id: insumoBD?.id ?? null,
                        nombre_material: nombreMaterial,
                        cantidad_requerida: cortinasPorColor,
                        subtotal_calculado: costoCalculado,
                    });
                });
            }
        }


        // =======================================================
        // FASE 5: DECORACIÓN Y APLIQUES
        // =======================================================

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
                    let precioFantasma  = PRECIOS_FANTASMA.cinta_gross; // Default
                    let textoMinuscula  = nombreMaterial.toLowerCase();

                    if (textoMinuscula.includes('garza'))                          precioFantasma = PRECIOS_FANTASMA.cinta_garza;
                    else if (textoMinuscula.includes('satin') ||
                             textoMinuscula.includes('satín'))                     precioFantasma = PRECIOS_FANTASMA.cinta_satin;

                    costoCalculado = metrosTotales * precioFantasma;
                }

                costoTotalMateriales += costoCalculado;

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

        // 5.1 Lazo Simple
        if ($('#swLazoSimple').is(':checked')) procesarCinta('select[name="cinta_lazo_simple"]', 'Lazo Simple', 1.5, 0);

        // 5.2 Flores Dinámicas
        if ($('#swLazoFlor').is(':checked')) {
            let numeroFlor = 1;
            $('#contenedorFlores select').each(function () {
                procesarCinta(this, `Flor ${numeroFlor}`, 1.0, 0);
                numeroFlor++;
            });
        }

        // 5.3 Lazo con Nombre
        if ($('#swLazoNombre').is(':checked')) procesarCinta('select[name="cinta_lazo_nombre"]', 'Lazo c/ Nombre', 1.0, 0.70);

        // 5.4 Apliques Manuales
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
        // FASE 6: DISEÑOS PERSONALIZADOS (MANO DE OBRA)
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
        // ACTUALIZACIÓN DEL PANEL FINANCIERO VISUAL
        // =======================================================

        let tarifaManoObraFija = 3.00;
        let totalGananciaFija  = tarifaManoObraFija * cantidadBastones;

        let granTotal     = costoTotalMateriales + costoTotalManoObra + totalGananciaFija;
        let costoUnitario = granTotal / cantidadBastones;

        $('#txtCostoMateriales').text(`$ ${costoTotalMateriales.toFixed(2)}`);
        $('#txtCostoManoObra').text(`$ ${costoTotalManoObra.toFixed(2)}`); 
        $('#txtGananciaFija').text(`$ ${totalGananciaFija.toFixed(2)}`);

        $('#txtCostoTotal').text(`$ ${granTotal.toFixed(2)}`);
        $('#txtCostoUnitario').text(`$ ${costoUnitario.toFixed(2)} c/u`);

        $('#btnGuardarCotizacion').prop('disabled', false);

    } 


    // =======================================================
    // 3. TRIGGERS — Eventos que disparan el recálculo
    // =======================================================

    // Debounce corto: agrupa cambios que llegan casi al mismo tiempo
    // (por ejemplo, Select2 puede disparar varios 'change' en cadena),
    // así calcularCotizacion() no se ejecuta 3-4 veces por un solo clic.
    let temporizadorRecalculo = null;
    function recalcularConDebounce() {
        clearTimeout(temporizadorRecalculo);
        temporizadorRecalculo = setTimeout(calcularCotizacion, 80);
    }

    // Controles simples que NO son <select> (inputs de texto/número y switches).
    // Los <select> se manejan aparte, en un único listener delegado más abajo.
    const controlesSimples = [
        '#inputCantidad',
        '#swCortinaLana', '#swCortinaFiesta',
        '#swLazoSimple', '#swLazoFlor', '#swLazoNombre',
        '#swApliques', '#cantApliques', '#swDisenoPersonalizado',
    ].join(', ');

    $(controlesSimples).on('input change', recalcularConDebounce);
    $('body').on('change', 'select', recalcularConDebounce);

    calcularCotizacion(); 


    // =======================================================
    // GUARDAR COTIZACIÓN — AJAX + modal de validación
    // =======================================================
    // =======================================================
    // PASO 1: VALIDACIÓN (Botón verde de la pantalla principal)
    // =======================================================
    $('#btnGuardarCotizacion').on('click', function (e) {
        e.preventDefault();

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

        if (faltantes.length > 0) {
            mostrarAlerta(faltantes, 'Faltan campos obligatorios', 'warning');
            return; 
        }

        // Todo bien: Mostramos el modal para pedir los datos del cliente
        const modalCliente = new bootstrap.Modal(document.getElementById('modalDatosCliente'));
        modalCliente.show();
    });

    // =======================================================
    // PASO 2: GUARDAR COTIZACIÓN (Botón dentro del Modal Cliente)
    // =======================================================
    $('#btnConfirmarPedidoModal').on('click', function (e) {
        e.preventDefault();

        const nombreCliente = $('#inputNombreCliente').val();
        const correoCliente = $('#inputCorreoCliente').val();

        if(nombreCliente.trim() === '') {
            mostrarAlerta('Por favor ingresa el nombre del cliente.', 'Falta información', 'warning');
            return;
        }

        const btnGuardar    = $(this);
        const textoOriginal = btnGuardar.html();
        btnGuardar.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando Pedido...');

        let datosFormulario = $('#formCotizador').serializeArray();
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
                    // A. Ocultamos el modal de captura de cliente (usando jQuery clásico)
                    $('#modalDatosCliente').modal('hide');

                    // B. Imprimimos el ID en los modales de éxito
                    $('#modalNumCotizacion').text(response.id);
                    $('#txtNumeroCotizacionExport').text(response.id);
                    
                    // C. Inyectamos el ID en los botones PDF
                    $('#btnPdfReceta').attr('href', '/pedidos/' + response.id + '/pdf-receta');
                    $('#btnPdfNota').attr('href', '/pedidos/' + response.id + '/pdf-nota');
                    
                    // D. Mostramos el éxito usando jQuery clásico
                    $('#modalExito').modal('show');

                    setTimeout(function () {
                        // Quitamos foco para evitar la alerta amarilla
                        if (document.activeElement) {
                            document.activeElement.blur();
                        }
                        
                        // Ocultamos el éxito
                        $('#modalExito').modal('hide');
                        
                        // Mostramos el panel (AQUÍ ES DONDE DEBE APARECER)
                        $('#panelExportar').removeClass('d-none').hide().slideDown();
                        
                        btnGuardar.prop('disabled', false).html(textoOriginal);
                    }, 1800);
                },
            error: function (xhr) {
                console.error('Fallo al guardar:', xhr.responseText);
                mostrarAlerta('No se pudo conectar con el servidor. Revisa tu conexión.', 'Error al guardar el pedido', 'danger');
                btnGuardar.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // =======================================================
    // PASO 3: CERRAR FLUJO Y LIMPIAR PANTALLA (Delegación)
    // =======================================================
    $(document).on('click', '#btnCerrarFlujoExportacion', function(e) {
        e.preventDefault(); 
        
        // 1. Limpiamos el modal del cliente
        $('#inputNombreCliente').val('');
        $('#inputCorreoCliente').val('');
        
        // 2. Ejecutamos tu función de reseteo original
        resetearFormulario();
    });

    // =======================================================
    // FUNCIÓN DE RESETEO
    // =======================================================
    function resetearFormulario() {
        $('#inputCantidad').val('');
        $('#selectTamano').val('').trigger('change');
        $('#selectCantColores').val('1').trigger('change');

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
    }

    // =======================================================
    // ENVÍO DE CORREOS
    // =======================================================
    $(document).on('click', '#btnConfirmarEnvio', function(e) {
        e.preventDefault();
        
        let btn = $(this);
        let textoOriginal = btn.html();
        let emailDestino = $('#emailCliente').val();
        let pedidoId = $('#txtNumeroCotizacionExport').text(); 

        if(!emailDestino) {
            alert("Por favor, ingresa un correo electrónico.");
            return;
        }

        // Efecto de carga en el botón
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: '/pedidos/enviar-correo',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                pedido_id: pedidoId,
                email: emailDestino
            },
            success: function(response) {
                // 1. HERRAMIENTA DE DISEÑO
                console.log("¡Servidor respondió con éxito!", response);
                
                if(response.success) {
                    // 2. Cerramos el modal de captura de forma limpia
                    $('#modalEnviarCorreo').modal('hide');
                    
                    // 3. Limpiamos el input para futuras cotizaciones
                    $('#emailCliente').val(''); 
                    
                    // 4. Mostramos el nuevo modal estético de éxito
                    $('#modalCorreoExito').modal('show');
                }
            },
            error: function(xhr) {
                alert("Hubo un error al enviar el correo. Revisa la consola.");
                console.error(xhr.responseText);
            },
            complete: function() {
                // Restauramos el botón a su estado original
                btn.prop('disabled', false).html(textoOriginal);
            }
        });
    });

});