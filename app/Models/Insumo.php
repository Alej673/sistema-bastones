<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'categoria', 
        'unidad_medida',
        'precio_comercial',
        'costo_unitario',
        'stock_actual',
        'stock_minimo',
    ];
}