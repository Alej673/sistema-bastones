<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

// Controladores
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactoController; // <-- nuevo, mueve la lógica del envío aquí
use App\Http\Controllers\Auth\GoogleController;
use App\Mail\MensajeContactoMail;

// Modelos
use App\Models\CatalogItem;
use App\Models\Review;

// ==========================================
// 1. LA CARA DEL SISTEMA (Landing Page)
// ==========================================

Route::get('/', function (Request $request) {
    $carruselItems = CatalogItem::where('activo', true)->where('en_carrusel', true)->latest()->take(3)->get();
    $destacados = CatalogItem::where('activo', true)->where('es_destacado', true)->take(6)->get();
    $recientes = CatalogItem::where('activo', true)->latest()->take(6)->get();

    $queryComentarios = Review::with('user')->where('activo', true)->latest();

    if ($request->filled('estrellas')) {
        $queryComentarios->where('calificacion', $request->estrellas);
    }

    $comentarios = $queryComentarios->paginate(6)->withQueryString()->fragment('comentarios');

    $top5Populares = CatalogItem::where('activo', true)
        ->where('contador_consultas', '>', 0)
        ->orderBy('contador_consultas', 'desc')
        ->take(5)
        ->get();

    return view('welcome', compact('carruselItems', 'destacados', 'recientes', 'comentarios', 'top5Populares'));
})->name('home');

// ==========================================
// 1.5. CATÁLOGO PÚBLICO
// ==========================================
Route::prefix('catalogo')->name('catalogo.')->group(function () {
    Route::get('/', [PublicCatalogController::class, 'index'])->name('index');
    Route::get('/{categoria}', [PublicCatalogController::class, 'showCategory'])->name('categoria');
});

// Páginas de contenido
Route::view('/nosotros', 'nosotros')->name('nosotros');
Route::view('/contacto', 'contacto')->name('contacto');
Route::view('/politica-privacidad', 'legal.privacidad')->name('legal.privacidad');
Route::view('/terminos-y-condiciones', 'legal.terminos')->name('legal.terminos');

// Contacto: la lógica pesada vive en el controller, la ruta queda limpia
Route::post('/contacto/enviar', [ContactoController::class, 'enviar'])->name('contacto.enviar');

Route::post('/productos/{id}/consultar', [CatalogController::class, 'registrarConsulta'])->name('productos.registrar_consulta');

// Autenticación con Google (Socialite)
Route::prefix('auth/google')->name('google.')->group(function () {
    Route::get('/', [GoogleController::class, 'redirectToGoogle'])->name('login');
    Route::get('/callback', [GoogleController::class, 'handleGoogleCallback'])->name('callback');
});

// ==========================================
// 2. RUTAS DEL CLIENTE EXTERNO
// ==========================================

// 2.0 Públicas (sin login) — generar link de WhatsApp
Route::post('/cotizacion/whatsapp', [CotizacionController::class, 'generarLinkWhatsapp'])->name('cotizacion.whatsapp');

// 2.1 Solo requieren estar logueado (sin exigir correo verificado)
Route::middleware('auth')->group(function () {
    Route::get('/mis-pedidos', [ClienteController::class, 'dashboard'])->name('cliente.dashboard');
    Route::post('/comentarios', [ReviewController::class, 'store'])->name('comentarios.store');
    Route::post('/comentarios/{id}/like', [ReviewController::class, 'toggleLike'])->name('comentarios.like');
    Route::post('/favoritos/toggle', [ClienteController::class, 'toggleFavorito'])->name('favoritos.toggle');
    Route::get('/mis-favoritos', [ClienteController::class, 'misFavoritos'])->name('cliente.favoritos');

    // Perfil (Breeze) — también solo requiere auth, lo unifico aquí
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 2.2 Además exigen correo verificado (Sistema Interno del cliente)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mi-cuenta/cotizar-nuevo', [QuoteRequestController::class, 'create'])->name('cotizacion.crear');
    Route::post('/cotizar', [QuoteRequestController::class, 'store'])->name('cotizacion.store');
    Route::get('/cotizacion/{id}/pdf', [QuoteRequestController::class, 'descargarPDF'])->name('cotizacion.pdf');
    Route::get('/pedidos/{id}/pdf-nota', [CotizadorController::class, 'generarPdfNota'])->name('pedidos.pdf_nota');
});

// ==========================================
// 3. RUTAS PRIVADAS (Taller y Administración)
// Acceso: admin Y super_admin (super_admin hereda permisos de admin
// dentro del propio middleware/CheckAdminRole)
// ==========================================
Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inicio', [DashboardController::class, 'index'])->name('inicio');
    Route::post('/dashboard/alerta/descartar/{id}', [DashboardController::class, 'descartarAlerta'])->name('dashboard.alerta.descartar');
    Route::post('/dashboard/stock/arreglar/{id}', [DashboardController::class, 'arreglarStock'])->name('dashboard.stock.arreglar');
    Route::get('/admin/solicitudes-web', [DashboardController::class, 'inboxSolicitudes'])->name('admin.solicitudes.inbox');

    // Kardex (Inventarios)
    Route::resource('insumos', InsumoController::class);
    Route::patch('/insumos/{id}/ajustar', [InsumoController::class, 'ajustarStock'])->name('insumos.ajustar');

    // Cotizador Automático
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
    Route::get('/admin/solicitudes-pendientes', [CotizadorController::class, 'buscarSolicitudesPendientes'])
        ->name('admin.solicitudes.pendientes');

    // Ventas e Historial (KPIs)
    Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
    Route::patch('/pedidos/{id}/estado', [VentasController::class, 'actualizarEstado'])->name('pedidos.estado');
    Route::get('/buscar-clientes-historial', [VentasController::class, 'buscarClientesAjax'])->name('clientes.buscar_ajax');
    Route::get('/pedidos/{id}/detalles', [VentasController::class, 'obtenerDetalles'])->name('pedidos.detalles');
    Route::post('/pedidos/{id}/vincular', [VentasController::class, 'vincularPedido'])->name('pedidos.vincular');
    // Reporte Mensual PDF
    Route::get('/ventas/reporte-mensual', [VentasController::class, 'generarReporteMensual'])->name('ventas.reporte_mensual');

    // Gestión del Catálogo Público
    Route::controller(CatalogController::class)->prefix('admin/catalogo')->name('admin.catalogo.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('/{id}/toggle', 'toggleActivo')->name('toggle');
        Route::patch('/{id}/carrusel', 'toggleCarrusel')->name('carrusel');
        Route::patch('/{id}/destacado', 'toggleDestacado')->name('destacado');
    });
});

// ==========================================
// 4. RUTAS DE SUPER ADMINISTRADOR (Control Total)
// ==========================================
Route::middleware(['auth', 'verified', 'super_admin'])->group(function () {
    Route::get('/super-admin/usuarios', [UserController::class, 'index'])->name('super.usuarios.index');
    Route::patch('/super-admin/usuarios/{id}/rol', [UserController::class, 'updateRole'])->name('super.usuarios.rol');
    Route::patch('/super-admin/usuarios/{id}/ban', [UserController::class, 'toggleBan'])->name('super.usuarios.ban');
});

require __DIR__.'/auth.php';