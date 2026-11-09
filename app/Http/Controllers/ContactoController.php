<?php

namespace App\Http\Controllers;

use App\Mail\MensajeContactoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactoController extends Controller
{
    public function enviar(Request $request)
    {
        $datos = $request->validate([
            'nombre'  => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'mensaje' => 'required|string|max:2000',
        ]);

        // Destinatario: el correo del taller (ajusta si usas otro, ej. config('mail.contacto'))
        Mail::to(config('mail.from.address'))->send(new MensajeContactoMail($datos));

        return back()->with('success', 'Tu mensaje fue enviado correctamente. Te responderemos pronto.');
    }
}