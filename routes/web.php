<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteRequestController;

// ==========================================
// RUTAS PÚBLICAS Y DE CLIENTES EXTERNOS
// ==========================================
Route::get('/', function () {
    return view('welcome'); // Futura Landing Page del catálogo
})->name('home');

// Ruta genérica para clientes (a donde irán después de loguearse)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mi-cuenta', function () {
        return '
            <div style="font-family: sans-serif; padding: 40px; text-align: center; background: #1b0f28; color: white; min-height: 100vh;">
                <h1>¡Bienvenido a Arte Titi_Val!</h1>
                <p>Aquí verás tus cotizaciones pronto.</p>
                <form method="POST" action="'.route('logout').'" style="margin-top: 20px;">
                    '.csrf_field().'
                    <button type="submit" style="background: #a855f7; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        ';
    })->name('cliente.dashboard');

    // NUEVO: La ruta que recibe los datos del formulario de la landing page
    Route::post('/cotizar', [QuoteRequestController::class, 'store'])->name('cotizacion.store');
});


// ==========================================
// RUTAS PRIVADAS (Taller y Administración)
// ==========================================
Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    // ------------------------------------------
    // Dashboard (página de aterrizaje post-login del admin)
    // ------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inicio', [DashboardController::class, 'index'])->name('inicio');

    Route::post('/dashboard/alerta/descartar/{id}', [DashboardController::class, 'descartarAlerta']);
    Route::post('/dashboard/stock/arreglar/{id}', [DashboardController::class, 'arreglarStock']);

    // ------------------------------------------
    // Módulo de Inventarios (Kardex)
    // ------------------------------------------
    Route::resource('insumos', InsumoController::class);
    Route::patch('/insumos/{id}/ajustar', [InsumoController::class, 'ajustarStock'])->name('insumos.ajustar');

    // ------------------------------------------
    // Módulo de Cotizador Automático
    // ------------------------------------------
    Route::controller(CotizadorController::class)->group(function () {
        Route::get('/cotizador', 'create')->name('cotizador.create');
        Route::post('/cotizador', 'store')->name('cotizador.store');
        Route::post('/cotizaciones/guardar', 'guardar')->name('cotizaciones.guardar');

        Route::get('/buscar-lanas', 'buscarLanas')->name('lanas.buscar');
        Route::get('/buscar-cortinas', 'buscarCortinas')->name('cortinas.buscar');
        Route::get('/buscar-cintas', 'buscarCintas')->name('cintas.buscar');

        Route::get('/pedidos/{id}/pdf-receta', 'generarPdfReceta')->name('pedidos.pdf_receta');
        Route::get('/pedidos/{id}/pdf-nota', 'generarPdfNota')->name('pedidos.pdf_nota');
        Route::post('/pedidos/enviar-correo', 'enviarCorreo')->name('pedidos.enviar_correo');
    });

    // ------------------------------------------
    // Módulo de Ventas e Historial (KPIs)
    // ------------------------------------------
    Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
    Route::patch('/pedidos/{id}/estado', [VentasController::class, 'actualizarEstado'])->name('pedidos.estado');
    Route::get('/buscar-clientes-historial', [VentasController::class, 'buscarClientesAjax'])->name('clientes.buscar_ajax');
    Route::get('/pedidos/{id}/detalles', [VentasController::class, 'obtenerDetalles'])->name('pedidos.detalles');
});


// ==========================================
// Rutas de Breeze (Perfil) — solo requieren estar logueado
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';