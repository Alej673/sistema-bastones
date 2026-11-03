<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteRequestController extends Controller
{
    // AJUSTA: carpetas (dentro de storage/app/public) donde viven las imágenes del catálogo
    private const CARPETAS_CATALOGO = ['catalogo'];

    /**
     * Convierte la URL del catálogo en una ruta interna SEGURA, o null si no es válida.
     */
    private function resolverImagenCatalogo(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        $path = urldecode($path);
        $path = ltrim(preg_replace('/^\/?storage\//', '', $path), '/');

        // Sin traversal ni bytes nulos
        if (str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '\\')) {
            return null;
        }

        // Solo imágenes
        if (!preg_match('/\.(jpe?g|png|webp)$/i', $path)) {
            return null;
        }

        // Solo dentro de las carpetas del catálogo
        $enCarpetaPermitida = false;
        foreach (self::CARPETAS_CATALOGO as $carpeta) {
            if (str_starts_with($path, trim($carpeta, '/') . '/')) {
                $enCarpetaPermitida = true;
                break;
            }
        }
        if (!$enCarpetaPermitida) {
            return null;
        }

        // Y que el archivo exista realmente
        return Storage::disk('public')->exists($path) ? $path : null;
    }

    /**
     * Guarda una nueva solicitud de cotización enviada por el cliente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'                      => 'required|string|max:255',
            'telefono'                    => ['required', 'string', 'max:20', 'regex:/^[0-9+\s()\-]{7,20}$/'],
            'cantidad'                    => 'required|integer|min:1|max:10000',
            'medida_cm'                   => 'required|in:45,50,55,60,na',
            'acabado'                     => 'required|in:Plata,Dorado,na',
            'colores'                     => 'nullable|string|max:500',
            'descripcion_diseno_especial' => 'nullable|string|max:2000',
            'imagen_referencia'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagen_catalogo_url'         => 'nullable|string|max:500',
            'categoria'                   => ['nullable', 'string', 'max:50', 'regex:/^[\p{L}\s_\-]+$/u'],
        ]);

        // Si el cliente mandó una URL de catálogo, debe ser válida (y no se acepta si es inválida)
        $rutaCatalogo = null;
        if ($request->filled('imagen_catalogo_url') && !$request->hasFile('imagen_referencia')) {
            $rutaCatalogo = $this->resolverImagenCatalogo($validated['imagen_catalogo_url']);

            if (!$rutaCatalogo) {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'La imagen del catálogo no es válida.'], 422)
                    : back()->withErrors(['imagen_catalogo_url' => 'La imagen del catálogo no es válida.'])->withInput();
            }
        }

        // Quitamos del arreglo lo que NO son columnas y limpiamos HTML de los textos libres
        $datos = collect($validated)
            ->except(['imagen_referencia', 'imagen_catalogo_url'])
            ->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)
            ->all();

        $rutaSubida = null;

        try {
            if ($request->hasFile('imagen_referencia')) {
                $rutaSubida = $request->file('imagen_referencia')->store('referencias_kardex', 'public');
                $datos['imagen_path'] = $rutaSubida;
            } elseif ($rutaCatalogo) {
                $datos['imagen_path'] = $rutaCatalogo;
            }

            // Datos controlados por el servidor (nunca por el cliente)
            $datos['user_id'] = Auth::id();
            $datos['estado']  = 'pendiente';

            QuoteRequest::create($datos);

        } catch (\Throwable $e) {
            if ($rutaSubida) {
                Storage::disk('public')->delete($rutaSubida);
            }

            Log::error('Error al guardar solicitud de cotización: ' . $e->getMessage());

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'No pudimos enviar tu solicitud. Intenta de nuevo.'], 500)
                : back()->with('error', 'No pudimos enviar tu solicitud. Intenta de nuevo.')->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Tu solicitud ha sido enviada al taller!'
            ]);
        }

        return redirect()->route('cliente.dashboard')
                         ->with('success', '¡Tu solicitud ha sido enviada al taller para su revisión!');
    }

    public function descargarPDF($id)
    {
        $cotizacion = QuoteRequest::findOrFail($id);

        // Dueño o staff admin
        $esStaffAdmin = in_array(Auth::user()->role, ['admin', 'super_admin']);

        if ($cotizacion->user_id != Auth::id() && !$esStaffAdmin) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $pdf = Pdf::loadView('pdf.cotizacion_formal', compact('cotizacion'));

        return $pdf->stream('Cotizacion_Arte_Titi_Val_' . $cotizacion->id . '.pdf');
    }

    public function create()
    {
        return view('cliente.cotizar_nuevo');
    }
}