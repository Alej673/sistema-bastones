<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Método para mostrar la tabla de usuarios registrados
    public function index()
    {
        // Traemos todos los usuarios paginados de 10 en 10
        $usuarios = User::latest()->paginate(10);
        
        // Retornaremos una vista (que crearemos en el siguiente paso)
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
}