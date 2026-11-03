@extends('layouts.public')

@section('title', 'Política de Privacidad - Arte Titi_Val')

@section('content')
<div class="container py-5" style="min-height: 75vh;">
    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            
            <div class="text-center mb-5">
                <h1 class="fw-bold" style="color: var(--color-lila-fuerte); font-family: 'Playfair Display', serif;">
                    Política de Privacidad
                </h1>
                <p class="text-muted">Última actualización: {{ date('F Y') }}</p>
            </div>

            <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius: 15px; background-color: #ffffff;">
                <div class="legal-content" style="color: #4a4a4a; line-height: 1.8;">
                    
                <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">1. Información que recopilamos</h4>
                <p>En Arte Titi_Val recopilamos información personal de dos maneras: cuando nos la proporcionas directamente (al solicitar una cotización o usar el formulario de contacto) y al registrarte. Si decides crear tu cuenta utilizando tu cuenta de <strong>Google</strong>, recibiremos únicamente tu nombre, dirección de correo electrónico y fotografía de perfil pública para agilizar tu registro y acceso.</p>

                <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">2. Uso de tu información</h4>
                <p>La información recopilada se utiliza exclusivamente con fines administrativos y de servicio al cliente del taller. Esto incluye:</p>
                <ul style="color: #6c757d;">
                    <li>Gestionar y procesar tus solicitudes de cotización de bastones a medida.</li>
                    <li>Contactarte para afinar detalles técnicos, colores y tiempos de entrega.</li>
                    <li>Permitirte mantener un registro de tus modelos en la lista de "Favoritos".</li>
                    <li>Mejorar tu experiencia de navegación dentro de nuestro catálogo.</li>
                </ul>

                <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">3. Servicios de Terceros (Google)</h4>
                <p>Para proteger nuestra plataforma contra ataques informáticos y envíos de spam, utilizamos <strong>Google reCAPTCHA v3</strong>. El uso de esta herramienta está sujeto a la <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" style="color: var(--color-lila-fuerte);">Política de Privacidad</a> y los <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer" style="color: var(--color-lila-fuerte);">Términos de Servicio</a> de Google. Asimismo, el inicio de sesión con Google se utiliza exclusivamente para autenticación segura; no tenemos acceso a tus contraseñas externas ni a otros datos de tu cuenta personal.</p>

                <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">4. Protección y retención de datos</h4>
                <p>Tus contraseñas internas están encriptadas bajo estrictos estándares de seguridad. No vendemos, alquilamos ni compartimos tu información personal con terceros. Los datos correspondientes a pedidos concretados se conservan en nuestro sistema Kardex únicamente para el registro contable, estadístico e histórico interno de Arte Titi_Val.</p>

                <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">5. Tus derechos</h4>
                <p>Como usuario registrado, tienes derecho a solicitar la actualización, corrección o eliminación de tu cuenta y datos personales en cualquier momento. Si deseas que eliminemos tu información de nuestra base de datos, por favor envíanos una solicitud a través de la sección de <a href="{{ route('contacto') }}" style="color: var(--color-lila-fuerte); text-decoration: none; font-weight: bold;">Contacto</a> y la procesaremos de inmediato.</p>

                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection