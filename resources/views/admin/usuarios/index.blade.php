@extends('console.layout')

@section('title', 'Usuarios')

@section('topbar.description', 'Gestao administrativa de usuarios, papeis e filtros do console.')

@section('topbar.nav')
    <span class="ui-console-topbar-tab is-active">Listagem</span>
    <a href="#usuarios-filtros" class="ui-console-topbar-tab">Filtros</a>
    <a href="#usuarios-tabela" class="ui-console-topbar-tab">Tabela</a>
    <a href="{{ route('admin.usuarios.create') }}" class="ui-console-topbar-tab">Novo usuario</a>
@endsection

@section('content')
<div class="ui-console-page">
    <x-dashboard.page-header
        title="Usuarios"
        subtitle="Gerencie contas do console com filtros rapidos, listagem estavel e acoes administrativas."
    >
        <x-slot:actions>
            <a href="{{ route('admin.usuarios.create') }}" class="ui-btn-primary">
                Novo usuario
            </a>
        </x-slot:actions>
    </x-dashboard.page-header>

    <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <x-dashboard.section-card
            id="usuarios-filtros"
            title="Filtros"
            subtitle="Refine a listagem por busca e papel"
            class="h-fit"
        >
            <form method="GET" class="space-y-4">
                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Buscar</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        class="ui-form-control text-sm"
                        placeholder="Nome, e-mail ou CPF"
                    >
                </div>

                <div>
                    <label class="ui-form-label text-xs font-semibold uppercase tracking-[0.12em]">Papel</label>
                    <select name="role" class="ui-form-select text-sm">
                        <option value="">Todos</option>
                        @foreach($roles as $id => $name)
                            <option value="{{ $name }}" @selected(($role ?? '') === $name)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button class="ui-btn-primary" type="submit">
                        Filtrar
                    </button>

                    <a href="{{ route('admin.usuarios.index') }}" class="ui-btn-secondary">
                        Limpar
                    </a>
                </div>
            </form>
        </x-dashboard.section-card>

        <x-dashboard.section-card
            id="usuarios-tabela"
            title="Lista de usuarios"
            subtitle="Visao atual das contas cadastradas no sistema"
        >
            <div class="ui-table-shell">
                <table class="min-w-full text-sm">
                    <thead class="ui-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Nome</th>
                            <th class="px-4 py-3 text-left font-semibold">CPF</th>
                            <th class="px-4 py-3 text-left font-semibold">E-mail</th>
                            <th class="px-4 py-3 text-left font-semibold">Papeis</th>
                            <th class="px-4 py-3 text-left font-semibold">Criado em</th>
                            <th class="px-4 py-3 text-right font-semibold">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--ui-border)]">
                        @forelse($users as $u)
                            <tr class="ui-table-row">
                                <td class="px-4 py-3 font-medium text-[var(--ui-text)]">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $u->cpf }}</td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $u->email ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php $rnames = $u->roles->pluck('name')->all(); @endphp
                                    @if($rnames)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($rnames as $rname)
                                                <span class="ui-badge ui-badge-neutral">{{ $rname }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="ui-faint">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ optional($u->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.usuarios.edit', $u) }}" class="ui-btn-secondary">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" onsubmit="return confirm('Excluir este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ui-btn-danger">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-[var(--ui-text-soft)]">
                                    Nenhum usuario encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </x-dashboard.section-card>
    </div>
</div>
@endsection
