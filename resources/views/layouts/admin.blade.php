<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') - Taller Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        
        /* Estilos generales del menú */
        .sidebar { background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 20px; display: block; border-radius: 5px; margin: 0 10px 5px 10px;}
        .sidebar a:hover, .sidebar a.active { color: white; background-color: #343a40; }
        
        /* FIJAR EL MENÚ SÓLO EN PANTALLAS GRANDES */
        @media (min-width: 992px) {
            .sidebar { 
                height: 100vh; 
                position: sticky; 
                top: 0; 
                overflow-y: auto; 
            }
        }
        
        .card { border-radius: 10px; border: none; }
        .card-header { font-weight: bold; background-color: white; border-bottom: 1px solid #eee; border-radius: 10px 10px 0 0 !important; }
        .card { border-radius: 10px; border: none; }
        .card-header { font-weight: bold; background-color: white; border-bottom: 1px solid #eee; border-radius: 10px 10px 0 0 !important; }
    </style>
</head>
<body>

<div class="d-lg-none bg-dark text-white p-3 d-flex justify-content-between align-items-center shadow-sm sticky-top" style="z-index: 1040;">
    <h5 class="mb-0">Taller Admin</h5>
    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
        ☰ Menú
    </button>
</div>

<div class="container-fluid">
    <div class="row">
        
        <nav class="col-lg-2 offcanvas-lg offcanvas-start bg-dark sidebar pt-3" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-lg-none border-bottom mb-3">
                <h5 class="offcanvas-title text-white">Taller Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
            </div>
            
            <div class="offcanvas-body d-flex flex-column h-100">
                <h5 class="text-center mb-4 text-light d-none d-lg-block">Taller Admin</h5>
                
                <a href="#">🏠 Inicio</a>
                <a href="{{ route('insumos.index') }}" class="{{ request()->routeIs('insumos.*') ? 'active text-white bg-primary' : '' }}">📦 Inventario de Insumos</a>
                <a href="{{ route('cotizador.create') }}" class="{{ request()->routeIs('cotizador.*') ? 'active text-white bg-primary' : '' }}">📝 Nueva Cotización</a>
                <a href="#">🗂️ Historial de Ventas</a>

                <div class="mt-auto">
                    <hr class="border-secondary"> <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); this.closest('form').submit();" 
                           class="text-danger fw-bold">
                            🚪 Cerrar Sesión
                        </a>
                    </form>
                </div>
            </div>
        </nav>

        <main class="col-lg-10 p-4">
            @yield('contenido')
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>