@extends('console.layout')

@section('title', 'Idiomas')

@section('topbar.description', 'Base administrativa do catálogo de idiomas, preparada para substituir a configuração fixa com segurança.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Listagem</span>
    <a href="#idiomas-filtros" class="ui-console-topbar-tab">Filtros</a>
    <a href="#idiomas-tabela" class="ui-console-topbar-tab">Tabela</a>
    <a href="{{ route('admin.idiomas.create') }}" class="ui-console-topbar-tab">Novo idioma</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Idiomas"
        subtitle="Cadastre o idioma padrão do sistema e mantenha a base preparada para a futura camada de tradução."
    >
        <x-slot:actions>
            <a href="{{ route('admin.idiomas.create') }}" class="ui-btn-primary">Novo idioma</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <x-dashboard.section-card id="idiomas-filtros" title="Filtros" subtitle="Refine a listagem por nome, sigla ou código" class="h-fit">
            <form method="GET" class="space-y-4">
                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Buscar</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="ui-form-control text-sm" placeholder="Nome, sigla ou código">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="ui-btn-primary">Filtrar</button>
                    <a href="{{ route('admin.idiomas.index') }}" class="ui-btn-secondary">Limpar</a>
                </div>
            </form>
        </x-dashboard.section-card>

        <x-dashboard.section-card id="idiomas-tabela" title="Lista de idiomas" subtitle="Visão atual da base administrativa usada para a nova arquitetura" class="overflow-hidden">
            <div class="ui-table-shell">
                <table class="min-w-full text-sm">
                    <thead class="ui-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Idioma</th>
                            <th class="px-4 py-3 text-left font-semibold">Código</th>
                            <th class="px-4 py-3 text-left font-semibold">Metadados</th>
                            <th class="px-4 py-3 text-left font-semibold">Estado</th>
                            <th class="px-4 py-3 text-right font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--ui-border)]">
                        @forelse($idiomas as $idioma)
                            <tr class="ui-table-row">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($idioma->bandeira_url)
                                            <img src="{{ $idioma->bandeira_url }}" alt="" class="h-9 w-9 rounded-full border border-[var(--ui-border)] object-cover">
                                        @else
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--ui-border)] bg-[var(--ui-input-bg)] text-xs font-semibold text-[var(--ui-text-soft)]">
                                                {{ $idioma->sigla }}
                                            </div>
                                        @endif

                                        <div>
                                            <div class="font-medium text-[var(--ui-text)]">{{ $idioma->nome }}</div>
                                            <div class="text-xs text-[var(--ui-text-soft)]">{{ $idioma->sigla }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $idioma->codigo }}</td>
                                <td class="px-4 py-3 text-xs text-[var(--ui-text-soft)]">
                                    <div>{{ $idioma->html_lang ?: '-' }}</div>
                                    <div>{{ $idioma->hreflang ?: '-' }}</div>
                                    <div>{{ $idioma->og_locale ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($idioma->is_default)
                                            <span class="ui-badge ui-badge-success">Padrão</span>
                                        @endif

                                        @if($idioma->is_active)
                                            <span class="ui-badge ui-badge-neutral">Ativo</span>
                                        @else
                                            <span class="ui-badge ui-badge-warning">Inativo</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.idiomas.edit', $idioma) }}" class="ui-btn-secondary">Editar</a>
                                        <form method="POST" action="{{ route('admin.idiomas.destroy', $idioma) }}" onsubmit="return confirm('Excluir este idioma?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-btn-danger" @disabled($idioma->is_default)>Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-[var(--ui-text-soft)]">Nenhum idioma encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $idiomas->links() }}
            </div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection
