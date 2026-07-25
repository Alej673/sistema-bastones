@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ route('cliente.dashboard') }}" class="btn-volver mb-3">
            <i class="bi bi-arrow-left"></i> Volver a mis pedidos
        </a>
        <h2 class="fw-bold mb-1" style="font-family:'Playfair Display', serif; color: var(--color-lila-fuerte);">
            Diseña tu Bastón desde Cero
        </h2>
        <p class="mb-0" style="color: var(--color-texto-mutado);">
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
                                    <option value="Oro">Dorado</option>
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
                <div class="card border-0 rounded-4 position-sticky titi-summary-card" style="top: 2rem;">
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
                        
                        <!-- NUEVO: Campo de Cantidad -->
                        <div class="mb-4">
                            <label for="cantidad" class="form-label titi-label">Cantidad de Bastones *</label>
                            <input type="number" class="form-control titi-input" id="cantidad" name="cantidad" min="1" value="1" required>
                        </div>

                        <hr style="border-color: rgba(107, 47, 163, 0.15);">

                        <p class="small mb-4" style="color: var(--color-texto-mutado);">
                            <i class="bi bi-info-circle me-1"></i> Selecciona cómo deseas cotizar tus bastones para bastoneras.
                        </p>

                        <!-- NUEVO: Botones Duales -->
                        <div class="d-flex flex-column gap-3">
                            <!-- Botón 1: Flujo Rápido (WhatsApp) -> type="button" -->
                            <button type="button" id="btnCotizarWhatsapp" class="btn btn-success w-100 py-2 fw-bold rounded-pill">
                                <i class="bi bi-whatsapp me-2"></i> Cotizar por WhatsApp
                            </button>
                            
                            <!-- Botón 2: Sistema Kardex (Interno) -> type="submit" (Solo visibles si es usuario institucional) -->
                            @auth
                            <button type="submit" id="btnGuardarInterno" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-pill" style="border-color: var(--color-lila-fuerte); color: var(--color-lila-fuerte);">
                                <i class="bi bi-save me-2"></i> Enviar al Sistema Interno
                            </button>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('css')
<style>
    /* ===== Tarjetas del formulario ===== */
    .titi-form-card {
        background-color: #ffffff;
        border: 1px solid var(--color-lila-suave);
        box-shadow: 0 6px 20px rgba(107, 47, 163, 0.08);
    }

    .titi-card-title {
        color: var(--color-lila-fuerte);
        font-family: 'Playfair Display', serif;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .titi-step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-lila-fuerte);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .titi-label {
        display: block;
        color: var(--color-lila-fuerte);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        margin-bottom: 6px;
    }

    /* ===== Inputs con identidad, no genéricos ===== */
    .titi-input {
        width: 100%;
        background-color: var(--color-fondo-claro);
        border: 1.5px solid var(--color-lila-suave);
        border-radius: 12px;
        color: var(--color-texto-principal);
        padding: 11px 14px;
        font-size: 0.92rem;
        box-shadow: inset 0 1px 3px rgba(107, 47, 163, 0.06);
        transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
    }

    .titi-input::placeholder {
        color: var(--color-texto-mutado);
        opacity: 0.8;
    }

    .titi-input:hover {
        border-color: var(--color-lila-medio);
    }

    .titi-input:focus {
        outline: none;
        background-color: #ffffff;
        border-color: var(--color-lila-medio);
        box-shadow: 0 0 0 3px rgba(157, 92, 224, 0.22);
    }

    select.titi-input {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%236B2FA3' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 11px;
        padding-right: 38px;
        cursor: pointer;
    }

    textarea.titi-input {
        resize: none;
        min-height: 90px;
    }

    .titi-input-group {
        position: relative;
    }

    .titi-input-group i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-lila-medio);
        font-size: 0.95rem;
        pointer-events: none;
    }

    .titi-input-group .titi-input {
        padding-left: 38px;
    }

    /* ===== Dropzone de imagen ===== */
    .titi-dropzone {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 140px;
        background-color: var(--color-fondo-claro);
        border: 1.5px dashed var(--color-lila-medio);
        border-radius: 14px;
        cursor: pointer;
        transition: border-color 0.25s ease, background-color 0.25s ease;
        padding: 16px;
    }

    .titi-dropzone:hover,
    .titi-dropzone.titi-dropzone-dragover {
        border-color: var(--color-lila-fuerte);
        background-color: #ffffff;
    }

    .titi-dropzone-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 4px;
        color: var(--color-lila-medio);
    }

    .titi-dropzone-empty i {
        font-size: 1.8rem;
        color: var(--color-lila-fuerte);
        margin-bottom: 4px;
    }

    .titi-dropzone-text {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--color-texto-principal);
    }

    .titi-dropzone-subtext {
        font-size: 0.75rem;
        color: var(--color-texto-mutado);
    }

    .titi-dropzone-preview {
        position: relative;
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .titi-dropzone-preview img {
        max-height: 180px;
        max-width: 100%;
        border-radius: 10px;
        object-fit: cover;
        box-shadow: 0 4px 14px rgba(107, 47, 163, 0.15);
    }

    .titi-remove-img {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        background: var(--color-lila-fuerte);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    .titi-remove-img:hover {
        background: #c0392b;
    }

    /* ===== Select2 restyled ===== */
    .select2-container--default .select2-selection--multiple {
        background-color: var(--color-fondo-claro) !important;
        border: 1.5px solid var(--color-lila-suave) !important;
        border-radius: 12px !important;
        min-height: 46px;
        padding: 4px 6px;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: var(--color-lila-medio) !important;
        box-shadow: 0 0 0 3px rgba(157, 92, 224, 0.22);
        background-color: #fff !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--color-lila-fuerte) !important;
        border: none !important;
        color: #fff !important;
        border-radius: 20px !important;
        padding: 3px 10px !important;
        font-size: 0.8rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.75) !important;
        margin-right: 6px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff !important;
    }

    .select2-dropdown {
        border-color: var(--color-lila-suave) !important;
        border-radius: 12px !important;
        overflow: hidden;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--color-lila-fuerte) !important;
    }

    /* ===== Panel resumen ===== */
    .titi-summary-card {
        background: linear-gradient(135deg, rgba(234, 217, 255, 0.5), rgba(255, 255, 255, 0.6));
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(157, 92, 224, 0.3) !important;
        box-shadow: 0 10px 26px rgba(107, 47, 163, 0.12);
    }
</style>
@endpush
@endsection