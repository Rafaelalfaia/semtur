@extends('console.layout')

@section('title', 'Traduções')

@section('topbar.description', 'Catálogo administrativo de chaves e valores traduzíveis, ainda isolado da leitura pública do site.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Listagem</span>
    <a href="#traducoes-filtros" class="ui-console-topbar-tab">Filtros</a>
    <a href="#traducoes-tabela" class="ui-console-topbar-tab">Tabela</a>
    <a href="{{ route('admin.traducoes.create') }}" class="ui-console-topbar-tab">Nova chave</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Traduções"
        subtitle="Cadastre chaves do sistema com texto base e valores por idioma, preparando a futura migração do frontend."
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.traducoes.sync') }}">
                @csrf
                <button type="submit" class="ui-btn-secondary">Sincronizar catálogo</button>
            </form>
            <a href="{{ route('admin.traducoes.export') }}" class="ui-btn-secondary">Exportar CSV</a>
            <a href="{{ route('admin.traducoes.create') }}" class="ui-btn-primary">Nova chave</a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <x-dashboard.section-card id="traducoes-filtros" title="Filtros" subtitle="Busque por chave, grupo ou texto base e controle quantos registros aparecem." class="h-fit">
            <form method="GET" class="space-y-4">
                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Buscar</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="ui-form-control text-sm" placeholder="home.hero.title">
                </div>

                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Grupo</label>
                    <select name="group" class="ui-form-control text-sm">
                        <option value="">Todos</option>
                        @foreach($groups as $groupOption)
                            <option value="{{ $groupOption }}" @selected(($group ?? '') === $groupOption)>{{ $groupOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Exibição</label>
                    <select name="per_page" class="ui-form-control text-sm">
                        @foreach($perPageOptions as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected(($perPage ?? '25') === $perPageOption)>
                                {{ $perPageOption === 'all' ? 'Mostrar tudo' : $perPageOption . ' por página' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Use a busca para reduzir a lista ou escolha mostrar tudo apenas quando precisar.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="ui-btn-primary">Filtrar</button>
                    <a href="{{ route('admin.traducoes.index') }}" class="ui-btn-secondary">Limpar</a>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.traducoes.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4 border-t border-[var(--ui-border)] pt-4">
                @csrf

                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Importar CSV</label>
                    <input type="file" name="arquivo" class="ui-form-control text-sm" accept=".csv,text/csv">
                    <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Colunas obrigatórias: `key`, `group`, `description`, `base_text` e uma coluna por código de idioma ativo.</p>
                    @error('arquivo')<p class="ui-form-error">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="ui-btn-secondary">Importar</button>
                    <a href="{{ route('admin.traducoes.export') }}" class="ui-btn-secondary">Baixar modelo</a>
                </div>
            </form>
        </x-dashboard.section-card>

        <x-dashboard.section-card id="traducoes-tabela" title="Lista de chaves" subtitle="Visão atual do catálogo administrativo de traduções." class="overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--ui-border)] px-4 py-3 text-sm">
                <div class="text-[var(--ui-text-soft)]">
                    Exibindo
                    <span class="font-semibold text-[var(--ui-text-title)]">{{ $translations->count() }}</span>
                    de
                    <span class="font-semibold text-[var(--ui-text-title)]">{{ $totalTranslations }}</span>
                    chave(s).
                </div>
                <div class="text-xs text-[var(--ui-text-soft)]">
                    @if(($perPage ?? '25') === 'all')
                        Modo atual: mostrar tudo
                    @else
                        Modo atual: {{ $perPage ?? '25' }} por página
                    @endif
                </div>
            </div>
            <div class="ui-table-shell">
                <table class="min-w-full text-sm">
                    <thead class="ui-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Chave</th>
                            <th class="px-4 py-3 text-left font-semibold">Grupo</th>
                            <th class="px-4 py-3 text-left font-semibold">Texto base</th>
                            <th class="px-4 py-3 text-left font-semibold">Valores</th>
                            <th class="px-4 py-3 text-left font-semibold">Estado</th>
                            <th class="px-4 py-3 text-right font-semibold">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--ui-border)]">
                        @forelse($translations as $translation)
                            <tr class="ui-table-row">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-[var(--ui-text)]">{{ $translation->key }}</div>
                                    @if($translation->description)
                                        <div class="text-xs text-[var(--ui-text-soft)]">{{ $translation->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $translation->group ?: '-' }}</td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ \Illuminate\Support\Str::limit($translation->base_text, 90) }}</td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $translation->values_count }} valor(es)</td>
                                <td class="px-4 py-3">
                                    @if($translation->is_active)
                                        <span class="ui-badge ui-badge-neutral">Ativa</span>
                                    @else
                                        <span class="ui-badge ui-badge-warning">Inativa</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.traducoes.edit', ['translation' => $translation]) }}" class="ui-btn-secondary">Editar</a>
                                        <form method="POST" action="{{ route('admin.traducoes.destroy', ['translation' => $translation]) }}" onsubmit="return confirm('Excluir esta chave de tradução?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-btn-danger">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-[var(--ui-text-soft)]">Nenhuma chave de tradução encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $translations->links() }}
            </div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection

