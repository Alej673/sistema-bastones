// =======================================================
// resources/js/cotizador/configNegocio.js
// =======================================================
//
// PANEL ÚNICO DE REGLAS DE NEGOCIO Y PRECIOS FANTASMA
// =======================================================
// Aquí viven TODOS los números "de negocio" que antes estaban
// hardcodeados dentro de cotizador.js: precios fantasma, recetas
// fijas, umbrales de mayoreo, márgenes de ganancia, metros/gramos
// por unidad, etc.
//
// REGLA DE ORO: cotizador.js NO debe tener números mágicos sueltos.
// Si mañana hay que subir un precio o cambiar una receta, se cambia
// AQUÍ, en un solo lugar, y no se toca la lógica de cálculo.
//
// Se importa como un solo objeto:
//   import { CONFIG_NEGOCIO } from './configNegocio.js';
// =======================================================

export const CONFIG_NEGOCIO = {

    // -------------------------------------------------------
    // PRECIOS FANTASMA
    // Precio de referencia que se usa SOLO cuando el cliente pide
    // un material nuevo que todavía no existe en el inventario real
    // (Select2 "Cotizar nuevo material"). Evita dejar el cálculo en
    // $0 mientras bodega registra el insumo.
    // -------------------------------------------------------
    preciosFantasma: {
        lana:                 0.0127,  // $1.15 / 90g
        cinta_garza:          0.11,    // $5.00 / 45.72m
        cinta_satin:          0.16,    // $3.00 / 18.28m
        cinta_gross:          0.15,    // $3.50 / 22.86m
        elastico:             0.09,    // $0.90 / 10m
        cinchos:              0.02,    // $2.00 / 100u
        cortina_fiesta_menor: 1.00,    // pedido < umbral de mayoreo
        cortina_fiesta_mayor: 0.50,    // pedido >= umbral de mayoreo
    },

    // -------------------------------------------------------
    // RECETA FIJA DE ENSAMBLAJE
    // Cantidades fijas de insumos por cada bastón producido,
    // sin importar el diseño elegido.
    // -------------------------------------------------------
    receta: {
        cinchos_por_baston:  3,     // 3 cinchos por unidad
        elastico_por_baston: 0.40,  // 0.40 m (40 cm) por unidad
    },

    // -------------------------------------------------------
    // BASE DEL BASTÓN (Fase 1)
    // Precio "fantasma" por color/acabado + umbral de mayoreo.
    // Se usa solo si no hay un insumo real en el Kardex que
    // coincida con color + tamaño.
    // -------------------------------------------------------
    baseBaston: {
        umbralMayoreo: 12, // cantidad mínima de bastones para precio de mayoreo
        dorado: {
            normal:  5.50,
            mayoreo: 5.00,
        },
        plata: {
            normal:  5.00,
            mayoreo: 4.50,
        },
        // Tamaños (en cm, como string) considerados "grandes" — afectan consumo de lana.
        tamanosGrandes: ['55', '60'],
    },

    // -------------------------------------------------------
    // CUERPO (LANA) — Fase 3
    // Consumo de lana en gramos por bastón, según el tamaño.
    // -------------------------------------------------------
    lana: {
        consumoGramosGrande: 150,
        consumoGramosNormal: 135,
        gramosPorMadeja:     90, // para calcular "madejas necesarias" a comprar
    },

    // -------------------------------------------------------
    // CORTINAS — Fase 4
    // -------------------------------------------------------
    cortinas: {
        lana: {
            gramosPorCortina: 30, // por bastón, por cada color de cortina de lana
        },
        fiesta: {
            unidadesPorPaquete: 4,   // se venden por paquete
            umbralMayoreo:      12,  // total de cortinas físicas del pedido
        },
    },

    // -------------------------------------------------------
    // DECORACIÓN Y APLIQUES — Fase 5
    // Metros de cinta consumidos por unidad, y recargos fijos de
    // mano de obra (ej. bordado de nombre).
    // -------------------------------------------------------
    decoracion: {
        lazoSimple: {
            metrosPorUnidad: 1.5,
            recargoManoObra: 0,
        },
        flor: {
            metrosPorUnidad: 1.0,
            recargoManoObra: 0,
        },
        lazoConNombre: {
            metrosPorUnidad: 1.0,
            recargoManoObra: 0.70, // bordado del nombre
        },
        apliques: {
            precioUnitario: 0.50,
        },
    },

    // -------------------------------------------------------
    // MARGEN DE GANANCIA — Panel financiero final
    // -------------------------------------------------------
    finanzas: {
        porcentajeGanancia: 0.60, // 60% sobre el costo de materiales
    },

};