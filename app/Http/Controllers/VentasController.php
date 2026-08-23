<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Insumo;
use App\Models\Movimiento;
use App\Models\QuoteRequest;
use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\NotaVentaMailable;

class VentasController extends Controller
{
    // =======================================================
    // CONFIGURACIÓN CENTRAL: qué se considera "no es un insumo físico"
    // =======================================================
    // Cualquier línea del carrito cuyo nombre contenga uno de estos
    // fragmentos es mano de obra, servicio o una cotización rápida ya
    // facturada como bloque único. NUNCA se debe:
    //   - buscar en el Kardex
    //   - descontar stock
    //   - reportar como "material fantasma" (no encontrado)
    // Si mañana agregas una nueva categoría de "servicio" (ej. grabado,
    // empaque especial, etc.) solo la agregas aquí y automáticamente
    // queda blindada en TODO el controlador.
    private const FRAGMENTOS_IGNORABLES = [
        'aplique',
        'diseño',
        'diseno',
        '[coti-rápida]',
        '[coti-rapida]',
    ];

    /**
     * True si esta línea del carrito es un servicio / mano de obra / coti-rápida,
     * y por lo tanto debe excluirse de descuentos de inventario y de auditorías
     * de "material no encontrado".
     */
    private function esMaterialIgnorable(string $nombreMaterial): bool
    {
        $nombreLower = strtolower($nombreMaterial);

        foreach (self::FRAGMENTOS_IGNORABLES as $fragmento) {
            if (str_contains($nombreLower, strtolower($fragmento))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Intenta resolver el Insumo real del Kardex correspondiente a una línea
     * de material cotizada. Devuelve null si no lo logra encontrar.
     *
     * Estrategia (en orden):
     *  1. Por insumo_id, si ya venía enlazado desde la cotización.
     *  2. Por coincidencia flexible del nombre completo cotizado.
     *  3. Por categoría detectada + palabras clave del "color/variante".
     *
     * NOTA IMPORTANTE: el frontend guarda los nombres con un PREFIJO fijo
     * seguido de dos puntos, ej: "Cortina de Fiesta: Rojo", "Cortina de
     * Lana: Azul", "Cinta Satín: Dorado". El insumo real en el Kardex solo
     * se llama por la variante ("Rojo", "Azul", "Dorado"). Por eso, en vez
     * de mantener una lista de "palabras basura" que hay que actualizar
     * cada vez que se inventa un prefijo nuevo, cortamos todo lo que va
     * ANTES de los ":" y trabajamos solo con lo que sobra.
     */
    private function resolverInsumo($item): ?Insumo
    {
        // 1. Ya viene enlazado
        if ($item->insumo_id) {
            $insumo = Insumo::find($item->insumo_id);
            if ($insumo) {
                return $insumo;
            }
        }

        $nombreCotizado = $item->nombre_material;

        // 2. Coincidencia flexible del nombre completo
        // (paréntesis y espacios dobles se vuelven comodines)
        $nombreLimpio = str_replace(['(', ')', ' '], '%', $nombreCotizado);
        $nombreLimpio = preg_replace('/%+/', '%', $nombreLimpio);

        $insumo = Insumo::where('nombre', 'LIKE', "%{$nombreLimpio}%")->first();
        if ($insumo) {
            return $insumo;
        }

        // 3. Detección de categoría + variante
        $nombreMinuscula = strtolower($nombreCotizado);
        $tagDetectado = $this->detectarCategoria($nombreMinuscula);

        if (!$tagDetectado) {
            return null;
        }

        // Insumos fijos de ensamblaje: uno solo por categoría, sin variantes
        if (in_array($tagDetectado, ['cinchos', 'elastico'])) {
            return Insumo::where('categoria', $tagDetectado)->first();
        }

        // Insumos con variedad de color/diseño: aislamos la variante real
        $textoVariante = $this->extraerVariante($nombreCotizado);
        $palabrasClave = array_filter(explode(' ', trim($textoVariante)));

        $query = Insumo::where('categoria', $tagDetectado);
        foreach ($palabrasClave as $palabra) {
            $palabraValida = trim($palabra);
            if (strlen($palabraValida) >= 2) {
                $query->where('nombre', 'LIKE', '%' . $palabraValida . '%');
            }
        }

        return $query->first();
    }

    /**
     * Detecta a qué categoría del Kardex pertenece un nombre de material
     * cotizado, según los 8 tags reales usados en toda la app.
     */
    private function detectarCategoria(string $nombreMinuscula): ?string
    {
        return match (true) {
            str_contains($nombreMinuscula, 'base')                                             => 'base_baston',
            str_contains($nombreMinuscula, 'lana') || str_contains($nombreMinuscula, 'cuerpo')  => 'lana',
            str_contains($nombreMinuscula, 'garza')                                             => 'cinta_garza',
            str_contains($nombreMinuscula, 'satin') || str_contains($nombreMinuscula, 'satín')  => 'cinta_satin',
            str_contains($nombreMinuscula, 'gross')                                             => 'cinta_gross',
            str_contains($nombreMinuscula, 'cortina')                                           => 'cortina_fiesta',
            str_contains($nombreMinuscula, 'cincho')                                            => 'cinchos',
            str_contains($nombreMinuscula, 'elástico') || str_contains($nombreMinuscula, 'elastico') => 'elastico',
            default => null,
        };
    }

    /**
     * Extrae la "variante" real (color / medida) de un nombre de material
     * cotizado con formato "Prefijo: Variante" (ej. "Cortina de Fiesta: Rojo").
     * Si no hay ":", cae de vuelta a limpiar palabras conocidas como respaldo.
     */
    private function extraerVariante(string $nombreCotizado): string
    {
        if (str_contains($nombreCotizado, ':')) {
            return trim(explode(':', $nombreCotizado, 2)[1]);
        }

        // Respaldo por si algún día se guarda un nombre sin ":"
        $palabrasBasura = [
            'lazo', 'simple', 'flor', 'corte', 'cinta', 'cortina',
            'de', 'fiesta', 'lana', 'base', 'cuerpo', 'c/', 'nombre',
            ':', '1', '2', '3',
        ];

        return str_ireplace($palabrasBasura, '', $nombreCotizado);
    }

    /**
     * Calcula el desglose financiero (ingresos / mano de obra / insumos)
     * de un solo pedido, usando el modelo híbrido:
     *   - Manualidades: regla fija 60% mano de obra / 40% insumos.
     *   - Todo lo demás (bastones, lazos, ensamblajes): top-down usando
     *     el costo_materiales real guardado, con respaldo del 40% si falta.
     *
     * Se reutiliza tanto en el index (KPIs del mes) como en el reporte
     * mensual en PDF, para que ambos SIEMPRE coincidan.
     */
    private function calcularFinancieroPedido(Pedido $pedido): array
    {
        $precioFinal = $pedido->costo_total ?? 0;
        $esManualidad = in_array(strtolower($pedido->categoria ?? ''), ['manualidad', 'manualidades']);

        if ($esManualidad) {
            $ganancia = $precioFinal * 0.60;
            $insumos  = $precioFinal * 0.40;
        } else {
            $insumos = (!empty($pedido->costo_materiales) && $pedido->costo_materiales > 0)
                ? $pedido->costo_materiales
                : ($precioFinal * 0.40);

            $ganancia = $precioFinal - $insumos;
        }

        return [
            'ingreso'  => $precioFinal,
            'ganancia' => $ganancia,
            'insumos'  => $insumos,
        ];
    }

    // =======================================================
    // LISTADO PRINCIPAL DE VENTAS + KPIs
    // =======================================================
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $fecha  = $request->input('fecha');
        $estado = $request->input('estado');

        $query = Pedido::with('materiales');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('cliente_nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('id', 'LIKE', "%{$buscar}%");
            });
        }

        if ($fecha) {
            $query->whereDate('created_at', $fecha);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $pedidos = $query->orderBy('created_at', 'desc')->paginate(10);

        // --- KPIs del mes ---
        $mesActual  = Carbon::now()->month;
        $anioActual = Carbon::now()->year;
        $nombreMes  = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));

