@extends('console.layout')

@section('title', 'Cursos')

@section('topbar.description', 'Gestão administrativa do catálogo de cursos, preparada para crescer em módulos e aulas dentro do mesmo dashboard.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Listagem</span>
    <a href="#cursos-filtros" class="ui-console-topbar-tab">Filtros</a>
    <a href="#cursos-tabela" class="ui-console-topbar-tab">Tabela</a>
    <a href="{{ route('admin.cursos.create') }}" class="ui-console-topbar-tab">Novo curso</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Cursos"
        subtitle="Cadastre os cursos base do painel antes de avançar para módulos e aulas."
    >
        <x-slot:actions>
            <a href="{{ route('admin.cursos.create') }}" class="ui-btn-primary">Novo curso</a>
        </x-slot:actions>
    </x-dashboard.page-header>

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
                    <a href="{{ route('admin.cursos.index') }}" class="ui-btn-secondary">Limpar</a>
                </div>
            </form>
        </x-dashboard.section-card>

        <x-dashboard.section-card id="cursos-tabela" title="Lista de cursos" subtitle="Visão atual do catálogo base da trilha de capacitação" class="overflow-hidden">
            <div class="ui-table-shell">
                <table class="min-w-full text-sm">
                    <thead class="ui-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Curso</th>
                            <th class="px-4 py-3 text-left font-semibold">Público</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Módulos</th>
                            <th class="px-4 py-3 text-left font-semibold">Ordem</th>
                            <th class="px-4 py-3 text-right font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--ui-border)]">
                        @forelse($cursos as $curso)
                            <tr class="ui-table-row">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($curso->capa_url)
                                            <img src="{{ $curso->capa_url }}" alt="" class="h-12 w-16 rounded-xl border border-[var(--ui-border)] object-cover">
                                        @else
                                            <div class="flex h-12 w-16 items-center justify-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-[11px] font-semibold text-[var(--ui-text-soft)]">
                                                CURSO
                                            </div>
                                        @endif

                                        <div>
                                            <div class="font-medium text-[var(--ui-text)]">{{ $curso->nome }}</div>
                                            <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $curso->descricao_curta ?: 'Sem descrição curta.' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="ui-badge ui-badge-neutral">{{ $curso->publico_alvo_label }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="ui-badge {{ $curso->status === 'publicado' ? 'ui-badge-success' : ($curso->status === 'arquivado' ? 'ui-badge-warning' : 'ui-badge-neutral') }}">
                                        {{ ucfirst($curso->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="inline-flex items-center gap-2 font-medium text-[var(--ui-accent)] hover:underline">
                                        <span>{{ number_format((int) $curso->modulos_count) }}</span>
                                        <span class="text-xs text-[var(--ui-text-soft)]">Gerenciar</span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ number_format((int) $curso->ordem) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if($curso->status !== 'publicado')
                                            <form method="POST" action="{{ route('admin.cursos.publicar', $curso) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ui-btn-secondary">Publicar</button>
                                            </form>
                                        @endif

                                        @if($curso->status !== 'rascunho')
                                            <form method="POST" action="{{ route('admin.cursos.rascunho', $curso) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ui-btn-secondary">Rascunho</button>
                                            </form>
                                        @endif

                                        @if($curso->status !== 'arquivado')
                                            <form method="POST" action="{{ route('admin.cursos.arquivar', $curso) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ui-btn-secondary">Arquivar</button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.cursos.edit', $curso) }}" class="ui-btn-secondary">Editar</a>
                                        <a href="{{ route('admin.cursos.modulos.index', $curso) }}" class="ui-btn-secondary">Módulos</a>

                                        <form method="POST" action="{{ route('admin.cursos.destroy', $curso) }}" onsubmit="return confirm('Excluir este curso?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-btn-danger">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-[var(--ui-text-soft)]">Nenhum curso encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $cursos->links() }}
            </div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection
