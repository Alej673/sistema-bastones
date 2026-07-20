<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotaVentaMailable;

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
        // 1. Validación de estructura: campos base + materiales como arreglo obligatorio
        $request->validate([
            'cantidad_total_bastones'            => 'required|numeric|min:1',
            'costo_total'                         => 'required|numeric',
            'correo_cliente'                      => 'nullable|email',
            'materiales'                          => 'required|string',
        ]);

        // 2. Decodificamos el carrito y validamos que sea un JSON válido y no esté vacío
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
                    'insumo_id'          => $insumoIdFinal, // ¡Usamos nuestra variable validada!
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
        // 1. Buscamos el pedido y "cargamos" todos sus materiales asociados
        $pedido = \App\Models\Pedido::with('materiales')->findOrFail($id);

        // 2. LA MAGIA: Calculamos cuánto falta comprar de cada insumo
        foreach ($pedido->materiales as $mat) {
            $stockActual = 0;
            $insumo = null;

            // Buscamos el insumo por ID o por Nombre (igual que en el Soft Fail)
            if ($mat->insumo_id) {
                $insumo = \App\Models\Insumo::find($mat->insumo_id);
            }
            if (!$insumo) {
                $insumo = \App\Models\Insumo::where('nombre', $mat->nombre_material)->first();
            }

            // Si el insumo existe en bodega, capturamos su stock real
            if ($insumo) {
                $stockActual = $insumo->stock_actual;
            }

            // Hacemos la resta: Requerido - Stock Actual
            $diferencia = $mat->cantidad_requerida - $stockActual;
            
            // Inyectamos nuevas propiedades "al vuelo" para que el PDF las use
            $mat->stock_bodega = $stockActual;
            $mat->falta_comprar = $diferencia > 0 ? $diferencia : 0;
        }

        // 3. Cargamos la vista de Blade de la RECETA y le pasamos los datos
        // Asegúrate de importar la clase Pdf arriba si no está: use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = Pdf::loadView('reportes.receta', compact('pedido'));

        // 4. stream() abre el PDF en el navegador
        return $pdf->stream('Receta_Bodega_Pedido_' . $pedido->id . '.pdf');
    }

    public function generarPdfNota($id)
    {
        // 1. Buscamos el pedido
        $pedido = Pedido::with('materiales')->findOrFail($id);

        // 2. Cargamos la vista de la nota de venta (diseño comercial)
        $pdf = Pdf::loadView('reportes.nota', compact('pedido'));

        // 3. Renderizamos y mostramos
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