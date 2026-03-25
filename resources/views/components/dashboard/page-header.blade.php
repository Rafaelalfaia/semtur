@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <h1 class="ui-page-header-title">{{ $title }}</h1>

        @if($subtitle)
            <p class="ui-page-header-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex flex-wrap items-center gap-2.5">
            {{ $actions }}
        </div>
    @endif
</div>
