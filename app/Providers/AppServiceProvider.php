<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\AjusteTaller;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Verificamos que la tabla exista para evitar fallos en instalaciones limpias
        if (Schema::hasTable('ajuste_tallers')) {
            View::composer('layouts.public', function ($view) {
                $ajustes = AjusteTaller::pluck('valor', 'clave')->toArray();
                $view->with('ajustesTaller', $ajustes);
            });
        }
    }
}
