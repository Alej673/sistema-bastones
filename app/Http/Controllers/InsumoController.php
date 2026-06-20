<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $insumos = \App\Models\Insumo::all(); // Solo trae los que NO están borrados
        
        // El withTrashed() permite leer el nombre del insumo incluso si fue eliminado
        $movimientos = \App\Models\Movimiento::with(['insumo' => function($query) {
            $query->withTrashed();
        }])->latest()->take(15)->get();
        
        return view('insumos.index', compact('insumos', 'movimientos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Enviar a la vista el create que esta en la carpeta insumos
        return view('insumos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validamos usando el nuevo campo precio_unitario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string',
            'cantidad_comprada' => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0', 
            'alerta_minima' => 'required|numeric|min:0',
        ]);

        //Preparar las variables que necesita la base de datos
        $multiplicador = 1;
        $unidad = 'Unidades';

        // 2. El Motor de Traducción (Mundo Físico -> Mundo Digital)
        switch ($request->categoria) {
            case 'lana':
                $multiplicador = 90; 
                $unidad = 'Gramos';
                break;
            case 'cinta_garza':
                $multiplicador = 45.72; 
                $unidad = 'Metros';
                break;
            case 'cinta_satin':
                $multiplicador = 18.28; 
                $unidad = 'Metros';
                break;
            case 'cortina_fiesta':
                $multiplicador = 4; 
                $unidad = 'Unidades';
                break;
            case 'elastico':
                $multiplicador = 10; 
                $unidad = 'Metros';
                break;
            case 'cinchos':
                $multiplicador = 100; 
                $unidad = 'Unidades';
                break;
            case 'base_baston': // <-- NUEVO: Agregado para que no falle al crear bastones
                $multiplicador = 1; 
                $unidad = 'Unidades';
                break;
            case 'unidad_simple':
                $multiplicador = 1; 
                $unidad = 'Unidades';
                break;
        }

        // 3. Ejecutar las matemáticas exactas
        $stock_actual_calculado = $request->cantidad_comprada * $multiplicador;
        $stock_minimo_calculado = $request->alerta_minima * $multiplicador;
        
        // Ingreso de cantidad por unidad/madeja
        if ($multiplicador > 0) {
            $costo_unitario_calculado = $request->precio_unitario / $multiplicador;
        } else {
            $costo_unitario_calculado = 0;
        }

        // 4. Atrapamos el resultado en la variable $insumo
        $insumo = \App\Models\Insumo::create([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'unidad_medida' => $unidad,
            'costo_unitario' => $costo_unitario_calculado,
            'stock_actual' => $stock_actual_calculado,
            'stock_minimo' => $stock_minimo_calculado,
        ]);

        // Registrar en bitácora la creación (Ahora $insumo sí existe y tiene ID)
        \App\Models\Movimiento::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'ingreso_nuevo',
            'cantidad' => $insumo->stock_actual,
            'detalle' => 'Creación de nuevo insumo en sistema'
        ]);

        return redirect()->route('insumos.index');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // FUNCIÓN PARA CORREGIR EL NOMBRE
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $insumo = \App\Models\Insumo::findOrFail($id);
        $insumo->update([
            'nombre' => $request->nombre
        ]);

        return redirect()->route('insumos.index');
    }

    // FUNCIÓN PARA SUMAR/RESTAR STOCK EN CANTIDADES CERRADAS
    public function ajustarStock(Request $request, $id)
    {
        $request->validate([
            'cantidad_movimiento' => 'required|numeric|min:0.01',
            'tipo_movimiento' => 'required|string'
        ]);

        $insumo = \App\Models\Insumo::findOrFail($id);
        
        $multiplicador = 1;

        // SEPARACIÓN LOGICA: ENTRADA (COMPRA COMERCIAL) VS SALIDA (DESPERDICIO/AJUSTE)
        if ($request->tipo_movimiento === 'entrada') {
            // Las entradas siempre suman paquetes/madejas/rollos completos
            switch ($insumo->categoria) {
                case 'lana': $multiplicador = 90; break; 
                case 'cinta_garza': $multiplicador = 45.72; break; 
                case 'cinta_satin': $multiplicador = 18.28; break; 
                case 'cortina_fiesta': $multiplicador = 4; break; 
                case 'elastico': $multiplicador = 10; break; 
                case 'cinchos': $multiplicador = 100; break; // 1 Paquete ingresado = 100 Cinchos en BD
                case 'base_baston': $multiplicador = 1; break; // Bastones ingresados uno a uno
                case 'unidad_simple': $multiplicador = 1; break;
            }
        } else {
            // Las salidas manuales descuentan directo en la unidad de consumo mínima (unidades, gramos, metros)
            switch ($insumo->categoria) {
                case 'lana': $multiplicador = 1; break; // gramos sueltos
                case 'cinta_garza': $multiplicador = 1; break; // metros sueltos
                case 'cinta_satin': $multiplicador = 1; break; // metros sueltos
                case 'cortina_fiesta': $multiplicador = 1; break; // cortinas sueltas
                case 'elastico': $multiplicador = 1; break; // metros sueltos
                case 'cinchos': $multiplicador = 1; break; //Descuenta por unidades de cinchos individuales
                case 'unidad_simple': $multiplicador = 1; break;
            }
        }

        // Calculamos el impacto real en la base de datos
        $unidadesAProcesar = $request->cantidad_movimiento * $multiplicador;

        if ($request->tipo_movimiento === 'entrada') {
            $insumo->stock_actual += $unidadesAProcesar;
        } else {
            if ($insumo->stock_actual < $unidadesAProcesar) {
                $insumo->stock_actual = 0; 
            } else {
                $insumo->stock_actual -= $unidadesAProcesar;
            }
        }

        // REGISTRO AUTOMÁTICO EN LA BITÁCORA (NUEVO)
        \App\Models\Movimiento::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => $request->tipo_movimiento,
            'cantidad' => $unidadesAProcesar,
            'detalle' => 'Ajuste manual en Kardex'
        ]);

        $insumo->save();

        return redirect()->route('insumos.index');
    }

    // NUEVA FUNCIÓN: ELIMINAR COMPLETAMENTE EL INSUMO
    public function destroy($id)
    {
        $insumo = \App\Models\Insumo::findOrFail($id);
        
        // NUEVO: Registrar en bitácora la eliminación
        \App\Models\Movimiento::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'eliminado',
            'cantidad' => $insumo->stock_actual,
            'detalle' => 'Insumo retirado o anulado del inventario'
        ]);

        // Hace el "Soft Delete" (Lo oculta, no lo destruye de la BD)
        $insumo->delete();

        return redirect()->route('insumos.index');
    }
}
