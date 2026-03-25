@extends('console.layout')
@section('title', 'Edicoes - '.$evento->nome)
@section('page.title', 'Edicoes')
@section('topbar.description', 'Gerencie as edicoes do evento, seus acessos internos e os modulos relacionados com o mesmo shell do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <span class="ui-console-topbar-tab is-active">Edicoes</span>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Edicoes"
    subtitle="Acompanhe anos, periodos, local e status das edicoes do evento."
  >
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('coordenador.eventos.edicoes.create', $evento) }}" class="ui-btn-primary">Nova edicao</a>
      <a href="{{ route('coordenador.eventos.index') }}" class="ui-btn-secondary">Voltar aos eventos</a>
    </div>
  </x-dashboard.page-header>

  <div class="mt-2 text-sm text-[var(--ui-text-soft)]">/eventos/{{ $evento->slug }}</div>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-5">{{ session('ok') }}</div>
  @endif

  <x-dashboard.section-card title="Lista de edicoes" subtitle="Acesse atrativos, galeria e atualize a estrutura de cada ano" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left">Ano</th>
            <th class="px-3 py-3 text-left">Periodo</th>
            <th class="px-3 py-3 text-left">Local</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($edicoes as $ed)
            <tr class="ui-table-row">
              <td class="px-3 py-3 font-semibold text-[var(--ui-text-title)]">{{ $ed->ano }}</td>
              <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ $ed->periodo }}</td>
              <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ $ed->local ?: '—' }}</td>
              <td class="px-3 py-3">
                @if($ed->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($ed->status === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap items-center justify-end gap-2">
                  <a class="ui-btn-secondary" href="{{ route('coordenador.edicoes.atrativos.index', $ed) }}">Atrativos</a>
                  <a class="ui-btn-secondary" href="{{ route('coordenador.edicoes.midias.index', $ed) }}">Galeria</a>
                  <a class="ui-btn-secondary" href="{{ route('coordenador.edicoes.edit', $ed) }}">Editar</a>
                  <form method="POST" action="{{ route('coordenador.edicoes.destroy', $ed) }}" onsubmit="return confirm('Remover edicao e sua galeria/atrativos?');">
                    @csrf
                    @method('DELETE')
                    <button class="ui-btn-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhuma edicao.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $edicoes->links() }}</div>
  </x-dashboard.section-card>
</div>
@endsection
