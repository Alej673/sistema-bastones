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
        'cantidad_bastones',
        'medida_cm',
        'acabado',
        'cantidad_colores_cuerpo',
        'colores_cuerpo',
        'incluye_cortina_lana',
        'colores_cortina_lana',
        'incluye_cortina_fiesta',
        'colores_cortina_fiesta',
        'color_lazo_simple',
        'color_lazo_nombre',
        'cantidad_flores',
        'colores_flores',
        'descripcion_apliques',
        'descripcion_diseno_especial',
        'precio_referencial',
        'precio_final',
        'estado',
        'observaciones_taller',
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