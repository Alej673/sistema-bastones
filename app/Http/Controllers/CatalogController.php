<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    // 1. Mostrar la lista de bastones en el panel de tu mamá
    public function index()
    {
        $items = CatalogItem::latest()->get();
        return view('catalogo.formulario', compact('items')); // Crearemos esta vista en el siguiente paso
    }

    // 2. Guardar un nuevo bastón con su foto
    public function store(Request $request)
    {
        // Validar los datos y la imagen (máximo 2MB)
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Guardar la foto en storage/app/public/catalogo
        $rutaImagen = $request->file('imagen')->store('catalogo', 'public');

        // Registrar en la base de datos
        CatalogItem::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen_path' => $rutaImagen,
            'activo' => true,
        ]);

        return back()->with('success', '¡Bastón agregado al catálogo con éxito!');
    }

    // 3. Activar/Desactivar un bastón (Ocultarlo de la landing page)
    public function toggleActivo($id)
    {
        $item = CatalogItem::findOrFail($id);
        $item->activo = !$item->activo;
        $item->save();

        return back()->with('success', 'Estado del bastón actualizado.');
    }

    // 4. Eliminar un bastón y su foto
    public function destroy($id)
    {
        $item = CatalogItem::findOrFail($id);
        
        // Borrar la foto física del servidor
        if (Storage::disk('public')->exists($item->imagen_path)) {
            Storage::disk('public')->delete($item->imagen_path);
        }
        
        // Borrar el registro de la BD
        $item->delete();

        return back()->with('success', 'Bastón eliminado del catálogo.');
    }
}