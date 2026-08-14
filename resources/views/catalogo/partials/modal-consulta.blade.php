<!-- Modal de Consulta Rápida -->
<div class="modal fade" id="modalConsultaCat" tabindex="-1" aria-labelledby="modalConsultaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background-color: var(--color-lila-oscuro); color: var(--color-texto-sobre-oscuro);">
                <h5 class="modal-title" id="modalConsultaLabel" style="font-family: 'Playfair Display', serif;">
                    <i class="fas fa-gem me-2" style="color: var(--color-oro);"></i> Iniciar Personalización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="background-color: var(--color-fondo-claro);">

                <div class="d-flex align-items-center mb-3 p-3 mc-resumen-box">
                    <img id="mc-imagen" src="" alt="Producto" class="mc-resumen-img me-3">
                    <div>
                        <h6 id="mc-nombre" class="mb-1" style="color: var(--color-lila-fuerte); font-weight: bold;">Nombre del Modelo</h6>
                        <span id="mc-tamano" class="badge mc-badge-tamano">50 cm</span>
                        <span id="mc-nivel" class="badge mc-badge-nivel">Básico</span>
                    </div>
                </div>

                <form id="formConsultaRapida">
                    <input type="hidden" id="mc-producto-titulo" name="producto_referencia">
                    <input type="hidden" id="mc-producto-imagen" name="imagen_referencia_url">
                    <input type="hidden" id="mc-producto-categoria" name="categoria">

                    <div class="mb-3">
                        <label class="titi-label">Tu Nombre *</label>
                        <input type="text" class="titi-input" id="clienteNombre" name="nombre" placeholder="Ej. María Pérez" required>
                    </div>

                    <div class="mb-3">
                        <label class="titi-label">Teléfono de Contacto (WhatsApp) *</label>
                        <input type="text" class="titi-input" id="clienteTelefono" name="telefono" placeholder="Ej. 098xxxxx21" required>
                    </div>

                    <div class="mb-4">
                        <label class="titi-label">¿Qué modificaciones deseas o qué idea tienes en mente?</label>
                        <textarea class="titi-input" id="clienteMensaje" name="descripcion_diseno_especial" rows="3" placeholder="Ej. Me gusta este modelo, pero lo necesito en tonos azul marino..." required></textarea>
                    </div>

                    <div class="mt-4 pt-3 mc-actions">
                        <div class="d-flex flex-column flex-md-row justify-content-center gap-3 titi-action-choices">
                            @guest
                                <a href="{{ route('login') }}" class="titi-action-btn titi-action-btn-login text-decoration-none">
                                    <i class="fa-solid fa-lock"></i>
                                    <span class="titi-action-btn-title">Iniciar Sesión</span>
                                    <span class="titi-action-btn-sub">Para cotización formal en PDF</span>
                                </a>
                            @else
                                @if(!auth()->user()->hasVerifiedEmail())
                                    <a href="{{ route('verification.notice') }}" class="titi-action-btn titi-action-btn-verificar text-decoration-none">
                                        <i class="fa-solid fa-envelope-circle-check"></i>
                                        <span class="titi-action-btn-title">Verificar Correo</span>
                                        <span class="titi-action-btn-sub">Requerido para el sistema web</span>
                                    </a>
                                @else
                                    <button type="button" id="btnGuardarSistema" class="titi-action-btn titi-action-btn-interno">
                                        <i class="fas fa-inbox"></i>
                                        <span class="titi-action-btn-title">Enviar al Sistema Web</span>
                                        <span class="titi-action-btn-sub">Genera una cotización formal</span>
                                    </button>
                                @endif
                            @endguest

                            <button type="button" id="btnConsultarWhatsapp" class="titi-action-btn titi-action-btn-whatsapp">
                                <i class="fab fa-whatsapp"></i>
                                <span class="titi-action-btn-title">Chat Rápido</span>
                                <span class="titi-action-btn-sub">Escríbenos por WhatsApp</span>
                            </button>
                        </div>

                        @guest
                            <div class="text-center mt-3 p-2 rounded mc-tip-box">
                                <p class="mb-0" style="font-size: 0.8rem; color: var(--color-lila-fuerte);">
                                    <i class="fas fa-lightbulb text-warning me-1"></i> <strong>Tip:</strong> Regístrate gratis para guardar tus diseños favoritos y descargar cotizaciones formales.
                                </p>
                            </div>
                        @endguest
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>