<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    // Lista blanca para permitir el registro masivo seguro
    protected $fillable = [
        'cliente_nombre',
        'cantidad_total_bastones',
        'total_precio_cliente',
        'estado',
    ];

    // Relación: Un pedido tiene muchos materiales calculados (receta)
    public function materiales()
    {
        return $this->hasMany(PedidoMaterial::class, 'pedido_id');
    }
}