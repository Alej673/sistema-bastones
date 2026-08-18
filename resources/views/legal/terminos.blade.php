@extends('layouts.public')

@section('title', 'Términos y Condiciones - Arte Titi_Val')

@section('content')
<div class="container py-5" style="min-height: 75vh;">
    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            
            <div class="text-center mb-5">
                <h1 class="fw-bold" style="color: var(--color-lila-fuerte); font-family: 'Playfair Display', serif;">
                    Términos y Condiciones
                </h1>
                <p class="text-muted">Última actualización: {{ date('F Y') }}</p>
            </div>

            <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius: 15px; background-color: #ffffff;">
                <div class="legal-content" style="color: #4a4a4a; line-height: 1.8;">
                    
                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">1. Introducción</h4>
                    <p>Bienvenido al sitio web de Arte Titi_Val. Al acceder y utilizar nuestro catálogo y sistema de cotizaciones, aceptas cumplir con los siguientes términos y condiciones de uso. Si no estás de acuerdo con alguna parte de estos términos, te sugerimos no utilizar nuestro sistema.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">2. Productos y Cotizaciones</h4>
                    <p>Todos los modelos de bastones, lazos y accesorios presentados en el catálogo son confeccionados de manera artesanal. Las fotografías son referenciales. Las cotizaciones generadas a través del sistema están sujetas a revisión final por parte de la administración, dependiendo de la disponibilidad de materiales y tiempos de elaboración.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">3. Pedidos y Tiempos de Entrega</h4>
                    <p>Dado que nuestros productos son personalizados según los colores e identidad de cada institución, los tiempos de entrega se acordarán de manera interna con el cliente tras la aprobación de la cotización formal.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">4. Propiedad Intelectual</h4>
                    <p>Todo el contenido visual, fotografías de los modelos y diseños artesanales mostrados en esta plataforma son propiedad exclusiva de Arte Titi_Val.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">5. Contacto</h4>
                    <p>Para cualquier duda sobre estos términos, por favor escríbenos a través de nuestro formulario de <a href="{{ route('contacto') }}" style="color: var(--color-lila-fuerte); text-decoration: none; font-weight: bold;">Contacto</a> o a nuestra línea de WhatsApp oficial.</p>

                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection