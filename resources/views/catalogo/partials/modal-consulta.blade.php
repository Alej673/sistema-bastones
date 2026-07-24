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

                <!-- Aviso de Variación de Precio -->
                <div class="alert mb-4" style="background-color: var(--color-lila-pastel); color: var(--color-texto-principal); border-left: 4px solid var(--color-lila-fuerte);">
                    <i class="fas fa-info-circle me-2" style="color: var(--color-lila-fuerte);"></i>
                    <strong>Aviso importante:</strong> El precio final puede variar según las especificaciones o modificaciones adicionales que solicites en tu diseño[cite: 2].
                </div>

                <!-- Formulario de Consulta -->
                <form id="formConsultaRapida">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--color-texto-principal); font-weight: 500;">Tu Nombre</label>
                        <input type="text" class="form-control" id="clienteNombre" placeholder="Ej. María Pérez" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" style="color: var(--color-texto-principal); font-weight: 500;">¿Qué modificaciones deseas o qué idea tienes en mente?</label>
                        <textarea class="form-control" id="clienteMensaje" rows="4" placeholder="Ej. Me gusta este modelo, pero lo necesito en tonos azul marino y con la cinta de mi colegio..." required></textarea>
                    </div>

                    <!-- Botones de Acción Multicanal -->
                    <div class="d-flex justify-content-end gap-2">
                        <!-- Botón Sistema (Asíncrono) -->
                        <button type="button" id="btnGuardarSistema" class="btn" style="background-color: var(--color-lila-suave); color: var(--color-lila-oscuro); font-weight: 600;">
                            <i class="fas fa-inbox me-1"></i> Enviar al Sistema
                        </button>
                        
                        <!-- Botón WhatsApp (Directo) -->
                        <button type="button" id="btnConsultarWhatsapp" class="btn" style="background-color: #25D366; color: white; font-weight: 600;">
                            <i class="fab fa-whatsapp me-1"></i> Consultar por WhatsApp
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
