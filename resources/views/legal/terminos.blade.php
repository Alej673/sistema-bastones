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
                    <p>¡Bienvenido al catálogo digital de Arte Titi_Val! Nos alegra mucho tenerte aquí. Estas políticas han sido creadas para brindarte total claridad y garantizar la mejor experiencia en la confección de tus productos. Al explorar nuestro sistema de cotizaciones, entendemos que estás de acuerdo con nuestra forma de trabajo artesanal, la cual detallamos a continuación.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">2. Condiciones de Pago y Anticipos</h4>
                    <p>Debido a la naturaleza personalizada (Built-to-Order) de nuestros productos, todo pedido requiere un abono confirmatorio del 60% del valor total de la cotización para iniciar la fase de producción. El 40% restante deberá ser cancelado en su totalidad al momento de la entrega o previo al despacho del producto.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">3. Cancelaciones y Reembolsos</h4>
                    <p>El cliente cuenta con un plazo máximo de 24 horas tras realizar el abono para solicitar la cancelación de su pedido. Transcurrido este periodo, los materiales habrán sido procesados y la producción estará en marcha, por lo que el anticipo del 60% no será reembolsable bajo ninguna circunstancia.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">4. Modificaciones de Diseño</h4>
                    <p>Cualquier modificación en medidas, colores o acabados debe definirse durante la etapa de cotización. Para pedidos minoristas, no se admitirán cambios una vez confirmado el pago. Para pedidos institucionales o mayoristas, se elaborará un modelo físico de prueba (prefabricación); una vez el cliente apruebe dicho modelo, el diseño quedará bloqueado para el resto del lote.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">5. Envíos y Logística</h4>
                    <p>Los tiempos de elaboración y entrega se acordarán internamente tras la aprobación de la cotización. La entrega estándar se realiza en nuestro taller. Cualquier costo de flete, envío a domicilio o encomienda a otras ciudades corre exclusivamente por cuenta y riesgo del cliente.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">6. Garantías y Reparaciones</h4>
                    <p>Si un producto llega con daños físicos comprobables al abrir el paquete, la reparación será cubierta por el taller. Adicionalmente, Arte Titi_Val otorga una garantía de un (1) mes calendario que cubre una (1) única reparación sin costo por defectos de confección. A partir del segundo mes, o en caso de requerir una segunda intervención, los costos de reparación y logística serán asumidos íntegramente por el cliente.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">7. Propiedad Intelectual</h4>
                    <p>Todo el contenido visual, fotografías de los modelos y diseños artesanales mostrados en esta plataforma son propiedad exclusiva de Arte Titi_Val.</p>

                    <h4 class="fw-bold mt-4 mb-3" style="color: #1b0f28;">8. Contacto</h4>
                    <p>Para cualquier duda sobre estos términos, por favor escríbenos a través de nuestro formulario de <a href="{{ route('contacto') }}" style="color: var(--color-lila-fuerte); text-decoration: none; font-weight: bold;">Contacto</a> o a nuestra línea de WhatsApp oficial.</p>

                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection