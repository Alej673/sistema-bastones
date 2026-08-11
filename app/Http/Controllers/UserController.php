<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Método para mostrar la tabla de usuarios registrados
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta incluyendo a los suspendidos
        $query = User::withTrashed();

        // 2. Filtro de Búsqueda de Texto (Nombre o Correo)
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        // 3. Filtro por Rol
        if ($request->filled('rol')) {
            $query->where('role', $request->rol);
        }

        // 4. Filtro por Estado (Activos vs Suspendidos)
        if ($request->filled('estado')) {
            if ($request->estado === 'baneados') {
                $query->onlyTrashed(); // Solo muestra los que tienen deleted_at
            } elseif ($request->estado === 'activos') {
                $query->whereNull('deleted_at'); // Solo muestra los que NO están baneados
            }
        }

        // 5. Ordenamiento (Fechas)
        if ($request->filled('orden') && $request->orden === 'antiguos') {
            $query->oldest();
        } else {
            $query->latest(); // Por defecto muestra los recién registrados primero
        }

        // 6. Ejecutamos la paginación. 
        // withQueryString() es VITAL: Evita que se borren los filtros al pasar a la página 2
        $usuarios = $query->paginate(10)->withQueryString();

        // AÑADIR ESTO ANTES DEL RETURN FINAL:
        if ($request->ajax()) {
            return view('admin.usuarios._tabla', compact('usuarios'));
        }

        return view('admin.usuarios.usuarios', compact('usuarios'));
    }

    // Método para cambiar el rol de un usuario
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:cliente,admin,super_admin'
        ]);

        $usuario = User::findOrFail($id);
        $usuario->role = $request->role;
        $usuario->save();

        return redirect()->back()->with('success', 'Rol de usuario actualizado correctamente.');
    }

    // NUEVO MÉTODO: Alternar Ban / Desban
    public function toggleBan($id)
    {
        // withTrashed() para poder encontrarlo incluso si ya está baneado
        $usuario = User::withTrashed()->findOrFail($id);

        // Evitar que te banees a ti mismo por accidente
        if ($usuario->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes suspender tu propia cuenta principal.');
        }

        // Lógica: Si está baneado, lo restauro. Si está activo, lo baneo.
        if ($usuario->trashed()) {
            $usuario->restore();
            $mensaje = 'Cuenta restaurada con éxito. El usuario ya puede iniciar sesión.';
        } else {
            $usuario->delete();
            $mensaje = 'Usuario suspendido. Ya no tiene acceso al sistema.';
        }

        return redirect()->back()->with('success', $mensaje);
    }
}
