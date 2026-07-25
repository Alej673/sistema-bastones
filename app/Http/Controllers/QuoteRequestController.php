<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteRequestController extends Controller
{
    /**
     * Guarda una nueva solicitud de cotización enviada por el cliente.
     */
    public function store(Request $request)
    {
        // 1. Validar estrictamente los datos que vienen del formulario del catálogo
        $validated = $request->validate([
            'telefono' => 'required|string|max:20', // Añadido aquí
            'cantidad_bastones' => 'required|integer|min:1',
            'medida_cm' => 'required|in:45,50,55,60',
            'acabado' => 'required|in:Plata,Oro',
            
            'cantidad_colores_cuerpo' => 'required|integer|min:1|max:3',
            'colores_cuerpo' => 'required|array',
            
            'incluye_cortina_lana' => 'boolean',
            'colores_cortina_lana' => 'nullable|array',
            
            'incluye_cortina_fiesta' => 'boolean',
            'colores_cortina_fiesta' => 'nullable|array',
            
            'color_lazo_simple' => 'nullable|string',
            'color_lazo_nombre' => 'nullable|string',
            
            'cantidad_flores' => 'required|integer|min:0|max:6',
            'colores_flores' => 'nullable|array',
            
            'descripcion_apliques' => 'nullable|string',
            'descripcion_diseno_especial' => 'nullable|string',
            
            // El JavaScript enviará este valor ya calculado con los precios fantasma
            'precio_referencial' => 'required|numeric|min:0', 
        ]);

        // 2. Asociar la solicitud al cliente que está logueado
        $validated['user_id'] = Auth::id();
        
        // 3. Asegurar que toda nueva solicitud nazca en estado 'pendiente'
        $validated['estado'] = 'pendiente';

        // 4. Guardar en la base de datos
        QuoteRequest::create($validated);

        // 5. Redirigir al portal del cliente con un mensaje de éxito
        return redirect()->route('cliente.dashboard')
                         ->with('success', '¡Tu solicitud de cotización ha sido enviada al taller para su revisión!');
    }
}