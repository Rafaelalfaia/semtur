@props([
    'label',
    'value',
    'helper' => null,
    'badge' => null,
    'badgeTone' => 'primary',
])

@php
    $badgeClass = match($badgeTone) {
        'success' => 'ui-badge ui-badge-success',
        'warning' => 'ui-badge ui-badge-warning',
        'danger'  => 'ui-badge ui-badge-danger',
        default   => 'ui-badge ui-badge-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'ui-kpi-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="ui-kpi-label">{{ $label }}</div>

        @if($badge)
            <span class="{{ $badgeClass }}">{{ $badge }}</span>
        @endif
    </div>

    <div class="ui-kpi-value">{{ $value }}</div>

    @if($helper)
        <div class="ui-kpi-helper">{{ $helper }}</div>
    @endif
</div>
