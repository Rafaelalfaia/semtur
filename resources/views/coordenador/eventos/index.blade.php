@extends('console.layout')
@section('title', 'Eventos - Coordenador')
@section('page.title', 'Eventos')
@section('topbar.description', 'Gerencie eventos, edicoes e relacionamentos do modulo com o mesmo padrao visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Eventos</span>
  @can('eventos.manage')
    <a href="{{ route('coordenador.eventos.create') }}" class="ui-console-topbar-tab">Novo evento</a>
  @endcan
@endsection

@section('content')
@php
  use Illuminate\Support\Facades\Storage;

  $u = auth()->user();
  $canManage = $u->can('eventos.manage');
  $canSeeEdicoes = $u->canany(['eventos.manage', 'eventos.edicoes.manage']);
@endphp

<div class="ui-console-page">
  @if(session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <x-dashboard.page-header
    title="Eventos"
    subtitle="Acompanhe os eventos principais, status editoriais e acesso aos fluxos de edicoes em uma estrutura unificada do console."
  >
    @can('eventos.manage')
      <a href="{{ route('coordenador.eventos.create') }}" class="ui-btn-primary">Novo evento</a>
    @endcan
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por nome, cidade e status" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
      <input
        type="text"
        name="q"
        value="{{ $q ?? '' }}"
        placeholder="Buscar por nome ou cidade..."
        class="ui-form-control"
      >
      <select name="status" class="ui-form-select">
        <option value="">Todos os status</option>
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(($status ?? '') === $st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <button class="ui-btn-secondary">Filtrar</button>
    </form>
  </x-dashboard.section-card>

  <x-dashboard.section-card title="Lista de eventos" subtitle="Acesse edicoes, acompanhe o status e mantenha a organizacao do modulo" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left">Evento</th>
            <th class="px-3 py-3 text-left">Cidade</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-left">Edicoes</th>
            <th class="px-3 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($eventos as $e)
            <tr class="ui-table-row">
              <td class="px-3 py-3">
                <div class="flex items-center gap-3">
                  @if(!empty($e->perfil_path))
                    <img src="{{ Storage::disk('public')->url($e->perfil_path) }}" class="ui-event-thumb" alt="">
                  @else
                    <div class="ui-event-thumb ui-event-thumb-empty">EV</div>
                  @endif
                  <div class="min-w-0">
                    <div class="truncate font-semibold text-[var(--ui-text-title)]">{{ $e->nome }}</div>
                    @if(!empty($e->slug))
                      <div class="mt-1 text-xs text-[var(--ui-text-soft)]">/{{ $e->slug }}</div>
                    @endif
                  </div>
                </div>
              </td>
              <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ $e->cidade ?: '—' }}</td>
              <td class="px-3 py-3">
                @if(($e->status ?? 'rascunho') === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif(($e->status ?? 'rascunho') === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>
              <td class="px-3 py-3 font-semibold text-[var(--ui-text-title)]">{{ $e->edicoes()->count() }}</td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap items-center justify-end gap-2">
                  @if($canSeeEdicoes && Route::has('coordenador.eventos.edicoes.index'))
                    <a class="ui-btn-secondary" href="{{ route('coordenador.eventos.edicoes.index', $e) }}">Edicoes</a>
                  @endif
                  @can('eventos.manage')
                    <a class="ui-btn-secondary" href="{{ route('coordenador.eventos.edit', $e) }}">Editar</a>
                    <form method="POST" action="{{ route('coordenador.eventos.destroy', $e) }}" onsubmit="return confirm('Remover evento e edicoes?');">
                      @csrf
                      @method('DELETE')
                      <button class="ui-btn-danger">Excluir</button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhum evento encontrado.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $eventos->links() }}</div>
  </x-dashboard.section-card>
</div>
@endsection
