<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Insumo;
use App\Models\QuoteRequest;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // =========================================================
        // 1. KPIs FINANCIEROS Y DE PRODUCCIÓN (Modelo Híbrido)
        // =========================================================
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;
        
        $solicitudesWeb = QuoteRequest::where('estado', 'pendiente')->get();
        $nombreMes = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));

        // Traemos todos los pedidos en estado 'realizado' del mes actual
        // (Usamos whereIn por si tienes registros en mayúscula o minúscula)
        $pedidosMes = Pedido::with('quoteRequest')
            ->whereIn('estado', ['Realizado', 'realizado'])
            ->whereMonth('created_at', $mesActual)
            ->whereYear('created_at', $anioActual)
            ->get();

        // Inicializamos las variables del Modelo Híbrido
        $ingresosMes = 0;
        $manoObraEstimada = 0;
        $costoInsumosEstimado = 0;

        foreach ($pedidosMes as $pedido) {
            $precioFinal = $pedido->costo_total ?? 0;
            $ingresosMes += $precioFinal;

            // Evaluamos la categoría (Busca directamente en el pedido, o en su relación si viene de QuoteRequest)
            $categoriaPedido = $pedido->categoria ?? optional($pedido->quoteRequest)->categoria ?? '';
            $esManualidad = in_array(strtolower($categoriaPedido), ['manualidad', 'manualidades']);

            if ($esManualidad) {
                // FASE A: MODELO ARTESANAL (Regla del 60%)
                $ganancia = $precioFinal * 0.60;
                $insumos = $precioFinal * 0.40;
                
                $manoObraEstimada += $ganancia;
                $costoInsumosEstimado += $insumos;
            } else {
                // FASE B: MODELO TOP-DOWN (Ensamblajes, Bastones, Lazos)
                $insumos = (!empty($pedido->costo_materiales) && $pedido->costo_materiales > 0) 
                            ? $pedido->costo_materiales 
                            : ($precioFinal * 0.40);
                
                $ganancia = $precioFinal - $insumos;

                $costoInsumosEstimado += $insumos;
                $manoObraEstimada += $ganancia;
            }
        }

        $enProduccion = Pedido::whereIn('estado', ['en_produccion', 'En Producción', 'En Produccion'])->count();
        $cotizacionesPendientes = Pedido::whereIn('estado', ['pendiente', 'Pendiente'])->count();


        // =========================================================
        // 2. ACTIVIDAD RECIENTE 
        // =========================================================
        $actividadReciente = Pedido::orderBy('updated_at', 'desc')->take(4)->get();


        // =========================================================
        // 3. ALERTAS TIPO A: DEUDA DE INVENTARIO (Amarillo)
        // =========================================================
        $alertasStock = Insumo::where('stock_actual', '<', 0)->get();


        // =========================================================
        // 4. ALERTAS TIPO B: MATERIALES NO ENCONTRADOS (Rojo)
        // =========================================================
        $pedidosProcesados = Pedido::with(['materiales' => function($query) {
            $query->whereNull('insumo_id')
                  ->where('alerta_ignorada', false);
        }])
        ->whereIn('estado', ['Realizado', 'realizado', 'En Producción', 'en_produccion', 'En Produccion'])
        ->orderBy('updated_at', 'desc')
        ->take(15)
        ->get();

        $materialesHuerfanos = [];
        foreach ($pedidosProcesados as $pedido) {
            foreach ($pedido->materiales as $item) {
                $nombreLower = strtolower($item->nombre_material ?? '');

                // =======================================================
                // ARQUITECTURA BYPASS: Omitir mano de obra, servicios y coti-rápidas
                // =======================================================
                    if (str_contains($nombreLower, 'aplique') || 
                        str_contains($nombreLower, 'diseño') || 
                        str_contains($nombreLower, 'diseno') ||
                        str_contains($nombreLower, '[coti-rápida]') || 
                        str_contains($nombreLower, '[coti-rapida]') || 
                        str_contains($item->nombre_material, '[COTI-RÁPIDA]')) { 
                        continue;
                    }

                $materialesHuerfanos[] = [
                    'detalle_id'      => $item->id, 
                    'pedido_id'       => $pedido->id,
                    'nombre_material' => $item->nombre_material
                ];
            }
        }


        // =========================================================
        // 5. ALERTAS TIPO C: STOCK BAJO / POR AGOTARSE (Celeste)
        // =========================================================
        $insumosBajos = Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')
                              ->where('stock_actual', '>=', 0)
                              ->get();


        // =========================================================
        // RETORNO A VISTA
        // =========================================================
        return view('Inicio.inicio', compact(
            'ingresosMes', 
            'nombreMes',
            'manoObraEstimada',
            'costoInsumosEstimado',
            'solicitudesWeb',
            'enProduccion', 
            'cotizacionesPendientes', 
            'actividadReciente',
            'alertasStock',
            'materialesHuerfanos',
            'insumosBajos'
        ));
    }

    // =======================================================
    // ACCIONES RÁPIDAS DEL DASHBOARD
    // =======================================================

    public function inboxSolicitudes()
    {
        // 1. Traemos las solicitudes pendientes (Pestaña 1)
        $pendientes = QuoteRequest::with('user')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->paginate(12, ['*'], 'pendientes_page')
            ->withQueryString();

        // 2. Traemos las solicitudes ya gestionadas (Pestaña 2)
        $gestionadas = QuoteRequest::with('user')
            ->whereIn('estado', ['cotizado', 'en_produccion', 'entregado', 'cancelado'])
            ->orderBy('created_at', 'desc')
            ->paginate(12, ['*'], 'gestionadas_page')
            ->withQueryString();

        return view('dashboard.solicitudes_inbox', compact('pendientes', 'gestionadas'));
    }

    public function descartarAlerta($detalle_id)
    {
        // Usamos la fachada DB directo a la tabla pedido_materiales (a prueba de fallos)
        DB::table('pedido_materiales')
            ->where('id', $detalle_id)
            ->update(['alerta_ignorada' => true]);

        return response()->json(['success' => true]);
    }

    public function arreglarStock($insumo_id)
    {
        $insumo = Insumo::findOrFail($insumo_id);
        
        if ($insumo->stock_actual < 0) {
            // Calculamos cuánto material hay que "inyectar" para llegar a 0
            $cantidadAjuste = abs($insumo->stock_actual); 
            
            $insumo->stock_actual = 0;
            $insumo->save();

            // Guardamos el movimiento en el Kardex para no dejar huérfana la auditoría
            Movimiento::create([
                'insumo_id'       => $insumo->id,
                'tipo_movimiento' => 'Entrada (Ajuste)',
                'cantidad'        => $cantidadAjuste,
                'detalle'         => 'Ajuste automático (Deuda saneada desde Panel)'
            ]);
        }

        return response()->json(['success' => true]);
    }
}