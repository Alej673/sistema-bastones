{{-- resources/views/super/usuarios/_tabla.blade.php --}}
{{-- Este partial se renderiza tanto en la carga inicial de la página como --}}
{{-- en cada respuesta AJAX del filtrado, para mantener una sola fuente de verdad. --}}

<div class="gu-table-card">
    <div class="table-responsive">
        <table class="gu-table">
            <thead>
                <tr>
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
                    <tr class="{{ $usuario->trashed() ? 'gu-row--suspended' : '' }}">
                        <td class="ps-4"><span class="gu-id">#{{ $usuario->id }}</span></td>
                        <td>
                            <div class="gu-user-cell">
                                <div class="gu-avatar {{ $usuario->trashed() ? 'gu-avatar--danger' : '' }}">
                                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                </div>
                                <span class="gu-user-name {{ $usuario->trashed() ? 'gu-user-name--suspended' : '' }}">
                                    {{ $usuario->name }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ $usuario->email }}
                            @if($usuario->trashed())
                                <span class="gu-pill gu-pill--suspended">
                                    <i class="fa-solid fa-ban"></i> Suspendido
                                </span>
                            @endif
                        </td>
                        <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="gu-pill gu-pill--{{ $usuario->role }}">
                                {{ ucfirst($usuario->role) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="gu-actions">
                                @if(!$usuario->trashed())
                                    <form action="{{ route('super.usuarios.rol', $usuario->id) }}" method="POST" class="d-inline-flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="gu-dropdown gu-dropdown--role">
                                            <select name="role" class="gu-role-select" {{ $usuario->id === auth()->id() ? 'disabled' : '' }}>
                                                <option value="cliente" {{ $usuario->role == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                                <option value="admin" {{ $usuario->role == 'admin' ? 'selected' : '' }}>Admin (Taller)</option>
                                                <option value="super_admin" {{ $usuario->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                            </select>
                                        </div>
                                        @if($usuario->id !== auth()->id())
                                            <button type="submit" class="gu-btn gu-btn--primary" style="width:auto;">Guardar</button>
                                        @endif
                                    </form>
                                @endif

                                @if($usuario->id !== auth()->id())
                                    <button type="button" class="gu-icon-btn {{ $usuario->trashed() ? 'gu-icon-btn--success' : 'gu-icon-btn--danger' }}"
                                            data-bs-toggle="modal" data-bs-target="#banModal{{ $usuario->id }}"
                                            title="{{ $usuario->trashed() ? 'Restaurar cuenta' : 'Suspender cuenta' }}">
                                        <i class="fa-solid {{ $usuario->trashed() ? 'fa-unlock' : 'fa-ban' }}"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="gu-empty">
                                <i class="fa-solid fa-users-slash"></i>
                                No hay usuarios registrados en el sistema.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="gu-pagination-wrap">
    {{ $usuarios->links() }}
</div>

{{-- ============================================================ --}}
{{-- MODALES DE CONFIRMACIÓN --}}
{{-- ============================================================ --}}
@foreach ($usuarios as $usuario)
    @if($usuario->id !== auth()->id())
        <div class="modal fade gu-modal text-start" id="banModal{{ $usuario->id }}" tabindex="-1"
             aria-labelledby="banModalLabel{{ $usuario->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    
                    <div class="modal-header {{ $usuario->trashed() ? 'modal-header--success' : 'modal-header--danger' }}">
                        <h5 class="modal-title fw-bold" id="banModalLabel{{ $usuario->id }}">
                            <i class="fa-solid {{ $usuario->trashed() ? 'fa-unlock' : 'fa-triangle-exclamation' }}"></i>
                            Confirmar Acción
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    
                    <div class="modal-body text-center">
                        <p class="fs-5 mb-2">
                            ¿Estás seguro de que deseas <strong>{{ $usuario->trashed() ? 'restaurar' : 'suspender' }}</strong> a este usuario?
                        </p>
                        <p class="text-muted fw-semibold mb-0 fs-5">{{ $usuario->email }}</p>
                        
                        @if(!$usuario->trashed())
                            <div class="alert alert-warning mt-4 mb-0 small text-start border-warning-subtle rounded-3 shadow-sm">
                                <i class="fa-solid fa-circle-info me-1"></i> El usuario no podrá iniciar sesión en el sistema hasta que lo restaures.
                            </div>
                        @endif
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="gu-btn gu-btn--cancel" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        
                        <form action="{{ route('super.usuarios.ban', $usuario->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="gu-btn {{ $usuario->trashed() ? 'gu-btn--success' : 'gu-btn--danger' }}">
                                Sí, {{ $usuario->trashed() ? 'Restaurar Cuenta' : 'Suspender Cuenta' }}
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    @endif
@endforeach