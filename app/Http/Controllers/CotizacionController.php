<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CotizacionController extends Controller
{
    public function generarLinkWhatsapp(Request $request)
    {
        // 1. Validamos los datos que envía tu fetch() desde catalogo.js
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'colores' => 'nullable|string',
            'detalles' => 'nullable|string',
            // Ojo aquí: usamos el name "imagen_referencia" que configuraste en el HTML
            'imagen_referencia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        $urlImagen = null;

        // 2. Si el cliente subió una imagen (o si tu JS la comprimió y la envió)
        if ($request->hasFile('imagen_referencia')) {
            // La guardamos en una carpeta separada para no mezclarla con las del catálogo oficial
            $rutaImagen = $request->file('imagen_referencia')->store('referencias_clientes', 'public');
            
            // Generamos la URL completa (ej: http://tusitio.com/storage/referencias_clientes/foto.jpg)
            $urlImagen = asset('storage/' . $rutaImagen);
        }

        // 3. Devolvemos una respuesta JSON. Tu catalogo.js la atrapará.
        return response()->json([
            'success' => true,
            'url_imagen' => $urlImagen, // Puede ir llena o null si no subieron foto
            'datos' => [
                'nombre' => $request->nombre,
                'colores' => $request->colores,
                'detalles' => $request->detalles
            ]
        ]);
    }
}