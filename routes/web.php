<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteRequestController;
use App\Models\CatalogItem;

// ==========================================
// 1. LA CARA DEL SISTEMA (Landing Page)
// ==========================================

Route::get('/', function () {
    // 1. Carrusel (ya lo tienes)
    $carruselItems = CatalogItem::where('activo', true)
                                ->where('en_carrusel', true)
                                ->get();

    // 2. NUEVO: Solo los productos destacados (máximo 6)
    $destacados = CatalogItem::where('activo', true)
                             ->where('es_destacado', true)
                             ->take(6) 
                             ->get();

    return view('welcome', compact('carruselItems', 'destacados'));
})->name('home');
// ==========================================
// 2. RUTAS DEL CLIENTE EXTERNO
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Aquí construiremos más adelante una tabla bonita para que el cliente vea sus pedidos
    Route::get('/mis-pedidos', function () {
        return "Próximamente: Historial de pedidos del cliente.";
    })->name('cliente.dashboard');

    // Recibe los datos del formulario de cotización
    Route::post('/cotizar', [QuoteRequestController::class, 'store'])->name('cotizacion.store');
});


// ==========================================
// 3. RUTAS PRIVADAS (Taller y Administración)
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

    // ------------------------------------------
    // Módulo de Gestión del Catálogo Público
    // ------------------------------------------
    Route::controller(App\Http\Controllers\CatalogController::class)->group(function () {
        Route::get('/admin/catalogo', 'index')->name('admin.catalogo.index');
        Route::post('/admin/catalogo', 'store')->name('admin.catalogo.store');
        Route::patch('/admin/catalogo/{id}/toggle', 'toggleActivo')->name('admin.catalogo.toggle');
        Route::delete('/admin/catalogo/{id}', 'destroy')->name('admin.catalogo.destroy');
        Route::put('/admin/catalogo/{id}', 'update')->name('admin.catalogo.update');
        Route::patch('/admin/catalogo/{id}/carrusel', 'toggleCarrusel')->name('admin.catalogo.carrusel');
        Route::patch('/admin/catalogo/{id}/destacado', 'toggleDestacado')->name('admin.catalogo.destacado');
    });
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