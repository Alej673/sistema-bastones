@extends('layouts.admin') 

@section('contenido') 

    @vite([
        'resources/css/variables.css',
        'resources/css/gestion_usuarios.css',
        'resources/js/gestion_usuarios.js',
    ])

    <div class="gu-page">

        <!-- Encabezado -->
        <div class="gu-header">
            <h3 class="gu-header__title">
                <span class="gu-header__icon"><i class="fa-solid fa-users-gear"></i></span>
                Gestión de Usuarios
            </h3>
            <span class="gu-badge-technical">Control Técnico</span>
        </div>

        <!-- Alerta de éxito -->
        @if (session('success'))
            <div class="gu-alert-success" role="alert">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Barra de búsqueda y filtros -->
        <div class="gu-filters-card">
            <form id="gu-filtros"
                  action="{{ route('super.usuarios.index') }}"
                  data-endpoint="{{ route('super.usuarios.index') }}"
                  method="GET"
                  class="row g-2 align-items-center">

                <div class="col-12 col-md-4 gu-field">
                    <i class="fa-solid fa-magnifying-glass gu-field__icon"></i>
                    <input type="text" name="buscar" class="gu-input"
                           placeholder="Buscar por nombre o correo..." value="{{ request('buscar') }}" autocomplete="off">
                </div>

                <div class="col-6 col-md-2">
                    <select name="rol" class="gu-select">
                        <option value="">Todos los roles</option>
                        <option value="cliente" {{ request('rol') == 'cliente' ? 'selected' : '' }}>Clientes</option>
                        <option value="admin" {{ request('rol') == 'admin' ? 'selected' : '' }}>Admin (Taller)</option>
                        <option value="super_admin" {{ request('rol') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="estado" class="gu-select">
                        <option value="">Cualquier estado</option>
                        <option value="activos" {{ request('estado') == 'activos' ? 'selected' : '' }}>Activos</option>
                        <option value="baneados" {{ request('estado') == 'baneados' ? 'selected' : '' }}>Suspendidos</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="orden" class="gu-select">
                        <option value="recientes" {{ request('orden') == 'recientes' ? 'selected' : '' }}>Más recientes</option>
                        <option value="antiguos" {{ request('orden') == 'antiguos' ? 'selected' : '' }}>Más antiguos</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 d-flex justify-content-end gap-2">
                    <button type="submit" class="gu-btn gu-btn--primary">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                    <button type="button" class="gu-btn gu-btn--clear" data-gu-clear-filters title="Limpiar filtros" hidden>
                        <i class="fa-solid fa-eraser"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Contenedor que el JS reemplaza en cada filtro (tabla + paginación + modales) -->
        <div id="gu-tabla-container">
            @include('admin.usuarios._tabla')
        </div>

    </div>

@endsection