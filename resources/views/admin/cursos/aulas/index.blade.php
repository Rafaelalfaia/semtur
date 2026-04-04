@extends('console.layout')

@section('title', 'Aulas - '.$modulo->nome)

@section('topbar.description', 'Gestão das aulas do módulo atual, mantendo a trilha inteira organizada dentro do dashboard.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-console-topbar-tab">Módulos</a>
    <a href="{{ route('admin.cursos.modulos.edit', [$curso, $modulo]) }}" class="ui-console-topbar-tab">{{ \Illuminate\Support\Str::limit($modulo->nome, 28) }}</a>
    <span class="ui-console-topbar-tab is-active">Aulas</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Aulas"
        subtitle="Cadastre as aulas do módulo com descrição, link do vídeo e estado editorial."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.cursos.modulos.aulas.create', [$curso, $modulo]) }}" class="ui-btn-primary">Nova aula</a>
                <a href="{{ route('admin.cursos.modulos.edit', [$curso, $modulo]) }}" class="ui-btn-secondary">Voltar ao módulo</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-2 text-sm text-[var(--ui-text-soft)]">/cursos/{{ $curso->slug }}/{{ $modulo->slug }}</div>

    <x-dashboard.section-card title="Lista de aulas" subtitle="Cada aula concentra a descrição e o link principal do vídeo no Google Drive." class="mt-5">
        <div class="ui-table-shell">
            <table class="min-w-full text-sm">
                <thead class="ui-table-head">
                    <tr>
                        <th class="px-3 py-3 text-left">Aula</th>
                        <th class="px-3 py-3 text-left">Status</th>
                        <th class="px-3 py-3 text-left">Vídeo</th>
                        <th class="px-3 py-3 text-left">Ordem</th>
                        <th class="px-3 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aulas as $aula)
                        <tr class="ui-table-row">
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    @if($aula->capa_url)
                                        <img src="{{ $aula->capa_url }}" alt="" class="h-12 w-16 rounded-xl border border-[var(--ui-border)] object-cover">
                                    @else
                                        <div class="flex h-12 w-16 items-center justify-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-[11px] font-semibold text-[var(--ui-text-soft)]">
                                            AULA
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-[var(--ui-text-title)]">{{ $aula->nome }}</div>
                                        <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ \Illuminate\Support\Str::limit($aula->descricao ?: 'Sem descrição.', 90) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="ui-badge {{ $aula->status === 'publicado' ? 'ui-badge-success' : ($aula->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                                    {{ ucfirst($aula->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $aula->embed_url ?: $aula->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Preview</a>
                                    <a href="{{ $aula->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Abrir</a>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ number_format((int) $aula->ordem) }}</td>
                            <td class="px-3 py-3">
                                <div class="flex min-w-[14rem] flex-wrap items-center justify-end gap-2">
                                    @if($aula->status !== 'publicado')
                                        <form method="POST" action="{{ route('admin.cursos.modulos.aulas.publicar', [$curso, $modulo, $aula]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-btn-secondary">Publicar</button>
                                        </form>
                                    @endif
                                    @if($aula->status !== 'rascunho')
                                        <form method="POST" action="{{ route('admin.cursos.modulos.aulas.rascunho', [$curso, $modulo, $aula]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-btn-secondary">Rascunho</button>
                                        </form>
                                    @endif
                                    @if($aula->status !== 'arquivado')
                                        <form method="POST" action="{{ route('admin.cursos.modulos.aulas.arquivar', [$curso, $modulo, $aula]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-btn-secondary">Arquivar</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.cursos.modulos.aulas.edit', [$curso, $modulo, $aula]) }}" class="ui-btn-secondary">Editar</a>
                                    <form method="POST" action="{{ route('admin.cursos.modulos.aulas.destroy', [$curso, $modulo, $aula]) }}" onsubmit="return confirm('Excluir esta aula?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ui-btn-danger">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-table-row">
                            <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhuma aula cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $aulas->links() }}</div>
    </x-dashboard.section-card>
</div>
@endsection
