<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InsumoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // 1. El resource crea automáticamente la ruta 'update' por nosotros
    Route::resource('insumos', InsumoController::class);
    
    // 2. Agregamos nuestra ruta personalizada usando la sintaxis correcta
    Route::patch('/insumos/{id}/ajustar', [InsumoController::class, 'ajustarStock'])->name('insumos.ajustar');
});


require __DIR__.'/auth.php';
