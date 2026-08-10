<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificamos si el usuario tiene el control técnico total
        if (Auth::check() && Auth::user()->role === 'super_admin') {
            return $next($request);
        }

        // 2. Si es tu mamá (admin) o un cliente, los devolvemos al inicio o dashboard
        return redirect()->route('home')->with('error', 'Acceso denegado. Se requieren permisos técnicos.');
    }
}
