<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificamos si el usuario está logueado y si su rol es 'admin'
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // Déjalo pasar
        }

        // 2. Si no es admin (es un cliente externo), lo redirigimos a la página pública
        return redirect()->route('home')->with('error', 'No tienes permisos para acceder al área administrativa.');
    }
}