@extends('console.layout')

@section('title', 'Temas')

@section('topbar.description', 'Execute temas aprovados no console e no site sem assumir a governança da biblioteca visual.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Temas</span>
    <a href="#temas-execucao" class="ui-console-topbar-tab">Biblioteca</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Temas aprovados"
        subtitle="O Coordenador pode pre-visualizar e aplicar temas disponíveis por escopo, sem criar, editar ou arquivar."
    />

    <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <div class="space-y-4">
            <x-dashboard.section-card title="Estado atual" subtitle="Leitura rápida dos temas em execução.">
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Ativo no console</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $activeConsoleTheme?->name ?? 'Tema institucional' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Ativo no site</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $activeSiteTheme?->name ?? 'Tema institucional' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Preview no console</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $previewTheme?->name ?? 'Nenhum' }}</dd>
                    </div>
                </dl>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Buscar tema" subtitle="Filtre por nome ou slug para encontrar o tema aprovado.">
                <form method="GET" action="{{ route('coordenador.temas.index') }}" class="space-y-4">
                    <div>
                        <label class="ui-form-label" for="q">Busca</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" class="ui-form-control" placeholder="Nome ou slug do tema">
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="ui-btn-primary">Aplicar</button>
                        @if(($filters['q'] ?? '') !== '')
                            <a href="{{ route('coordenador.temas.index') }}" class="ui-btn-secondary">Limpar</a>
                        @endif
                    </div>
                </form>
            </x-dashboard.section-card>
        </div>

        <x-dashboard.section-card id="temas-execucao" title="Biblioteca de execução" subtitle="Temas disponíveis para preview e ativação por escopo.">
            @if($themes->count() === 0)
                <div class="ui-empty-state">
                    <div class="ui-empty-state-title">Nenhum tema disponível</div>
                    <p class="ui-empty-state-copy">Não há temas aprovados compatíveis com a busca atual.</p>
                </div>
            @else
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach($themes as $theme)
                        @php
                            $isConsoleActive = $activeConsoleTheme && $activeConsoleTheme->is($theme);
                            $isSiteActive = $activeSiteTheme && $activeSiteTheme->is($theme);
                            $isPreview = $previewTheme && $previewTheme->is($theme);
                            $supportsConsole = $theme->appliesTo(\App\Models\Theme::SCOPE_CONSOLE);
                            $supportsSite = $theme->appliesTo(\App\Models\Theme::SCOPE_SITE);
                        @endphp
                        <article class="overflow-hidden rounded-[26px] border border-[var(--ui-border)] bg-[var(--ui-surface)] shadow-[var(--ui-shadow-surface)]">
                            <div class="relative h-44 w-full overflow-hidden bg-[var(--ui-surface-soft)]">
                                <img src="{{ $theme->preview_image_url ?: theme_asset('hero_image', $theme) }}" alt="Preview do tema {{ $theme->name }}" class="h-full w-full object-cover">
                                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                                <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                                    @if($isConsoleActive)
                                        <span class="ui-badge ui-badge-success">Ativo no console</span>
                                    @endif
                                    @if($isSiteActive)
                                        <span class="ui-badge ui-badge-neutral">Ativo no site</span>
                                    @endif
                                    @if($isPreview)
                                        <span class="ui-badge ui-badge-warning">Em preview</span>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-5 p-5">
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h2 class="truncate text-lg font-semibold text-[var(--ui-text-title)]">{{ $theme->name }}</h2>
                                            <p class="mt-1 text-sm text-[var(--ui-text-soft)]">{{ $theme->slug }}</p>
                                        </div>
                                        <div class="rounded-[16px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] px-3 py-2 text-right text-xs text-[var(--ui-text-soft)]">
                                            <div>Escopos</div>
                                            <div class="mt-1 font-medium text-[var(--ui-text-title)]">{{ $theme->application_scopes_label }}</div>
                                        </div>
                                    </div>

                                    <p class="text-sm text-[var(--ui-text-soft)]">
                                        {{ $theme->description ?: 'Tema aprovado para execução institucional.' }}
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Base</div>
                                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ $theme->base_theme }}</div>
                                    </div>
                                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Atualizado</div>
                                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ optional($theme->updated_at)->format('d/m/Y H:i') ?: 'Sem registro' }}</div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @can('themes.preview')
                                        @if($supportsConsole)
                                            @if($isPreview)
                                                <form method="POST" action="{{ route('coordenador.temas.preview-console.clear') }}">
                                                    @csrf
                                                    <button class="ui-btn-secondary">Limpar preview</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('coordenador.temas.preview-console', $theme) }}">
                                                    @csrf
                                                    <button class="ui-btn-secondary">Preview no console</button>
                                                </form>
                                            @endif
                                        @endif
                                    @endcan

                                    @can('themes.execute.console')
                                        @if($supportsConsole && $isConsoleActive && ! $theme->is_default)
                                            <form method="POST" action="{{ route('coordenador.temas.restore-default-console') }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-secondary">Desativar no console</button>
                                            </form>
                                        @elseif($supportsConsole && ! $isConsoleActive)
                                            <form method="POST" action="{{ route('coordenador.temas.activate-console', $theme) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-primary">Aplicar no console</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('themes.execute.site')
                                        @if($supportsSite && $isSiteActive && ! $theme->is_default)
                                            <form method="POST" action="{{ route('coordenador.temas.restore-default-site') }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-secondary">Desativar no site</button>
                                            </form>
                                        @elseif($supportsSite && ! $isSiteActive)
                                            <form method="POST" action="{{ route('coordenador.temas.activate-site', $theme) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-secondary">Aplicar no site</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="mt-5">
                {{ $themes->links() }}
            </div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection
