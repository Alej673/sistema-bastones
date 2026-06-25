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
        tabla.empty();

        let costoTotalMateriales = 0;
        let costoTotalManoObra   = 0;

        const cantidadBastones = parseInt($('#inputCantidad').val()) || 0;
        const colorBase        = $('#selectAcabado').val();
        const tamanoBase       = $('#selectTamano').val();

        // --- GUARDA DEL FORMULARIO ---
        if (cantidadBastones <= 0 || !colorBase || !tamanoBase) {
            tabla.append(`
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

        tabla.append(`
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

        tabla.append(`
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
        // FASE 3: CUERPO (LANA) - ✅ CORREGIDO Y OPTIMIZADO
        // ---------------------------------------------------
        const consumoLana_g = esGrande ? 150 : 135;
        let coloresActivos  = 0;

        $('#contenedorColoresLana .select2-ajax').each(function () {
            const data = $(this).select2('data');
            if (data && data.length > 0 && data[0].id !== '') coloresActivos++;
        });

        if (coloresActivos === 0) coloresActivos = 1;
        const gramosPorColorTotal = (consumoLana_g / coloresActivos) * cantidadBastones;

        $('#contenedorColoresLana .select2-ajax').each(function () {
            const dataSelect = $(this).select2('data');
            if (!dataSelect || dataSelect.length === 0) return;

            const seleccion  = dataSelect[0];
            if (!seleccion.id) return;

            let nombreLana   = seleccion.text;
            const esTagNuevo = seleccion.newTag || isNaN(seleccion.id);
            let costoLana    = 0;
            let stockActual  = 0;
            
            // ✅ Declaramos insumoBD en el scope correcto
            let insumoBD = null; 

            if (!esTagNuevo) {
                insumoBD = INVENTARIO.find(item => item.id == seleccion.id);
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

            tabla.append(`
                <tr>
                    <td class="fw-bold text-dark">Cuerpo: ${nombreLana}</td>
                    <td class="text-muted small">${gramosPorColorTotal.toFixed(1)}g calculados</td>
                    <td class="text-muted fw-bold">$${costoLana.toFixed(2)}</td>
                    <td class="text-end">${textoStock}</td>
                </tr>
            `);

            agregarAlCarrito({
                // ✅ Ahora insumoBD existe en este scope y el ternario evaluará correctamente
                insumo_id: insumoBD?.id ?? null, 
                nombre_material: nombreLana,
                cantidad_requerida: gramosPorColorTotal,
                subtotal_calculado: costoLana,
            });
        });


        // ---------------------------------------------------
        // FASE 4: CORTINAS (LANA Y FIESTA) - ✅ OPTIMIZADO
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
                    let insumoBD        = null; // ✅ Optimizando scope

                    if (!esTagNuevo) {
                        insumoBD = INVENTARIO.find(item => item.id == idMaterial);
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

                    tabla.append(`
                        <tr>
                            <td class="fw-bold text-dark">Cortina Lana: ${nombreMaterial}</td>
                            <td class="text-muted small">${gramosPorCortinaLana.toFixed(1)}g calculados</td>
                            <td class="text-muted fw-bold">$${costoCalculado.toFixed(2)}</td>
                            <td class="text-end">${textoAlerta}</td>
                        </tr>
                    `);

                    agregarAlCarrito({
                        insumo_id: insumoBD?.id ?? null, // ✅ Ya no itera todo el arreglo de nuevo
                        nombre_material: nombreMaterial,
                        cantidad_requerida: gramosPorCortinaLana,
                        subtotal_calculado: costoCalculado,
                    });
                }
            });
        }

        // 4.2 Cortinas de Fiesta
        if ($('#swCortinaFiesta').is(':checked')) {
            let coloresFiesta = 0;
            $('#contenedorCortinasFiesta select').each(function () {
                let data = $(this).select2('data');
                if (data && data.length > 0 && data[0].id !== '') coloresFiesta++;
            });

            if (coloresFiesta > 0) {
                let totalCortinasFisicas = cantidadBastones * coloresFiesta;
                let precioFantasmaFiesta = (totalCortinasFisicas >= 12)
                    ? PRECIOS_FANTASMA.cortina_fiesta_mayor
                    : PRECIOS_FANTASMA.cortina_fiesta_menor;

                let cortinasPorColor = cantidadBastones;
                let paquetesPorColor = cortinasPorColor / 4;

                $('#contenedorCortinasFiesta select').each(function () {
                    let dataSelect = $(this).select2('data');
                    if (dataSelect && dataSelect.length > 0 && dataSelect[0].id !== '') {
                        let seleccion      = dataSelect[0];
                        let nombreMaterial = seleccion.text.replace(' (Cotizar nuevo material)', '');
                        let idMaterial     = seleccion.id;
                        let esTagNuevo     = seleccion.newTag || isNaN(idMaterial);

                        let costoCalculado = 0;
                        let faltaStock     = false;
                        let textoAlerta    = '';
                        let insumoBD       = null; // ✅ Optimizando scope

                        if (!esTagNuevo) {
                            insumoBD = INVENTARIO.find(item => item.id == idMaterial);
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

                        tabla.append(`
                            <tr>
                                <td class="fw-bold text-dark">Cortina Fiesta: ${nombreMaterial}</td>
                                <td class="text-muted small">${cortinasPorColor} cortinas calculadas</td>
                                <td class="text-muted fw-bold">$${costoCalculado.toFixed(2)}</td>
                                <td class="text-end">${textoAlerta}</td>
                            </tr>
                        `);

                        agregarAlCarrito({
                            insumo_id: insumoBD?.id ?? null, // ✅ Más limpio
                            nombre_material: nombreMaterial,
                            cantidad_requerida: cortinasPorColor,
                            subtotal_calculado: costoCalculado,
                        });
                    }
                });
            }
        }


        // =======================================================
        // FASE 5: DECORACIÓN Y APLIQUES - ✅ OPTIMIZADO
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
                let insumoBD        = null; // ✅ Optimizando scope

                if (!esTagNuevo) {
                    insumoBD = INVENTARIO.find(item => item.id == idMaterial);
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

                tabla.append(`
                    <tr>
                        <td class="fw-bold text-dark">${nombreFila}: ${nombreMaterial}</td>
                        <td class="text-muted small">${metrosTotales.toFixed(2)}m calculados</td>
                        <td class="fw-bold text-muted">$${subtotalVisual}${detalleRecargo}</td>
                        <td class="text-end">${textoAlerta}</td>
                    </tr>
                `);

                agregarAlCarrito({
                    insumo_id: insumoBD?.id ?? null, // ✅ Evita la doble búsqueda
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

            tabla.append(`
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

            tabla.append(`
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

    const selectoresFormulario = [
        '#inputCantidad', '#selectAcabado', '#selectTamano', '#selectCantColores',
        '#swCortinaLana', '#selectCantCortinaLana', '#swCortinaFiesta', '#selectCantCortinaFiesta',
        '#swLazoSimple', '#swLazoFlor', '#selectCantFlores', '#swLazoNombre',
        '#swApliques', '#cantApliques', '#swDisenoPersonalizado', '#selectNivelDiseno',
    ].join(', ');

    $(selectoresFormulario).on('input change', function (e) {
        calcularCotizacion();
    });

    $('body').on('change', 'select', function () {
        calcularCotizacion();
    });

    calcularCotizacion();


    // =======================================================
    // GUARDAR COTIZACIÓN — AJAX + modal de validación
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
            mostrarAlerta(faltantes);
            return; 
        }

        const btnGuardar    = $(this);
        const textoOriginal = btnGuardar.html();
        btnGuardar.prop('disabled', true)
                  .html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando Pedido...');

        let datosFormulario = $('#formCotizador').serializeArray();
        datosFormulario.push({ name: 'costo_materiales', value: $('#txtCostoMateriales').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'costo_extras',     value: $('#txtCostoManoObra').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'ganancia_fija',    value: $('#txtGananciaFija').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'costo_total',      value: $('#txtCostoTotal').text().replace('$', '').trim() });
        datosFormulario.push({ name: 'costo_unitario',   value: $('#txtCostoUnitario').text().replace('$', '').replace('c/u', '').trim() });

        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        datosFormulario.push({ name: 'materiales', value: JSON.stringify(window.carritoInsumos) });

        $.ajax({
            url:  '/cotizaciones/guardar',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: datosFormulario,
            success: function (response) {
                $('#modalNumCotizacion').text(response.id);
                $('#modalExito').modal('show');

                setTimeout(function () {
                    $('#modalExito').modal('hide');
                    $('#panelExportar').removeClass('d-none').hide().slideDown();
                    btnGuardar.prop('disabled', false)
                              .html('<i class="fa-solid fa-floppy-disk"></i> Confirmar y Guardar Pedido');
                    resetearFormulario();
                }, 2000);
            },
            error: function (xhr) {
                console.error('Fallo al guardar:', xhr.responseText);
                mostrarAlerta(
                    'No se pudo conectar con el servidor. Revisa tu conexión o avisa a soporte técnico.',
                    'Error al guardar el pedido',
                    'danger'
                );
                btnGuardar.prop('disabled', false).html(textoOriginal);
            }
        });
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

});