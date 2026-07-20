<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DashboardController;

// ==========================================
// REDIRECCIONES PRINCIPALES
// ==========================================
// Si alguien entra a la raíz o al dashboard vacío, va directo al inventario operativo
Route::redirect('/', '/insumos');
Route::redirect('/dashboard', '/insumos')->middleware(['auth', 'verified'])->name('dashboard');


// ==========================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ==========================================
Route::middleware('auth')->group(function () {

    // ------------------------------------------
    // Módulo de Perfil (Breeze)
    // ------------------------------------------
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // ------------------------------------------
    // Módulo de Inventarios (Kardex)
    // ------------------------------------------
    Route::resource('insumos', InsumoController::class);
    Route::patch('/insumos/{id}/ajustar', [InsumoController::class, 'ajustarStock'])->name('insumos.ajustar');

    // ------------------------------------------
    // Módulo de Cotizador Automático
    // ------------------------------------------
    Route::controller(CotizadorController::class)->group(function () {
        // Vistas y guardado general
        Route::get('/cotizador', 'create')->name('cotizador.create');
        Route::post('/cotizador', 'store')->name('cotizador.store');
        Route::post('/cotizaciones/guardar', 'guardar')->name('cotizaciones.guardar');

        // Endpoints de Búsqueda Asíncrona (AJAX / Select2)
        Route::get('/buscar-lanas', 'buscarLanas')->name('lanas.buscar');
        Route::get('/buscar-cortinas', 'buscarCortinas')->name('cortinas.buscar');
        Route::get('/buscar-cintas', 'buscarCintas')->name('cintas.buscar');

        // Generación de Documentos (PDF On-the-Fly) y Notificaciones
        Route::get('/pedidos/{id}/pdf-receta', 'generarPdfReceta')->name('pedidos.pdf_receta');
        Route::get('/pedidos/{id}/pdf-nota', 'generarPdfNota')->name('pedidos.pdf_nota');
        Route::post('/pedidos/enviar-correo', 'enviarCorreo')->name('pedidos.enviar_correo');
    });

    // ------------------------------------------
    // Módulo de Ventas e Historial (KPIs)
    // ------------------------------------------
    Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
    // Módulo de Ventas e Historial (KPIs)
    Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
    // NUEVA RUTA PARA AJAX:
    Route::patch('/pedidos/{id}/estado', [VentasController::class, 'actualizarEstado'])->name('pedidos.estado');
    // Módulo de Ventas e Historial (KPIs)
    Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
    Route::get('/buscar-clientes-historial', [VentasController::class, 'buscarClientesAjax'])->name('clientes.buscar_ajax');
    // Ruta para consultar los materiales de un pedido por AJAX
    Route::get('/pedidos/{id}/detalles', [VentasController::class, 'obtenerDetalles'])->name('pedidos.detalles');
    // Ruta principal del Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('inicio');
    // Rutas para las acciones del Dashboard
    Route::post('/dashboard/alerta/descartar/{id}', [App\Http\Controllers\DashboardController::class, 'descartarAlerta']);
    Route::post('/dashboard/stock/arreglar/{id}', [App\Http\Controllers\DashboardController::class, 'arreglarStock']);
});

require __DIR__.'/auth.php';