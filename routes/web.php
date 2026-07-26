<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteRequestController;
use App\Models\CatalogItem;
use App\Http\Controllers\PublicCatalogController;
use App\Models\Review;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CatalogController;
use Illuminate\Http\Request;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\CotizacionController;

// ==========================================
// 1. LA CARA DEL SISTEMA (Landing Page)
// ==========================================

Route::get('/', function (Request $request) {
    // 1. Carrusel y Destacados...
    $carruselItems = CatalogItem::where('activo', true)->where('en_carrusel', true)->latest()->take(3)->get();
    $destacados = CatalogItem::where('activo', true)->where('es_destacado', true)->take(6)->get();
    $recientes = CatalogItem::where('activo', true)->latest()->take(6)->get();
    
    // 2. Comentarios (CON FILTRO Y PAGINACIÓN)
    $queryComentarios = Review::with('user')->where('activo', true)->latest();
    
    // Si el cliente hizo clic en un filtro (ej. 5 estrellas)
    if ($request->has('estrellas') && $request->estrellas != '') {
        $queryComentarios->where('calificacion', $request->estrellas);
    }

    // Paginamos de 6 en 6. 
    // fragment('comentarios') hace que al cambiar de página, el navegador baje automáticamente a esta sección.
    $comentarios = $queryComentarios->paginate(6)->withQueryString()->fragment('comentarios');

    return view('welcome', compact('carruselItems', 'destacados', 'recientes', 'comentarios'));
})->name('home');

// ==========================================
// 1.5. CATÁLOGO PÚBLICO (Vitrinas y Categorías)
// ==========================================
Route::prefix('catalogo')->name('catalogo.')->group(function () {
    // El Hub Principal (Vitrinas públicas) -> URL: /catalogo
    Route::get('/', [PublicCatalogController::class, 'index'])->name('index');

    // Vista dedicada por categoría -> URL: /catalogo/baston, /catalogo/lazo, etc.
    Route::get('/{categoria}', [PublicCatalogController::class, 'showCategory'])->name('categoria');
});

// ==========================================
// 2. RUTAS DEL CLIENTE EXTERNO
// ==========================================

// 2.0 Rutas Públicas (Cero fricción)
// Cualquiera puede generar el link de WhatsApp sin estar logueado
Route::post('/cotizacion/whatsapp', [App\Http\Controllers\CotizacionController::class, 'generarLinkWhatsapp'])->name('cotizacion.whatsapp');


// 2.1 Rutas que solo requieren estar logueado (sin exigir correo verificado)
Route::middleware(['auth'])->group(function () {
    Route::get('/mis-pedidos', [App\Http\Controllers\ClienteController::class, 'dashboard'])->name('cliente.dashboard');
    Route::post('/comentarios', [App\Http\Controllers\ReviewController::class, 'store'])->name('comentarios.store');
    Route::post('/comentarios/{id}/like', [App\Http\Controllers\ReviewController::class, 'toggleLike'])->name('comentarios.like');
});


// 2.2 Rutas que además exigen correo verificado (Sistema Interno)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mi-cuenta/cotizar-nuevo', [App\Http\Controllers\QuoteRequestController::class, 'create'])->name('cotizacion.crear');
    Route::post('/cotizar', [App\Http\Controllers\QuoteRequestController::class, 'store'])->name('cotizacion.store');
    Route::get('/cotizacion/{id}/pdf', [App\Http\Controllers\QuoteRequestController::class, 'descargarPDF'])->name('cotizacion.pdf');
    Route::get('/pedidos/{id}/pdf-nota', [App\Http\Controllers\CotizadorController::class, 'generarPdfNota'])->name('pedidos.pdf_nota');
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
    Route::get('/admin/solicitudes-web', [DashboardController::class, 'inboxSolicitudes'])->name('admin.solicitudes.inbox');

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
        Route::post('/pedidos/enviar-correo', 'enviarCorreo')->name('pedidos.enviar_correo');
    });

    // ------------------------------------------
    // Módulo de Ventas e Historial (KPIs)
    // ------------------------------------------
    Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
    Route::patch('/pedidos/{id}/estado', [VentasController::class, 'actualizarEstado'])->name('pedidos.estado');
    Route::get('/buscar-clientes-historial', [VentasController::class, 'buscarClientesAjax'])->name('clientes.buscar_ajax');
    Route::get('/pedidos/{id}/detalles', [VentasController::class, 'obtenerDetalles'])->name('pedidos.detalles');
    Route::post('/pedidos/{id}/vincular', [VentasController::class, 'vincularPedido'])->name('pedidos.vincular');

    // ------------------------------------------
    // Módulo de Gestión del Catálogo Público
    // ------------------------------------------
    Route::controller(CatalogController::class)->group(function () {
        Route::get('/admin/catalogo', 'index')->name('admin.catalogo.index');
        Route::post('/admin/catalogo', 'store')->name('admin.catalogo.store');
        Route::patch('/admin/catalogo/{id}/toggle', 'toggleActivo')->name('admin.catalogo.toggle');
        Route::delete('/admin/catalogo/{id}', 'destroy')->name('admin.catalogo.destroy');
        Route::put('/admin/catalogo/{id}', 'update')->name('admin.catalogo.update');
        Route::patch('/admin/catalogo/{id}/carrusel', 'toggleCarrusel')->name('admin.catalogo.carrusel');
        Route::patch('/admin/catalogo/{id}/destacado', 'toggleDestacado')->name('admin.catalogo.destacado');
    });

    Route::get('/admin/solicitudes-pendientes', [CotizadorController::class, 'buscarSolicitudesPendientes'])
    ->name('admin.solicitudes.pendientes');
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