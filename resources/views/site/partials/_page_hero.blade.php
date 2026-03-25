@props([
    'backHref' => null,
    'breadcrumbs' => [],
    'badge' => null,
    'title' => null,
    'subtitle' => null,
    'meta' => [],
    'primaryActionLabel' => null,
    'primaryActionHref' => null,
    'secondaryActionLabel' => null,
    'secondaryActionHref' => null,
    'image' => null,
    'imageAlt' => null,
    'compact' => false,
])

@php
    $heroImage = $image ?: theme_asset('hero_image');
    $resolvedBackHref = $backHref ?: url()->previous();
    $meta = collect($meta)->filter(fn ($item) => filled($item))->values();
    $heroClasses = trim('site-detail-hero site-page-hero'.($compact ? ' site-page-hero-compact' : ''));
@endphp

<section class="site-section">
    <div class="{{ $heroClasses }}">
        <img
            src="{{ $heroImage }}"
            alt="{{ $imageAlt ?: $title }}"
            class="site-detail-hero-image"
            loading="eager"
            decoding="async"
        >

        <div class="site-detail-hero-overlay site-page-hero-overlay">
            <div class="site-page-hero-top">
                <a href="{{ $resolvedBackHref }}" class="site-page-back">
                    <svg class="site-page-back-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Voltar</span>
                </a>

                @if(!empty($breadcrumbs))
                    <nav class="site-page-breadcrumbs" aria-label="Breadcrumb">
                        @foreach($breadcrumbs as $item)
                            @if(!empty($item['href']))
                                <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                            @else
                                <span class="site-page-breadcrumbs-current">{{ $item['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                @endif
            </div>

            <div class="site-detail-hero-copy site-page-hero-copy">
                @if($badge)
                    <span class="site-badge">{{ $badge }}</span>
                @endif

                <h1 class="site-detail-title site-page-hero-title">{{ $title }}</h1>

                @if($subtitle)
                    <p class="site-detail-subtitle site-page-hero-subtitle">{{ $subtitle }}</p>
                @endif

                @if($meta->isNotEmpty())
                    <div class="site-page-hero-meta">
                        @foreach($meta as $item)
                            <span class="site-page-hero-meta-item">{{ $item }}</span>
                        @endforeach
                    </div>
                @endif

                @if(($primaryActionLabel && $primaryActionHref) || ($secondaryActionLabel && $secondaryActionHref))
                    <div class="site-page-hero-actions">
                        @if($primaryActionLabel && $primaryActionHref)
                            <a href="{{ $primaryActionHref }}" class="site-button-primary">{{ $primaryActionLabel }}</a>
                        @endif

                        @if($secondaryActionLabel && $secondaryActionHref)
                            <a href="{{ $secondaryActionHref }}" class="site-button-secondary">{{ $secondaryActionLabel }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
