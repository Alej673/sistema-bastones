<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'estado',
        'precio_final',
        'categoria' // <- ¡AQUÍ ESTÁ LA MAGIA!
    ];

    // Ya no necesitas el array de casts para los colores viejos, 
    // pero si tienes booleanos o fechas a futuro, este es el lugar.
    protected function casts(): array
    {
        return [
            // Puedes dejarlo vacío por ahora si ya no usas esos JSON
        ];
    }

    // Recuerda que ya tenías esta relación definida para que el ->with('user') funcione
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}