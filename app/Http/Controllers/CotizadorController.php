<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insumo;
use App\Models\Ajuste;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotaVentaMailable;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


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

        // Bases pre-cortadas (Bases Plata 45cm, Dorada 60cm, etc.)
        $bases = $insumos->where('categoria', 'base_baston')->values();
        $unidadesSimples = $insumos->where('categoria', 'unidad_simple')->values();

        // 3. LA CLAVE DE LA REACTIVIDAD: Serializamos toda la colección a JSON
        $insumosJson = $insumos->keyBy('id')->toJson();

        // Configuración para el JS
        $configuraciones = Ajuste::pluck('valor', 'llave');

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
            'insumosJson',
            'configuraciones'
        ));
    }

    /**
     * Guarda la cotización final como un pedido en firme (Fase 4)
     */
    public function store(Request $request)
    {
        // Esto lo desarrollaremos cuando la matemática del Frontend esté 100% sólida
    }

    // =======================================================
    // HELPERS PRIVADOS
    // =======================================================

    /**
     * Escapa los comodines de LIKE (%, _ y \) para que el término
     * buscado se trate como texto literal.
     */
    private function escaparLike(?string $term): string
    {
        return addcslashes((string) $term, '%_\\');
    }

    /**
     * Elimina del disco una imagen subida durante una operación que falló.
     */
    private function limpiarImagenHuerfana(?string $rutaImagen): void
    {
        if ($rutaImagen) {
            try {
                Storage::disk('public')->delete($rutaImagen);
            } catch (\Throwable $e) {
                Log::warning('No se pudo limpiar la imagen huérfana: ' . $e->getMessage());
            }
        }
    }

    // Función para llamar a la base de datos de lanas
    public function buscarLanas(Request $request)
    {
        // Capturamos el término de búsqueda que envía Select2
        $term = $this->escaparLike(mb_substr((string) $request->input('q', ''), 0, 100));

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
        $term = $this->escaparLike(mb_substr((string) $request->input('q', ''), 0, 100));

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
        $term = $this->escaparLike(mb_substr((string) $request->input('q', ''), 0, 100));

        // Categorías donde el sistema tiene permiso para buscar
        $categorias = ['cinta_satin', 'cinta_garza', 'cinta_gross'];

        $cintas = Insumo::whereIn('categoria', $categorias)
            ->where('nombre', 'LIKE', '%' . $term . '%')
            ->get()
            ->map(function ($cinta) {

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
            $request->validate(['id' => 'required|integer']);

            return response()->json(
                QuoteRequest::with('user')
                    ->where('estado', 'pendiente')
                    ->find($request->id)
            );
        }

        // Búsqueda para el Select2
        $term = $this->escaparLike(mb_substr((string) $request->get('q', ''), 0, 100));

        $solicitudes = QuoteRequest::with('user')
            ->where('estado', 'pendiente')
            ->when($term !== '', function ($query) use ($term) {
                // Agrupamos el OR para que NO rompa el filtro estado = 'pendiente'
                $query->where(function ($q) use ($term) {
                    $q->where('id', 'LIKE', "%{$term}%")
                      ->orWhereHas('user', function ($userQuery) use ($term) {
                          $userQuery->where('name', 'LIKE', "%{$term}%");
                      });
                });
            })
            ->latest()
            ->take(10)
            ->get();

        // Formatear el texto a medida según el tipo de producto
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
                $extra = $categoriaFormateada;
            }

            $texto = 'RQ-' . str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) . ' | ' . $solicitud->nombre . ' (' . $extra . ')';

            // Inyectamos la propiedad 'text' al objeto para Select2
            $solicitud->text = $texto;

            return $solicitud;
        });

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
                'nombre_cliente'    => 'required|string|max:255',
                'correo_cliente'    => 'nullable|email|max:255',
                'quote_request_id'  => 'nullable|integer|exists:quote_requests,id',
                'costo_total'       => 'required|numeric|min:0.01|max:99999',
                'concepto_rapido'   => 'required|string|max:255',
                'detalles_rapido'   => 'nullable|string|max:1000',
                'imagen_referencia' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $rutaImagen = null;

            try {
                DB::beginTransaction();

                // A. Creamos el registro maestro del pedido
                $pedido = new \App\Models\Pedido();
                $pedido->quote_request_id = $request->input('quote_request_id');
                $pedido->cliente_nombre   = $request->input('nombre_cliente');
                $pedido->correo_cliente   = $request->input('correo_cliente');

                // Llenamos con "ceros" la estructura de bastones para no romper la base de datos
                $pedido->cantidad_total_bastones = 1;
                $pedido->costo_materiales = 0;
                $pedido->costo_extras     = 0;
                $pedido->ganancia_fija    = 0;
                $pedido->costo_total      = (float) $request->input('costo_total');
                $pedido->costo_unitario   = (float) $request->input('costo_total');
                $pedido->estado           = 'pendiente';
                $pedido->save();

                // GUARDAR IMAGEN (Modo Rápido)
                if ($request->hasFile('imagen_referencia')) {
                    $rutaImagen = $request->file('imagen_referencia')->store('cotizaciones_referencias', 'public');
                    $pedido->imagen_referencia = $rutaImagen;
                    $pedido->save();
                }

                // B. Sincronización BTO
                if ($request->filled('quote_request_id')) {
                    $solicitudWeb = \App\Models\QuoteRequest::find($request->input('quote_request_id'));
                    if ($solicitudWeb) {
                        $solicitudWeb->precio_final = (float) $request->input('costo_total');
                        $solicitudWeb->estado = 'cotizado';
                        $solicitudWeb->save();
                    }
                }

                // C. Guardamos el concepto como un único "material".
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
                $this->limpiarImagenHuerfana($rutaImagen);

                Log::error('Error crítico en el servidor: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'mensaje' => config('app.debug')
                        ? 'Error crítico en el servidor: ' . $e->getMessage()
                        : 'Error al procesar la solicitud. Intente nuevamente más tarde.'
                ], 500);
            }
        }

        // =======================================================
        // 2. FLUJO NORMAL: MODO BASTONES (La calculadora pesada)
        // =======================================================

        // 1. Validación de estructura: campos base + materiales como arreglo obligatorio
        $request->validate([
            'quote_request_id'        => 'nullable|integer|exists:quote_requests,id',
            'nombre_cliente'          => 'nullable|string|max:255',
            'correo_cliente'          => 'nullable|email|max:255',
            'cantidad_total_bastones' => 'required|integer|min:1|max:10000',
            'costo_materiales'        => 'nullable|numeric|min:0|max:99999',
            'costo_extras'            => 'nullable|numeric|min:0|max:99999',
            'ganancia_fija'           => 'nullable|numeric|min:0|max:99999',
            'costo_unitario'          => 'nullable|numeric|min:0|max:99999',
            'costo_total'             => 'required|numeric|min:0.01|max:99999',
            'materiales'              => 'required|string|max:200000',
            'imagen_referencia'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // 2. Decodificamos el carrito...
        $listaMateriales = json_decode($request->input('materiales'), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($listaMateriales) || !is_array($listaMateriales)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El detalle de materiales llegó vacío o corrupto. No se puede guardar un pedido sin materiales.'
            ], 422);
        }

        // Límite razonable de líneas para evitar payloads abusivos
        if (count($listaMateriales) > 200) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El carrito tiene demasiadas líneas de materiales.'
            ], 422);
        }

        // 3. Validamos la estructura de CADA línea del carrito (defensa contra payloads manuales)
        foreach ($listaMateriales as $mat) {
            $v = Validator::make(is_array($mat) ? $mat : [], [
                'nombre_material'    => 'required|string|max:255',
                // Se permite signo negativo porque el frontend puede enviarlo (el PDF aplica abs())
                'cantidad_requerida' => 'required|numeric|between:-100000,100000',
                'subtotal_calculado' => 'required|numeric|min:0|max:99999',
                'insumo_id'          => ['nullable', 'integer', Rule::exists('insumos', 'id')->whereNull('deleted_at')],
            ]);

            if ($v->fails() || trim((string) ($mat['nombre_material'] ?? '')) === '') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Uno de los materiales del carrito tiene datos inválidos o incompletos.'
                ], 422);
            }
        }

        // 4. REGLA DE NEGOCIO: espejo de la validación del front (validarCintasNuevas en cotizador.js).
        // Si un material es una cinta nueva (sin insumo_id), su nombre debe incluir el tipo (Satín / Gross / Garza).
        foreach ($listaMateriales as $mat) {
            $nombreCinta = mb_strtolower($mat['nombre_material'], 'UTF-8');

            $esCintaSinTipo = empty($mat['insumo_id'])
                && str_contains($nombreCinta, 'cinta')
                && !str_contains($nombreCinta, 'satin')
                && !str_contains($nombreCinta, 'satín')
                && !str_contains($nombreCinta, 'gross')
                && !str_contains($nombreCinta, 'garza');

            if ($esCintaSinTipo) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El material "' . $mat['nombre_material'] . '" es una cinta nueva sin tipo especificado (Satín, Gross o Garza).'
                ], 422);
            }
        }

        $rutaImagen = null;

        try {
            DB::beginTransaction();

            // 5. Guardar la cabecera del pedido (Maestro)
            $pedido = new \App\Models\Pedido();
            $pedido->quote_request_id = $request->input('quote_request_id');
            $pedido->cliente_nombre = $request->input('nombre_cliente', 'Cliente de Mostrador');
            $pedido->correo_cliente = $request->input('correo_cliente');

            $pedido->cantidad_total_bastones = (int) $request->input('cantidad_total_bastones');

            // Desglose financiero
            $pedido->costo_materiales = (float) $request->input('costo_materiales');
            $pedido->costo_extras     = (float) $request->input('costo_extras');
            $pedido->ganancia_fija    = (float) $request->input('ganancia_fija');
            $pedido->costo_total      = (float) $request->input('costo_total');
            $pedido->costo_unitario   = (float) $request->input('costo_unitario');

            $pedido->estado = 'pendiente';
            $pedido->save();

            // GUARDAR IMAGEN (Modo Bastones)
            if ($request->hasFile('imagen_referencia')) {
                // Sube la imagen a storage/app/public/cotizaciones_referencias
                $rutaImagen = $request->file('imagen_referencia')->store('cotizaciones_referencias', 'public');
                $pedido->imagen_referencia = $rutaImagen;
                $pedido->save();
            }

            // Si el pedido tiene un ID web asociado, actualizamos la tabla del cliente
            if ($request->filled('quote_request_id')) {
                $solicitudWeb = \App\Models\QuoteRequest::find($request->input('quote_request_id'));

                if ($solicitudWeb) {
                    $solicitudWeb->precio_final = (float) $request->input('costo_total');
                    $solicitudWeb->estado = 'cotizado';
                    $solicitudWeb->save();
                }
            }

            // 6. Insertar cada material del carrito ya validado
            foreach ($listaMateriales as $mat) {

                $insumoIdFinal = $mat['insumo_id'] ?? null;
                $nombreOriginal = trim($mat['nombre_material']);
                $nombreMaterialLimpiado = mb_strtolower($nombreOriginal, 'UTF-8');

                // Detectar si es la estructura base provista por el cliente ("Sin bastón")
                $esSinBaston = str_contains($nombreMaterialLimpiado, 'sin bastón')
                            || str_contains($nombreMaterialLimpiado, 'sin baston')
                            || str_contains($nombreMaterialLimpiado, 'provista por cliente');

                if ($esSinBaston) {
                    // Forzamos insumo_id a NULL y costo $0 para que Kardex jamás lo toque
                    $insumoIdFinal = null;
                    $mat['subtotal_calculado'] = 0;

                    // Estandarizamos el prefijo igual que en [COTI-RÁPIDA]
                    if (!str_starts_with($nombreOriginal, '[SIN-BASTÓN]')) {
                        $mat['nombre_material'] = '[SIN-BASTÓN] ' . $nombreOriginal;
                    }
                } elseif (empty($insumoIdFinal)) {
                    // --- INICIO: LÓGICA DE RECONOCIMIENTO AUTOMÁTICO DE INSUMOS ---
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
                    // --- FIN: LÓGICA DE RECONOCIMIENTO ---
                }

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
            $this->limpiarImagenHuerfana($rutaImagen);

            Log::error('Error crítico en el servidor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => config('app.debug')
                    ? 'Error crítico en el servidor: ' . $e->getMessage()
                    : 'Error crítico en el servidor. Por favor, intente más tarde.'
            ], 500);
        }
    }

    // =======================================================
    // HELPER: resuelve la ruta ABSOLUTA de la imagen de referencia
    // (la usan la receta y la nota de venta)
    // =======================================================
    private function resolverRutaImagenPedido($pedido): ?string
    {
        $relativa = null;

        // 1. Prioridad alta: imagen subida manualmente en el cotizador
        if ($pedido->imagen_referencia) {
            $relativa = $pedido->imagen_referencia;
        }
        // 2. Prioridad baja: imagen proveniente de la solicitud web (BTO)
        elseif ($pedido->quote_request_id) {
            $solicitud = \App\Models\QuoteRequest::find($pedido->quote_request_id);
            if ($solicitud && $solicitud->imagen_path) {
                $relativa = $solicitud->imagen_path;
            }
        }

        if (!$relativa) {
            return null;
        }

        $relativa = ltrim($relativa, '/');

        // Defensa contra path traversal (../) y bytes nulos
        if (str_contains($relativa, '..') || str_contains($relativa, "\0")) {
            return null;
        }

        // Probamos primero el disco real y luego el symlink public/storage
        $candidatas = [
            storage_path('app/public/' . $relativa),
            public_path('storage/' . $relativa),
        ];

        foreach ($candidatas as $ruta) {
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        // Si el archivo no existe, devolvemos null para que el PDF
        // muestre "Sin imagen de referencia" en vez de fallar.
        return null;
    }

    // =======================================================
    // GENERACIÓN DE REPORTES PDF (On-the-Fly)
    // =======================================================
    public function generarPdfReceta($id)
    {
        $pedido = \App\Models\Pedido::with('materiales')->findOrFail($id);

        // Traemos los ajustes de la BD
        $config = \App\Models\Ajuste::pluck('valor', 'llave');

        $costoTotalMateriales     = 0;
        $costoDisenoPersonalizado = 0;
        $costoApliques            = 0;
        $costoRecargoBordado      = 0;

        // --- Parámetros desde ajustes (con respaldo si falta la llave) ---
        $cantBastones = $pedido->cantidad_total_bastones;

        $esPedidoGrande   = $cantBastones >= (int)($config['base_umbral_mayoreo'] ?? 12);
        $esCortinaMayoreo = $cantBastones >= (int)($config['cortina_fiesta_umbral'] ?? 12);

        $recargoBordadoUnitario = (float)($config['deco_lazo_nombre_mo'] ?? 0.70);
        $precioAplique          = (float)($config['deco_apliques_precio'] ?? 0.50);

        $gramosMadeja     = (float)($config['lana_gramos_madeja'] ?? 90);
        $cortinasPaquete  = (float)($config['cortina_unidades_paquete'] ?? 4);
        $metrosLazoSimple = (float)($config['deco_lazo_simple_m'] ?? 1.5);
        $metrosFlor       = (float)($config['deco_flor_m'] ?? 1.0);

        $preciosDiseno = [
            'basico'     => (float)($config['deco_diseno_basico'] ?? 1.50),
            'intermedio' => (float)($config['deco_diseno_intermedio'] ?? 2.00),
            'premium'    => (float)($config['deco_diseno_premium'] ?? 3.00),
        ];

        foreach ($pedido->materiales as $mat) {
            $stockActual = 0;
            $precioUnitario = 0;
            $insumo = null;
            $nombreLower = mb_strtolower($mat->nombre_material, 'UTF-8');
            $esDiseno = str_contains($nombreLower, 'diseño') || str_contains($nombreLower, 'diseno');

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

            // Aplicamos abs() para eliminar signos negativos del frontend
            $cantReq = abs((float) $mat->cantidad_requerida);
            $stock   = (float) $stockActual;

            $diferencia = $cantReq - $stock;
            $faltaComprarNumerico = $diferencia > 0 ? $diferencia : 0;
            $falta = (float) $faltaComprarNumerico;

            // 1. OBTENCIÓN DEL PRECIO
            if (!$esDiseno) {
                if ($insumo && $insumo->costo_unitario > 0) {
                    $precioUnitario = $insumo->costo_unitario;
                } else {
                    if (str_contains($nombreLower, 'base')) {
                        if (str_contains($nombreLower, 'dorado')) {
                            $precioUnitario = $esPedidoGrande
                                ? (float)($config['base_dorado_mayoreo'] ?? 5.00)
                                : (float)($config['base_dorado_normal'] ?? 5.50);
                        } else {
                            $precioUnitario = $esPedidoGrande
                                ? (float)($config['base_plata_mayoreo'] ?? 4.50)
                                : (float)($config['base_plata_normal'] ?? 5.00);
                        }
                    } elseif (str_contains($nombreLower, 'lana')) {
                        $precioUnitario = (float)($config['pf_lana'] ?? 0.0127);
                    } elseif (str_contains($nombreLower, 'garza')) {
                        $precioUnitario = (float)($config['pf_cinta_garza'] ?? 0.11);
                    } elseif (str_contains($nombreLower, 'satin') || str_contains($nombreLower, 'satín')) {
                        $precioUnitario = (float)($config['pf_cinta_satin'] ?? 0.16);
                    } elseif (str_contains($nombreLower, 'gross')) {
                        $precioUnitario = (float)($config['pf_cinta_gross'] ?? 0.15);
                    } elseif (str_contains($nombreLower, 'cortina')) {
                        $precioUnitario = $esCortinaMayoreo
                            ? (float)($config['pf_cortina_mayor'] ?? 0.50)
                            : (float)($config['pf_cortina_menor'] ?? 1.00);
                    } elseif (str_contains($nombreLower, 'elástico') || str_contains($nombreLower, 'elastico')) {
                        $precioUnitario = (float)($config['pf_elastico'] ?? 0.09);
                    } elseif (str_contains($nombreLower, 'cincho')) {
                        $precioUnitario = (float)($config['pf_cinchos'] ?? 0.02);
                    }
                }
            }

            $mat->requerido_visual = '';
            $mat->stock_visual = '';
            $mat->falta_visual = '';
            $mat->es_diseno = $esDiseno;

            // --- LÓGICA VISUAL DE BODEGA ---
            if (str_contains($nombreLower, 'flor') || str_contains($nombreLower, 'lazo')) {
                $divisor = str_contains($nombreLower, 'lazo simple') ? $metrosLazoSimple : $metrosFlor;
                $unidades = round($cantReq / $divisor);
                $mat->requerido_visual = "{$unidades} unid. (" . number_format($cantReq, 1) . "m)";
                $mat->stock_visual = number_format($stock, 1) . "m";
                $mat->falta_visual = $falta > 0 ? number_format($falta, 1) . "m" : "0";
            } elseif (str_contains($nombreLower, 'lana')) {
                $madejasReq   = ceil($cantReq / $gramosMadeja);
                $madejasFalta = ceil($falta / $gramosMadeja);
                $mat->requerido_visual = "~{$madejasReq} madejas (" . round($cantReq) . "g)";
                $mat->stock_visual = round($stock) . "g";
                $mat->falta_visual = $falta > 0 ? "~{$madejasFalta} madejas (" . round($falta) . "g)" : "0";
            } elseif (str_contains($nombreLower, 'cortina')) {
                $paquetesReq   = ceil($cantReq / $cortinasPaquete);
                $paquetesFalta = ceil($falta / $cortinasPaquete);
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
                $precioDiseno = $preciosDiseno['basico'];
                if (str_contains($nombreLower, 'intermedio')) $precioDiseno = $preciosDiseno['intermedio'];
                if (str_contains($nombreLower, 'premium'))    $precioDiseno = $preciosDiseno['premium'];

                $subtotal = $cantReq * $precioDiseno;

                $mat->precio_unitario_visual = "N/A";
                $mat->requerido_visual = "$" . number_format($subtotal, 2) . " extra";
                $mat->stock_visual = "N/A";
                $mat->falta_visual = "0";
                $mat->falta_comprar_num = 0;

                $costoDisenoPersonalizado += $subtotal;

            } elseif (str_contains($nombreLower, 'aplique')) {
                $precioUnitario = $precioAplique;
                $subtotal = $cantReq * $precioUnitario;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitario, 2);
                $mat->falta_comprar_num = $faltaComprarNumerico;

                $costoApliques += $subtotal;

            } elseif (str_contains($nombreLower, 'lazo') && str_contains($nombreLower, 'nombre')) {
                $recargoBordado = $recargoBordadoUnitario * $cantBastones;
                $subtotalMaterial = $cantReq * $precioUnitario;
                $subtotal = $subtotalMaterial + $recargoBordado;

                $mat->precio_unitario_visual = "$" . number_format($precioUnitario, 4);
                $mat->falta_comprar_num = $faltaComprarNumerico;
                // Texto plano: la vista usa {{ }} que escapa el HTML (<small> se vería literal)
                $mat->requerido_visual .= " (+ $" . number_format($recargoBordado, 2) . " bordado)";

                $costoTotalMateriales += $subtotalMaterial;
                $costoRecargoBordado  += $recargoBordado;

            } else {
                $subtotal = $cantReq * $precioUnitario;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitario, 4);
                $mat->falta_comprar_num = $faltaComprarNumerico;

                $costoTotalMateriales += $subtotal;
            }

            $mat->subtotal_visual = "$" . number_format($subtotal, 2);
        }

        // 3. MANO DE OBRA Y GRAN TOTAL (lectura directa de lo guardado en el pedido)
        $costoTotalMateriales = (float) $pedido->costo_materiales;

        $costoManoObra = (float) ($pedido->costo_mano_obra ?? ($costoTotalMateriales * (float)($config['margen_ganancia'] ?? 0.60)));

        $costoTotalProduccion = (float) $pedido->costo_total;

        // 4. IMAGEN DE REFERENCIA (misma lógica que la nota de venta)
        $rutaImagenPdf = $this->resolverRutaImagenPedido($pedido);

        // Mandamos a generar el PDF
        $pdf = Pdf::loadView('reportes.receta', compact(
            'pedido',
            'costoTotalMateriales',
            'costoManoObra',
            'costoTotalProduccion',
            'costoDisenoPersonalizado',
            'costoApliques',
            'costoRecargoBordado',
            'rutaImagenPdf'
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
        // 'admin' y 'super_admin' pueden ver la nota de cualquier pedido del taller;
        // cualquier otro usuario solo puede ver la suya (dueno_id === Auth::id()).
        $esStaffAdmin = in_array(Auth::user()->role, ['admin', 'super_admin']);

        if ($dueno_id != Auth::id() && !$esStaffAdmin) {
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

        // 5. IMAGEN DE REFERENCIA (helper compartido con la receta)
        $rutaImagenPdf = $this->resolverRutaImagenPedido($pedido);

        // 6. Generar el PDF
        $pdf = Pdf::loadView('reportes.nota', compact('pedido', 'esCotizacionRapida', 'rutaImagenPdf'));

        return $pdf->stream('Nota_Venta_Pedido_' . $pedido->id . '.pdf');
    }

    // =======================================================
    // ENVÍO DE CORREOS
    // =======================================================

    public function enviarCorreo(Request $request)
    {
        // 1. Validamos que nos llegue un ID válido y un correo con formato correcto
        $request->validate([
            'pedido_id' => 'required|integer|exists:pedidos,id',
            'email'     => 'required|email|max:255'
        ]);

        try {
            // 2. Buscamos el pedido con todo su detalle (para el PDF)
            $pedido = Pedido::with('materiales')->findOrFail($request->pedido_id);

            // 3. Enviamos el correo usando nuestra clase Mailable
            Mail::to($request->email)->send(new NotaVentaMailable($pedido));

            // 4. Respondemos al Frontend que todo salió bien
            return response()->json([
                'success' => true,
                'mensaje' => 'Correo enviado exitosamente a la bandeja de prueba.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar el correo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => config('app.debug')
                    ? 'Error al enviar el correo: ' . $e->getMessage()
                    : 'Error al enviar el correo. Por favor, intente nuevamente más tarde.'
            ], 500);
        }
    }
}