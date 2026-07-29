<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest; 
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function dashboard()
    {

        $pedidos = QuoteRequest::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('cliente.dashboard', compact('pedidos'));
    }
}