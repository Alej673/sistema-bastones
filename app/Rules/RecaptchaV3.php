<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class RecaptchaV3 implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. BYPASS EN LOCAL: Si estamos en Laragon, aprobamos automáticamente
        if (app()->environment('local')) {
            return;
        }

        // 2. VALIDACIÓN EN VPS: Si estamos en producción, consultamos a Google
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        $result = $response->json();

        // Validamos que la petición sea exitosa y que el score sea mayor a 0.5 (es humano)
        if (!isset($result['success']) || !$result['success'] || $result['score'] < 0.5) {
            $fail('La verificación de seguridad falló o el sistema detectó actividad inusual.');
        }
    }
}