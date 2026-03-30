@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'label' => ui_text('ui.common.view_all'),
    'eyebrow' => null,
])

<div {{ $attributes->class('site-section-head') }}>
    <div class="site-section-head-copy">
        @if($eyebrow)
            <p class="site-section-head-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2 class="site-section-head-title">{{ $title }}</h2>
        @if($subtitle)
            <p class="site-section-head-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if($href)
        <a href="{{ $href }}" class="site-link site-section-head-link">
            {{ $label }}
        </a>
    @endif
</div>
