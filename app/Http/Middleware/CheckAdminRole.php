<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificamos si el usuario está logueado y si su rol es 'admin' o 'super_admin'
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            return $next($request); // Déjalos pasar a ambos
        }

        // 2. Si no cumple, lo redirigimos a la página pública
        return redirect()->route('home')->with('error', 'No tienes permisos para acceder al área administrativa.');
    }
}