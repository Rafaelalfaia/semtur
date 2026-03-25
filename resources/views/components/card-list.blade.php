@props([
    'title',
    'subtitle' => null,
    'summary' => null,
    'image' => null,
    'href' => '#',
    'badge' => null,
    'cta' => 'Ver mais',
    'meta' => null,
    'variant' => null,
])

<a href="{{ $href }}" {{ $attributes->class(['site-card-list', "site-card-list--{$variant}" => filled($variant)]) }}>
    <div class="site-card-list-media">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" decoding="async" class="site-card-list-image">
        @else
            <div class="site-card-list-placeholder">Sem imagem</div>
        @endif
    </div>

    <div class="site-card-list-body">
        <div class="site-card-list-head">
            <div>
                @if($badge)
                    <span class="site-badge">{{ $badge }}</span>
                @endif

                <h3 class="site-card-list-title">{{ $title }}</h3>
            </div>

            <span class="site-button-secondary site-card-list-cta">{{ $cta }}</span>
        </div>

        @if($subtitle || $meta)
            <div class="site-card-list-meta">
                @if($subtitle)
                    <span>{{ $subtitle }}</span>
                @endif
                @if($meta)
                    <span>{{ $meta }}</span>
                @endif
            </div>
        @endif

        @if($summary)
            <p class="site-card-list-summary">{{ $summary }}</p>
        @endif
    </div>
</a>
