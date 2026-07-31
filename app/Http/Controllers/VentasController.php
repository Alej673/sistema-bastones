<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VentasController extends Controller
{
    public function index(Request $request)
    {
        // 1. OBTENER VARIABLES DE FILTROS (Para la búsqueda asíncrona o síncrona)
        $buscar = $request->input('buscar');
        $fecha = $request->input('fecha');
        $estado = $request->input('estado');

        // 2. CONSTRUIR LA CONSULTA BASE DE PEDIDOS
        // Usamos Eager Loading con 'materiales' para optimizar la base de datos
        $query = Pedido::with('materiales');

        // Aplicar filtro de búsqueda por cliente o número de documento
        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                // CORRECCIÓN: Usamos 'cliente_nombre' tal como está en la BD
                $q->where('cliente_nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('id', 'LIKE', "%{$buscar}%");
            });
        }

        // Aplicar filtro de fecha exacta
        if ($fecha) {
            $query->whereDate('created_at', $fecha);
        }

        // Aplicar filtro por estado de producción
        if ($estado) {
            $query->where('estado', $estado);
        }

        // Obtener los pedidos ordenados por el más reciente
        $pedidos = $query->orderBy('created_at', 'desc')->paginate(10);


        // 3. CÁLCULO DE KPIs (Estadísticas para las tarjetas superiores)
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;
        
        // Nombre del mes dinámico en español
        $nombreMes = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));

        // Traemos todos los pedidos en estado 'realizado' del mes actual
        $pedidosMes = Pedido::where('estado', 'realizado')
            ->whereMonth('created_at', $mesActual)
            ->whereYear('created_at', $anioActual)
            ->get();

        // =======================================================
        // NUEVO MODELO HÍBRIDO FINANCIERO
        // =======================================================
        $ingresosMes = 0;
        $manoObraEstimada = 0;
        $costoInsumosEstimado = 0;

        foreach ($pedidosMes as $pedido) {
            $precioFinal = $pedido->costo_total ?? 0;
            $ingresosMes += $precioFinal;

            // Evaluamos la categoría (Asegúrate de que 'categoria' exista en tu modelo Pedido, 
            // si viene de una relación usa algo como $pedido->quoteRequest->categoria)
            $esManualidad = in_array(strtolower($pedido->categoria ?? ''), ['manualidad', 'manualidades']);

            if ($esManualidad) {
                // FASE A: MODELO ARTESANAL (Regla del 60%)
                // Venta directa sin desglose de materiales
                $ganancia = $precioFinal * 0.60;
                $insumos = $precioFinal * 0.40;
                
                $manoObraEstimada += $ganancia;
                $costoInsumosEstimado += $insumos;
                } else {
                // FASE B: MODELO TOP-DOWN (Ensamblajes, Bastones, Lazos)
                
                // CORRECCIÓN: Leemos la columna correcta de tu BD (costo_materiales)
                // Si es mayor a 0 usa el valor real, si no, usa la emergencia del 40%.
                $insumos = (!empty($pedido->costo_materiales) && $pedido->costo_materiales > 0) 
                            ? $pedido->costo_materiales 
                            : ($precioFinal * 0.40);
                
                // La ganancia es la diferencia exacta: absorbe el margen base y diseños especiales.
                $ganancia = $precioFinal - $insumos;

                $costoInsumosEstimado += $insumos;
                $manoObraEstimada += $ganancia;
            }
        }

        // Tarjeta 2: Cantidad de pedidos actualmente en producción
        $enProduccion = Pedido::where('estado', 'en_produccion')->count();

        // Tarjeta 3: Cantidad de cotizaciones en estado pendiente
        $cotizacionesPendientes = Pedido::where('estado', 'pendiente')->count();

        // Tarjeta 4: Diseño Más Popular y Top 5 (Marketing Web)
        $top5Populares = \App\Models\CatalogItem::where('activo', true)
                            ->where('contador_consultas', '>', 0)
                            ->orderBy('contador_consultas', 'desc')
                            ->take(5)
                            ->get();
        
        // El modelo estrella será simplemente el primero de esa lista de 5
        $modeloEstrella = $top5Populares->first();
        
        // Verificamos si existe
        if ($modeloEstrella) {
            $nombreModeloEstrella = $modeloEstrella->titulo;
            $consultasModeloEstrella = $modeloEstrella->contador_consultas;
        } else {
            $nombreModeloEstrella = 'Ninguno aún';
            $consultasModeloEstrella = 0;
        }

        // 4. RETORNAR LA VISTA CON LOS DATOS COMPLETOS
        return view('Ventas.ventas', compact(
            'pedidos', 
            'ingresosMes', 
            'nombreMes',
            'manoObraEstimada',
            'costoInsumosEstimado',
            'enProduccion', 
            'cotizacionesPendientes', 
            'nombreModeloEstrella',
            'consultasModeloEstrella',
            'top5Populares' // <-- ¡AQUÍ AGREGAMOS LA NUEVA VARIABLE PARA EL MENÚ!
        ));
    }

    public function vincularPedido(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($id);

            // 1. Vincular a la Web (Si se seleccionó un ID)
            if ($request->filled('quote_request_id')) {
                
                // --- LÓGICA DE LIMPIEZA (La que ya pusimos) ---
                if ($pedido->quote_request_id != null && $pedido->quote_request_id != $request->quote_request_id) {
                    $solicitudAnterior = \App\Models\QuoteRequest::find($pedido->quote_request_id);
                    if ($solicitudAnterior) {
                        $solicitudAnterior->estado = 'pendiente';
                        $solicitudAnterior->precio_final = null; 
                        $solicitudAnterior->save();
                    }
                }

                // Actualizamos el pedido físico con el NUEVO ID
                $pedido->quote_request_id = $request->quote_request_id;
                
                // Actualizamos el estado y precio de la solicitud web para el NUEVO cliente
                $solicitudWeb = \App\Models\QuoteRequest::find($request->quote_request_id);
                if ($solicitudWeb) {
                    $solicitudWeb->precio_final = $pedido->costo_total;
                    
                    // Emparejamos los estados
                    $estadoInterno = strtolower($pedido->estado);
                    if ($estadoInterno === 'realizado') {
                        $solicitudWeb->estado = 'entregado';
                    } elseif ($estadoInterno === 'en_produccion') {
                        $solicitudWeb->estado = 'en_produccion';
                    } else {
                        $solicitudWeb->estado = 'cotizado';
                    }
                    $solicitudWeb->save();

                    // --- NUEVO: ACTUALIZAR DATOS DEL CLIENTE EN EL PEDIDO ---
                    $pedido->cliente_nombre = $solicitudWeb->nombre; 

                    // Si el usuario web tiene correo, lo forzamos en el pedido para no mandar correos al cliente equivocado
                    if ($solicitudWeb->user) {
                        $pedido->correo_cliente = $solicitudWeb->user->email;
                    }
                    // --- FIN DE LO NUEVO ---
                }

                // Guardamos los cambios del nombre, correo y el nuevo ID en el pedido
                $pedido->save();
            }

            // 2. Reenviar Correo (Si escribió un email en el modal y NO se reescribió arriba)
            if ($request->filled('correo') && !$request->filled('quote_request_id')) {
                $pedido->correo_cliente = $request->correo;
                $pedido->save();
            }

            // Si hay correo, mandamos la nota de venta
            if ($pedido->correo_cliente) {
                \Illuminate\Support\Facades\Mail::to($pedido->correo_cliente)
                    ->send(new \App\Mail\NotaVentaMailable($pedido));
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarEstado(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::with('materiales')->findOrFail($id);
            $nuevoEstado = $request->input('estado');
            $estadoAnterior = $pedido->estado;
            
            $materialesNoEncontrados = []; // Aquí guardaremos los "fantasmas"
            $materialesEnNegativo = []; // Para controlar la deuda
            $listaExitosos = [];

            // REGLA DE NEGOCIO BLINDADA: Solo descontar si pasa a 'realizado' 
            // Y comprobamos en la base de datos que NUNCA se haya descontado antes.
            if ($nuevoEstado === 'realizado' && $pedido->inventario_descontado == false) {
                
                foreach ($pedido->materiales as $item) {
                    $nombreLower = strtolower($item->nombre_material);

                    // =======================================================
                    // ARQUITECTURA BYPASS: Ignorar mano de obra, servicios y cotizaciones rápidas
                    // =======================================================
                    if (str_contains($nombreLower, 'aplique') || 
                        str_contains($nombreLower, 'diseño') || 
                        str_contains($nombreLower, 'diseno') ||
                        str_contains($nombreLower, '[coti-rápida]') || 
                        str_contains($nombreLower, '[coti-rapida]') || 
                        str_contains($item->nombre_material, '[COTI-RÁPIDA]')) { 
                        continue;
                    }

                    $insumo = null;

                    // 1. Intentar buscar por ID (si ya estaba enlazado desde la cotización)
                    if ($item->insumo_id) {
                        $insumo = \App\Models\Insumo::find($item->insumo_id);
                    }
                    
                    // 2. LA MAGIA EVOLUCIONADA: Búsqueda Inteligente (Fusión Tag + Nombre)[cite: 2]
                    if (!$insumo) {
                        $nombreCotizado = $item->nombre_material;

                        // Intento A: Búsqueda flexible (Illa paréntesis y espacios dobles con comodines %)[cite: 2]
                        $nombreLimpio = str_replace(['(', ')', ' '], '%', $nombreCotizado);
                        $nombreLimpio = preg_replace('/%+/', '%', $nombreLimpio);

                        $insumo = \App\Models\Insumo::where('nombre', 'LIKE', "%{$nombreLimpio}%")->first();

                        // Intento B: Mapeo exhaustivo por CATEGORÍAS (Tags exactos de la BD)
                        if (!$insumo) {
                            $tagDetectado = null;
                            $nombreMinuscula = strtolower($nombreCotizado);

                            // Mapeo contra los 8 tags reales de tu objeto JS / BD:
                            if (str_contains($nombreMinuscula, 'base')) {
                                $tagDetectado = 'base_baston';
                            } elseif (str_contains($nombreMinuscula, 'lana') || str_contains($nombreMinuscula, 'cuerpo')) {
                                $tagDetectado = 'lana';
                            } elseif (str_contains($nombreMinuscula, 'garza')) {
                                $tagDetectado = 'cinta_garza';
                            } elseif (str_contains($nombreMinuscula, 'satin') || str_contains($nombreMinuscula, 'satín')) {
                                $tagDetectado = 'cinta_satin';
                            } elseif (str_contains($nombreMinuscula, 'gross')) {
                                $tagDetectado = 'cinta_gross';
                            } elseif (str_contains($nombreMinuscula, 'cortina')) {
                                $tagDetectado = 'cortina_fiesta';
                            } elseif (str_contains($nombreMinuscula, 'cincho')) {
                                $tagDetectado = 'cinchos';
                            } elseif (str_contains($nombreMinuscula, 'elástico') || str_contains($nombreMinuscula, 'elastico')) {
                                $tagDetectado = 'elastico';
                            }

                            // Búsqueda dentro de la categoría detectada
                            if ($tagDetectado) {
                                // Caso 1: Insumos fijos de ensamblaje (cinchos, elástico)
                                if (in_array($tagDetectado, ['cinchos', 'elastico'])) {
                                    $insumo = \App\Models\Insumo::where('categoria', $tagDetectado)->first();
                                } 
                                // Caso 2: Insumos con variedad de color/diseño (bases, lanas, cintas, cortinas)
                                else {
                                    // Aislamos palabras clave del texto (ej. "roja", "azul", "dorada", "55cm")
                                    $palabrasBasura = ['lazo', 'simple', 'flor', 'corte', 'cinta', 'cortina', 'base', 'cuerpo', 'c/', 'nombre', ':', '1', '2', '3'];
                                    
                                    // Limpiamos la cadena cotizada
                                    $textoLimpio = str_ireplace($palabrasBasura, '', $nombreCotizado);
                                    $palabrasClave = array_filter(explode(' ', trim($textoLimpio)));

                                    $query = \App\Models\Insumo::where('categoria', $tagDetectado);
                                    
                                    // Buscamos que el insumo en el Kardex contenga al menos el atributo (color o tamaño)
                                    foreach ($palabrasClave as $palabra) {
                                        $palabraValida = trim($palabra);
                                        if (strlen($palabraValida) >= 2) {
                                            $query->where('nombre', 'LIKE', '%' . $palabraValida . '%');
                                        }
                                    }
                                    $insumo = $query->first();
                                }
                            }
                        }

                        // Si la magia funcionó, amarramos permanentemente el insumo_id en la BD
                        if ($insumo) {
                            $item->insumo_id = $insumo->id;
                            $item->save();
                        }
                    }

                    // 3. Proceder con el descuento si logramos encontrarlo de alguna de las dos formas
                    if ($insumo) {
                        // Restamos el stock físico
                        $insumo->stock_actual -= $item->cantidad_requerida;
                        $insumo->save();

                        // NUEVO: Registramos el éxito para que el modal lo muestre en verde
                        $listaExitosos[] = $item->nombre_material . ' (' . $item->cantidad_requerida . ')';

                        // LA NUEVA MAGIA: Si después de restar quedó en negativo, lo guardamos
                        if ($insumo->stock_actual < 0) {
                            $materialesEnNegativo[] = $insumo->nombre . ' (Quedó en ' . $insumo->stock_actual . ')';
                        }

                        // Escribimos en la bitácora de auditoría (Kardex)
                        \App\Models\Movimiento::create([
                            'insumo_id' => $insumo->id,
                            'tipo_movimiento' => 'Salida (Venta)',
                            'cantidad' => -$item->cantidad_requerida,
                            'detalle' => 'Descuento automático por Pedido #' . str_pad($pedido->id, 4, '0', STR_PAD_LEFT)
                        ]);
                    } else {
                        // 4. Definitivamente sigue sin existir en el inventario (Soft Fail)
                        $materialesNoEncontrados[] = $item->nombre_material;
                    }
                }

                // 🛑 EL CANDADO DE SEGURIDAD 🛑
                // Marcamos que ya se descontó para que jamás vuelva a entrar a este bloque IF
                $pedido->inventario_descontado = true;
            }

            // Guardamos el nuevo estado de la cabecera (y el candado si se activó)
            $pedido->estado = $nuevoEstado;
            $pedido->save();

        // --- INICIO: SINCRONIZACIÓN CON EL PORTAL WEB (EL ESPEJO) ---
            if (!is_null($pedido->quote_request_id)) {
                $solicitudWeb = \App\Models\QuoteRequest::find($pedido->quote_request_id);
                
                if ($solicitudWeb) {
                    $estadoLimpio = strtolower($nuevoEstado); 
                    
                    // Traductor de estados (Taller -> Cliente Web)
                    if ($estadoLimpio === 'en_produccion') {
                        $solicitudWeb->estado = 'en_produccion';
                    } elseif ($estadoLimpio === 'realizado') {
                        $solicitudWeb->estado = 'entregado'; 
                    } elseif ($estadoLimpio === 'cancelado') {
                        $solicitudWeb->estado = 'cancelado';
                    }

                    $solicitudWeb->save();
                }
            }
            // --- FIN: SINCRONIZACIÓN CON EL PORTAL WEB ---

            DB::commit();

            // Retornamos la respuesta a JavaScript
            return response()->json([
                'success' => true,
                'descontados' => $listaExitosos,
                'no_encontrados' => $materialesNoEncontrados,
                'en_negativo' => $materialesEnNegativo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error crítico: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarClientesAjax(Request $request)
    {
        $term = $request->input('q');

        // Buscamos clientes únicos que coincidan con el término escrito
        $clientes = Pedido::select('cliente_nombre')
            ->where('cliente_nombre', 'LIKE', "%{$term}%")
            ->groupBy('cliente_nombre')
            ->orderBy('cliente_nombre', 'asc')
            ->limit(10) // Limitamos a 10 sugerencias para optimizar rendimiento
            ->get();

        // Formateamos la respuesta para que Select2 la entienda (id y text)
        $results = $clientes->map(function ($pedido) {
            return [
                'id' => $pedido->cliente_nombre,
                'text' => $pedido->cliente_nombre
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function obtenerDetalles($id)
    {
        // Traemos el pedido incluyendo sus materiales asociados
        $pedido = \App\Models\Pedido::with('materiales')->findOrFail($id);
        
        return response()->json($pedido);
    }
}

