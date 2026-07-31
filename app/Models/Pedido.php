<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_nombre',
        'cantidad_total_bastones',
        'total_precio_cliente',
        'estado',
        // Asegúrate de tener aquí también la llave foránea si la usas para crear, por ejemplo 'quote_request_id'
    ];

    // Relación: Un pedido tiene muchos materiales calculados (receta)
    public function materiales()
    {
        return $this->hasMany(PedidoMaterial::class, 'pedido_id');
    }

    // =======================================================
    // NUEVA RELACIÓN AGREGADA
    // =======================================================
    public function quoteRequest()
    {
        // Ajusta 'quote_request_id' al nombre real de la columna en tu tabla pedidos
        // Si tu columna se llama de otra forma (ej. 'solicitud_id'), cámbialo aquí.
        return $this->belongsTo(QuoteRequest::class, 'quote_request_id'); 
    }
}