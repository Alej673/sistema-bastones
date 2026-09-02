<div class="review-scroll-item">
    <div class="card review-card h-100 shadow-sm text-center scroll-hidden d-flex flex-column">
        <div class="card-body p-4">
            <div class="review-stars mb-3">
                @for($i = 1; $i <= 5; $i++)
                    <i class="{{ $i <= $comentario->calificacion ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                @endfor
            </div>

            <p class="review-texto">"{{ $comentario->contenido }}"</p>
            <h6 class="fw-bold review-autor">{{ $comentario->user?->name ?? 'Usuario Anónimo' }}</h6>
        </div>

        <div class="card-footer bg-transparent border-0 d-flex justify-content-around pb-3">
            <button type="button"
                    class="btn btn-sm btn-link text-decoration-none btn-util {{ $comentario->isLikedByAuthUser() ? 'is-liked' : '' }}"
                    data-id="{{ $comentario->id }}">
                <i class="{{ $comentario->isLikedByAuthUser() ? 'fa-solid' : 'fa-regular' }} fa-heart me-1 icon-heart"></i> Útil
                <span class="badge ms-1 like-counter {{ $comentario->isLikedByAuthUser() ? 'is-liked' : '' }}">
                    {{ $comentario->likes->count() }}
                </span>
            </button>

            @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']))
                <button type="button" class="btn btn-sm btn-link text-decoration-none btn-responder"
                        data-id="{{ $comentario->id }}"
                        data-nombre="{{ $comentario->user?->name ?? 'Anónimo' }}">
                    <i class="fa-solid fa-reply me-1"></i> Responder
                </button>
            @endif
        </div>

        @if($comentario->respuestas && $comentario->respuestas->count())
            <div class="review-reply-wrap">
                @foreach($comentario->respuestas as $respuesta)
                    <div class="review-reply-item">
                        <span class="badge-taller-sm">
                            <i class="fa-solid fa-scissors"></i> Equipo Arte Titi_Val
                        </span>
                        <p class="review-reply-texto">"{{ $respuesta->contenido }}"</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>