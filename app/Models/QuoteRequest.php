<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre', 
        'telefono', 
        'cantidad', 
        'medida_cm', 
        'acabado', 
        'colores', 
        'descripcion_diseno_especial', 
        'imagen_path', 
        'estado'
    ];

    // Convertir automáticamente los campos JSON a Arrays de PHP
    protected function casts(): array
    {
        return [
            'colores_cuerpo' => 'array',
            'colores_cortina_lana' => 'array',
            'colores_cortina_fiesta' => 'array',
            'colores_flores' => 'array',
            'incluye_cortina_lana' => 'boolean',
            'incluye_cortina_fiesta' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}