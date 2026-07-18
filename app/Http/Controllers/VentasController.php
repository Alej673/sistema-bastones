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
                $q->where('cliente', 'LIKE', "%{$buscar}%")
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
        $pedidos = $query->orderBy('created_at', 'desc')->get();


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
            ->select('insumos.nombre', DB::raw('COUNT(pedido_materiales.insumo_id) as total_veces'))
            ->where('insumos.nombre', 'LIKE', '%Base%') // Filtro sutil por nombre de estructura
            ->groupBy('pedido_materiales.insumo_id', 'insumos.nombre')
            ->orderBy('total_veces', 'desc')
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
}