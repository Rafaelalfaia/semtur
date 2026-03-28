@props([
    'title',
    'subtitle' => null,
    'image' => null,
    'href' => '#',
    'badge' => null,
    'meta' => null,
    'variant' => null,
])

@php
    $imageSources = $image ? site_image_sources($image, 'mini') : null;
@endphp

<a href="{{ $href }}" {{ $attributes->class(['site-card-mini', "site-card-mini--{$variant}" => filled($variant)]) }}>
    <div class="site-card-mini-media">
        @if($image)
            <x-picture
                :jpg="$imageSources['jpg'] ?? $image"
                :webp="$imageSources['webp'] ?? null"
                :alt="$title"
                class="site-card-mini-image"
                sizes="(max-width: 768px) 74vw, 20vw"
                :width="$imageSources['width'] ?? null"
                :height="$imageSources['height'] ?? null"
            />
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
