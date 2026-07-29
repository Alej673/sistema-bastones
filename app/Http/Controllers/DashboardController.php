<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Insumo;
use Carbon\Carbon;
use App\Models\QuoteRequest;

class DashboardController extends Controller
{
    public function index()
    {
    // =========================================================
        // 1. KPIs FINANCIEROS Y DE PRODUCCIÓN (Estimación Referencial)
        // =========================================================
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;
        $solicitudesWeb = QuoteRequest::where('estado', 'pendiente')->get();
        
        // Obtenemos el nombre del mes en español (ej. "Julio")
        $nombreMes = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));

        // Traemos todos los pedidos realizados en el mes actual
        $pedidosMes = Pedido::where('estado', 'Realizado')
                            ->whereMonth('created_at', $mesActual)
                            ->whereYear('created_at', $anioActual)
                            ->get();

        // 1. Ingresos Brutos (Total cobrado en el mes)
        $ingresosMes = $pedidosMes->sum('costo_total');

        // 2. Estimación de Mano de Obra / Ganancia ($3.00 por cada bastón fabricado)
        // Nota: Asegúrate de que tu columna se llame 'cantidad_total_bastones' o ajústala
        $totalBastonesMes = $pedidosMes->sum('cantidad_total_bastones');
        $manoObraEstimada = $totalBastonesMes * 3.00;

        // 3. Estimación del costo de reposición de materiales
        $costoInsumosEstimado = $ingresosMes - $manoObraEstimada;

        $enProduccion = Pedido::where('estado', 'en_produccion')->count();
        $cotizacionesPendientes = Pedido::where('estado', 'Pendiente')->count();


        // =========================================================
        // 2. ACTIVIDAD RECIENTE 
        // =========================================================
        $actividadReciente = Pedido::orderBy('updated_at', 'desc')->take(4)->get();


        // =========================================================
        // 3. ALERTAS TIPO A: DEUDA DE INVENTARIO (Amarillo)
        // =========================================================
        // Insumos que sí existen, pero el Kardex quedó en negativo
        $alertasStock = Insumo::where('stock_actual', '<', 0)->get();


        // =========================================================
        // 4. ALERTAS TIPO B: MATERIALES NO ENCONTRADOS (Rojo)
        // =========================================================
        /* 
           Traemos los últimos 15 pedidos procesados y cargamos solo sus 
           materiales que tengan insumo_id en NULL (los huérfanos)
        */
        $pedidosProcesados = Pedido::with(['materiales' => function($query) {
            $query->whereNull('insumo_id')
                  ->where('alerta_ignorada', false);
        }])
        ->whereIn('estado', ['Realizado', 'En Producción', 'En Produccion'])
        ->orderBy('updated_at', 'desc')
        ->take(15)
        ->get();

        $materialesHuerfanos = [];
        foreach ($pedidosProcesados as $pedido) {
            foreach ($pedido->materiales as $item) {
                $materialesHuerfanos[] = [
                    // ESTA LÍNEA ES LA QUE PROVOCA EL ERROR SI NO ESTÁ
                    'detalle_id' => $item->id, 
                    'pedido_id' => $pedido->id,
                    'nombre_material' => $item->nombre_material
                ];
            }
        }

        // =========================================================
        // 5. ALERTAS TIPO C: STOCK BAJO / POR AGOTARSE (Celeste)
        // =========================================================
        // Compara que el stock_actual sea menor o igual al stock_minimo.
        // Además, filtramos que sea mayor o igual a 0 para que no se dupliquen 
        // con los insumos que ya están en "Deuda de Inventario" (alertasStock).
        $insumosBajos = Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')
                              ->where('stock_actual', '>=', 0)
                              ->get();

        // Retornamos todo a la vista inicio.blade.php
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
        $pendientes = \App\Models\QuoteRequest::with('user')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->paginate(12, ['*'], 'pendientes_page')
            ->withQueryString();

        // 2. Traemos las solicitudes ya gestionadas (Pestaña 2)
        // Agrupamos los estados que pertenecen al ciclo de vida del BTO 
        $gestionadas = \App\Models\QuoteRequest::with('user')
            ->whereIn('estado', ['cotizado', 'en_produccion', 'entregado', 'cancelado'])
            ->orderBy('created_at', 'desc')
            ->paginate(12, ['*'], 'gestionadas_page')
            ->withQueryString();

        // 3. Enviamos ambas variables a la vista
        return view('dashboard.solicitudes_inbox', compact('pendientes', 'gestionadas'));
    }

    // ==========================================================================
    // acciones RÁPIDAS DEL DASHBOARD
    // ========================================================================== 

    public function descartarAlerta($detalle_id)
    {
        // Usamos la fachada DB directo a la tabla pedido_materiales (a prueba de fallos)
        \Illuminate\Support\Facades\DB::table('pedido_materiales')
            ->where('id', $detalle_id)
            ->update(['alerta_ignorada' => true]);

        return response()->json(['success' => true]);
    }

    public function arreglarStock($insumo_id)
    {
        $insumo = \App\Models\Insumo::findOrFail($insumo_id);
        
        if ($insumo->stock_actual < 0) {
            // Calculamos cuánto material hay que "inyectar" para llegar a 0
            $cantidadAjuste = abs($insumo->stock_actual); 
            
            $insumo->stock_actual = 0;
            $insumo->save();

            // Guardamos el movimiento en el Kardex para no dejar huérfana la auditoría
            \App\Models\Movimiento::create([
                'insumo_id' => $insumo->id,
                'tipo_movimiento' => 'Entrada (Ajuste)',
                'cantidad' => $cantidadAjuste,
                'detalle' => 'Ajuste automático (Deuda saneada desde Panel)'
            ]);
        }

        return response()->json(['success' => true]);
    }
}