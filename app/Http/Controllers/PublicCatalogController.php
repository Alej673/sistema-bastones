<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use Illuminate\Http\Request;
use App\Models\Review;

class PublicCatalogController extends Controller
{
    public function index()
    {
        $bastones = CatalogItem::where('activo', true)
                               ->where('categoria', 'baston')
                               ->latest()
                               ->take(6)
                               ->get();

        $lazos = CatalogItem::where('activo', true)
                            ->where('categoria', 'lazo')
                            ->latest()
                            ->take(6)
                            ->get();

        // SEPARAMOS LOS APLIQUES
        $apliques = CatalogItem::where('activo', true)
                               ->where('categoria', 'aplique')
                               ->latest()
                               ->take(6)
                               ->get();

        // SEPARAMOS LAS MANUALIDADES
        $manualidades = CatalogItem::where('activo', true)
                                   ->where('categoria', 'manualidad')
                                   ->latest()
                                   ->take(6)
                                   ->get();

        // ===== COMENTARIOS (solo el primer lote, 6) =====
        $comentariosQuery = Review::whereNull('review_padre_id')
            ->where('activo', true)
            ->with(['user', 'likes', 'respuestas.user']);

        if (request()->filled('estrellas')) {
            $comentariosQuery->where('calificacion', request('estrellas'));
        }

        $comentarios = $comentariosQuery->latest()->paginate(6);

        return view('catalogo.index', compact(
            'bastones', 'lazos', 'apliques', 'manualidades', 'comentarios'
        ));
    }

    public function showCategory(Request $request, $categoria)
    {
        $categoriasValidas = ['baston', 'lazo', 'aplique', 'manualidad'];
        if (!in_array($categoria, $categoriasValidas)) {
            abort(404);
        }

        // Iniciamos la consulta base
        $query = CatalogItem::where('activo', true)
                            ->where('categoria', $categoria);

        // --- INICIO LÓGICA DE FILTROS ---
        if ($request->filled('medida')) {
            $query->where('medida_cm', $request->medida);
        }
        if ($request->filled('diseno')) {
            $query->where('nivel_diseno', $request->diseno);
        }
        if ($request->filled('accesorios')) {
            $query->where('nivel_accesorios', $request->accesorios);
        }
        // --- FIN LÓGICA DE FILTROS ---

        // IMPORTANTE: usamos withQueryString() para que al cambiar de página (paginación 1, 2, 3...) no se borren los filtros aplicados
        $items = $query->latest()->paginate(9)->withQueryString();

        $titulos = [
            'baston' => 'Bastones',
            'lazo' => 'Lazos y Cintas',
            'aplique' => 'Apliques y Flores',
            'manualidad' => 'Manualidades'
        ];
        
        $tituloCategoria = $titulos[$categoria];

        return view('catalogo.categoria', compact('items', 'categoria', 'tituloCategoria'));
    }

}