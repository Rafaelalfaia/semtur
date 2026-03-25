@props([
    'title',
    'subtitle' => null,
    'padding' => 'p-4 lg:p-5',
])

<div {{ $attributes->merge(['class' => 'ui-card']) }}>
    <div class="ui-section-head">
        <h3 class="ui-section-title">{{ $title }}</h3>

        @if($subtitle)
            <p class="ui-section-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
