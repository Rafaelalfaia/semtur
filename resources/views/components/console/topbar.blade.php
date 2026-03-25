@props([
    'title',
    'kicker' => null,
    'description' => null,
    'nav' => null,
    'meta' => null,
    'actions' => null,
    'profileUrl' => null,
])

<div class="ui-console-topbar-shell">
    @php
        $activeThemeName = $resolvedActiveTheme?->name ?? 'Tema institucional';
        $previewThemeName = $resolvedPreviewTheme?->name;
        $consoleThemeLocksMode = (bool) ($resolvedThemeHasCustomConsoleTheme ?? false);
    @endphp

    <div class="ui-console-topbar">
        <div class="min-w-0">
            @if($kicker)
                <div class="ui-console-topbar-kicker">{{ $kicker }}</div>
            @endif

            <div class="text-base font-semibold tracking-[-0.02em] text-[var(--ui-text)] sm:text-[1.05rem]">
                {{ $title }}
            </div>

            @if($description)
                <div class="mt-1 text-xs leading-5 text-[var(--ui-text-soft)]">
                    {{ $description }}
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($meta)
                {!! $meta !!}
            @else
                <span class="ui-badge ui-badge-success">{{ $activeThemeName }}</span>
                @if($resolvedThemeIsPreview && $previewThemeName)
                    <span class="ui-badge ui-badge-warning">Preview: {{ $previewThemeName }}</span>
                @endif
                <span class="ui-badge ui-badge-neutral">{{ app()->environment() }}</span>
            @endif

            @if(! $consoleThemeLocksMode)
                <button type="button" class="ui-console-mode-toggle" data-console-mode-toggle aria-live="polite" aria-pressed="false">
                    <span class="ui-console-mode-toggle-icon" aria-hidden="true">
                        <svg data-console-mode-icon="light" viewBox="0 0 20 20" fill="none">
                            <path d="M10 3.25v1.5M10 15.25v1.5M15.25 10h1.5M3.25 10h1.5M14.77 5.23l1.06-1.06M4.17 15.83l1.06-1.06M14.77 14.77l1.06 1.06M4.17 4.17l1.06 1.06M13.25 10A3.25 3.25 0 1 1 6.75 10a3.25 3.25 0 0 1 6.5 0Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        <svg data-console-mode-icon="dark" viewBox="0 0 20 20" fill="none">
                            <path d="M13.75 2.75a6.75 6.75 0 1 0 3.5 12.53A7 7 0 0 1 13.75 2.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="ui-console-mode-toggle-copy">
                        <span class="ui-console-mode-toggle-label">
                            <span data-console-mode-state="light">Modo claro</span>
                            <span data-console-mode-state="dark">Modo escuro</span>
                        </span>
                        <span class="ui-console-mode-toggle-hint">
                            <span data-console-mode-action="light">Trocar para escuro</span>
                            <span data-console-mode-action="dark">Trocar para claro</span>
                        </span>
                    </span>
                </button>
            @else
                <span class="ui-badge ui-badge-neutral">Modo fixado pelo tema ativo</span>
            @endif

            @if($actions)
                {!! $actions !!}
            @elseif($profileUrl)
                <a href="{{ $profileUrl }}" class="ui-btn-secondary">
                    Meu perfil
                </a>
            @endif
        </div>
    </div>

    @if($nav)
        <div class="ui-console-topbar-context">
            {!! $nav !!}
        </div>
    @endif
</div>
