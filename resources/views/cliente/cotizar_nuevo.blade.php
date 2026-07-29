@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ route('cliente.dashboard') }}" class="btn-volver mb-3">
            <i class="bi bi-arrow-left"></i> Volver a mis pedidos
        </a>
        <h2 class="fw-bold mb-1 titi-page-title">
            Diseña tu Bastón desde Cero
        </h2>
        <p class="mb-0 titi-page-subtitle">
            Selecciona las especificaciones exactas para tu cotización. Nuestro taller evaluará los materiales y te dará el mejor precio.
        </p>
    </div>

    <form action="{{ route('cotizacion.store') }}" method="POST" id="formNuevoBaston" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- COLUMNA IZQUIERDA: Especificaciones Técnicas --}}
            <div class="col-lg-8 mb-4">

                {{-- SECCIÓN 1: Estructura Base --}}
                <div class="card border-0 rounded-4 mb-4 titi-form-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 titi-card-title">
                            <span class="titi-step-num">1</span> Estructura Base
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label titi-label">Medida (cm) *</label>
                                <select class="form-select titi-input" name="medida_cm" required>
                                    <option value="" selected disabled>Seleccione la longitud...</option>
                                    <option value="45">45 cm (Infantil)</option>
                                    <option value="50">50 cm (Estándar)</option>
                                    <option value="55">55 cm (Intermedio)</option>
                                    <option value="60">60 cm (Profesional)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label titi-label">Acabado del Tubo *</label>
                                <select class="form-select titi-input" name="acabado" required>
                                    <option value="" selected disabled>Seleccione el tono...</option>
                                    <option value="Plata">Plata</option>
                                    <option value="Dorado">Dorado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: Personalización de Colores e Imagen --}}
                <div class="card border-0 rounded-4 mb-4 titi-form-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 titi-card-title">
                            <span class="titi-step-num">2</span> Diseño y Colores
                        </h5>

                        <div class="mb-4">
                            <label class="form-label titi-label">Gama de Colores para el Bastón</label>
                            <input type="text" class="form-control titi-input" name="colores"
                                   placeholder="Ej. Azul marino con franjas plateadas...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label titi-label">Detalles Adicionales, Apliques o Cantidades</label>
                            <textarea class="form-control titi-input" name="descripcion_diseno_especial" rows="3"
                                      placeholder="Ej. Deseo que lleve un lazo doble azul marino y cristales plateados en la empuñadura..."></textarea>
                        </div>

                        {{-- Dropzone de imagen de referencia --}}
                        <div>
                            <label class="form-label titi-label">Imagen de Referencia (Opcional)</label>

                            <label for="imagenReferencia" class="titi-dropzone" id="titiDropzoneLabel">
                                <div id="titiDropzoneEmpty" class="titi-dropzone-empty">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <span class="titi-dropzone-text">Arrastra una foto o haz clic para subirla</span>
                                    <span class="titi-dropzone-subtext">JPG o PNG · máx. recomendado 5MB</span>
                                </div>

                                <div id="titiDropzonePreview" class="titi-dropzone-preview d-none">
                                    <img id="titiPreviewImg" src="" alt="Vista previa">
                                    <button type="button" id="titiRemoveImg" class="titi-remove-img" aria-label="Quitar imagen">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </label>

                            <input type="file" class="d-none" id="imagenReferencia" name="imagen_referencia" accept="image/jpeg, image/png">
                        </div>
                    </div>
                </div>

            </div>

            {{-- COLUMNA DERECHA: Resumen y Envío --}}
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 position-sticky titi-summary-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 titi-card-title">Resumen de Solicitud</h5>
                        <div class="mb-4">
                            <label class="form-label titi-label">Nombre de la Institución o Cliente *</label>
                            <input type="text" class="form-control titi-input" name="nombre" required placeholder="Ej. Unidad Educativa...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label titi-label">Teléfono de Contacto (WhatsApp) *</label>
                            <div class="titi-input-group">
                                <i class="bi bi-whatsapp"></i>
                                <input type="text" class="form-control titi-input" name="telefono" required placeholder="099...">
                            </div>
                        </div>

                        <!-- Campo de Cantidad -->
                        <div class="mb-4">
                            <label for="cantidad" class="form-label titi-label">Cantidad de Bastones *</label>
                            <input type="number" class="form-control titi-input" id="cantidad" name="cantidad" min="1" value="1" required>
                        </div>

                        <hr class="titi-divider">

                        <div class="titi-action-info mb-3">
                            <i class="bi bi-info-circle"></i>
                            <span>
                                @auth
                                    <strong>Sistema Interno</strong> genera una cotización formal en tu panel de pedidos, o usa <strong>WhatsApp</strong> para chatear directo con el taller.
                                @else
                                    Escríbenos por <strong>WhatsApp</strong> y el taller te responderá con tu cotización personalizada.
                                @endauth
                            </span>
                        </div>

                        <div class="titi-action-choices">
                            @auth
                            <button type="submit" id="btnGuardarInterno" class="titi-action-btn titi-action-btn-interno">
                                <i class="bi bi-send-check text-white"></i>
                                <span class="titi-action-btn-title">Sistema Interno</span>
                                <span class="titi-action-btn-sub">Cotización formal</span>
                            </button>
                            @endauth

                            <button type="button" id="btnCotizarWhatsapp" class="titi-action-btn titi-action-btn-whatsapp">
                                <i class="bi bi-whatsapp text-white"></i>
                                <span class="titi-action-btn-title">Chat Rápido</span>
                                <span class="titi-action-btn-sub">Directo por WhatsApp</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection