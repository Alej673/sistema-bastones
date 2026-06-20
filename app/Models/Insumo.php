<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- NUEVO

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'categoria', // ¡No olvides agregar esta línea!
        'unidad_medida',
        'costo_unitario',
        'stock_actual',
        'stock_minimo',
    ];
}