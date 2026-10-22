<?php

namespace App\Support;

class MaterialesIgnorables
{
    // Líneas que NO son insumos físicos: mano de obra, servicios o
    // cotizaciones rápidas facturadas como bloque único.
    // Para agregar una nueva categoría, solo se añade aquí.
    public const FRAGMENTOS = [
        'aplique',
        'diseño',
        'diseno',
        '[coti-rápida]',
        '[coti-rapida]',
        '[sin-bastón]',
        '[sin-baston]',
        'sin bastón',
        'sin baston',
        'provista por cliente',
    ];

    public static function esIgnorable(?string $nombre): bool
    {
        // mb_strtolower es clave: strtolower no convierte bien "Á", "Ñ", etc.
        $nombre = mb_strtolower($nombre ?? '');

        foreach (self::FRAGMENTOS as $fragmento) {
            if (str_contains($nombre, $fragmento)) {
                return true;
            }
        }

        return false;
    }
}