@extends('console.layout')

@section('title', 'Temas do sistema')

@section('topbar.description', 'Administre o catálogo de temas, governe preview local e publique a identidade ativa do console.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Temas</span>
    <a href="#temas-lista" class="ui-console-topbar-tab">Biblioteca</a>
    @can('themes.create')
        <a href="{{ route('admin.temas.create') }}" class="ui-console-topbar-tab">Novo tema</a>
    @endcan
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Temas do sistema"
        subtitle="O Admin governa a identidade visual institucional do console, auth e site sem misturar tema com conteúdo editorial."
    >
        <x-slot:actions>
            @if($previewTheme && Route::has('admin.temas.preview.clear'))
                <form method="POST" action="{{ route('admin.temas.preview.clear') }}">
                    @csrf
                    <button class="ui-btn-secondary">Limpar preview</button>
                </form>
            @endif

            @if($activeTheme && ! $activeTheme->is_default && Route::has('admin.temas.restore-default'))
                <form method="POST" action="{{ route('admin.temas.restore-default') }}">
                    @csrf
                    @method('PATCH')
                    <button class="ui-btn-secondary">Voltar ao padrão</button>
                </form>
            @endif

            @can('themes.create')
                <a href="{{ route('admin.temas.create') }}" class="ui-btn-primary">Novo tema</a>
            @endcan
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 grid gap-4 xl:grid-cols-[340px_minmax(0,1fr)]">
        <div class="space-y-4">
            <x-dashboard.section-card title="Contexto atual" subtitle="Leitura rápida do estado do módulo.">
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Tema ativo</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $activeTheme?->name ?? 'Tema institucional' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Preview local</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $previewTheme?->name ?? 'Nenhum' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[var(--ui-text-soft)]">Total listado</dt>
                        <dd class="font-medium text-[var(--ui-text-title)]">{{ $themes->total() }}</dd>
                    </div>
                </dl>
            </x-dashboard.section-card>

            <x-dashboard.section-card title="Filtrar temas" subtitle="Busque por nome ou slug e refine por status.">
                <form method="GET" action="{{ route('admin.temas.index') }}" class="space-y-4">
                    <div>
                        <label class="ui-form-label" for="q">Busca</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" class="ui-form-control" placeholder="Nome ou slug do tema">
                    </div>

                    <div>
                        <label class="ui-form-label" for="status">Status</label>
                        <select id="status" name="status" class="ui-form-select">
                            <option value="">Todos</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="ui-btn-primary">Aplicar filtros</button>
                        @if(($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
                            <a href="{{ route('admin.temas.index') }}" class="ui-btn-secondary">Limpar</a>
                        @endif
                    </div>
                </form>
            </x-dashboard.section-card>
        </div>

        <x-dashboard.section-card id="temas-lista" title="Biblioteca de temas" subtitle="Gerencie criação, edição, preview, ativação e arquivamento sem sair do fluxo administrativo.">
            @if($themes->count() === 0)
                <div class="ui-empty-state">
                    <div class="ui-empty-state-title">Nenhum tema encontrado</div>
                    <p class="ui-empty-state-copy">Ajuste os filtros ou crie o primeiro tema institucional para iniciar a biblioteca visual do sistema.</p>
                </div>
            @else
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach($themes as $theme)
                        @php
                            $isActive = $activeTheme && $activeTheme->is($theme);
                            $isPreview = $previewTheme && $previewTheme->is($theme);
                        @endphp
                        <article class="overflow-hidden rounded-[26px] border {{ $isActive ? 'border-[var(--ui-primary)] shadow-[0_24px_60px_rgba(12,33,22,0.18)]' : 'border-[var(--ui-border)] shadow-[var(--ui-shadow-surface)]' }} bg-[var(--ui-surface)]">
                            <div class="relative h-44 w-full overflow-hidden bg-[var(--ui-surface-soft)]">
                                <img src="{{ $theme->preview_image_url ?: theme_asset('hero_image', $theme) }}" alt="Preview do tema {{ $theme->name }}" class="h-full w-full object-cover">
                                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                                <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                                    @if($isActive)
                                        <span class="ui-badge ui-badge-success">Ativo</span>
                                    @endif
                                    @if($isPreview)
                                        <span class="ui-badge ui-badge-warning">{{ $isActive ? 'Preview ativo' : 'Preview local' }}</span>
                                    @endif
                                    @if($theme->is_default)
                                        <span class="ui-badge ui-badge-neutral">Default</span>
                                    @endif
                                    <span class="ui-badge ui-badge-neutral">{{ ucfirst($theme->normalizedStatus()) }}</span>
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
                                            <div>Base</div>
                                            <div class="mt-1 font-medium text-[var(--ui-text-title)]">{{ $theme->base_theme }}</div>
                                        </div>
                                    </div>

                                    <p class="text-sm text-[var(--ui-text-soft)]">
                                        {{ $theme->description ?: 'Tema sem descrição interna. Use a edição para registrar propósito, tom visual e contexto de uso.' }}
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Escopos</div>
                                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ $theme->application_scopes_label }}</div>
                                    </div>
                                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3">
                                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Atualizado</div>
                                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">{{ optional($theme->updated_at)->format('d/m/Y H:i') ?: 'Sem registro' }}</div>
                                    </div>
                                    <div class="rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-3 sm:col-span-2">
                                        <div class="text-xs uppercase tracking-[0.12em] text-[var(--ui-text-soft)]">Vigência</div>
                                        <div class="mt-1 text-sm font-medium text-[var(--ui-text-title)]">
                                            {{ optional($theme->starts_at)->format('d/m/Y H:i') ?: 'Imediata' }}
                                            até
                                            {{ optional($theme->ends_at)->format('d/m/Y H:i') ?: 'Sem fim' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @can('themes.edit')
                                        <a href="{{ route('admin.temas.edit', $theme) }}" class="ui-btn-secondary">Editar</a>
                                    @endcan

                                    @can('themes.preview')
                                        @if($isPreview)
                                            <form method="POST" action="{{ route('admin.temas.preview.clear') }}">
                                                @csrf
                                                <button class="ui-btn-secondary">Limpar preview</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.temas.preview', $theme) }}">
                                                @csrf
                                                <button class="ui-btn-secondary">Preview</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('themes.activate')
                                        @if($isActive && ! $theme->is_default)
                                            <form method="POST" action="{{ route('admin.temas.restore-default') }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-secondary">Voltar ao padrão</button>
                                            </form>
                                        @elseif(! $isActive)
                                            <form method="POST" action="{{ route('admin.temas.activate', $theme) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-primary">Ativar</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('themes.archive')
                                        @if($theme->normalizedStatus() !== \App\Models\Theme::STATUS_ARQUIVADO)
                                            <form method="POST" action="{{ route('admin.temas.archive', $theme) }}" onsubmit="return confirm('Arquivar este tema?');">
                                                @csrf
                                                @method('PATCH')
                                                <button class="ui-btn-danger">Arquivar</button>
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
