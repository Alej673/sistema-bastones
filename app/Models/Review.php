<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'review_padre_id',
        'contenido',
        'calificacion',
        'activo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(ReviewLike::class); // ajusta al nombre real de tu modelo de likes
    }

    public function respuestas()
    {
        return $this->hasMany(Review::class, 'review_padre_id')->with('user');
    }

    public function padre()
    {
        return $this->belongsTo(Review::class, 'review_padre_id');
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