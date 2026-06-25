// {{-- ==========================================
// MÓDULO 5 — SWITCHES PARA DISEÑOS PERSONALIZADOS
// ========================================== --}}
export function inicializarModuloDiseno() {
    const swDiseno  = $('#swDisenoPersonalizado');
    const cajaDiseno = $('#cajaDisenoPersonalizado');

    swDiseno.on('change', function() {
        cajaDiseno.toggleClass('d-none', !$(this).is(':checked'));
    });
}