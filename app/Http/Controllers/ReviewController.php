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
            'review_padre_id' => 'nullable|exists:reviews,id',
        ]);

        // Solo admin/superadmin pueden crear una respuesta (con padre)
        if ($request->filled('review_padre_id')) {
            if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'superadmin'])) {
                abort(403, 'No autorizado para responder comentarios.');
            }
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'review_padre_id' => $request->review_padre_id,
            'contenido' => $request->contenido,
            'calificacion' => $request->calificacion,
            'activo' => true,
        ]);

        $review->load('user');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comentario publicado con éxito.',
                'review' => $review
            ]);
        }

        return back()->with('success', '¡Gracias por compartir tu experiencia con Arte Titi_Val!');
    }

    public function toggleLike(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $user_id = Auth::id();

        $like = $review->likes()->where('user_id', $user_id)->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            $review->likes()->create(['user_id' => $user_id]);
            $isLiked = true;
        }

        return response()->json([
            'success' => true,
            'isLiked' => $isLiked,
            'likesCount' => $review->likes()->count()
        ]);
    }

    // NUEVO: carga incremental de comentarios (AJAX)
    public function cargarMas(Request $request)
    {
        $query = Review::whereNull('review_padre_id')
            ->where('activo', true)
            ->with(['user', 'likes', 'respuestas.user']);

        if ($request->filled('estrellas')) {
            $query->where('calificacion', $request->estrellas);
        }

        $comentarios = $query->latest()
            ->paginate(6, ['*'], 'page', $request->input('page', 2));

        return response()->json([
            'html' => view('partials.review-card-list', compact('comentarios'))->render(),
            'has_more' => $comentarios->hasMorePages(),
            'next_page' => $comentarios->currentPage() + 1,
        ]);
    }
}