@extends('console.layout')
@section('title','Técnicos')
@section('page.title','Técnicos')
@section('topbar.description', 'Gestão de técnicos vinculados ao coordenador, com filtros, tabela estável e o mesmo padrão visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Listagem</span>
  <a href="#tecnicos-filtros" class="ui-console-topbar-tab">Filtros</a>
  <a href="#tecnicos-tabela" class="ui-console-topbar-tab">Tabela</a>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Técnicos"
    subtitle="Gerencie contas tecnicas com busca rapida e acoes administrativas compatíveis com o shell atual."
  >
    <x-slot:actions>
      <a href="{{ route('coordenador.tecnicos.create') }}" class="ui-btn-primary">Novo técnico</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-4">{{ session('ok') }}</div>
  @endif

  <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
    <x-dashboard.section-card id="tecnicos-filtros" title="Filtros" subtitle="Busca por nome, e-mail ou CPF" class="h-fit">
      <form method="GET" class="space-y-4">
        <div>
          <label class="ui-form-label">Buscar</label>
          <input type="text" name="q" value="{{ $q ?? '' }}" class="ui-form-control" placeholder="Digite pelo menos 3 letras...">
        </div>
        <button class="ui-btn-primary">Filtrar</button>
      </form>
    </x-dashboard.section-card>

    <x-dashboard.section-card id="tecnicos-tabela" title="Lista de técnicos" subtitle="Contas vinculadas ao coordenador">
      <div class="ui-table-shell">
        <table class="min-w-full text-sm">
          <thead class="ui-table-head">
            <tr>
              <th class="text-left font-medium px-4 py-3">Nome</th>
              <th class="text-left font-medium px-4 py-3">CPF</th>
              <th class="text-left font-medium px-4 py-3">E-mail</th>
              <th class="text-left font-medium px-4 py-3">Criado em</th>
              <th class="text-right font-medium px-4 py-3">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--ui-border)]">
            @forelse($users as $u)
              <tr class="ui-table-row">
                <td class="px-4 py-3 text-[var(--ui-text-title)] font-medium">{{ $u->name }}</td>
                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $u->cpf ?: '-' }}</td>
                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ $u->email ?: '-' }}</td>
                <td class="px-4 py-3 text-[var(--ui-text-soft)]">{{ optional($u->created_at)->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('coordenador.tecnicos.edit',$u) }}" class="ui-btn-secondary">Editar</a>
                    <form method="POST" action="{{ route('coordenador.tecnicos.destroy',$u) }}" onsubmit="return confirm('Excluir este técnico?');">
                      @csrf
                      @method('DELETE')
                      <button class="ui-btn-danger">Excluir</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-[var(--ui-text-soft)]">
                  {{ mb_strlen(trim((string) ($q ?? ''))) < 3
                      ? 'Digite pelo menos 3 letras para pesquisar técnicos.'
                      : 'Nenhum técnico encontrado.' }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">{{ $users->links() }}</div>
    </x-dashboard.section-card>
  </div>
</div>
@endsection
