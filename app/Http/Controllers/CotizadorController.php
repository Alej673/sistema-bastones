<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotaVentaMailable;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Auth;

class CotizadorController extends Controller
{
    /**
     * Muestra la pantalla del Cotizador Automático (Fase 1 y 2)
     */
    public function create()
    {
        // 1. Recuperamos todos los insumos activos del inventario (no eliminados lógicamente)
        $insumos = \App\Models\Insumo::all();

        // 2. Los filtramos en colecciones separadas por categoría para armar los desplegables de la vista
        $lanas = $insumos->where('categoria', 'lana')->values();
        $cintasSatin = $insumos->where('categoria', 'cinta_satin')->values();
        $cintasGarza = $insumos->where('categoria', 'cinta_garza')->values();
        $cintasGross = $insumos->where('categoria', 'cinta_gross')->values();
        $cortinas = $insumos->where('categoria', 'cortina_fiesta')->values();
        $elasticos = $insumos->where('categoria', 'elastico')->values();
        $cinchos = $insumos->where('categoria', 'cinchos')->values();

        // Aquí cargamos las bases pre-cortadas (Bases Plata 45cm, Dorada 60cm, etc.) que estructuramos antes como insumos para que el cliente pueda elegirlas directamente
        $bases = $insumos->where('categoria', 'base_baston')->values();
        $unidadesSimples = $insumos->where('categoria', 'unidad_simple')->values();
        


        // 3. LA CLAVE DE LA REACTIVIDAD: Serializamos toda la colección a JSON
        // Esto le permitirá a JavaScript conocer ID, costos exactos por gramo/metro y stocks en tiempo real
        $insumosJson = $insumos->keyBy('id')->toJson();

        // 4. Enviamos los datos empaquetados a la vista del cotizador
        return view('cotizador.create', compact(
            'insumos',
            'lanas', 
            'cintasGarza', 
            'cintasSatin',
            'cintasGross', 
            'cortinas', 
            'elasticos', 
            'cinchos', 
            'bases', 
            'unidadesSimples',
            'insumosJson'
        ));
    }

    /**
     * Guarda la cotización final como un pedido en firme (Fase 4)
     */
    public function store(Request $request)
    {
        // Esto lo desarrollaremos cuando la matemática del Frontend esté 100% sólida
    }

    // Función para llamar a la base de datos de lanas
    public function buscarLanas(Request $request)
    {
        // Capturamos el término de búsqueda que envía Select2
        $term = $request->input('q');

        // Buscamos en la base de datos lanas que coincidan con lo escrito
        $lanas = Insumo::where('categoria', 'lana')
            ->where('nombre', 'LIKE', '%' . $term . '%')
            ->get()
            ->map(function ($lana) {
                return [
                    'id' => $lana->id,
                    'text' => $lana->nombre // Select2 exige obligatoriamente la propiedad "text"
                ];
            });

        return response()->json($lanas);
    }

    // Funcion para llamar a la base de datos de cortinas de fiesta
    public function buscarCortinas(Request $request)
    {
        // Capturamos el término de búsqueda
        $term = $request->input('q');

        // Filtramos específicamente por la categoría de la imagen: 'cortina_fiesta'
        $cortinas = Insumo::where('categoria', 'cortina_fiesta')
            ->where('nombre', 'LIKE', '%' . $term . '%')
            ->get()
            ->map(function ($cortina) {
                return [
                    'id' => $cortina->id,
                    'text' => $cortina->nombre 
                ];
            });

        return response()->json($cortinas);
    }

    // Función para llamar a la base de datos de cintas
    public function buscarCintas(Request $request)
    {
        $term = $request->input('q');

        // 1. Definimos las 3 categorías donde el sistema tiene permiso para buscar
        $categorias = ['cinta_satin', 'cinta_garza', 'cinta_gross'];

        // 2. Buscamos usando whereIn para abarcar todas esas familias
        $cintas = Insumo::whereIn('categoria', $categorias)
            ->where('nombre', 'LIKE', '%' . $term . '%')
            ->get()
            ->map(function ($cinta) {
                
                // Truco UX: Limpiamos la palabra "cinta_" para que se vea estético
                // Ej: "cinta_satin" -> "SATIN"
                $tipoVisual = strtoupper(str_replace('cinta_', '', $cinta->categoria));

                return [
                    'id' => $cinta->id,
                    // Devolverá algo como: "Color azul (SATIN)"
                    'text' => $cinta->nombre . ' (' . $tipoVisual . ')'
                ];
            });

        return response()->json($cintas);
    }

