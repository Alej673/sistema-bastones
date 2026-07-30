<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    // Límites de saturación para no llenar de más la landing page
    const LIMITE_CARRUSEL = 3;
    const LIMITE_DESTACADOS = 6;

    // 1. Mostrar la lista de bastones con Filtros y Paginación
    public function index(Request $request)
    {
        // Iniciamos el constructor de consultas
        $query = CatalogItem::query();

        // Filtro 1: Búsqueda por texto (Título)
        if ($request->filled('buscar')) {
            $query->where('titulo', 'LIKE', '%' . $request->buscar . '%');
        }

        // Filtro 2: Por Categoría
        if ($request->filled('categoria') && $request->categoria !== 'todas') {
            $query->where('categoria', $request->categoria);
        }

        // Filtro 3: Estados Especiales (Carrusel, Destacado, Ocultos)
        if ($request->filled('estado')) {
            if ($request->estado === 'carrusel') {
                $query->where('en_carrusel', true);
            } elseif ($request->estado === 'destacado') {
                $query->where('es_destacado', true);
            } elseif ($request->estado === 'oculto') {
                $query->where('activo', false);
            }
        }

        // Filtro 4 (NUEVO): Por Fecha
        if ($request->filled('fecha')) {
            if ($request->fecha === 'ultimos_7_dias') {
                // Filtramos registros de los últimos 7 días y ordenamos por el más reciente
                $query->where('created_at', '>=', now()->subDays(7))->latest();
            } elseif ($request->fecha === 'antiguos') {
                // Ordenamos del más viejo al más nuevo
                $query->oldest();
            } else {
                // Opción 'recientes' (por defecto)
                $query->latest();
            }
        } else {
            // Si no hay filtro de fecha en la URL, el comportamiento por defecto es mostrar lo más nuevo
            $query->latest();
        }

        // Ejecutamos la consulta paginando de 9 en 9.
        // OJO: quitamos el latest() hardcodeado de aquí porque el Filtro 4 ya se encarga del orden.
        $items = $query->paginate(9)->withQueryString();

        // Totales GLOBALES (no afectados por filtros/paginación) para los
        // contadores del blade y la validación visual de límites.
        $totalEnCarrusel = CatalogItem::where('en_carrusel', true)->count();
        $totalEnDestacados = CatalogItem::where('es_destacado', true)->count();

        // Los límites viven SOLO aquí (constantes de la clase). Se mandan
        // a la vista para que el blade no tenga su propia copia duplicada.
        $LIMITE_CARRUSEL = self::LIMITE_CARRUSEL;
        $LIMITE_DESTACADOS = self::LIMITE_DESTACADOS;

        return view('catalogo.formulario', compact(
            'items',
            'totalEnCarrusel',
            'totalEnDestacados',
            'LIMITE_CARRUSEL',
            'LIMITE_DESTACADOS'
        ));
    }

    // 2. Guardar un nuevo bastón con su foto
    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categoria' => 'required|string',
            'medida_cm' => 'nullable|string',
            'nivel_diseno' => 'nullable|string',
            'nivel_accesorios' => 'nullable|string',
        ]);

        // Guardar la foto
        $rutaImagen = $request->file('imagen')->store('catalogo', 'public');

        // Registrar en la base de datos
        CatalogItem::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen_path' => $rutaImagen,
            'activo' => true,
            'categoria' => $request->categoria,
            // Magia aquí: Si viene nulo, le asignamos 'na' para que la BD no colapse
            'medida_cm' => $request->medida_cm ?? 'na',
            'nivel_diseno' => $request->nivel_diseno ?? 'na',
            'nivel_accesorios' => $request->nivel_accesorios ?? 'na',
        ]);

        return back()->with('success', '¡Artículo agregado al catálogo con éxito!');
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

    // 5. Actualizar un artículo existente
    public function update(Request $request, $id)
    {
        // 1. Validar los datos
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categoria' => 'required|string',
            'medida_cm' => 'nullable|string',
            'nivel_diseno' => 'nullable|string',
            'nivel_accesorios' => 'nullable|string',
        ]);

        // 2. Buscar el ítem en la base de datos
        $item = CatalogItem::findOrFail($id);
        
        // 3. Asignar los valores (con protección contra nulos)
        $item->titulo = $request->titulo;
        $item->descripcion = $request->descripcion;
        $item->categoria = $request->categoria;
        $item->medida_cm = $request->medida_cm ?? 'na';
        $item->nivel_diseno = $request->nivel_diseno ?? 'na';
        $item->nivel_accesorios = $request->nivel_accesorios ?? 'na';

        // 4. Si el usuario subió una imagen nueva...
        if ($request->hasFile('imagen')) {
            if ($item->imagen_path && Storage::disk('public')->exists($item->imagen_path)) {
                Storage::disk('public')->delete($item->imagen_path);
            }
            $item->imagen_path = $request->file('imagen')->store('catalogo', 'public');
        }

        // 5. Guardar cambios
        $item->save();

        return back()->with('success', '¡Artículo actualizado correctamente!');
    }

    // 6. Activar/Desactivar del Carrusel (Hero)
    public function toggleCarrusel($id)
    {
        $item = CatalogItem::findOrFail($id);

        // Solo validamos el límite cuando se está ACTIVANDO (false -> true).
        // Desactivar siempre está permitido.
        if (!$item->en_carrusel) {
            $totalActual = CatalogItem::where('en_carrusel', true)->count();

            if ($totalActual >= self::LIMITE_CARRUSEL) {
                return back()->with(
                    'error',
                    'Ya tienes ' . self::LIMITE_CARRUSEL . ' diseños en el carrusel principal. Quita uno antes de añadir otro.'
                );
            }
        }

        $item->en_carrusel = !$item->en_carrusel;
        $item->save();

        return back()->with('success', 'Visibilidad en el carrusel actualizada.');
    }

    // 7. Activar/Desactivar de Productos Destacados
    public function toggleDestacado($id)
    {
        $item = CatalogItem::findOrFail($id);

        // Solo validamos el límite cuando se está ACTIVANDO (false -> true).
        // Desactivar siempre está permitido.
        if (!$item->es_destacado) {
            $totalActual = CatalogItem::where('es_destacado', true)->count();

            if ($totalActual >= self::LIMITE_DESTACADOS) {
                return back()->with(
                    'error',
                    'Ya tienes ' . self::LIMITE_DESTACADOS . ' diseños marcados como destacados. Quita uno antes de añadir otro.'
                );
            }
        }

        $item->es_destacado = !$item->es_destacado;
        $item->save();

        return back()->with('success', 'Estado de producto destacado actualizado.');
    }
}