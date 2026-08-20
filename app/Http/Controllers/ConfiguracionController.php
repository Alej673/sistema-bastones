<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ajuste;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el panel de configuraciones.
     */
    public function index()
    {
        // Traemos todos los ajustes de la BD
        $ajustes = Ajuste::all();

        // Los agrupamos por la columna 'grupo' para armar las pestañas en la vista
        $grupos = $ajustes->groupBy('grupo');

        return view('Configuracion.configuraciones', compact('grupos'));
    }

    /**
     * Guarda los cambios enviados desde el formulario.
     */
    public function update(Request $request)
    {
        // 1. Recibimos todos los datos del formulario
        $datos = $request->except(['_token', '_method']);

        // =========================================================
        // 2. EL TRADUCTOR INVERSO (Comercial -> Costo Interno)
        // =========================================================
        // Leemos los divisores directamente de lo que el usuario acaba de enviar
        $divisores = [
            'pf_lana'        => (float)($datos['lana_gramos_madeja'] ?? 90),
            'pf_cinta_garza' => 45.72,
            'pf_cinta_satin' => 18.28,
            'pf_cinta_gross' => 22.86,
            'pf_elastico'    => 10,
            'pf_cinchos'     => 100,
        ];

        foreach ($divisores as $llave => $divisor) {
            // Si el campo existe en la petición y el divisor no es cero (para evitar errores matemáticos)
            if (isset($datos[$llave]) && $divisor > 0) {
                // $datos[$llave] trae el precio comercial (Ej: 1.15). 
                // Lo dividimos y lo reemplazamos por el milimétrico (Ej: 0.0127) ANTES de guardar
                $datos[$llave] = round((float)$datos[$llave] / $divisor, 4);
            }
        }
        // =========================================================

        try {
            DB::beginTransaction();

            // 3. Guardamos los datos (ahora sí, con la matemática interna correcta)
            foreach ($datos as $llave => $valor) {
                Ajuste::where('llave', $llave)->update(['valor' => $valor]);
            }

            DB::commit();

            // Respuesta para el Fetch API (AJAX)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Reglas de negocio actualizadas y recalculadas.'
                ]);
            }

            return redirect()->back()->with('success', 'Configuraciones actualizadas.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Ocurrió un error al guardar: ' . $e->getMessage());
        }
    }
}