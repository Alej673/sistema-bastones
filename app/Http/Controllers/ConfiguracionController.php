<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ajuste;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el panel de configuraciones.
     */
    public function index()
    {
        $ajustes = Ajuste::all();
        $grupos = $ajustes->groupBy('grupo');
        
        // Extraemos el mapa plano para armar las reglas de negocio
        $mapaAjustes = $ajustes->pluck('valor', 'llave')->toArray();

        // DICCIONARIO 100% DINÁMICO (Fin del hardcoding)
        $traductores = [
            'pf_lana'        => ['divisor' => (float)($mapaAjustes['lana_gramos_madeja'] ?? 90), 'unidad' => 'g'],
            'pf_cinta_garza' => ['divisor' => (float)($mapaAjustes['insumo_rollo_garza_m'] ?? 45.72), 'unidad' => 'm'],
            'pf_cinta_satin' => ['divisor' => (float)($mapaAjustes['insumo_rollo_satin_m'] ?? 18.28), 'unidad' => 'm'],
            'pf_cinta_gross' => ['divisor' => (float)($mapaAjustes['insumo_rollo_gross_m'] ?? 22.86), 'unidad' => 'm'],
            'pf_elastico'    => ['divisor' => (float)($mapaAjustes['insumo_paquete_elastico_m'] ?? 10), 'unidad' => 'm'],
            'pf_cinchos'     => ['divisor' => (float)($mapaAjustes['insumo_paquete_cinchos_u'] ?? 100), 'unidad' => 'u'],
        ];

        // Enviamos los grupos y el traductor a la vista
        return view('Configuracion.configuraciones', compact('grupos', 'traductores'));
    }

    /**
     * Guarda los cambios enviados desde el formulario.
     */
    public function update(Request $request)
    {
        // 1. Recibimos todos los datos del formulario
        $datos = $request->except(['_token', '_method']);

        // =========================================================
        // 2. VALIDACIÓN (vacíos, negativos, tipo de dato)
        // =========================================================
        // Necesitamos saber el grupo y la descripción de cada llave para:
        //  a) decidir si la regla es numérica (precios/margenes/recetas) o de texto (contacto)
        //  b) mostrar el nombre real del ajuste en el mensaje de error, no la llave cruda
        $ajustesInfo = Ajuste::whereIn('llave', array_keys($datos))
            ->get(['llave', 'grupo', 'descripcion'])
            ->keyBy('llave');

        $reglas = [];
        $atributos = [];

        foreach (array_keys($datos) as $llave) {
            $info = $ajustesInfo->get($llave);
            $grupo = $info->grupo ?? null;

            $atributos[$llave] = $info->descripcion ?? $llave;

        if ($grupo && str_contains($grupo, 'contacto')) {
            // WhatsApp es obligatorio, pero las redes sociales (Facebook, Instagram, TikTok) son opcionales (nullable)
            $reglas[$llave] = str_contains($llave, 'whatsapp')
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
        } elseif ($llave === 'taller_estado') {
            $reglas[$llave] = 'required|in:abierto,cerrado,0,1';
        } else {
            // Todo lo demás son valores numéricos del cotizador e inventario
            $reglas[$llave] = 'required|numeric|min:0';
        }
        }

        $mensajes = [
            'required' => 'El campo ":attribute" no puede quedar vacío.',
            'numeric'  => 'El campo ":attribute" debe ser un valor numérico.',
            'min'      => 'El campo ":attribute" no puede ser negativo.',
            'string'   => 'El campo ":attribute" no es válido.',
        ];

        $validador = validator($datos, $reglas, $mensajes, $atributos);

        if ($validador->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validador->errors()->first(),
                    'errors'  => $validador->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validador)->withInput();
        }

        $datos = $validador->validated();
        // =========================================================

        // =========================================================
        // 3. EL TRADUCTOR INVERSO (Comercial -> Costo Interno)
        // =========================================================
        // Leemos los divisores directamente de lo que el usuario acaba de enviar
        $divisores = [
            'pf_lana'        => (float)($datos['lana_gramos_madeja'] ?? 90),
            'pf_cinta_garza' => (float)($datos['insumo_rollo_garza_m'] ?? 45.72),
            'pf_cinta_satin' => (float)($datos['insumo_rollo_satin_m'] ?? 18.28),
            'pf_cinta_gross' => (float)($datos['insumo_rollo_gross_m'] ?? 22.86),
            'pf_elastico'    => (float)($datos['insumo_paquete_elastico_m'] ?? 10),
            'pf_cinchos'     => (float)($datos['insumo_paquete_cinchos_u'] ?? 100),
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

            // 4. Guardamos los datos (ahora sí, validados y con la matemática interna correcta)
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

            // 1. Registro silencioso del error real para tu auditoría
            Log::error('Error en la operación: ' . $e->getMessage());

            // 2. Mensaje dinámico (Técnico en local, Genérico en producción)
            $mensajeSeguro = config('app.debug') 
                ? $e->getMessage() 
                : 'Ocurrió un error interno al procesar la solicitud. Por favor, intenta de nuevo.';

            // 3. Despacho para peticiones AJAX / Axios / jQuery
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $mensajeSeguro], 500);
            }

            // 4. Despacho para peticiones web tradicionales (Blade)
            return redirect()->back()->with('error', $mensajeSeguro);
        }
    }
}