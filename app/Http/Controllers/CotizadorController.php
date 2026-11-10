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

    public function guardar(Request $request)
    {
        // =======================================================
        // 1. EL BYPASS: MODO RÁPIDO (Manualidades, Lazos, Flores)
        // =======================================================
        if ($request->filled('modo_rapido') && ($request->modo_rapido == 'true' || $request->modo_rapido == '1')) {

            $validadorRapido = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'nombre_cliente'    => 'required|string|max:255',
                'correo_cliente'    => 'nullable|email|max:255',
                'quote_request_id'  => 'nullable|integer|exists:quote_requests,id',
                'costo_total'       => 'required|numeric|min:0.01|max:99999',
                'concepto_rapido'   => 'required|string|max:255',
                'detalles_rapido'   => 'nullable|string|max:1000',
                'imagen_referencia' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            ]);

            if ($validadorRapido->fails()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Revisa los campos del formulario.',
                    'errors'  => $validadorRapido->errors()
                ], 422);
            }

            $rutaImagen = null;

            try {
                DB::beginTransaction();

                // A. Creamos el registro maestro del pedido
                $pedido = new \App\Models\Pedido();
                $pedido->quote_request_id = $request->input('quote_request_id');
                $pedido->cliente_nombre   = $request->input('nombre_cliente');
                $pedido->correo_cliente   = $request->input('correo_cliente');

                // Estructura base para orden manual / manualidad
                $pedido->cantidad_total_bastones = 1;
                $pedido->costo_materiales = (float) $request->input('costo_total'); // Lo reflejamos como base para que no salga $0 en reportes
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

                // C. Guardamos el concepto como un único "material" con el tag identificador
                $detalle = trim($request->input('concepto_rapido'));
                if ($request->filled('detalles_rapido')) {
                    // Usamos un salto de línea HTML (<br>) en lugar de un guion
                    // nl2br convierte los "Enters" del textarea en <br> reales
                    $textoDetalles = nl2br(htmlspecialchars(trim($request->input('detalles_rapido'))));
                    $detalle .= '<br><small style="color: #7a7585;">' . $textoDetalles . '</small>';
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
                if (method_exists($this, 'limpiarImagenHuerfana')) {
                    $this->limpiarImagenHuerfana($rutaImagen);
                }

                Log::error('Error crítico en el servidor (Modo Rápido): ' . $e->getMessage());

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
        $validadorBaston = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
            'imagen_referencia'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validadorBaston->fails()) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en la estructura base.',
                'errors'  => $validadorBaston->errors()
            ], 422);
        }

        $listaMateriales = json_decode($request->input('materiales'), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($listaMateriales) || !is_array($listaMateriales)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El detalle de materiales llegó vacío o corrupto.',
                'errors'  => ['materiales' => ['El detalle de materiales llegó vacío o corrupto.']] 
            ], 422);
        }

        if (count($listaMateriales) > 200) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El carrito tiene demasiadas líneas de materiales.',
                'errors'  => ['materiales' => ['El carrito supera el límite de 200 líneas.']]
            ], 422);
        }

        foreach ($listaMateriales as $mat) {
            $v = \Illuminate\Support\Facades\Validator::make(is_array($mat) ? $mat : [], [
                'nombre_material'    => 'required|string|max:255',
                'cantidad_requerida' => 'required|numeric|between:-100000,100000',
                'subtotal_calculado' => 'required|numeric|min:0|max:99999',
                'insumo_id'          => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('insumos', 'id')->whereNull('deleted_at')],
            ]);

            if ($v->fails() || trim((string) ($mat['nombre_material'] ?? '')) === '') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Datos inválidos en el carrito.',
                    'errors'  => ['carrito' => ['Uno de los materiales tiene datos inválidos o su stock ha sido eliminado.']]
                ], 422);
            }
        }

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
                    'mensaje' => 'Tipo de cinta inválido.',
                    'errors'  => ['cinta' => ['El material "' . $mat['nombre_material'] . '" es una cinta nueva sin tipo (Satín, Gross o Garza).']]
                ], 422);
            }
        }

        $rutaImagen = null;

        try {
            DB::beginTransaction();

            $pedido = new \App\Models\Pedido();
            $pedido->quote_request_id        = $request->input('quote_request_id');
            $pedido->cliente_nombre          = $request->input('nombre_cliente', 'Cliente de Mostrador');
            $pedido->correo_cliente          = $request->input('correo_cliente');
            $pedido->cantidad_total_bastones = (int) $request->input('cantidad_total_bastones');

            // Desglose financiero exacto enviado por el frontend
            $pedido->costo_materiales = (float) $request->input('costo_materiales');
            $pedido->costo_extras     = (float) $request->input('costo_extras');
            $pedido->ganancia_fija    = (float) $request->input('ganancia_fija');
            $pedido->costo_total      = (float) $request->input('costo_total');
            $pedido->costo_unitario   = (float) $request->input('costo_unitario');

            $pedido->estado = 'pendiente';
            $pedido->save();

            if ($request->hasFile('imagen_referencia')) {
                $rutaImagen = $request->file('imagen_referencia')->store('cotizaciones_referencias', 'public');
                $pedido->imagen_referencia = $rutaImagen;
                $pedido->save();
            }

            if ($request->filled('quote_request_id')) {
                $solicitudWeb = \App\Models\QuoteRequest::find($request->input('quote_request_id'));
                if ($solicitudWeb) {
                    $solicitudWeb->precio_final = (float) $request->input('costo_total');
                    $solicitudWeb->estado = 'cotizado';
                    $solicitudWeb->save();
                }
            }

            foreach ($listaMateriales as $mat) {
                $insumoIdFinal = $mat['insumo_id'] ?? null;
                $nombreOriginal = trim($mat['nombre_material']);
                $nombreMaterialLimpiado = mb_strtolower($nombreOriginal, 'UTF-8');

                // Detectar si es la estructura base provista por el cliente ("Sin bastón")
                $esSinBaston = str_contains($nombreMaterialLimpiado, 'sin bastón')
                            || str_contains($nombreMaterialLimpiado, 'sin baston')
                            || str_contains($nombreMaterialLimpiado, 'provista por cliente');

                // Detectar ítems que son de lana (Cuerpo o Cortina) pero vinieron con tag nuevo sin la palabra "lana"
                // En cotizador.js el Cuerpo se guarda solo con el nombre del color (ej: "azul").
                // Si no tiene insumo_id y no es ninguno de los otros módulos, sabemos que viene del selector de lana.
                $esItemNoLana = $esSinBaston
                    || str_contains($nombreMaterialLimpiado, 'base')
                    || str_contains($nombreMaterialLimpiado, 'cincho')
                    || str_contains($nombreMaterialLimpiado, 'elástico')
                    || str_contains($nombreMaterialLimpiado, 'elastico')
                    || str_contains($nombreMaterialLimpiado, 'cortina de fiesta')
                    || str_contains($nombreMaterialLimpiado, 'lazo')
                    || str_contains($nombreMaterialLimpiado, 'flor')
                    || str_contains($nombreMaterialLimpiado, 'aplique')
                    || str_contains($nombreMaterialLimpiado, 'diseño')
                    || str_contains($nombreMaterialLimpiado, 'diseno');

                if ($esSinBaston) {
                    $insumoIdFinal = null;
                    $mat['subtotal_calculado'] = 0;

                    if (!str_starts_with($nombreOriginal, '[SIN-BASTÓN]')) {
                        $mat['nombre_material'] = '[SIN-BASTÓN] ' . $nombreOriginal;
                    }
                } elseif (empty($insumoIdFinal)) {
                    if (str_contains($nombreMaterialLimpiado, 'cincho')) {
                        $insumoDetectado = \App\Models\Insumo::where('categoria', 'cinchos')->whereNull('deleted_at')->first();
                        if ($insumoDetectado) $insumoIdFinal = $insumoDetectado->id;
                    } elseif (str_contains($nombreMaterialLimpiado, 'elástico') || str_contains($nombreMaterialLimpiado, 'elastico')) {
                        $insumoDetectado = \App\Models\Insumo::where('categoria', 'elastico')->whereNull('deleted_at')->first();
                        if ($insumoDetectado) $insumoIdFinal = $insumoDetectado->id;
                    } elseif (!$esItemNoLana && !str_contains($nombreMaterialLimpiado, 'lana')) {
                        // Si era un color nuevo de lana (ej. "azul"), le anteponemos "Lana: " para que la receta sepa que se mide en gramos/madejas
                        $mat['nombre_material'] = 'Lana: ' . $nombreOriginal;
                    }
                }

                DB::table('pedido_materiales')->insert([
                    'pedido_id'          => $pedido->id,
                    'insumo_id'          => $insumoIdFinal,
                    'nombre_material'    => $mat['nombre_material'],
                    'cantidad_requerida' => abs((float) $mat['cantidad_requerida']), // Guardamos siempre positivo
                    'subtotal_calculado' => (float) $mat['subtotal_calculado'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Pedido y lista de materiales guardados con éxito.',
                'id'      => $pedido->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (method_exists($this, 'limpiarImagenHuerfana')) {
                $this->limpiarImagenHuerfana($rutaImagen);
            }

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
    // GENERACIÓN DE REPORTES PDF (On-the-Fly)
    // =======================================================
    public function generarPdfReceta($id)
    {
        $pedido = \App\Models\Pedido::with('materiales')->findOrFail($id);
        $config = \App\Models\Ajuste::pluck('valor', 'llave');

        $costoDisenoPersonalizado = 0;
        $costoApliques            = 0;
        $costoRecargoBordado      = 0;

        $cantBastones = max(1, (int) $pedido->cantidad_total_bastones);

        // Detectar si este pedido es una Cotización Rápida (Manualidad / Encargo Especial)
        $esCotizacionRapida = $pedido->materiales->count() === 1
            && str_starts_with($pedido->materiales->first()->nombre_material, '[COTI-RÁPIDA]');

        // Etiquetas dinámicas que puedes usar en tu Blade si quieres mostrar el título general
        $tituloTipoPedido = $esCotizacionRapida ? 'Manualidades / Encargo Especial' : 'Bastones de Cachiporrera';
        $unidadPedido     = $esCotizacionRapida ? 'encargo(s)' : 'bastones';

        // Parámetros de conversión física para bodega
        $recargoBordadoUnitario = (float)($config['deco_lazo_nombre_mo'] ?? 0.70);
        $gramosMadeja           = (float)($config['lana_gramos_madeja'] ?? 90);
        $cortinasPaquete        = (float)($config['cortina_unidades_paquete'] ?? 4);
        $metrosLazoSimple       = (float)($config['deco_lazo_simple_m'] ?? 1.5);
        $metrosFlor             = (float)($config['deco_flor_m'] ?? 1.0);

        foreach ($pedido->materiales as $mat) {
            $stockActual = 0;
            $insumo      = null;
            $nombreLower = mb_strtolower($mat->nombre_material, 'UTF-8');

            // Clasificación de la fila
            $esCotiRapida = str_starts_with($mat->nombre_material, '[COTI-RÁPIDA]');
            $esSinBaston  = str_contains($nombreLower, '[sin-bastón]') || str_contains($nombreLower, 'sin bastón') || str_contains($nombreLower, 'sin baston');
            $esDiseno     = str_contains($nombreLower, 'diseño') || str_contains($nombreLower, 'diseno');
            $esAplique    = str_contains($nombreLower, 'aplique');

            // Limpiamos el tag técnico si es cotización rápida para que se vea limpio en el PDF
            if ($esCotiRapida) {
                $mat->nombre_material = trim(str_replace('[COTI-RÁPIDA]', '', $mat->nombre_material));
            }

            // Solo buscamos stock en BD si es un insumo físico real de inventario
            if (!$esDiseno && !$esSinBaston && !$esAplique && !$esCotiRapida) {
                if ($mat->insumo_id) {
                    $insumo = \App\Models\Insumo::find($mat->insumo_id);
                }
                if (!$insumo) {
                    $insumo = \App\Models\Insumo::where('nombre', $mat->nombre_material)->first();
                }
                if ($insumo) {
                    $stockActual = (float) $insumo->stock_actual;
                }
            }

            // Detectamos si es lana (ya sea por su categoría en BD, por su nombre, o porque su cantidad en gramos es alta > 100 y no es otro ítem)
            $esLana = str_contains($nombreLower, 'lana')
                || ($insumo && $insumo->categoria === 'lana')
                || (!$insumo && !$esDiseno && !$esSinBaston && !$esAplique && !$esCotiRapida
                    && !str_contains($nombreLower, 'base') && !str_contains($nombreLower, 'cincho')
                    && !str_contains($nombreLower, 'elástico') && !str_contains($nombreLower, 'elastico')
                    && !str_contains($nombreLower, 'cortina') && !str_contains($nombreLower, 'lazo')
                    && !str_contains($nombreLower, 'flor') && !str_contains($nombreLower, 'cinta'));

            // Cantidad siempre positiva
            $cantReq  = abs((float) $mat->cantidad_requerida);
            $stock    = max(0, (float) $stockActual);
            $subtotal = (float) $mat->subtotal_calculado; // <-- LEEMOS EL SUBTOTAL REAL GUARDADO, NO RECALCULAMOS

            // Si NO descuenta inventario (Sin bastón, Diseño, Apliques, Coti Rápida), no falta comprar nada en Kardex
            if ($esSinBaston || $esDiseno || $esAplique || $esCotiRapida) {
                $faltaComprarNumerico = 0;
            } else {
                $diferencia = $cantReq - $stock;
                $faltaComprarNumerico = $diferencia > 0 ? $diferencia : 0;
            }
            $falta = (float) $faltaComprarNumerico;

            $mat->requerido_visual  = '';
            $mat->stock_visual      = '';
            $mat->falta_visual      = '';
            $mat->es_diseno         = $esDiseno;
            $mat->falta_comprar_num = $faltaComprarNumerico;

            // --- 1. LÓGICA VISUAL DE BODEGA Y PRECIO UNITARIO ---
            if ($esCotiRapida) {
                $mat->precio_unitario_visual = "$" . number_format($subtotal, 2);
                $mat->requerido_visual       = "1 encargo";
                $mat->stock_visual           = "N/A";
                $mat->falta_visual           = "0";

            } elseif ($esSinBaston) {
                // RESUELVE PROBLEMA 1: El bastón del cliente cuesta $0.00 y no pide comprar
                $mat->precio_unitario_visual = "$0.00";
                $mat->requerido_visual       = round($cantReq) . " unid. (Cliente)";
                $mat->stock_visual           = "N/A";
                $mat->falta_visual           = "0";

            } elseif ($esDiseno) {
                $precioUnitDiseno = $cantReq > 0 ? ($subtotal / $cantReq) : $subtotal;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitDiseno, 2);
                $mat->requerido_visual       = round($cantReq) . " unid. (Mano de Obra)";
                $mat->stock_visual           = "N/A";
                $mat->falta_visual           = "0";

                $costoDisenoPersonalizado += $subtotal;

            } elseif ($esAplique) {
                $precioUnitAplique = $cantReq > 0 ? ($subtotal / $cantReq) : 0;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitAplique, 2);
                $mat->requerido_visual       = round($cantReq) . " unid. (Manual)";
                $mat->stock_visual           = "N/A";
                $mat->falta_visual           = "0";

                $costoApliques += $subtotal;

            } elseif (str_contains($nombreLower, 'lazo') && str_contains($nombreLower, 'nombre')) {
                $recargoBordado   = $recargoBordadoUnitario * $cantBastones;
                $subtotalMaterial = max(0, $subtotal - $recargoBordado);
                $precioUnitCinta  = $cantReq > 0 ? ($subtotalMaterial / $cantReq) : 0;

                $unidades = round($cantReq / $metrosFlor);
                $mat->precio_unitario_visual = "$" . number_format($precioUnitCinta, 4);
                $mat->requerido_visual       = "{$unidades} unid. (" . number_format($cantReq, 1) . "m) (+ $" . number_format($recargoBordado, 2) . " bordado)";
                $mat->stock_visual           = number_format($stock, 1) . "m";
                $mat->falta_visual           = $falta > 0 ? number_format($falta, 1) . "m" : "0";

                $costoRecargoBordado += $recargoBordado;

            } else {
                // RESUELVE PROBLEMA 2 y 3: El precio unitario se deduce del subtotal real guardado
                $precioUnitario = $cantReq > 0 ? ($subtotal / $cantReq) : 0;
                $mat->precio_unitario_visual = "$" . number_format($precioUnitario, 4);

                if (str_contains($nombreLower, 'flor') || str_contains($nombreLower, 'lazo')) {
                    $divisor  = str_contains($nombreLower, 'lazo simple') ? $metrosLazoSimple : $metrosFlor;
                    $unidades = round($cantReq / $divisor);
                    $mat->requerido_visual = "{$unidades} unid. (" . number_format($cantReq, 1) . "m)";
                    $mat->stock_visual     = number_format($stock, 1) . "m";
                    $mat->falta_visual     = $falta > 0 ? number_format($falta, 1) . "m" : "0";

                } elseif ($esLana) {
                    // Sin signos negativos: usamos ~ para indicar aproximado de madejas positivas
                    $madejasReq   = ceil($cantReq / $gramosMadeja);
                    $madejasFalta = ceil($falta / $gramosMadeja);
                    $mat->requerido_visual = "{$madejasReq} madejas (" . round($cantReq) . "g)";
                    $mat->stock_visual     = round($stock) . "g";
                    $mat->falta_visual     = $falta > 0 ? "{$madejasFalta} madejas (" . round($falta) . "g)" : "0";

                } elseif (str_contains($nombreLower, 'cortina')) {
                    $paquetesReq   = ceil($cantReq / $cortinasPaquete);
                    $paquetesFalta = ceil($falta / $cortinasPaquete);
                    $mat->requerido_visual = "{$paquetesReq} paquetes (" . round($cantReq) . " unid.)";
                    $mat->stock_visual     = round($stock) . " unid.";
                    $mat->falta_visual     = $falta > 0 ? "{$paquetesFalta} paquetes (" . round($falta) . " unid.)" : "0";

                } elseif (str_contains($nombreLower, 'elástico') || str_contains($nombreLower, 'elastico') || str_contains($nombreLower, 'cinta')) {
                    $mat->requerido_visual = $cantReq < 1 ? round($cantReq * 100) . " cm" : number_format($cantReq, 1) . " m";
                    $mat->stock_visual     = $stock < 1 && $stock > 0 ? round($stock * 100) . " cm" : number_format($stock, 1) . " m";
                    $mat->falta_visual     = $falta < 1 && $falta > 0 ? round($falta * 100) . " cm" : ($falta > 0 ? number_format($falta, 1) . " m" : "0");

                } else {
                    $mat->requerido_visual = round($cantReq) . " unid.";
                    $mat->stock_visual     = round($stock) . " unid.";
                    $mat->falta_visual     = $falta > 0 ? round($falta) . " unid." : "0";
                }
            }

            $mat->subtotal_visual = "$" . number_format($subtotal, 2);
        }

        // --- 2. RESUMEN FINANCIERO (RESUELVE PROBLEMA 4: EL CENTAVO DE LA DISCORDIA) ---
        // En `guardar()` el campo se guarda como `ganancia_fija`, pero antes leías `$pedido->costo_mano_obra` (que no existe y daba null).
        // Al leer `$pedido->ganancia_fija`, toma exactamente los $60.00 que guardó el frontend sin multiplicar 99.99 * 0.60.
        $costoTotalMateriales = (float) $pedido->costo_materiales;
        $costoManoObra        = (float) ($pedido->ganancia_fija ?? ($costoTotalMateriales * (float)($config['margen_ganancia'] ?? 0.60)));
        $costoTotalProduccion = (float) $pedido->costo_total;

        $rutaImagenPdf = $this->resolverRutaImagenPedido($pedido);

        $pdf = Pdf::loadView('reportes.receta', compact(
            'pedido',
            'costoTotalMateriales',
            'costoManoObra',
            'costoTotalProduccion',
            'costoDisenoPersonalizado',
            'costoApliques',
            'costoRecargoBordado',
            'rutaImagenPdf',
            'esCotizacionRapida',
            'tituloTipoPedido',
            'unidadPedido'
        ));

        return $pdf->stream('Receta_Bodega_Pedido_' . $pedido->id . '.pdf');
    }

    public function generarPdfNota($id)
    {
        $pedido = Pedido::with('materiales')->findOrFail($id);

        $dueno_id = $pedido->user_id;
        if (!$dueno_id && $pedido->quote_request_id) {
            $solicitudOriginal = \App\Models\QuoteRequest::find($pedido->quote_request_id);
            $dueno_id = $solicitudOriginal ? $solicitudOriginal->user_id : null;
        }

        $esStaffAdmin = in_array(Auth::user()->role, ['admin', 'super_admin']);

        if ($dueno_id != Auth::id() && !$esStaffAdmin) {
            abort(403, 'No tienes permiso para ver esta nota de venta.');
        }

        $esCotizacionRapida = false;
        $tituloTipoPedido   = 'Bastones de Cachiporrera';

        if ($pedido->materiales->count() === 1 && str_starts_with($pedido->materiales->first()->nombre_material, '[COTI-RÁPIDA]')) {
            $esCotizacionRapida = true;
            $tituloTipoPedido   = 'Manualidades / Encargo Especial';

            $nombreLimpio = trim(str_replace('[COTI-RÁPIDA]', '', $pedido->materiales->first()->nombre_material));
            $pedido->materiales->first()->nombre_material = $nombreLimpio;
        }

        $rutaImagenPdf = $this->resolverRutaImagenPedido($pedido);

        $pdf = Pdf::loadView('reportes.nota', compact(
            'pedido',
            'esCotizacionRapida',
            'tituloTipoPedido',
            'rutaImagenPdf'
        ));

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