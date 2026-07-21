<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    use HasFactory;

    // Los campos que tu mamá podrá llenar desde su formulario
    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_path',
        'activo',
    ];

    // Le decimos a Laravel que 'activo' siempre debe ser tratado como booleano (true/false)
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
}