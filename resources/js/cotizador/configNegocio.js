// =======================================================
// resources/js/cotizador/configNegocio.js
// =======================================================
// Ahora este archivo actúa como un ADAPTADOR. 
// Toma los datos vivos de la Base de Datos (window.DB_AJUSTES)
// y los estructura para que cotizador.js los consuma sin romperse.
// =======================================================

const db = window.DB_AJUSTES || {};

export const CONFIG_NEGOCIO = {

    preciosFantasma: {
        lana:                 parseFloat(db['pf_lana'] || 0.0127),
        cinta_garza:          parseFloat(db['pf_cinta_garza'] || 0.11),
        cinta_satin:          parseFloat(db['pf_cinta_satin'] || 0.16),
        cinta_gross:          parseFloat(db['pf_cinta_gross'] || 0.15),
        elastico:             parseFloat(db['pf_elastico'] || 0.09),
        cinchos:              parseFloat(db['pf_cinchos'] || 0.02),
        cortina_fiesta_menor: parseFloat(db['pf_cortina_menor'] || 1.00),
        cortina_fiesta_mayor: parseFloat(db['pf_cortina_mayor'] || 0.50),
    },

    receta: {
        cinchos_por_baston:  parseInt(db['receta_cinchos'] || 3),
        elastico_por_baston: parseFloat(db['receta_elastico'] || 0.40),
    },

    baseBaston: {
        umbralMayoreo: parseInt(db['base_umbral_mayoreo'] || 12),
        dorado: {
            normal:  parseFloat(db['base_dorado_normal'] || 5.50),
            mayoreo: parseFloat(db['base_dorado_mayoreo'] || 5.00),
        },
        plata: {
            normal:  parseFloat(db['base_plata_normal'] || 5.00),
            mayoreo: parseFloat(db['base_plata_mayoreo'] || 4.50),
        },
        tamanosGrandes: ['55', '60'], // Esto lo podemos dejar estático, rara vez cambia
    },

    lana: {
        consumoGramosGrande: parseInt(db['lana_consumo_grande'] || 150),
        consumoGramosNormal: parseInt(db['lana_consumo_normal'] || 135),
        gramosPorMadeja:     parseInt(db['lana_gramos_madeja'] || 90),
    },

    cortinas: {
        lana: {
            gramosPorCortina: parseInt(db['cortina_lana_gramos'] || 30),
        },
        fiesta: {
            unidadesPorPaquete: 4,
            umbralMayoreo:      parseInt(db['cortina_fiesta_umbral'] || 12),
        },
    },

    decoracion: {
        lazoSimple: {
            metrosPorUnidad: parseFloat(db['deco_lazo_simple_m'] || 1.5),
            recargoManoObra: 0,
        },
        flor: {
            metrosPorUnidad: parseFloat(db['deco_flor_m'] || 1.0),
            recargoManoObra: 0,
        },
        lazoConNombre: {
            metrosPorUnidad: parseFloat(db['deco_lazo_nombre_m'] || 1.0),
            recargoManoObra: parseFloat(db['deco_lazo_nombre_mo'] || 0.70),
        },
        apliques: {
            precioUnitario: parseFloat(db['deco_apliques_precio'] || 0.50),
        },
    },

    finanzas: {
        porcentajeGanancia: parseFloat(db['margen_ganancia'] || 0.60),
    },

};