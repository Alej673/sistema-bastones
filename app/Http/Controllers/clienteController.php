<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest; 
use Illuminate\Support\Facades\Auth; // Agregamos la fachada Auth

class ClienteController extends Controller
{
    public function dashboard()
    {
        // Recuperamos los pedidos/cotizaciones del usuario logueado
        $pedidos = QuoteRequest::where('user_id', Auth::id()) // Cambiamos auth()->id() por Auth::id()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cliente.dashboard', compact('pedidos'));
    }
}