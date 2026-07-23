<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    public function index()
    {
        $bastones = CatalogItem::where('activo', true)
                               ->where('categoria', 'baston')
                               ->latest()
                               ->take(3)
                               ->get();

        $lazos = CatalogItem::where('activo', true)
                            ->where('categoria', 'lazo')
                            ->latest()
                            ->take(3)
                            ->get();

        // SEPARAMOS LOS APLIQUES
        $apliques = CatalogItem::where('activo', true)
                               ->where('categoria', 'aplique')
                               ->latest()
                               ->take(3)
                               ->get();

        // SEPARAMOS LAS MANUALIDADES
        $manualidades = CatalogItem::where('activo', true)
                                   ->where('categoria', 'manualidad')
                                   ->latest()
                                   ->take(3)
                                   ->get();

        return view('catalogo.index', compact('bastones', 'lazos', 'apliques', 'manualidades'));
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
            'baston' => 'Bastones para Bastoneras',
            'lazo' => 'Lazos y Cintas',
            'aplique' => 'Apliques y Flores',
            'manualidad' => 'Manualidades (Extras)'
        ];
        
        $tituloCategoria = $titulos[$categoria];

        return view('catalogo.categoria', compact('items', 'categoria', 'tituloCategoria'));
    }

}