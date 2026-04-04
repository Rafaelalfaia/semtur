@extends('console.layout')

@section('title', $modulo->nome)

@section('topbar.description', 'Visualização do módulo e das aulas disponíveis, em modo leitura dentro do dashboard.')

@section('topbar.nav')
    <a href="{{ route('coordenador.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('coordenador.cursos.show', $curso) }}" class="ui-console-topbar-tab">Curso</a>
    <span class="ui-console-topbar-tab is-active">Módulo</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        :title="$modulo->nome"
        :subtitle="$modulo->descricao_curta ?: 'Acesse as aulas deste módulo em modo leitura.'"
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <span class="ui-badge {{ $modulo->status === 'publicado' ? 'ui-badge-success' : ($modulo->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                    {{ ucfirst($modulo->status) }}
                </span>
                <a href="{{ route('coordenador.cursos.show', $curso) }}" class="ui-btn-secondary">Voltar ao curso</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <x-dashboard.section-card class="mt-5" title="Aulas do módulo" subtitle="Consulte cada aula e acesse seu vídeo diretamente no painel.">
        <div class="space-y-4">
            @forelse($modulo->aulas as $aula)
                <article class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)] p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            @if($aula->capa_url)
                                <img src="{{ $aula->capa_url }}" alt="" class="h-24 w-36 rounded-2xl border border-[var(--ui-border)] object-cover">
                            @else
                                <div class="flex h-24 w-36 items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-xs text-[var(--ui-text-soft)]">
                                    Sem capa
                                </div>
                            @endif

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-[var(--ui-text-title)]">{{ $aula->nome }}</h3>
                                    <span class="ui-badge {{ $aula->status === 'publicado' ? 'ui-badge-success' : ($aula->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                                        {{ ucfirst($aula->status) }}
                                    </span>
                                </div>

                                <p class="mt-2 text-sm leading-6 text-[var(--ui-text-soft)]">
                                    {{ $aula->descricao ?: 'Sem descrição cadastrada para esta aula.' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('coordenador.cursos.modulos.aulas.show', [$curso, $modulo, $aula]) }}" class="ui-btn-primary">Ver aula</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)] px-6 py-10 text-center text-[var(--ui-text-soft)]">
                    Nenhuma aula cadastrada para este módulo.
                </div>
            @endforelse
        </div>
    </x-dashboard.section-card>
</div>
@endsection
