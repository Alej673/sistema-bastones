<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Insumo;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    // AJUSTA: valores que envía tu select de "tipo de movimiento" en el modal de ajuste de stock.
    // 'entrada' suma paquetes completos; cualquier otro valor descuenta unidades sueltas.
    private const TIPOS_MOVIMIENTO_MANUAL = ['entrada', 'salida'];

    // =======================================================
    // HELPERS PRIVADOS
    // =======================================================

    /**
     * Mapa categoría => [multiplicador de compra, unidad de medida].
     * Los multiplicadores salen de la tabla `ajustes` (con respaldo si falta la llave).
     * Si una llave no existe o es <= 0, se usa el valor de respaldo.
     */
    private function mapaCategorias(): array
    {
        $config = Ajuste::pluck('valor', 'llave');

        $valor = function (string $llave, float $respaldo) use ($config): float {
            $v = (float) ($config[$llave] ?? $respaldo);
            return $v > 0 ? $v : $respaldo;
        };

        return [
            'lana'           => ['mult' => $valor('lana_gramos_madeja', 90),          'unidad' => 'Gramos'],
            'cinta_garza'    => ['mult' => $valor('insumo_rollo_garza_m', 45.72),     'unidad' => 'Metros'],
            'cinta_satin'    => ['mult' => $valor('insumo_rollo_satin_m', 18.28),     'unidad' => 'Metros'],
            'cinta_gross'    => ['mult' => $valor('insumo_rollo_gross_m', 22.86),     'unidad' => 'Metros'],
            'cortina_fiesta' => ['mult' => $valor('cortina_unidades_paquete', 4),     'unidad' => 'Unidades'],
            'elastico'       => ['mult' => $valor('insumo_paquete_elastico_m', 10),   'unidad' => 'Metros'],
            'cinchos'        => ['mult' => $valor('insumo_paquete_cinchos_u', 100),   'unidad' => 'Unidades'],
            'base_baston'    => ['mult' => 1,                                         'unidad' => 'Unidades'],
            'unidad_simple'  => ['mult' => 1,                                         'unidad' => 'Unidades'],
        ];
    }

    /**
     * Estandariza el nombre: sin HTML, sin espacios dobles, minúsculas y primera letra en mayúscula
     * (compatible con tildes y Ñ).
     */
    private function normalizarNombre($nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', strip_tags((string) $nombre)));
        $nombre = mb_strtolower($nombre, 'UTF-8');

        return mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($nombre, 1, null, 'UTF-8');
    }

    // =======================================================
    // CRUD
    // =======================================================

    // Función para mostrar la página de index
    public function index()
    {
        // 1. Esto traerá todos los insumos activos (automáticamente oculta los SoftDeletes)
        $insumos = Insumo::all();

        // 2. Solo movimientos de insumos que NO están eliminados
        $movimientos = Movimiento::with('insumo')
            ->has('insumo')
            ->latest()
            ->take(15)
            ->get();

        // Extraer configuración global como arreglo asociativo
        $config = \App\Models\Ajuste::pluck('valor', 'llave')->toArray();

        return view('insumos.index', compact('insumos', 'movimientos', 'config'));
    }

    public function create()
    {
        return view('insumos.create');
    }

    public function store(Request $request)
    {
        // 1. Estandarizar el texto ANTES de validar (Blindaje de datos)
        $request->merge([
            'nombre' => $this->normalizarNombre($request->input('nombre')),
        ]);

        $mapa = $this->mapaCategorias();

        // 2. Validamos SIEMPRE (también cuando se va a restaurar un insumo eliminado).
        // La regla unique ignora los eliminados lógicamente: ese caso se maneja abajo.
        $request->validate([
            'nombre'            => ['required', 'string', 'max:255', Rule::unique('insumos', 'nombre')->whereNull('deleted_at')],
            'categoria'         => ['required', 'string', Rule::in(array_keys($mapa))],
            'cantidad_comprada' => 'required|numeric|min:0|max:1000000',
            'precio_unitario'   => 'required|numeric|min:0|max:1000000',
            'alerta_minima'     => 'required|numeric|min:0|max:1000000',
        ], [
            'nombre.unique' => 'Este insumo ya se encuentra registrado en el Kardex y está activo.',
            'categoria.in'  => 'La categoría seleccionada no es válida.',
        ]);

        // 3. Motor de traducción: valores desde la configuración
        $multiplicador = $mapa[$request->categoria]['mult'];
        $unidad        = $mapa[$request->categoria]['unidad'];

        $stock_actual_calculado   = $request->cantidad_comprada * $multiplicador;
        $stock_minimo_calculado   = $request->alerta_minima * $multiplicador;
        $costo_unitario_calculado = $multiplicador > 0
            ? $request->precio_unitario / $multiplicador
            : 0;

        // 4. Buscar si existe el insumo pero está eliminado lógicamente
        $insumoEliminado = Insumo::withTrashed()
            ->where('nombre', $request->nombre)
            ->whereNotNull('deleted_at')
            ->first();

        if ($insumoEliminado) {
            DB::transaction(function () use ($insumoEliminado, $request, $unidad, $costo_unitario_calculado, $stock_actual_calculado, $stock_minimo_calculado) {
                $insumoEliminado->restore(); // Quita el deleted_at

                $insumoEliminado->update([
                    'categoria'      => $request->categoria,
                    'unidad_medida'  => $unidad,
                    'precio_comercial' => $request->precio_unitario,
                    'costo_unitario' => $costo_unitario_calculado,
                    'stock_actual'   => $stock_actual_calculado,
                    'stock_minimo'   => $stock_minimo_calculado,
                ]);

                Movimiento::create([
                    'insumo_id'       => $insumoEliminado->id,
                    'tipo_movimiento' => 'ingreso_nuevo',
                    'cantidad'        => $insumoEliminado->stock_actual,
                    'detalle'         => 'Restauración de insumo previamente eliminado',
                ]);
            });

            return redirect()->route('insumos.index')
                ->with('success', 'El insumo que estaba eliminado ha sido restaurado exitosamente con el nuevo stock.');
        }

        // 5. FLUJO NORMAL DE CREACIÓN
        DB::transaction(function () use ($request, $unidad, $costo_unitario_calculado, $stock_actual_calculado, $stock_minimo_calculado) {
            $insumo = Insumo::create([
                'nombre'         => $request->nombre,
                'categoria'      => $request->categoria,
                'unidad_medida'  => $unidad,
                'precio_comercial' => $request->precio_unitario,
                'costo_unitario' => $costo_unitario_calculado,
                'stock_actual'   => $stock_actual_calculado,
                'stock_minimo'   => $stock_minimo_calculado,
            ]);

            Movimiento::create([
                'insumo_id'       => $insumo->id,
                'tipo_movimiento' => 'ingreso_nuevo',
                'cantidad'        => $insumo->stock_actual,
                'detalle'         => 'Creación de nuevo insumo en sistema',
            ]);
        });

        return redirect()->route('insumos.index')
            ->with('success', 'El insumo fue registrado exitosamente en el Kardex.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    // FUNCIÓN PARA CORREGIR EL NOMBRE
    public function update(Request $request, $id)
    {
        // Misma estandarización que en store, para no crear duplicados "disfrazados"
        $request->merge([
            'nombre' => $this->normalizarNombre($request->input('nombre')),
        ]);

        $request->validate([
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('insumos', 'nombre')->ignore($id)->whereNull('deleted_at'),
            ],
        ], [
            'nombre.unique' => 'Ya existe otro insumo activo con ese nombre.',
        ]);

        $insumo = Insumo::findOrFail($id);
        $insumo->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('insumos.index');
    }

    // FUNCIÓN PARA SUMAR/RESTAR STOCK EN CANTIDADES CERRADAS
    public function ajustarStock(Request $request, $id)
    {
        $request->validate([
            'cantidad_movimiento' => 'required|numeric|min:0.01|max:1000000',
            'tipo_movimiento'     => ['required', 'string', Rule::in(self::TIPOS_MOVIMIENTO_MANUAL)],
        ]);

        $mapa = $this->mapaCategorias();

        // Transacción + bloqueo de fila: evita que dos ajustes simultáneos pisen el stock
        DB::transaction(function () use ($request, $id, $mapa) {
            $insumo = Insumo::lockForUpdate()->findOrFail($id);

            $esEntrada = $request->tipo_movimiento === 'entrada';

            // ENTRADA (compra comercial): suma paquetes/madejas/rollos completos
            //   -> multiplicador de la configuración.
            // SALIDA (desperdicio/ajuste): descuenta directo en la unidad mínima -> x1.
            $multiplicador = $esEntrada
                ? ($mapa[$insumo->categoria]['mult'] ?? 1)
                : 1;

            $unidadesAProcesar = $request->cantidad_movimiento * $multiplicador;

            if ($esEntrada) {
                $insumo->stock_actual += $unidadesAProcesar;
            } else {
                if ($insumo->stock_actual < $unidadesAProcesar) {
                    $insumo->stock_actual = 0;
                } else {
                    $insumo->stock_actual -= $unidadesAProcesar;
                }
            }

            // REGISTRO AUTOMÁTICO EN LA BITÁCORA
            Movimiento::create([
                'insumo_id'       => $insumo->id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'cantidad'        => $unidadesAProcesar,
                'detalle'         => 'Ajuste manual en Kardex',
            ]);

            $insumo->save();
        });

        return redirect()->route('insumos.index');
    }

    // ELIMINAR (SOFT DELETE) EL INSUMO
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);

        DB::transaction(function () use ($insumo) {
            // Registrar en bitácora la eliminación
            Movimiento::create([
                'insumo_id'       => $insumo->id,
                'tipo_movimiento' => 'eliminado',
                'cantidad'        => $insumo->stock_actual,
                'detalle'         => 'Insumo retirado o anulado del inventario',
            ]);

            // "Soft Delete" (lo oculta, no lo destruye de la BD)
            $insumo->delete();
        });

        return redirect()->route('insumos.index');
    }
}