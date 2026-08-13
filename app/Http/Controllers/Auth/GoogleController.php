<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirige al usuario a la pantalla de inicio de sesión de Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Recibe la respuesta de Google cuando el usuario acepta
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 1. Buscamos al usuario INCLUSO si está en la papelera (baneado)
            $user = User::withTrashed()->where('email', $googleUser->getEmail())->first();

            // 2. Si el usuario existe y está baneado, detenemos el proceso y enviamos el mensaje con WhatsApp
            if ($user && $user->trashed()) {
                $telefono = '593984922541'; 
                $textoWS = urlencode("Hola Arte Titi_Val, mi cuenta (" . $user->email . ") aparece suspendida en el portal y deseo consultar mi estado.");
                $linkWhatsapp = "https://wa.me/{$telefono}?text={$textoWS}";

                return redirect()->route('login')->withErrors([
                    'email' => "Tu cuenta ha sido suspendida por el administrador. Si crees que es un error, <a href='{$linkWhatsapp}' target='_blank' style='color: #25d366; font-weight: bold; text-decoration: underline;'>contáctanos por WhatsApp aquí</a>.",
                ]);
            }

            // 3. Si existe y NO está baneado, verificamos su correo si faltaba
            if ($user) {
                if (is_null($user->email_verified_at)) {
                    $user->markEmailAsVerified();
                }
            } else {
                // 4. Si no existe en absoluto, lo creamos nuevo y verificado
                $user = User::create([
                    'name'     => $googleUser->getName(),
                    'email'    => $googleUser->getEmail(),
                    'password' => Hash::make(\Illuminate\Support\Str::random(24)),
                    'role'     => 'cliente',
                ]);
                $user->markEmailAsVerified();
            }

            // 5. Autenticamos al usuario y lo enviamos al inicio
            Auth::login($user, true);

            return redirect()->intended('/');

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'No se pudo autenticar con Google. Inténtalo nuevamente.',
            ]);
        }
    }
}