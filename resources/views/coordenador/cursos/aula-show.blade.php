@extends('console.layout')

@section('title', $aula->nome)

@section('topbar.description', 'Visualização da aula e acesso ao vídeo sem sair do dashboard.')

@section('topbar.nav')
    <a href="{{ route('coordenador.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('coordenador.cursos.show', $curso) }}" class="ui-console-topbar-tab">Curso</a>
    <a href="{{ route('coordenador.cursos.modulos.show', [$curso, $modulo]) }}" class="ui-console-topbar-tab">Módulo</a>
    <span class="ui-console-topbar-tab is-active">Aula</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        :title="$aula->nome"
        subtitle="Acompanhe a descrição da aula e abra o vídeo do Google Drive sem sair do contexto do curso."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <span class="ui-badge {{ $aula->status === 'publicado' ? 'ui-badge-success' : ($aula->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                    {{ ucfirst($aula->status) }}
                </span>
                <a href="{{ route('coordenador.cursos.modulos.show', [$curso, $modulo]) }}" class="ui-btn-secondary">Voltar ao módulo</a>
                <a href="{{ $aula->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-primary">Abrir vídeo</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_360px]">
        <x-dashboard.section-card title="Vídeo da aula" subtitle="Preview rápido do conteúdo vinculado pelo Google Drive.">
            <div class="space-y-4">
                <div class="overflow-hidden rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-subtle)]">
                    @if($aula->embed_url)
                        <iframe src="{{ $aula->embed_url }}" class="h-[420px] w-full" allow="autoplay" loading="lazy"></iframe>
                    @else
                        <div class="flex h-[420px] items-center justify-center px-8 text-center text-sm text-[var(--ui-text-soft)]">
                            Não foi possível gerar um preview interno para este link.
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ $aula->embed_url ?: $aula->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Testar preview</a>
                    <a href="{{ $aula->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Abrir link original</a>
                </div>
            </div>
        </x-dashboard.section-card>

        <x-dashboard.section-card title="Resumo da aula" subtitle="Contexto rápido da aula dentro da trilha.">
            <div class="space-y-4">
                @if($aula->capa_url)
                    <img src="{{ $aula->capa_url }}" alt="" class="h-44 w-full rounded-2xl border border-[var(--ui-border)] object-cover">
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <span class="ui-badge {{ $aula->status === 'publicado' ? 'ui-badge-success' : ($aula->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                        {{ ucfirst($aula->status) }}
                    </span>
                    <span class="ui-badge ui-badge-neutral">Ordem {{ number_format((int) $aula->ordem) }}</span>
                </div>

                <p class="text-sm leading-7 text-[var(--ui-text-soft)]">
                    {{ $aula->descricao ?: 'Sem descrição cadastrada para esta aula.' }}
                </p>
            </div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection
