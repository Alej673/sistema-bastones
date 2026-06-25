<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizadorController;

// Si alguien entra a la raíz, lo mandamos directo al inventario
Route::get('/', function () {
    return redirect()->route('insumos.index');
});

Route::get('/dashboard', function () {
    // En lugar de cargar la vista vacía del dashboard, redireccionamos al Kardex
    return redirect()->route('insumos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // 1. El resource crea automáticamente la ruta 'update' por nosotros
    Route::resource('insumos', InsumoController::class);
    
    // 2. Agregamos nuestra ruta personalizada usando la sintaxis correcta
    Route::patch('/insumos/{id}/ajustar', [InsumoController::class, 'ajustarStock'])->name('insumos.ajustar');

    // Rutas del Cotizador
    Route::get('/cotizador', [\App\Http\Controllers\CotizadorController::class, 'create'])->name('cotizador.create');
    Route::post('/cotizador', [\App\Http\Controllers\CotizadorController::class, 'store'])->name('cotizador.store');
    // Ruta para la búsqueda en tiempo real de lanas mediante Select2
    Route::get('/buscar-lanas', [App\Http\Controllers\CotizadorController::class, 'buscarLanas'])->name('lanas.buscar');
    // Ruta para la búsqueda asíncrona de Cortinas de Fiesta
    Route::get('/buscar-cortinas', [App\Http\Controllers\CotizadorController::class, 'buscarCortinas'])->name('cortinas.buscar');
    // Ruta para la búsqueda asíncrona de Cintas (Abarca Satín, Garza y Gross)
    Route::get('/buscar-cintas', [App\Http\Controllers\CotizadorController::class, 'buscarCintas'])->name('cintas.buscar');
    // Ruta para recibir el secuestro de datos por AJAX
    Route::post('/cotizaciones/guardar', [CotizadorController::class, 'guardar'])->name('cotizaciones.guardar');
});


require __DIR__.'/auth.php';
