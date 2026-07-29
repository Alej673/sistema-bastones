<!-- Modal de Consulta Rápida -->
<div class="modal fade" id="modalConsultaCat" tabindex="-1" aria-labelledby="modalConsultaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: 2px solid var(--color-oro-claro); border-radius: 15px; overflow: hidden;">
            
            <!-- Cabecera -->
            <div class="modal-header" style="background-color: var(--color-lila-oscuro); color: var(--color-texto-sobre-oscuro);">
                <h5 class="modal-title" id="modalConsultaLabel" style="font-family: 'Playfair Display', serif;">
                    <i class="fas fa-gem me-2" style="color: var(--color-oro);"></i> Iniciar Personalización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="background-color: var(--color-fondo-claro);">
                
                <!-- Resumen del Producto Seleccionado -->
                <div class="d-flex align-items-center mb-3 p-3" style="background-color: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <img id="mc-imagen" src="" alt="Producto" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid var(--color-lila-pastel);" class="me-3">
                    <div>
                        <h6 id="mc-nombre" class="mb-1" style="color: var(--color-lila-fuerte); font-weight: bold;">Nombre del Modelo</h6>
                        <span id="mc-tamano" class="badge" style="background-color: var(--color-lila-medio);">50 cm</span>
                        <span id="mc-nivel" class="badge" style="background-color: var(--color-oro);">Básico</span>
                    </div>
                </div>

                {{-- 
                <!-- Aviso de Variación de Precio (Comentado temporalmente con Blade para que no se renderice) -->
                <div class="alert mb-4" style="background-color: var(--color-lila-pastel); color: var(--color-texto-principal); border-left: 4px solid var(--color-lila-fuerte);">
                    <i class="fas fa-info-circle me-2" style="color: var(--color-lila-fuerte);"></i>
                    <strong>Aviso importante:</strong> El precio final puede variar según las especificaciones o modificaciones adicionales que solicites en tu diseño.
                </div>
                --}}

                <!-- Formulario de Consulta -->
                <form id="formConsultaRapida">
                    <!-- Campos Ocultos -->
                    <input type="hidden" id="mc-producto-titulo" name="producto_referencia">
                    <input type="hidden" id="mc-producto-imagen" name="imagen_referencia_url">
                    <input type="hidden" id="mc-producto-categoria" name="categoria">

                    <div class="mb-3">
                        <label class="form-label" style="color: var(--color-texto-principal); font-weight: 500;">Tu Nombre *</label>
                        <input type="text" class="form-control" id="clienteNombre" name="nombre" placeholder="Ej. María Pérez" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="color: var(--color-texto-principal); font-weight: 500;">Teléfono de Contacto (WhatsApp) *</label>
                        <input type="text" class="form-control" id="clienteTelefono" name="telefono" placeholder="Ej. 098xxxxx21" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" style="color: var(--color-texto-principal); font-weight: 500;">¿Qué modificaciones deseas o qué idea tienes en mente?</label>
                        <textarea class="form-control" id="clienteMensaje" name="descripcion_diseno_especial" rows="3" placeholder="Ej. Me gusta este modelo, pero lo necesito en tonos azul marino..." required></textarea>
                    </div>

                    <!-- Botones de Acción Multicanal Refactorizados -->
                    <div class="mt-4 pt-3" style="border-top: 1px solid rgba(157, 92, 224, 0.2);">
                        
                        @auth
                            <!-- Vista para usuarios registrados: Se explican las dos vías -->
                            <p class="text-center small mb-3" style="color: var(--color-texto-mutado);">
                                <i class="fas fa-info-circle me-1"></i> Elige cómo prefieres enviar tu solicitud al taller:
                            </p>
                            
                            <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                                <!-- Botón Sistema (Formal) -->
                                <button type="button" id="btnGuardarSistema" class="btn flex-grow-1" style="background-color: var(--color-lila-fuerte); color: white; padding: 12px; border-radius: 10px;">
                                    <div class="fw-bold"><i class="fas fa-inbox me-1"></i> Enviar al Sistema Web</div>
                                    <small class="d-block fw-normal" style="font-size: 0.75rem; opacity: 0.9;">Genera una cotización formal</small>
                                </button>
                                
                                <!-- Botón WhatsApp (Ágil) -->
                                <button type="button" id="btnConsultarWhatsapp" class="btn flex-grow-1" style="background-color: #25D366; color: white; padding: 12px; border-radius: 10px;">
                                    <div class="fw-bold"><i class="fab fa-whatsapp me-1"></i> Chat Rápido</div>
                                    <small class="d-block fw-normal" style="font-size: 0.75rem; opacity: 0.9;">Escríbenos directo por WhatsApp</small>
                                </button>
                            </div>
                        @else
                            <!-- Vista para invitados: No los confundimos, solo los mandamos a WhatsApp -->
                            <button type="button" id="btnConsultarWhatsapp" class="btn w-100" style="background-color: #25D366; color: white; padding: 14px; font-size: 1.1rem; border-radius: 10px;">
                                <i class="fab fa-whatsapp me-2"></i> Consultar por WhatsApp
                            </button>
                            <p class="text-center mt-2 mb-0" style="font-size: 0.8rem; color: var(--color-texto-mutado);">
                                Te redirigiremos a un chat directo con la administración del taller.
                            </p>
                        @endauth
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>