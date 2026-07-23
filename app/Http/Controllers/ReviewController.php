<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contenido' => 'required|string|max:500',
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'contenido' => $request->contenido,
            'calificacion' => $request->calificacion,
            'activo' => true,
        ]);

        // Cargamos la relación del usuario para poder mostrar su nombre
        $review->load('user');

        // Si la petición se envía mediante fetch/AJAX, respondemos con JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comentario publicado con éxito.',
                'review' => $review
            ]);
        }

        // Fallback por si acaso falla JavaScript
        return back()->with('success', '¡Gracias por compartir tu experiencia con Arte Titi_Val!');
    }

    public function toggleLike(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $user_id = Auth::id();

        // Buscamos si ya existe el like de este usuario
        $like = $review->likes()->where('user_id', $user_id)->first();

        if ($like) {
            $like->delete(); // Se lo quitamos
            $isLiked = false;
        } else {
            $review->likes()->create(['user_id' => $user_id]); // Se lo ponemos
            $isLiked = true;
        }

        // Devolvemos la respuesta al JavaScript
        return response()->json([
            'success' => true,
            'isLiked' => $isLiked,
            'likesCount' => $review->likes()->count()
        ]);
    }
}