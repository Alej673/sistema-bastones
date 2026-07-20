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
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // Tarjeta 1: Ingresos de pedidos completados en el mes actual
        // CAMBIO: Usamos 'realizado' (como en tu ENUM) y sum('costo_total')
        $ingresosMes = Pedido::where('estado', 'realizado')
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('costo_total'); 

        // Tarjeta 2: Cantidad de pedidos actualmente en producción
        $enProduccion = Pedido::where('estado', 'en_produccion')->count();

        // Tarjeta 3: Cantidad de cotizaciones en estado pendiente
        $cotizacionesPendientes = Pedido::where('estado', 'pendiente')->count();

        // Tarjeta 4: Base más solicitada (Consulta relacional sobre la tabla pivote)
        // Busca el insumo con categoría 'base_baston' que más se repite en pedido_materiales
        $baseMasSolicitada = DB::table('pedido_materiales')
            ->join('insumos', 'pedido_materiales.insumo_id', '=', 'insumos.id')
            ->select(
                'insumos.nombre', 
                DB::raw('SUM(pedido_materiales.cantidad_requerida) as total_unidades')
            )
            ->where('insumos.nombre', 'LIKE', '%Base%') // Filtro por estructura base
            ->groupBy('pedido_materiales.insumo_id', 'insumos.nombre')
            ->orderBy('total_unidades', 'desc') // Ordenamos por el volumen real acumulado
            ->first();

        $nombreBaseEstrella = $baseMasSolicitada ? $baseMasSolicitada->nombre : 'Ninguna aún';


        // 4. RETORNAR LA VISTA CON LOS DATOS
        return view('Ventas.ventas', compact(
            'pedidos', 
            'ingresosMes', 
            'enProduccion', 
            'cotizacionesPendientes', 
            'nombreBaseEstrella'
        ));
    }

    public function actualizarEstado(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::with('materiales')->findOrFail($id);
            $nuevoEstado = $request->input('estado');
            $estadoAnterior = $pedido->estado;
            
            $materialesNoEncontrados = []; // Aquí guardaremos los "fantasmas"
            $materialesEnNegativo = []; // NUEVO: Para controlar la deuda

            // REGLA DE NEGOCIO BLINDADA: Solo descontar si pasa a 'realizado' 
            // Y comprobamos en la base de datos que NUNCA se haya descontado antes.
            if ($nuevoEstado === 'realizado' && $pedido->inventario_descontado == false) {
                
                foreach ($pedido->materiales as $item) {
                    $insumo = null;

                    // 1. Intentar buscar por ID (si ya estaba enlazado desde la cotización)
                    if ($item->insumo_id) {
                        $insumo = \App\Models\Insumo::find($item->insumo_id);
                    }
                    
                    // 2. LA MAGIA EVOLUCIONADA: Búsqueda Inteligente (Fusión Tag + Nombre)
                    if (!$insumo) {
                        $nombreCotizado = $item->nombre_material;

                        // Intento A: Búsqueda flexible (Illa paréntesis y espacios dobles con comodines %)
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

            DB::commit();

            // Retornamos la respuesta a JavaScript
            return response()->json([
                'success' => true,
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

