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
                    <p>En Arte Titi_Val recopilamos información personal únicamente cuando tú nos la proporcionas voluntariamente. Esto ocurre al registrarte en nuestro sistema, al guardar modelos en tu lista de "Favoritos", al solicitar una cotización formal o al comunicarte mediante nuestro buzón de contacto. Los datos pueden incluir tu nombre, correo electrónico y número de teléfono.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">2. Uso de la información</h4>
                    <p>La información recopilada se utiliza exclusivamente con fines administrativos y de servicio al cliente. Esto incluye:</p>
                    <ul style="color: #6c757d;">
                        <li>Gestionar tus solicitudes de cotización y pedidos de bastones.</li>
                        <li>Permitirte mantener un registro de tus modelos favoritos.</li>
                        <li>Responder a tus consultas, quejas o sugerencias.</li>
                        <li>Mejorar la experiencia de navegación en nuestro catálogo.</li>
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">3. Protección de tus datos</h4>
                    <p>Tus datos personales, incluyendo tu contraseña de acceso al sistema, están encriptados y protegidos mediante los estándares de seguridad de nuestra plataforma. No vendemos, alquilamos ni compartimos tu información personal con terceros ajenos a la administración del taller.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">4. Uso de Cookies</h4>
                    <p>Nuestro sistema utiliza cookies técnicas estrictamente necesarias para mantener tu sesión activa cuando inicias sesión y para proteger los formularios contra ataques informáticos y envíos automatizados de spam (reCAPTCHA).</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">5. Tus derechos</h4>
                    <p>Como usuario registrado, tienes derecho a solicitar la actualización, corrección o eliminación permanente de tu cuenta y tus datos personales de nuestro sistema. Para ello, puedes enviarnos una solicitud a través de la sección de <a href="{{ route('contacto') }}" style="color: var(--color-lila-fuerte); text-decoration: none; font-weight: bold;">Contacto</a>.</p>

                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection