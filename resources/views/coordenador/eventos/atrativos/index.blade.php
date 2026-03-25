@extends('console.layout')
@section('title', 'Atrativos - '.$edicao->evento->nome.' ('.$edicao->ano.')')
@section('page.title', 'Atrativos')
@section('topbar.description', 'Gerencie atrativos da edicao mantendo o shell global, a leitura premium e a futura base de temas.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <a href="{{ route('coordenador.eventos.edicoes.index', $edicao->evento) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Atrativos</span>
@endsection

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Atrativos"
    subtitle="Controle os itens em destaque da edicao, com ordem, status e acesso rapido a edicao."
  >
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('coordenador.edicoes.atrativos.create', $edicao) }}" class="ui-btn-primary">Novo atrativo</a>
      <a href="{{ route('coordenador.eventos.edicoes.index', $edicao->evento) }}" class="ui-btn-secondary">Voltar as edicoes</a>
    </div>
  </x-dashboard.page-header>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-5">{{ session('ok') }}</div>
  @endif

  <x-dashboard.section-card title="Lista de atrativos" subtitle="Acompanhe a ordem visual, status e acesso de edicao" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left">Ordem</th>
            <th class="px-3 py-3 text-left">Atrativo</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($atrativos as $a)
            <tr class="ui-table-row">
              <td class="px-3 py-3 font-semibold text-[var(--ui-text-title)]">{{ $a->ordem }}</td>
              <td class="px-3 py-3">
                <div class="flex items-center gap-3">
                  @if($a->thumb_path)
                    <img src="{{ Storage::disk('public')->url($a->thumb_path) }}" class="ui-event-thumb" alt="">
                  @else
                    <div class="ui-event-thumb ui-event-thumb-empty">AT</div>
                  @endif
                  <div class="min-w-0">
                    <div class="font-semibold text-[var(--ui-text-title)]">{{ $a->nome }}</div>
                    <div class="text-xs text-[var(--ui-text-soft)]">/{{ $a->slug }}</div>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                @if($a->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($a->status === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap items-center justify-end gap-2">
                  <a class="ui-btn-secondary" href="{{ route('coordenador.atrativos.edit', $a) }}">Editar</a>
                  <form method="POST" action="{{ route('coordenador.atrativos.destroy', $a) }}" onsubmit="return confirm('Excluir atrativo?');">
                    @csrf
                    @method('DELETE')
                    <button class="ui-btn-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="4" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhum atrativo.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <form method="POST" action="{{ route('coordenador.edicoes.atrativos.reordenar', $edicao) }}" class="ui-event-inline-form mt-4">
      @csrf
      <input name="ordem" placeholder='{"12":1,"15":2}' class="ui-form-control">
      <button class="ui-btn-secondary">Aplicar ordem</button>
    </form>

    <div class="mt-4">{{ $atrativos->links() }}</div>
  </x-dashboard.section-card>
</div>
@endsection
