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
        $datos = $request->except(['_token', '_method']);

        try {
            DB::beginTransaction();

            foreach ($datos as $llave => $valor) {
                Ajuste::where('llave', $llave)->update(['valor' => $valor]);
            }

            DB::commit();

            // RESPUESTA ASÍNCRONA (AJAX)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Configuraciones actualizadas correctamente.'
                ]);
            }

            // Fallback por si entran sin JS
            return redirect()->back()->with('success', 'Configuraciones actualizadas correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Ocurrió un error al guardar: ' . $e->getMessage());
        }
    }
}