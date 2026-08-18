<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // <-- 1. Importación necesaria agregada aquí

class ClienteController extends Controller
{
    public function dashboard()
    {
        $pedidos = QuoteRequest::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('cliente.dashboard', compact('pedidos'));
    }

    public function toggleFavorito(Request $request)
    {
        $request->validate([
            'modelo_id' => 'required|integer'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ahora el editor sabe que $user es tu modelo User y reconocerá favoritos()
        $user->favoritos()->toggle($request->modelo_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Favoritos actualizados'
        ]);
    }

    public function misFavoritos()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Buscamos los favoritos del usuario con paginación por si guarda muchos
        $favoritos = $user->favoritos()->paginate(9);

        return view('catalogo.favoritos', compact('favoritos'));
    }
}