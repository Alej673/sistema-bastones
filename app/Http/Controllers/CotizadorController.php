<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;

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

    public function guardar(Request $request)
    {
        $request->validate([
            'cantidad_total_bastones' => 'required|numeric|min:1',
            'costo_total' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // 1. Guardar la cabecera del pedido (Maestro)
            $pedido = new \App\Models\Pedido();
            $pedido->cliente_nombre = $request->input('cliente_nombre', 'Cliente de Mostrador'); 
            $pedido->cantidad_total_bastones = $request->input('cantidad_total_bastones');
            $pedido->total_precio_cliente = $request->input('costo_total');
            $pedido->estado = 'pendiente'; 
            $pedido->save();

            // 2. Descifrar el carrito de materiales enviado por JS 
            $materialesJson = $request->input('materiales');
            $listaMateriales = json_decode($materialesJson, true); // Lo convertimos en un Array de PHP

            if (!empty($listaMateriales)) {
                foreach ($listaMateriales as $mat) {
                    // Insertamos cada fila en la tabla de detalles
                    DB::table('pedido_materiales')->insert([
                        'pedido_id' => $pedido->id, // Vinculamos al ID del pedido recién creado
                        'insumo_id' => $mat['insumo_id'], // Puede ser un número o NULL si es un tag nuevo
                        'nombre_material' => $mat['nombre_material'],
                        'cantidad_requerida' => $mat['cantidad_requerida'],
                        'subtotal_calculado' => $mat['subtotal_calculado'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Pedido y lista de materiales guardados con éxito.',
                'id' => $pedido->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error crítico en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}