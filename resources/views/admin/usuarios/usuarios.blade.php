@extends('layouts.admin') <!-- Cambia 'layouts.app' por el layout de tu panel admin -->

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Panel de Registrados (Control Técnico)</h2>
    </div>

    <!-- Alerta de éxito cuando cambias un rol -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Fecha de Registro</th>
                            <th>Gestión de Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                        <tr>
                            <td class="align-middle">{{ $usuario->id }}</td>
                            <td class="align-middle">{{ $usuario->name }}</td>
                            <td class="align-middle">{{ $usuario->email }}</td>
                            <td class="align-middle">{{ $usuario->created_at->format('d/m/Y') }}</td>
                            <td class="align-middle">
                                <!-- Formulario independiente para cada usuario -->
                                <form action="{{ route('super.usuarios.rol', $usuario->id) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width: 150px;">
                                        <option value="cliente" {{ $usuario->role == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                        <option value="admin" {{ $usuario->role == 'admin' ? 'selected' : '' }}>Admin (Taller)</option>
                                        <option value="super_admin" {{ $usuario->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No hay usuarios registrados aún.</td>
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