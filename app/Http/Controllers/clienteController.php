<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest; 
use Illuminate\Support\Facades\Auth; // Agregamos la fachada Auth

class ClienteController extends Controller
{
    public function dashboard()
    {

        $pedidos = QuoteRequest::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('cliente.dashboard', compact('pedidos'));
    }
}