    public function buscarSolicitudesPendientes(Request $request)
    {
        // Si la petición viene con un ID específico (para autocompletar)
        if ($request->has('id')) {
            return response()->json(
                QuoteRequest::with('user')->find($request->id)
            );
        }

        // Búsqueda para el Select2
        $term = $request->get('q');

        $solicitudes = QuoteRequest::with('user')
            ->where('estado', 'pendiente')
            ->when($term, function ($query, $term) {
                $query->where('id', 'LIKE', "%{$term}%")
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('name', 'LIKE', "%{$term}%");
                    });
            })
            ->latest()
            ->take(10)
            ->get();

        // PASO CLAVE: Formatear el texto a medida según el tipo de producto
        $resultados = $solicitudes->map(function ($solicitud) {
            $categoriaOriginal = strtolower($solicitud->categoria ?? 'general');
            $categoriaFormateada = ucfirst($solicitud->categoria ?? 'Varios');

            // Verificamos si es un bastón
            $esBaston = in_array($categoriaOriginal, ['baston', 'bastones']);

            if ($esBaston) {
                // Extraemos medida y acabado (ignorando 'na')
                $medida = (strtolower($solicitud->medida_cm) !== 'na' && $solicitud->medida_cm) 
                            ? $solicitud->medida_cm . 'cm' : '';
                $acabado = (strtolower($solicitud->acabado) !== 'na' && $solicitud->acabado) 
                            ? $solicitud->acabado : '';
                
                $detalles = trim("$medida $acabado");
                
                // Resultado visual: (Bastón - 60cm Plata)
                $extra = $detalles ? "{$categoriaFormateada} - {$detalles}" : $categoriaFormateada;
            } else {
                // Para Manualidades, Flores o Lazos, solo mostramos la categoría
                // Resultado visual: (Manualidad)
                $extra = $categoriaFormateada;
            }

            // Armamos el texto final inyectando el $extra
            $texto = 'RQ-' . str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) . ' | ' . $solicitud->nombre . ' (' . $extra . ')';

            // Inyectamos la propiedad 'text' al objeto para Select2
            $solicitud->text = $texto;
            
            return $solicitud;
        });

        // Devolvemos los resultados ya limpios
        return response()->json($resultados);
    }

    public function guardar(Request $request)
    {
        // =======================================================
        // 1. EL BYPASS: MODO RÁPIDO (Manualidades, Lazos, Flores)
        // =======================================================
        if ($request->filled('modo_rapido') && $request->modo_rapido == 'true') {
            
            // Validación exprés
            $request->validate([
                'nombre_cliente'  => 'required|string|max:255',
                'costo_total'     => 'required|numeric',
                'concepto_rapido' => 'required|string|max:255',
            ]);

            try {
                DB::beginTransaction();

                // A. Creamos el registro maestro del pedido
                $pedido = new \App\Models\Pedido();
                $pedido->quote_request_id = $request->input('quote_request_id');
                $pedido->cliente_nombre   = $request->input('nombre_cliente');
                $pedido->correo_cliente   = $request->input('correo_cliente');
                
                // Llenamos con "ceros" la estructura de bastones para no romper tu base de datos
                $pedido->cantidad_total_bastones = 1; 
                $pedido->costo_materiales = 0;
                $pedido->costo_extras     = 0;
                $pedido->ganancia_fija    = 0;
                $pedido->costo_total      = (float) $request->input('costo_total');
                $pedido->costo_unitario   = (float) $request->input('costo_total');
                $pedido->estado           = 'pendiente'; 
                $pedido->save();

                // B. Sincronización BTO
                if ($request->filled('quote_request_id')) {
                    $solicitudWeb = \App\Models\QuoteRequest::find($request->input('quote_request_id'));
                    if ($solicitudWeb) {
                        $solicitudWeb->precio_final = (float) $request->input('costo_total');
                        $solicitudWeb->estado = 'cotizado';
                        $solicitudWeb->save();
                    }
                }

                // C. TRUCO ARQUITECTÓNICO: Guardamos el concepto como un único "material".
                $detalle = $request->input('concepto_rapido');
                if ($request->filled('detalles_rapido')) {
                    $detalle .= ' - ' . $request->input('detalles_rapido');
                }

                DB::table('pedido_materiales')->insert([
                    'pedido_id'          => $pedido->id, 
                    'insumo_id'          => null, 
                    'nombre_material'    => '[COTI-RÁPIDA] ' . $detalle, 
                    'cantidad_requerida' => 1,
                    'subtotal_calculado' => (float) $request->input('costo_total'),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cotización rápida guardada con éxito.',
                    'id'      => $pedido->id
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error crítico en el servidor: ' . $e->getMessage()
                ], 500);
            }
        }

        // =======================================================
        // 2. FLUJO NORMAL: MODO BASTONES (La calculadora pesada)
        // =======================================================
        
        // 1. Validación de estructura: campos base + materiales como arreglo obligatorio
        $request->validate([
            'quote_request_id'        => 'nullable|exists:quote_requests,id',
            'cantidad_total_bastones' => 'required|numeric|min:1',
            'costo_total'             => 'required|numeric',
            'correo_cliente'          => 'nullable|email',
            'materiales'              => 'required|string',
        ]);

        // 2. Decodificamos el carrito...
        $listaMateriales = json_decode($request->input('materiales'), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($listaMateriales) || !is_array($listaMateriales)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El detalle de materiales llegó vacío o corrupto. No se puede guardar un pedido sin materiales.'
            ], 422);
        }
        
        // 3. Validamos la estructura de CADA línea del carrito (defensa contra payloads manuales)
        foreach ($listaMateriales as $mat) {
            if (!isset($mat['nombre_material'], $mat['cantidad_requerida'], $mat['subtotal_calculado'])
                || trim($mat['nombre_material']) === ''
                || !is_numeric($mat['cantidad_requerida'])
                || !is_numeric($mat['subtotal_calculado'])
            ) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Uno de los materiales del carrito tiene datos inválidos o incompletos.'
                ], 422);
            }
        }

        // 4. REGLA DE NEGOCIO: espejo de la validación del front (validarCintasNuevas en cotizador.js).
        // Si un material es una cinta nueva (sin insumo_id, es decir aún no existe en inventario),
        // su nombre debe incluir el tipo (Satín / Gross / Garza). Esto evita que alguien se salte
        // el JS y guarde una cinta ambigua directo contra el endpoint.
        foreach ($listaMateriales as $mat) {
            $esCintaSinTipo = empty($mat['insumo_id'])
                && str_contains(strtolower($mat['nombre_material']), 'cinta')
                && !str_contains(strtolower($mat['nombre_material']), 'satin')
                && !str_contains(strtolower($mat['nombre_material']), 'satín')
                && !str_contains(strtolower($mat['nombre_material']), 'gross')
                && !str_contains(strtolower($mat['nombre_material']), 'garza');

            if ($esCintaSinTipo) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El material "' . $mat['nombre_material'] . '" es una cinta nueva sin tipo especificado (Satín, Gross o Garza).'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // 5. Guardar la cabecera del pedido (Maestro)
            $pedido = new \App\Models\Pedido();
            // Asignamos el ID de la solicitud web si viene en la petición (null si es venta física)
            $pedido->quote_request_id = $request->input('quote_request_id');
            // Datos del cliente mapeados exactamente a como los envía tu AJAX
            $pedido->cliente_nombre = $request->input('nombre_cliente', 'Cliente de Mostrador'); 
            $pedido->correo_cliente = $request->input('correo_cliente'); 
            
            $pedido->cantidad_total_bastones = $request->input('cantidad_total_bastones');
            
            // Desglose financiero
            $pedido->costo_materiales = (float) $request->input('costo_materiales');
            $pedido->costo_extras     = (float) $request->input('costo_extras');
            $pedido->ganancia_fija    = (float) $request->input('ganancia_fija');
            $pedido->costo_total      = (float) $request->input('costo_total');
            $pedido->costo_unitario   = (float) $request->input('costo_unitario');
            
            $pedido->estado = 'pendiente'; 
            $pedido->save();

            // Si el pedido tiene un ID web asociado, actualizamos la tabla del cliente
            if ($request->filled('quote_request_id')) {
                $solicitudWeb = \App\Models\QuoteRequest::find($request->input('quote_request_id'));
                
                if ($solicitudWeb) {
                    // Guardamos el costo calculado por tu mamá y lo pasamos a estado 'cotizado'
                    $solicitudWeb->precio_final = (float) $request->input('costo_total');
                    $solicitudWeb->estado = 'cotizado';
                    $solicitudWeb->save();
                }
            }

            // 6. Insertar cada material del carrito ya validado
            foreach ($listaMateriales as $mat) {
                
                // --- INICIO: LÓGICA DE RECONOCIMIENTO AUTOMÁTICO DE INSUMOS ---
                $insumoIdFinal = $mat['insumo_id'];
                $nombreMaterialLimpiado = strtolower(trim($mat['nombre_material']));

                // Solo intentamos autocompletar si el JS no envió un ID
                if (is_null($insumoIdFinal) || empty($insumoIdFinal)) {
                    
                    // Lógica para interceptar "Cinchos"
                    if (str_contains($nombreMaterialLimpiado, 'cincho')) {
                        $insumoDetectado = \App\Models\Insumo::where('categoria', 'cinchos')->whereNull('deleted_at')->first();
                        if ($insumoDetectado) {
                            $insumoIdFinal = $insumoDetectado->id;
                        }
                    }
                    
                    // Lógica para interceptar "Elástico"
                    if (str_contains($nombreMaterialLimpiado, 'elástico') || str_contains($nombreMaterialLimpiado, 'elastico')) {
                        $insumoDetectado = \App\Models\Insumo::where('categoria', 'elastico')->whereNull('deleted_at')->first();
                        if ($insumoDetectado) {
                            $insumoIdFinal = $insumoDetectado->id;
                        }
                    }
                }
                // --- FIN: LÓGICA DE RECONOCIMIENTO ---

                // Insertamos cada fila en la tabla de detalles
                DB::table('pedido_materiales')->insert([
                    'pedido_id'          => $pedido->id, 
                    'insumo_id'          => $insumoIdFinal, 
                    'nombre_material'    => $mat['nombre_material'],
                    'cantidad_requerida' => $mat['cantidad_requerida'],
                    'subtotal_calculado' => $mat['subtotal_calculado'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::commit();

            // El Frontend espera response.id para inyectarlo en los botones PDF
            return response()->json([
                'success' => true,
                'mensaje' => 'Pedido y lista de materiales guardados con éxito.',
                'id'      => $pedido->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error crítico en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    // =======================================================
    // GENERACIÓN DE REPORTES PDF (On-the-Fly)
    // =======================================================
    public function generarPdfReceta($id)
    {
        $pedido = \App\Models\Pedido::with('materiales')->findOrFail($id);

        $costoTotalMateriales = 0;
        $costoDisenoPersonalizado = 0;
        $esPedidoGrande = $pedido->cantidad_total_bastones >= 12;

        foreach ($pedido->materiales as $mat) {
            $stockActual = 0;
            $precioUnitario = 0;
            $insumo = null;
            $esDiseno = str_contains(strtolower($mat->nombre_material), 'diseño') 
                     || str_contains(strtolower($mat->nombre_material), 'diseno');

            if (!$esDiseno) {
                if ($mat->insumo_id) {
                    $insumo = \App\Models\Insumo::find($mat->insumo_id);
                }
                if (!$insumo) {
                    $insumo = \App\Models\Insumo::where('nombre', $mat->nombre_material)->first();
                }
                if ($insumo) {
                    $stockActual = $insumo->stock_actual;
                }
            }

            $diferencia = $mat->cantidad_requerida - $stockActual;
            $faltaComprarNumerico = $diferencia > 0 ? $diferencia : 0;

            $nombreLower = strtolower($mat->nombre_material);
            $cantReq = (float) $mat->cantidad_requerida;
            $stock = (float) $stockActual;
            $falta = (float) $faltaComprarNumerico;

            // 1. OBTENCIÓN DEL PRECIO (Real del Kardex vs Fantasma del JS)
            if (!$esDiseno) {
                if ($insumo && $insumo->costo_unitario > 0) {
                    $precioUnitario = $insumo->costo_unitario;
                } else {
                    if (str_contains($nombreLower, 'base')) {
                        if (str_contains($nombreLower, 'dorado')) {
                            $precioUnitario = $esPedidoGrande ? 5.00 : 5.50;
                        } else {
                            $precioUnitario = $esPedidoGrande ? 4.50 : 5.00;
                        }
                    } elseif (str_contains($nombreLower, 'lana')) {
                        $precioUnitario = 0.0127;
                    } elseif (str_contains($nombreLower, 'garza')) {
                        $precioUnitario = 0.11;
                    } elseif (str_contains($nombreLower, 'satin') || str_contains($nombreLower, 'satín')) {
                        $precioUnitario = 0.16;
                    } elseif (str_contains($nombreLower, 'gross')) {
                        $precioUnitario = 0.15;
                    } elseif (str_contains($nombreLower, 'cortina')) {
                        $precioUnitario = $esPedidoGrande ? 0.50 : 1.00;
                    } elseif (str_contains($nombreLower, 'elástico') || str_contains($nombreLower, 'elastico')) {
                        $precioUnitario = 0.09;
                    } elseif (str_contains($nombreLower, 'cincho')) {
                        $precioUnitario = 0.02;
                    }
                }
            }

            $mat->requerido_visual = '';
            $mat->stock_visual = '';
            $mat->falta_visual = '';
            $mat->es_diseno = $esDiseno;

            // --- LÓGICA VISUAL DE BODEGA ---
            if (str_contains($nombreLower, 'flor') || str_contains($nombreLower, 'lazo')) {
                $divisor = str_contains($nombreLower, 'lazo simple') ? 1.5 : 1.0;
                $unidades = round($cantReq / $divisor);
                $mat->requerido_visual = "{$unidades} unid. (" . number_format($cantReq, 1) . "m)";
                $mat->stock_visual = number_format($stock, 1) . "m";
                $mat->falta_visual = $falta > 0 ? number_format($falta, 1) . "m" : "0";
            } elseif (str_contains($nombreLower, 'lana')) {
                $madejasReq = ceil($cantReq / 90);
                $madejasFalta = ceil($falta / 90);
                $mat->requerido_visual = "~{$madejasReq} madejas (" . round($cantReq) . "g)";
                $mat->stock_visual = round($stock) . "g";
                $mat->falta_visual = $falta > 0 ? "~{$madejasFalta} madejas (" . round($falta) . "g)" : "0";
            } elseif (str_contains($nombreLower, 'cortina')) {
                // NUEVO: cada paquete de cortina rinde 4 unidades, igual criterio que las madejas de lana
                $paquetesReq   = ceil($cantReq / 4);
                $paquetesFalta = ceil($falta / 4);
                $mat->requerido_visual = "~{$paquetesReq} paquetes (" . round($cantReq) . " unid.)";
                $mat->stock_visual = round($stock) . " unid.";
                $mat->falta_visual = $falta > 0 ? "~{$paquetesFalta} paquetes (" . round($falta) . " unid.)" : "0";
            } elseif (str_contains($nombreLower, 'elástico') || str_contains($nombreLower, 'elastico') || str_contains($nombreLower, 'cinta')) {
                $mat->requerido_visual = $cantReq < 1 ? round($cantReq * 100) . " cm" : number_format($cantReq, 1) . " m";
                $mat->stock_visual = $stock < 1 && $stock > 0 ? round($stock * 100) . " cm" : number_format($stock, 1) . " m";
                $mat->falta_visual = $falta < 1 && $falta > 0 ? round($falta * 100) . " cm" : ($falta > 0 ? number_format($falta, 1) . " m" : "0");
            } elseif ($esDiseno) {
                $mat->requerido_visual = "$" . number_format($cantReq, 2) . " extra";
                $mat->stock_visual = "N/A";
                $mat->falta_visual = "0";
            } else {
                $mat->requerido_visual = round($cantReq) . " unid.";
                $mat->stock_visual = round($stock) . " unid.";
                $mat->falta_visual = $falta > 0 ? round($falta) . " unid." : "0";
            }

            // 2. CÁLCULO FINANCIERO
            $subtotal = 0;

            if ($esDiseno) {
                $precioDiseno = 1.50;
                if (str_contains($nombreLower, 'intermedio')) $precioDiseno = 2.00;
                if (str_contains($nombreLower, 'premium')) $precioDiseno = 3.00;

                $subtotal = $cantReq * $precioDiseno;

                $mat->precio_unitario_visual = "N/A";
                $mat->requerido_visual = "$" . number_format($subtotal, 2) . " extra";
                $mat->stock_visual = "N/A";
                $mat->falta_visual = "0";
                $mat->falta_comprar_num = 0;

                $costoDisenoPersonalizado += $subtotal;

            } elseif (str_contains($nombreLower, 'aplique')) {
                $precioUnitario = 0.50;
                $subtotal = $cantReq * $precioUnitario;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitario, 2);
                $mat->falta_comprar_num = $faltaComprarNumerico;
            } else {
                $subtotal = $cantReq * $precioUnitario;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitario, 4);
                $mat->falta_comprar_num = $faltaComprarNumerico;
            }

            $mat->subtotal_visual = "$" . number_format($subtotal, 2);
            $costoTotalMateriales += $subtotal;
        }

        // 3. CÁLCULO DE MANO DE OBRA Y GRAN TOTAL (Alineado con el Frontend)
        
        // A. Aislamos el costo de los insumos físicos (restando los extras de diseño)
        $costoTotalMateriales = $costoTotalMateriales - $costoDisenoPersonalizado;
        
        // B. La Mano de Obra ahora es exclusivamente la Ganancia Base (60% de materiales)
        // Usamos la variable $costoManoObra para que tu Blade del PDF la reciba sin problemas
        $costoManoObra = $costoTotalMateriales * 0.60;
        
        // C. Gran Total de Producción sumando las 3 partes por separado:
        // Materiales + Extras (Diseño Personalizado) + Mano de Obra (60%)
        $costoTotalProduccion = $costoTotalMateriales + $costoDisenoPersonalizado + $costoManoObra;

        // Mandamos a generar el PDF
        $pdf = Pdf::loadView('reportes.receta', compact(
            'pedido', 'costoTotalMateriales', 'costoManoObra', 'costoTotalProduccion', 'costoDisenoPersonalizado'
        ));

        return $pdf->stream('Receta_Bodega_Pedido_' . $pedido->id . '.pdf');
    }

    public function generarPdfNota($id)
    {
        // 1. Buscamos el pedido
        $pedido = Pedido::with('materiales')->findOrFail($id);

        // 2. RASTREO DEL DUEÑO
        $dueno_id = $pedido->user_id;
        if (!$dueno_id && $pedido->quote_request_id) {
            $solicitudOriginal = \App\Models\QuoteRequest::find($pedido->quote_request_id);
            $dueno_id = $solicitudOriginal ? $solicitudOriginal->user_id : null;
        }

        // 3. SEGURIDAD
        if ($dueno_id != Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para ver esta nota de venta.');
        }

        // 4. DETECCIÓN INTELIGENTE DE COTIZACIÓN RÁPIDA
        $esCotizacionRapida = false;
        
        // Si el pedido solo tiene 1 "material" y su nombre empieza con nuestro tag oculto
        if ($pedido->materiales->count() === 1 && str_starts_with($pedido->materiales->first()->nombre_material, '[COTI-RÁPIDA]')) {
            $esCotizacionRapida = true;
            
            // Limpiamos el texto para borrar el tag técnico antes de que llegue a la vista Blade
            $nombreLimpio = str_replace('[COTI-RÁPIDA] ', '', $pedido->materiales->first()->nombre_material);
            $pedido->materiales->first()->nombre_material = $nombreLimpio;
        }

        // 5. Generar el PDF enviando la nueva variable de estado
        $pdf = Pdf::loadView('reportes.nota', compact('pedido', 'esCotizacionRapida'));

        return $pdf->stream('Nota_Venta_Pedido_' . $pedido->id . '.pdf');
    }
    // =======================================================
    // ENVÍO DE CORREOS
    // =======================================================

    public function enviarCorreo(Request $request)
    {
        // 1. Validamos que nos llegue un ID válido y un correo con formato correcto
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'email'     => 'required|email'
        ]);

        try {
            // 2. Buscamos el pedido con todo su detalle (para el PDF)
            $pedido = Pedido::with('materiales')->findOrFail($request->pedido_id);

            // 3. ¡La Magia! Enviamos el correo usando nuestra clase Mailable
            Mail::to($request->email)->send(new NotaVentaMailable($pedido));

            // 4. Respondemos al Frontend que todo salió bien
            return response()->json([
                'success' => true,
                'mensaje' => 'Correo enviado exitosamente a la bandeja de prueba.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }
}