<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\AjusteTaller;

// 1. Nuevas importaciones para personalizar los correos de Breeze
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Verificamos que la tabla exista para evitar fallos en instalaciones limpias
        if (Schema::hasTable('ajuste_tallers')) {
            View::composer('layouts.public', function ($view) {
                $ajustes = AjusteTaller::pluck('valor', 'clave')->toArray();
                $view->with('ajustesTaller', $ajustes);
            });
        }

        // ==========================================
        // PERSONALIZACIÓN DE CORREOS DE BREEZE
        // ==========================================

        // 2. Correo de Verificación de Cuenta
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirma tu cuenta en Arte Titi_Val')
                ->greeting('¡Hola, ' . $notifiable->name . '!')
                ->line('Nos alegra tenerte aquí. Por favor, haz clic en el botón de abajo para verificar tu correo electrónico y empezar a cotizar tus productos a medida.')
                ->action('Confirmar mi correo', $url)
                ->line('Si no creaste esta cuenta, simplemente ignora este mensaje.')
                ->salutation("Saludos,\nEl equipo de Arte Titi_Val");
        });

        // 3. Correo de Restablecimiento de Contraseña
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Recuperación de contraseña - Arte Titi_Val')
                ->greeting('¡Hola!')
                ->line('Estás recibiendo este correo porque solicitaste un restablecimiento de contraseña para tu cuenta.')
                ->action('Restablecer Contraseña', $url)
                ->line('Este enlace de restablecimiento de contraseña expirará en 60 minutos.')
                ->line('Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.')
                ->salutation("Saludos,\nEl equipo de Arte Titi_Val");
        });
    }
}