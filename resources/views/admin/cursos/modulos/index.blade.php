@extends('console.layout')

@section('title', 'Módulos - '.$curso->nome)

@section('topbar.description', 'Gestão dos módulos vinculados ao curso atual, mantendo a hierarquia inteira dentro do dashboard.')

@section('topbar.nav')
    <a href="{{ route('admin.cursos.index') }}" class="ui-console-topbar-tab">Cursos</a>
    <a href="{{ route('admin.cursos.edit', $curso) }}" class="ui-console-topbar-tab">{{ \Illuminate\Support\Str::limit($curso->nome, 28) }}</a>
    <span class="ui-console-topbar-tab is-active">Módulos</span>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Módulos"
        subtitle="Cadastre os módulos do curso e prepare a estrutura para a próxima etapa de aulas."
    >
        <x-slot:actions>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.cursos.modulos.create', $curso) }}" class="ui-btn-primary">Novo módulo</a>
                <a href="{{ route('admin.cursos.edit', $curso) }}" class="ui-btn-secondary">Voltar ao curso</a>
            </div>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-2 text-sm text-[var(--ui-text-soft)]">/cursos/{{ $curso->slug }}</div>

    <x-dashboard.section-card title="Lista de módulos" subtitle="Cada módulo organizará o conjunto de aulas relacionado a este curso." class="mt-5">
        <div class="ui-table-shell">
            <table class="min-w-full text-sm">
                <thead class="ui-table-head">
                    <tr>
                        <th class="px-3 py-3 text-left">Módulo</th>
                        <th class="px-3 py-3 text-left">Status</th>
                        <th class="px-3 py-3 text-left">Aulas</th>
                        <th class="px-3 py-3 text-left">Ordem</th>
                        <th class="px-3 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modulos as $modulo)
                        <tr class="ui-table-row">
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    @if($modulo->capa_url)
                                        <img src="{{ $modulo->capa_url }}" alt="" class="h-12 w-16 rounded-xl border border-[var(--ui-border)] object-cover">
                                    @else
                                        <div class="flex h-12 w-16 items-center justify-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-[11px] font-semibold text-[var(--ui-text-soft)]">
                                            MÓDULO
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-[var(--ui-text-title)]">{{ $modulo->nome }}</div>
                                        <div class="mt-1 text-xs text-[var(--ui-text-soft)]">/{{ $modulo->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="ui-badge {{ $modulo->status === 'publicado' ? 'ui-badge-success' : ($modulo->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                                    {{ ucfirst($modulo->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="inline-flex items-center gap-2 font-medium text-[var(--ui-accent)] hover:underline">
                                    <span>{{ number_format((int) $modulo->aulas_count) }}</span>
                                    <span class="text-xs text-[var(--ui-text-soft)]">Gerenciar</span>
                                </a>
                            </td>
                            <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ number_format((int) $modulo->ordem) }}</td>
                            <td class="px-3 py-3">
                                <div class="flex min-w-[14rem] flex-wrap items-center justify-end gap-2">
                                    @if($modulo->status !== 'publicado')
                                        <form method="POST" action="{{ route('admin.cursos.modulos.publicar', [$curso, $modulo]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-btn-secondary">Publicar</button>
                                        </form>
                                    @endif
                                    @if($modulo->status !== 'rascunho')
                                        <form method="POST" action="{{ route('admin.cursos.modulos.rascunho', [$curso, $modulo]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-btn-secondary">Rascunho</button>
                                        </form>
                                    @endif
                                    @if($modulo->status !== 'arquivado')
                                        <form method="POST" action="{{ route('admin.cursos.modulos.arquivar', [$curso, $modulo]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="ui-btn-secondary">Arquivar</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.cursos.modulos.edit', [$curso, $modulo]) }}" class="ui-btn-secondary">Editar</a>
                                    <a href="{{ route('admin.cursos.modulos.aulas.index', [$curso, $modulo]) }}" class="ui-btn-secondary">Aulas</a>
                                    <form method="POST" action="{{ route('admin.cursos.modulos.destroy', [$curso, $modulo]) }}" onsubmit="return confirm('Excluir este módulo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ui-btn-danger">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="ui-table-row">
                            <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhum módulo cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $modulos->links() }}</div>
    </x-dashboard.section-card>
</div>
@endsection
