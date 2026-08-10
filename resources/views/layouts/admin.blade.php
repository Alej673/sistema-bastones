<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('titulo') - Kardex Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/estilos_nav.css', 'resources/css/variables.css'])

    @stack('css')
</head>
<body class="theme-synthwave">

<div class="d-lg-none mobile-header p-3 d-flex justify-content-between align-items-center shadow-sm sticky-top">
    <h5 class="mb-0 fw-bold">Taller Admin</h5>
    <button class="btn btn-sm glass-btn-mobile" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
        <i class="fa-solid fa-bars"></i> Menú
    </button>
</div>

<div class="container-fluid">
    <div class="row">
        
        <nav class="col-lg-2 offcanvas-lg offcanvas-start sidebar-glass pt-3" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-lg-none border-bottom border-secondary mb-3">
                <h5 class="offcanvas-title fw-bold">Taller Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
            </div>
            
            <div class="offcanvas-body d-flex flex-column h-100">
                <h5 class="text-center mb-4 fw-bold d-none d-lg-block brand-glow">Kardex Bastoneras</h5>
                
                <div class="sidebar-links">
                    <a href="{{ route('inicio') }}" class="nav-link-glass {{ request()->routeIs('inicio') ? 'active' : '' }}">
                        <i class="fa-solid fa-house me-2"></i> Inicio
                    </a>
                    
                    <a href="{{ route('insumos.index') }}" class="nav-link-glass {{ request()->routeIs('insumos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked me-2"></i> Inventario
                    </a>
                    
                    <a href="{{ route('cotizador.create') }}" class="nav-link-glass {{ request()->routeIs('cotizador.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> Cotizador
                    </a>
                    
                    <a href="{{ route('ventas.index') }}" class="nav-link-glass">
                        <i class="fa-solid fa-chart-line me-2"></i> Ventas
                    </a>

                    <a href="{{ route('admin.catalogo.index') }}" class="nav-link-glass {{ request()->routeIs('admin.catalogo.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-images me-2"></i> Gestión de Catálogo
                    </a>

                    <!-- NUEVO: Buzón de Solicitudes Web -->
                    <a href="{{ route('admin.solicitudes.inbox') }}" class="nav-link-glass d-flex justify-content-between align-items-center {{ request()->routeIs('admin.solicitudes.*') ? 'active' : '' }}">
                        <span><i class="fa-solid fa-inbox me-2"></i> Buzón de Solicitudes</span>
                        {{-- @if($solicitudesWeb->count() > 0) 
                            <span class="badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                                {{ $solicitudesWeb->count() }} 
                            </span>
                        @endif  --}}
                    </a>

                    <!-- PANEL TÉCNICO: Solo visible para el Super Administrador -->
                    @if(auth()->user()->role === 'super_admin')
                        <hr class="border-secondary opacity-25 my-2">
                        <a href="{{ route('super.usuarios.index') }}" class="nav-link-glass {{ request()->routeIs('super.usuarios.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users-gear me-2"></i> Gestión de Usuarios
                        </a>
                    @endif
                </div>

                <div class="mt-auto pb-3">
                    <a href="{{ route('home') }}" target="_blank" class="nav-link-glass text-warning mb-2">
                        <i class="fa-solid fa-globe me-2"></i> Ver Sitio Web
                        <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 10px;"></i>
                    </a>

                    <hr class="border-secondary opacity-25"> 
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); this.closest('form').submit();" 
                           class="nav-link-glass text-danger fw-bold btn-logout">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Cerrar Sesión
                        </a>
                    </form>
                </div>
            </div>
        </nav>

        <main class="col-lg-10 p-4 main-content">
            @yield('contenido')
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('js')

</body>
</html>