<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'insumo_id',
        'tipo_movimiento',
        'cantidad',
        'detalle',
    ];

    // Relación: Un movimiento pertenece a un insumo específico
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}