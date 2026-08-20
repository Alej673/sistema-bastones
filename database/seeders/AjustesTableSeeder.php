<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AjustesTableSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = Carbon::now();

        $ajustes = [
            // FINANZAS
            ['llave' => 'margen_ganancia', 'valor' => '0.60', 'grupo' => 'finanzas', 'descripcion' => 'Margen de ganancia sobre el costo de materiales (60%)'],

            // PRECIOS FANTASMA
            ['llave' => 'pf_lana', 'valor' => '0.0127', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio fantasma lana ($1.15 / 90g)'],
            ['llave' => 'pf_cinta_garza', 'valor' => '0.11', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio fantasma cinta garza ($5.00 / 45.72m)'],
            ['llave' => 'pf_cinta_satin', 'valor' => '0.16', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio fantasma cinta satín ($3.00 / 18.28m)'],
            ['llave' => 'pf_cinta_gross', 'valor' => '0.15', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio fantasma cinta gross ($3.50 / 22.86m)'],
            ['llave' => 'pf_elastico', 'valor' => '0.09', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio fantasma elástico ($0.90 / 10m)'],
            ['llave' => 'pf_cinchos', 'valor' => '0.02', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio fantasma cinchos ($2.00 / 100u)'],
            ['llave' => 'pf_cortina_menor', 'valor' => '1.00', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio cortina fiesta (Menudeo)'],
            ['llave' => 'pf_cortina_mayor', 'valor' => '0.50', 'grupo' => 'precios_fantasma', 'descripcion' => 'Precio cortina fiesta (Mayoreo)'],

            // RECETAS Y CONSUMOS
            ['llave' => 'receta_cinchos', 'valor' => '3', 'grupo' => 'recetas', 'descripcion' => 'Cinchos por bastón'],
            ['llave' => 'receta_elastico', 'valor' => '0.40', 'grupo' => 'recetas', 'descripcion' => 'Metros de elástico por bastón'],
            ['llave' => 'lana_consumo_grande', 'valor' => '150', 'grupo' => 'recetas', 'descripcion' => 'Gramos de lana para tamaño grande (55cm - 60cm)'],
            ['llave' => 'lana_consumo_normal', 'valor' => '135', 'grupo' => 'recetas', 'descripcion' => 'Gramos de lana para tamaño normal'],
            ['llave' => 'lana_gramos_madeja', 'valor' => '90', 'grupo' => 'recetas', 'descripcion' => 'Gramos por madeja estándar'],
            ['llave' => 'cortina_lana_gramos', 'valor' => '30', 'grupo' => 'recetas', 'descripcion' => 'Gramos de lana por cada cortina'],

            // UMBRALES Y MAYOREO
            ['llave' => 'base_umbral_mayoreo', 'valor' => '12', 'grupo' => 'mayoreo', 'descripcion' => 'Cantidad mínima para mayoreo de bases'],
            ['llave' => 'cortina_fiesta_umbral', 'valor' => '12', 'grupo' => 'mayoreo', 'descripcion' => 'Umbral de mayoreo para cortinas de fiesta'],

            // BASES (Precios Fantasma)
            ['llave' => 'base_dorado_normal', 'valor' => '5.50', 'grupo' => 'bases', 'descripcion' => 'Precio base dorado normal'],
            ['llave' => 'base_dorado_mayoreo', 'valor' => '5.00', 'grupo' => 'bases', 'descripcion' => 'Precio base dorado mayoreo'],
            ['llave' => 'base_plata_normal', 'valor' => '5.00', 'grupo' => 'bases', 'descripcion' => 'Precio base plata normal'],
            ['llave' => 'base_plata_mayoreo', 'valor' => '4.50', 'grupo' => 'bases', 'descripcion' => 'Precio base plata mayoreo'],

            // DECORACION Y MANO DE OBRA
            ['llave' => 'deco_lazo_simple_m', 'valor' => '1.5', 'grupo' => 'decoracion', 'descripcion' => 'Metros por lazo simple'],
            ['llave' => 'deco_flor_m', 'valor' => '1.0', 'grupo' => 'decoracion', 'descripcion' => 'Metros por flor'],
            ['llave' => 'deco_lazo_nombre_m', 'valor' => '1.0', 'grupo' => 'decoracion', 'descripcion' => 'Metros por lazo con nombre'],
            ['llave' => 'deco_lazo_nombre_mo', 'valor' => '0.70', 'grupo' => 'decoracion', 'descripcion' => 'Costo mano de obra bordado de nombre'],
            ['llave' => 'deco_apliques_precio', 'valor' => '0.50', 'grupo' => 'decoracion', 'descripcion' => 'Precio unitario apliques extra'],
            
            // REDES Y CONTACTO
            ['llave' => 'contacto_whatsapp', 'valor' => '+593900000000', 'grupo' => 'contacto', 'descripcion' => 'Número de WhatsApp de la administradora'],
            ['llave' => 'red_tiktok', 'valor' => 'https://www.tiktok.com/@titi_val_0905?lang=es-419', 'grupo' => 'contacto', 'descripcion' => 'Enlace del perfil de TikTok'],
            ['llave' => 'red_facebook', 'valor' => '', 'grupo' => 'contacto', 'descripcion' => 'Enlace de la página de Facebook'],
            ['llave' => 'red_instagram', 'valor' => '', 'grupo' => 'contacto', 'descripcion' => 'Enlace del perfil de Instagram'],
            
            // SISTEMA
            ['llave' => 'taller_estado', 'valor' => 'abierto', 'grupo' => 'sistema', 'descripcion' => 'Estado del taller (abierto/cerrado para pedidos)'],
        ];

        // Añadir timestamps a todos los registros
        foreach ($ajustes as &$ajuste) {
            $ajuste['created_at'] = $ahora;
            $ajuste['updated_at'] = $ahora;
        }

        // Vaciar la tabla primero (por si ejecutas el seeder varias veces) y luego insertar
        DB::table('ajustes')->truncate();
        DB::table('ajustes')->insert($ajustes);
    }
}