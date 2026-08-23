@forelse($comentarios as $comentario)
    @include('partials.review-card', ['comentario' => $comentario])
@empty
    <div class="review-scroll-empty text-center p-5 scroll-hidden review-empty">
        <i class="fa-regular fa-comment-dots fa-3x mb-3" style="color: var(--color-lila-fuerte); opacity: 0.5;"></i>
        <h4 style="color: var(--color-texto-principal); font-family: 'Playfair Display', serif;">Aún no hay opiniones</h4>
    </div>
@endforelse