        $pedidosMes = Pedido::where('estado', 'realizado')
            ->whereMonth('created_at', $mesActual)
            ->whereYear('created_at', $anioActual)
            ->get();

        $ingresosMes          = 0;
        $manoObraEstimada     = 0;
        $costoInsumosEstimado = 0;

        foreach ($pedidosMes as $pedido) {
            $desglose = $this->calcularFinancieroPedido($pedido);
            $ingresosMes          += $desglose['ingreso'];
            $manoObraEstimada     += $desglose['ganancia'];
            $costoInsumosEstimado += $desglose['insumos'];
        }

        $enProduccion           = Pedido::where('estado', 'en_produccion')->count();
        $cotizacionesPendientes = Pedido::where('estado', 'pendiente')->count();

        $top5Populares = CatalogItem::where('activo', true)
            ->where('contador_consultas', '>', 0)
            ->orderBy('contador_consultas', 'desc')
            ->take(5)
            ->get();

        $modeloEstrella = $top5Populares->first();
        $nombreModeloEstrella     = $modeloEstrella->titulo ?? 'Ninguno aún';
        $consultasModeloEstrella  = $modeloEstrella->contador_consultas ?? 0;

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
            'top5Populares'
        ));
    }

    // =======================================================
    // VINCULAR PEDIDO A SOLICITUD WEB
    // =======================================================
    public function vincularPedido(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($id);

            if ($request->filled('quote_request_id')) {
                if ($pedido->quote_request_id != null && $pedido->quote_request_id != $request->quote_request_id) {
                    $solicitudAnterior = QuoteRequest::find($pedido->quote_request_id);
                    if ($solicitudAnterior) {
                        $solicitudAnterior->estado = 'pendiente';
                        $solicitudAnterior->precio_final = null;
                        $solicitudAnterior->save();
                    }
                }

                $pedido->quote_request_id = $request->quote_request_id;

                $solicitudWeb = QuoteRequest::find($request->quote_request_id);
                if ($solicitudWeb) {
                    $solicitudWeb->precio_final = $pedido->costo_total;

                    $estadoInterno = strtolower($pedido->estado);
                    if ($estadoInterno === 'realizado') {
                        $solicitudWeb->estado = 'entregado';
                    } elseif ($estadoInterno === 'en_produccion') {
                        $solicitudWeb->estado = 'en_produccion';
                    } else {
                        $solicitudWeb->estado = 'cotizado';
                    }
                    $solicitudWeb->save();

                    $pedido->cliente_nombre = $solicitudWeb->nombre;

                    if ($solicitudWeb->user) {
                        $pedido->correo_cliente = $solicitudWeb->user->email;
                    }
                }

                $pedido->save();
            }

            if ($request->filled('correo') && !$request->filled('quote_request_id')) {
                $pedido->correo_cliente = $request->correo;
                $pedido->save();
            }

            if ($pedido->correo_cliente) {
                Mail::to($pedido->correo_cliente)->send(new NotaVentaMailable($pedido));
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // =======================================================
    // CAMBIO DE ESTADO + DESCUENTO DE INVENTARIO
    // =======================================================
    public function actualizarEstado(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::with('materiales')->findOrFail($id);
            $nuevoEstado    = $request->input('estado');

            $materialesNoEncontrados = [];
            $materialesEnNegativo    = [];
            $listaExitosos           = [];

            // Solo descontar si pasa a 'realizado' y NUNCA se ha descontado antes.
            if ($nuevoEstado === 'realizado' && $pedido->inventario_descontado == false) {
                foreach ($pedido->materiales as $item) {

                    // Servicios / mano de obra / coti-rápida: nunca tocan inventario
                    // ni cuentan como "material no encontrado".
                    if ($this->esMaterialIgnorable($item->nombre_material)) {
                        continue;
                    }

                    $insumo = $this->resolverInsumo($item);

                    // Si la búsqueda inteligente encontró el insumo pero la línea
                    // no traía insumo_id, lo amarramos permanentemente.
                    if ($insumo && !$item->insumo_id) {
                        $item->insumo_id = $insumo->id;
                        $item->save();
                    }

                    if ($insumo) {
                        $insumo->stock_actual -= $item->cantidad_requerida;
                        $insumo->save();

                        $listaExitosos[] = $item->nombre_material . ' (' . $item->cantidad_requerida . ')';

                        if ($insumo->stock_actual < 0) {
                            $materialesEnNegativo[] = $insumo->nombre . ' (Quedó en ' . $insumo->stock_actual . ')';
                        }

                        Movimiento::create([
                            'insumo_id'       => $insumo->id,
                            'tipo_movimiento' => 'Salida (Venta)',
                            'cantidad'        => -$item->cantidad_requerida,
                            'detalle'         => 'Descuento automático por Pedido #' . str_pad($pedido->id, 4, '0', STR_PAD_LEFT),
                        ]);
                    } else {
                        $materialesNoEncontrados[] = $item->nombre_material;
                    }
                }

                $pedido->inventario_descontado = true;
            }

            $pedido->estado = $nuevoEstado;
            $pedido->save();

            // --- Sincronización con el portal web ---
            if (!is_null($pedido->quote_request_id)) {
                $solicitudWeb = QuoteRequest::find($pedido->quote_request_id);

                if ($solicitudWeb) {
                    $estadoLimpio = strtolower($nuevoEstado);

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

            DB::commit();

            return response()->json([
                'success'        => true,
                'descontados'    => $listaExitosos,
                'no_encontrados' => $materialesNoEncontrados,
                'en_negativo'    => $materialesEnNegativo,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error crítico: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =======================================================
    // BÚSQUEDA DE CLIENTES (Select2)
    // =======================================================
    public function buscarClientesAjax(Request $request)
    {
        $term = $request->input('q');

        $clientes = Pedido::select('cliente_nombre')
            ->where('cliente_nombre', 'LIKE', "%{$term}%")
            ->groupBy('cliente_nombre')
            ->orderBy('cliente_nombre', 'asc')
            ->limit(10)
            ->get();

        $results = $clientes->map(function ($pedido) {
            return [
                'id'   => $pedido->cliente_nombre,
                'text' => $pedido->cliente_nombre,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function obtenerDetalles($id)
    {
        $pedido = Pedido::with('materiales')->findOrFail($id);

        return response()->json($pedido);
    }

    // =======================================================
    // REPORTE MENSUAL (PDF)
    // =======================================================
    public function generarReporteMensual(Request $request)
    {
        $mes  = (int) $request->input('mes', Carbon::now()->month);
        $anio = (int) $request->input('anio', Carbon::now()->year);
        $nombreMes = ucfirst(Carbon::create()->month($mes)->locale('es')->translatedFormat('F'));

        $todosPedidosMes = Pedido::with('materiales')
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->get();

        $estadosCount = [
            'realizado'     => $todosPedidosMes->where('estado', 'realizado')->count(),
            'en_produccion' => $todosPedidosMes->where('estado', 'en_produccion')->count(),
            'pendiente'     => $todosPedidosMes->where('estado', 'pendiente')->count(),
            'cancelado'     => $todosPedidosMes->where('estado', 'cancelado')->count(),
        ];

        $pedidosCompletados = $todosPedidosMes->where('estado', 'realizado');

        $ingresosTotales   = 0;
        $manoObraTotal     = 0;
        $costoInsumosTotal = 0;
        $consumoInsumos       = [];
        $materialesFantasmas  = [];

        foreach ($pedidosCompletados as $pedido) {
            $desglose = $this->calcularFinancieroPedido($pedido);
            $ingresosTotales   += $desglose['ingreso'];
            $manoObraTotal     += $desglose['ganancia'];
            $costoInsumosTotal += $desglose['insumos'];

            foreach ($pedido->materiales as $mat) {
                // MISMA regla que actualizarEstado(): servicios, diseños,
                // apliques y coti-rápida jamás son "material fantasma".
                if ($this->esMaterialIgnorable($mat->nombre_material)) {
                    continue;
                }

                if ($mat->insumo_id) {
                    $nombre = $mat->nombre_material;
                    if (!isset($consumoInsumos[$nombre])) {
                        $consumoInsumos[$nombre] = 0;
                    }
                    $consumoInsumos[$nombre] += $mat->cantidad_requerida;
                } else {
                    // Si a estas alturas (pedido ya 'realizado') sigue sin
                    // insumo_id, es porque actualizarEstado() tampoco pudo
                    // resolverlo. Reintentamos una vez más aquí por si el
                    // insumo se dio de alta DESPUÉS de descontar el pedido.
                    $insumo = $this->resolverInsumo($mat);

                    if ($insumo) {
                        $nombre = $mat->nombre_material;
                        if (!isset($consumoInsumos[$nombre])) {
                            $consumoInsumos[$nombre] = 0;
                        }
                        $consumoInsumos[$nombre] += $mat->cantidad_requerida;
                    } else {
                        $materialesFantasmas[] = [
                            'nombre'   => $mat->nombre_material,
                            'pedido'   => $pedido->id,
                            'cantidad' => $mat->cantidad_requerida,
                        ];
                    }
                }
            }
        }

        arsort($consumoInsumos);
        $topInsumos = array_slice($consumoInsumos, 0, 5, true);

        $stockNegativo = Insumo::where('stock_actual', '<', 0)->get();

        $topProductos = CatalogItem::where('activo', true)
            ->orderBy('contador_consultas', 'desc')
            ->take(5)
            ->get();

        $pdf = Pdf::loadView('reportes.reporte_mensual_pdf', compact(
            'pedidosCompletados',
            'ingresosTotales',
            'costoInsumosTotal',
            'manoObraTotal',
            'nombreMes',
            'anio',
            'estadosCount',
            'topInsumos',
            'materialesFantasmas',
            'stockNegativo',
            'topProductos'
        ));

        return $pdf->stream('Reporte_Gerencial_' . $nombreMes . '_' . $anio . '.pdf');
    }
}