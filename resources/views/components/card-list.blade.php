@props([
    'title',
    'subtitle' => null,
    'summary' => null,
    'image' => null,
    'href' => '#',
    'badge' => null,
    'cta' => ui_text('ui.common.view_more'),
    'meta' => null,
    'variant' => null,
    'adminAction' => null,
])

@php
    $imageSources = $image ? site_image_sources($image, 'card') : null;
@endphp

<article {{ $attributes->class(['site-card-list-shell']) }}>
    @if(!empty($adminAction['href']) && !empty($adminAction['label']))
        <div class="site-inline-actions">
            <a href="{{ $adminAction['href'] }}" class="{{ $adminAction['class'] ?? 'site-button-secondary' }}">{{ $adminAction['label'] }}</a>
        </div>
    @endif

    <a href="{{ $href }}" class="{{ trim('site-card-list'.(filled($variant) ? " site-card-list--{$variant}" : '')) }}">
        <div class="site-card-list-media">
            @if($image)
                <x-picture
                    :jpg="$imageSources['jpg'] ?? $image"
                    :webp="$imageSources['webp'] ?? null"
                    :alt="$title"
                    class="site-card-list-image"
                    sizes="(max-width: 768px) 86vw, (max-width: 1280px) 42vw, 28vw"
                    :width="$imageSources['width'] ?? null"
                    :height="$imageSources['height'] ?? null"
                />
            @else
                <div class="site-card-list-placeholder" aria-hidden="true">{{ ui_text('ui.common.empty_image') }}</div>
            @endif
        </div>

        <div class="site-card-list-body">
            @if($variant === 'compact')
                <div class="site-card-list-copy">
                    @if($badge)
                        <span class="site-badge">{{ $badge }}</span>
                    @endif

                    <h3 class="site-card-list-title">{{ $title }}</h3>

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

                <span class="site-button-secondary site-card-list-cta">{{ $cta }}</span>
            @else
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
            @endif
        </div>
    </a>
</article>
