@extends('console.layout')

@section('title', 'Cursos')

@section('topbar.description', 'Visão de leitura dos cursos disponíveis no painel, organizada por curso, módulo e aula.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Cursos</span>
    <a href="#cursos-filtros" class="ui-console-topbar-tab">Filtros</a>
    <a href="#cursos-lista" class="ui-console-topbar-tab">Lista</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Cursos"
        subtitle="Consulte os cursos disponíveis dentro do mesmo dashboard, sem ações de edição."
    />

    <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <x-dashboard.section-card id="cursos-filtros" title="Filtros" subtitle="Refine a listagem por nome, resumo ou status" class="h-fit">
            <form method="GET" class="space-y-4">
                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Buscar</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="ui-form-control text-sm" placeholder="Nome ou descrição curta">
                </div>

                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Status</label>
                    <select name="status" class="ui-form-control text-sm">
                        <option value="todos" @selected(($status ?? 'todos') === 'todos')>Todos</option>
                        @foreach(($statuses ?? []) as $itemStatus)
                            <option value="{{ $itemStatus }}" @selected(($status ?? 'todos') === $itemStatus)>{{ ucfirst($itemStatus) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="ui-btn-primary">Filtrar</button>
                    <a href="{{ route('coordenador.cursos.index') }}" class="ui-btn-secondary">Limpar</a>
                </div>
            </form>
        </x-dashboard.section-card>

        <x-dashboard.section-card id="cursos-lista" title="Lista de cursos" subtitle="Acesse a estrutura de módulos e aulas em modo leitura." class="overflow-hidden">
            <div class="space-y-4">
                @forelse($cursos as $curso)
                    <article class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)] p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-4">
                                @if($curso->capa_url)
                                    <img src="{{ $curso->capa_url }}" alt="" class="h-24 w-36 rounded-2xl border border-[var(--ui-border)] object-cover">
                                @else
                                    <div class="flex h-24 w-36 items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-xs font-semibold text-[var(--ui-text-soft)]">
                                        CURSO
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold text-[var(--ui-text-title)]">{{ $curso->nome }}</h3>
                                        <span class="ui-badge {{ $curso->status === 'publicado' ? 'ui-badge-success' : ($curso->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                                            {{ ucfirst($curso->status) }}
                                        </span>
                                        <span class="ui-badge ui-badge-neutral">{{ $curso->publico_alvo_label }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-[var(--ui-text-soft)]">
                                        {{ $curso->descricao_curta ?: 'Sem descrição curta cadastrada para este curso.' }}
                                    </p>
                                    <div class="mt-3 text-xs text-[var(--ui-text-soft)]">
                                        {{ number_format((int) $curso->modulos_count) }} módulo(s)
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('coordenador.cursos.show', $curso) }}" class="ui-btn-primary">Ver curso</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)] px-6 py-10 text-center text-[var(--ui-text-soft)]">
                        Nenhum curso encontrado.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $cursos->links() }}</div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection
