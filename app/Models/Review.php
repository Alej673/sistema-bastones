<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'contenido', 'calificacion', 'activo'];

    // Relación: Una reseña pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un comentario tiene muchos likes
    public function likes()
    {
        return $this->hasMany(ReviewLike::class);
    }

    // Función rápida para saber si el usuario conectado ya le dio like
    public function isLikedByAuthUser()
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->likes()->where('user_id', Auth::id())->exists();
    }
}