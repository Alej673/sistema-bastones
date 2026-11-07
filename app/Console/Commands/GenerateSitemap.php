<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Genera el archivo sitemap.xml para los buscadores';

    public function handle()
    {
        Sitemap::create()
            // 1. Página principal
            ->add(Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))

            // 2. Catálogo público
            ->add(Url::create('/catalogo')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY))

            // Si tienes rutas de productos dinámicas o términos, puedes agregarlas aquí

            ->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap XML generado con éxito en public/sitemap.xml');
    }
}