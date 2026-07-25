<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteRequestController extends Controller
{
    /**
     * Guarda una nueva solicitud de cotización enviada por el cliente.
     */
    public function store(Request $request)
    {
        // 1. Validar estrictamente los datos del NUEVO formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20', 
            'cantidad' => 'required|integer|min:1', // Cambiado de cantidad_bastones
            'medida_cm' => 'required|in:45,50,55,60',
            'acabado' => 'required|in:Plata,Oro',
            'colores' => 'nullable|string', // Reemplaza a todos los arrays de colores
            'descripcion_diseno_especial' => 'nullable|string',
            'imagen_referencia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Si subieron imagen, la guardamos
        if ($request->hasFile('imagen_referencia')) {
            $validated['imagen_path'] = $request->file('imagen_referencia')->store('referencias_kardex', 'public');
        }

        // 2. Asociar la solicitud al cliente logueado
        $validated['user_id'] = Auth::id();
        
        // 3. Estado inicial
        $validated['estado'] = 'pendiente';

        // 4. Guardar en la base de datos
        // OJO: Asegúrate de que tu tabla 'quote_requests' tenga estas nuevas columnas 
        // (colores, cantidad, imagen_path) o deberás crear una migración para actualizarlas.
        QuoteRequest::create($validated);

        // 5. Redirigir con éxito
        return redirect()->route('cliente.dashboard')
                         ->with('success', '¡Tu solicitud ha sido enviada al taller para su revisión!');
    }

    public function descargarPDF($id)
    {
    // Buscamos la cotización
    $cotizacion = QuoteRequest::findOrFail($id);

    // Verificamos que el usuario logueado sea el dueño (o el administrador)
    if ($cotizacion->user_id !== Auth::id() /* aquí a futuro validas si es admin */) {
        abort(403, 'No tienes permiso para ver este documento.');
    }

    // Cargamos una vista de blade (que crearemos en el paso D) y le pasamos los datos
    $pdf = Pdf::loadView('pdf.cotizacion_formal', compact('cotizacion'));

    // Puedes usar download() para que se baje automático, o stream() para que se abra en el navegador
    return $pdf->stream('Cotizacion_Arte_Titi_Val_' . $cotizacion->id . '.pdf');
    }

    // En tu QuoteController o QuoteRequestController
    public function create()
    {
        // Aquí puedes cargar catálogos adicionales si los necesitas para los selects
        // Por ejemplo: $colores = Color::all(); 
        
        return view('cliente.cotizar_nuevo'); 
    }
}