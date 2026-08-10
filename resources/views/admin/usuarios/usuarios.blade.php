@extends('layouts.admin') {{-- Reemplaza 'layouts.app' por el nombre exacto de tu layout principal --}}

@section('contenido') {{-- Si tu layout usa @yield('contenido'), cambia 'content' por 'contenido' --}}
<div class="container-fluid px-4 py-3">

    <!-- Título de la Sección -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-users-gear me-2"></i> Gestión de Usuarios
        </h3>
        <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Control Técnico</span>
    </div>

    <!-- Alertas de éxito -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabla de Usuarios -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr class="text-secondary small text-uppercase fw-bold">
                            <th class="ps-4">ID</th>
                            <th>Usuario</th>
                            <th>Correo Electrónico</th>
                            <th>Fecha de Registro</th>
                            <th>Rol Asignado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">#{{ $usuario->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center me-3" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold text-dark">{{ $usuario->name }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $usuario->email }}</td>
                            <td class="text-muted">{{ $usuario->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($usuario->role === 'super_admin')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3 py-2">Super Admin</span>
                                @elseif($usuario->role === 'admin')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">Admin (Taller)</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3 py-2">Cliente</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('super.usuarios.rol', $usuario->id) }}" method="POST" class="d-inline-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    
                                    <select name="role" class="form-select form-select-sm shadow-none" style="width: 140px;" {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
                                        <option value="cliente" {{ $usuario->role == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                        <option value="admin" {{ $usuario->role == 'admin' ? 'selected' : '' }}>Admin (Taller)</option>
                                        <option value="super_admin" {{ $usuario->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    </select>

                                    @if($usuario->id !== auth()->id())
                                        <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">
                                            Guardar
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-users-slash fs-2 mb-2 d-block"></i>
                                No hay usuarios registrados en el sistema.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $usuarios->links() }}
    </div>

</div>
@endsection