@extends('layouts.public')

@section('title', 'Contacto y Quejas - Arte Titi_Val')

@section('content')
<div class="container py-5" style="min-height: 75vh;">
    
    <!-- Encabezado de la página -->
    <div class="text-center mb-5 mt-3">
        <h2 class="fw-bold" style="color: var(--color-lila-fuerte); font-family: 'Playfair Display', serif;">
            Contacto y Buzón de Sugerencias
        </h2>
        <p class="text-muted" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
            Tu opinión es fundamental para nosotros. Si tienes algún inconveniente, sugerencia o queja, redáctala aquí y se enviará directamente a la administración.
        </p>
    </div>

    <div class="row g-5 align-items-stretch">
        
        <!-- 1. Columna Izquierda: Buzón de Quejas y Sugerencias -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 p-md-5 h-100" style="border-radius: 20px;">
                <h4 class="fw-bold mb-4" style="color: #4a148c;">Envíanos tu reporte</h4>
                
                <!-- Form con id para el hook de reCAPTCHA v3 -->
                <form id="contacto-form" action="{{ route('contacto.enviar') }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label for="nombre_queja" class="form-label fw-semibold text-secondary">Nombre Completo</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="nombre_queja" name="nombre_queja" placeholder="Ej. Ana Pérez" required>
                        </div>
                        
                        <!-- Nuevos campos de contacto -->
                        <div class="col-md-6">
                            <label for="correo_queja" class="form-label fw-semibold text-secondary">Correo Electrónico</label>
                            <input type="email" class="form-control form-control-lg bg-light border-0" id="correo_queja" name="correo_queja" placeholder="tu@correo.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono_queja" class="form-label fw-semibold text-secondary">Teléfono / WhatsApp</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="telefono_queja" name="telefono_queja" placeholder="Ej. 099 123 4567">
                        </div>

                        <div class="col-12">
                            <label for="asunto_queja" class="form-label fw-semibold text-secondary">Asunto / Motivo</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="asunto_queja" name="asunto_queja" placeholder="Ej. Sugerencia sobre diseño" required>
                        </div>
                        <div class="col-12">
                            <label for="mensaje_queja" class="form-label fw-semibold text-secondary">Detalle del mensaje</label>
                            <textarea class="form-control form-control-lg bg-light border-0" id="mensaje_queja" name="mensaje_queja" rows="5" placeholder="Describe tu situación de manera detallada..." required></textarea>
                        </div>

                        <!-- Token oculto de reCAPTCHA v3, se llena por JS antes del submit -->
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                        <div class="col-12 mt-4 pt-2">
                            <button type="submit" class="btn w-100 rounded-pill shadow" style="background-color: var(--color-lila-fuerte); color: white; font-weight: 600; padding: 12px;">
                                <i class="fa-solid fa-paper-plane me-2"></i> Enviar Mensaje
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Columna Derecha: Información Directa -->
        <div class="col-lg-5">
            <div class="p-4 p-md-5 d-flex flex-column justify-content-between h-100" style="background-color: #f6e8ff; border-radius: 20px; border: 1px dashed var(--color-lila-fuerte);">
                
                <div>
                    <h4 class="fw-bold mb-5" style="color: #4a148c;">Atención Rápida</h4>
                    
                    <ul class="list-unstyled" style="line-height: 2; font-size: 1.05rem;">
                        <li class="mb-4 d-flex align-items-start">
                            <div class="me-3 mt-1 text-center" style="width: 30px;">
                                <i class="fa-solid fa-location-dot fs-4" style="color: var(--color-lila-fuerte);"></i>
                            </div>
                            <div>
                                <strong class="text-dark">Ubicación del Taller:</strong><br>
                                <span class="text-muted">Quito, Ecuador</span>
                            </div>
                        </li>

                        <li class="mb-4 d-flex align-items-start">
                            <div class="me-3 mt-1 text-center" style="width: 30px;">
                                <i class="fa-solid fa-phone fs-4" style="color: var(--color-lila-fuerte);"></i>
                            </div>
                            <div>
                                <strong class="text-dark">Teléfono de Contacto:</strong><br>
                                <span class="text-muted">{{ $ajustesTaller['telefono_whatsapp'] ?? '099 985 6725' }}</span>
                            </div>
                        </li>

                        <li class="mb-4 d-flex align-items-start">
                            <div class="me-3 mt-1 text-center" style="width: 30px;">
                                <i class="fa-regular fa-clock fs-4" style="color: var(--color-lila-fuerte);"></i>
                            </div>
                            <div>
                                <strong class="text-dark">Horario de Atención:</strong><br>
                                <span class="text-muted">Lunes a Viernes: 9:00 AM - 6:00 PM</span><br>
                                <span class="text-muted">Sábados: 9:00 AM - 1:00 PM</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 pt-4 border-top" style="border-color: rgba(157, 92, 224, 0.2) !important;">
                    <p class="text-muted small text-center mb-3">¿Dudas sobre un pedido? Escríbenos directo:</p>
                    
                    @php
                        // 1. Extraemos el número directamente de las reglas de negocio (BD)
                        $telefonoTaller = \App\Models\Ajuste::where('llave', 'contacto_whatsapp')->value('valor') ?? '593999856725';
                        
                        // 2. Limpiamos espacios y formateamos el código de país
                        $numeroLimpio = preg_replace('/[^0-9]/', '', $telefonoTaller);
                        if (str_starts_with($numeroLimpio, '0')) {
                            $numeroLimpio = '593' . substr($numeroLimpio, 1);
                        }
                    @endphp
                    
                    <a href="https://wa.me/{{ $numeroLimpio }}" target="_blank" class="btn rounded-pill w-100 shadow-sm" style="background-color: #25D366; color: white; font-weight: bold; padding: 12px; transition: transform 0.2s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fa-brands fa-whatsapp fs-5 me-2 align-middle"></i> Chatear por WhatsApp
                    </a>
                </div>

            </div>
        </div>
        
    </div>
</div>
@endsection

@push('js')
<!-- Cargamos la API de Google reCAPTCHA v3 -->
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer></script>

<script>
    document.getElementById('contacto-form').addEventListener('submit', function (e) {
        // Frenamos el envío nativo: primero necesitamos el token de Google
        e.preventDefault();

        grecaptcha.ready(function () {
            grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'submit' })
                .then(function (token) {
                    // Inyectamos el token en el input oculto
                    document.getElementById('g-recaptcha-response').value = token;

                    // Recién ahora sí enviamos el form a Laravel
                    document.getElementById('contacto-form').submit();
                });
        });
    });
</script>
@endpush