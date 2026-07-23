<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLike extends Model
{
    use HasFactory;

    // ESTA ES LA LÍNEA MÁGICA QUE FALTA:
    protected $fillable = ['review_id', 'user_id'];
}