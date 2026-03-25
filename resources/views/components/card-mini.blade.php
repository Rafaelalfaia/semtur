@props([
    'title',
    'subtitle' => null,
    'image' => null,
    'href' => '#',
    'badge' => null,
    'meta' => null,
    'variant' => null,
])

<a href="{{ $href }}" {{ $attributes->class(['site-card-mini', "site-card-mini--{$variant}" => filled($variant)]) }}>
    <div class="site-card-mini-media">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" decoding="async" class="site-card-mini-image">
        @else
            <div class="site-card-mini-placeholder">Sem imagem</div>
        @endif
    </div>

    <div class="site-card-mini-body">
        <div class="site-card-mini-top">
            @if($badge)
                <span class="site-badge">{{ $badge }}</span>
            @endif

            @if($meta)
                <div class="site-card-mini-meta">{{ $meta }}</div>
            @endif
        </div>

        <div class="site-card-mini-title">{{ $title }}</div>

        @if($subtitle)
            <div class="site-card-mini-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
</a>
