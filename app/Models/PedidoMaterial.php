<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoMaterial extends Model
{
    use HasFactory;

    // Forzamos el nombre correcto de la tabla en español
    protected $table = 'pedido_materiales';

    protected $fillable = [
        'pedido_id',
        'insumo_id',
        'cantidad_estimada',
    ];

    // Relación inversa: Este registro pertenece a un solo pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Relación: Este registro está amarrado a un insumo físico del inventario (Lana, Base, etc)
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}