@extends('console.layout')

@section('title', $curso->nome)

@section('topbar.description', 'Visualização do curso e seus módulos em modo leitura dentro do dashboard.')

@section('topbar.nav')
    <a href="{{ route('coordenador.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <span class="ui-console-topbar-tab is-active">Curso</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        :title="$curso->nome"
        :subtitle="$curso->descricao_curta ?: 'Estrutura completa do curso, com acesso aos módulos disponíveis.'"
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <span class="ui-badge {{ $curso->status === 'publicado' ? 'ui-badge-success' : ($curso->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                    {{ ucfirst($curso->status) }}
                </span>
                <span class="ui-badge ui-badge-neutral">{{ $curso->publico_alvo_label }}</span>
                <a href="{{ route('coordenador.cursos.index') }}" class="ui-btn-secondary">Voltar</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <x-dashboard.section-card class="mt-5" title="Módulos do curso" subtitle="Acesse cada módulo para consultar as aulas relacionadas.">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($curso->modulos as $modulo)
                <article class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)] p-4">
                    @if($modulo->capa_url)
                        <img src="{{ $modulo->capa_url }}" alt="" class="h-36 w-full rounded-2xl border border-[var(--ui-border)] object-cover">
                    @else
                        <div class="flex h-36 w-full items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-xs text-[var(--ui-text-soft)]">
                            Sem capa
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-semibold text-[var(--ui-text-title)]">{{ $modulo->nome }}</h3>
                        <span class="ui-badge {{ $modulo->status === 'publicado' ? 'ui-badge-success' : ($modulo->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                            {{ ucfirst($modulo->status) }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-[var(--ui-text-soft)]">
                        {{ $modulo->descricao_curta ?: 'Sem descrição curta cadastrada para este módulo.' }}
                    </p>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <span class="text-xs text-[var(--ui-text-soft)]">{{ number_format((int) $modulo->aulas_count) }} aula(s)</span>
                        <a href="{{ route('coordenador.cursos.modulos.show', [$curso, $modulo]) }}" class="ui-btn-primary">Ver módulo</a>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)] px-6 py-10 text-center text-[var(--ui-text-soft)]">
                    Nenhum módulo cadastrado para este curso.
                </div>
            @endforelse
        </div>
    </x-dashboard.section-card>
</div>
@endsection
