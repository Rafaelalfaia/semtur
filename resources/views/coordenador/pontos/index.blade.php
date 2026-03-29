@extends('console.layout')

@section('title', 'Pontos Turísticos')
@section('page.title', 'Pontos Turísticos')
@section('topbar.description', 'Gerencie pontos turísticos com filtros, status editoriais e ações de recomendação sem sair do shell principal.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Listagem</span>
  <a href="#pontos-filtros" class="ui-console-topbar-tab">Filtros</a>
  <a href="#pontos-lista" class="ui-console-topbar-tab">Lista</a>
@endsection

@section('content')
@php
  $u = auth()->user();
  $canCreate = $u->can('pontos.create');
  $canEdit = $u->can('pontos.update');
  $canDelete = $u->can('pontos.delete');
  $canRascunho = $u->can('pontos.rascunho');
  $canPublicar = $u->can('pontos.publicar');
  $canArquivar = $u->can('pontos.arquivar');
  $canAnyStatusAction = $canRascunho || $canPublicar || $canArquivar;
  $canRecommend = $canEdit;
@endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Pontos turísticos"
    subtitle="Listagem editorial com filtros, status e ações rápidas em um padrão compatível com o console."
  >
    <x-slot:actions>
      @if($canCreate)
        <a href="{{ route('coordenador.pontos.create') }}" class="ui-btn-primary">Novo ponto</a>
      @endif
    </x-slot:actions>
  </x-dashboard.page-header>

  <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
    <x-dashboard.section-card id="pontos-filtros" title="Filtros" subtitle="Busca e status editorial" class="h-fit">
      <form method="GET" class="space-y-4">
        <div>
          <label class="ui-form-label">Buscar</label>
          <input type="text" name="busca" value="{{ $busca ?? '' }}" class="ui-form-control" placeholder="Digite pelo menos 3 letras...">
        </div>

        <div>
          <label class="ui-form-label">Status</label>
          @php $statusAtual = $status ?? 'todos'; @endphp
          <select name="status" class="ui-form-select">
            <option value="todos" @selected($statusAtual==='todos')>Todos</option>
            <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
            <option value="rascunho" @selected($statusAtual==='rascunho')>Rascunho</option>
            <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <button class="ui-btn-primary">Filtrar</button>
          <a href="{{ route('coordenador.pontos.index') }}" class="ui-btn-secondary">Limpar</a>
        </div>
      </form>
    </x-dashboard.section-card>

    <x-dashboard.section-card id="pontos-lista" title="Lista de pontos" subtitle="Cards compactos com leitura visual e ações">
      @if(($pontos ?? collect())->count() === 0)
        <div class="ui-dashboard-empty">
          {{ mb_strlen(trim((string) ($busca ?? ''))) < 3
              ? 'Digite pelo menos 3 letras para pesquisar pontos.'
              : 'Nenhum ponto encontrado.' }}
        </div>
      @else
        <div class="ui-coord-entity-grid">
          @foreach($pontos as $ponto)
            @php
              $statusPonto = $ponto->status ?? ($ponto->publicado ? 'publicado' : 'rascunho');
              $statusClass = match ($statusPonto) {
                'publicado' => 'ui-badge-success',
                'arquivado' => 'ui-badge-warning',
                default => 'ui-badge-neutral',
              };
            @endphp

            <article class="ui-coord-entity-card">
              <div class="ui-coord-entity-cover">
                @if(!empty($ponto->capa_url))
                  <img src="{{ $ponto->capa_url }}" alt="Capa de {{ $ponto->nome }}" class="h-full w-full object-cover">
                @endif
              </div>

              <div class="p-4">
                <div class="flex items-center gap-3">
                  <div class="ui-coord-entity-thumb">
                    @if(!empty($ponto->perfil_url))
                      <img src="{{ $ponto->perfil_url }}" alt="Imagem {{ $ponto->nome }}" class="h-full w-full object-cover">
                    @endif
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="font-semibold text-[var(--ui-text-title)] truncate">{{ $ponto->nome }}</div>
                    @if(!empty($ponto->slug))
                      <div class="text-xs text-[var(--ui-primary)] truncate">/{{ $ponto->slug }}</div>
                    @endif
                  </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                  <span class="ui-badge {{ $statusClass }}">{{ ucfirst($statusPonto) }}</span>
                  @if(($ponto->em_destaque ?? false) === true)
                    <span class="ui-badge ui-badge-warning">Em destaque</span>
                  @endif
                </div>

                @if(!empty($ponto->maps_url))
                  <div class="mt-3 text-sm">
                    <a href="{{ $ponto->maps_url }}" target="_blank" class="text-[var(--ui-primary)] hover:underline">Ver no Maps</a>
                  </div>
                @endif

                @if($canEdit || $canDelete)
                  <div class="ui-coord-entity-actions mt-3">
                    @if($canEdit)
                      <a href="{{ route('coordenador.pontos.edit', $ponto) }}" class="ui-btn-secondary">Editar</a>
                    @endif

                    @if($canDelete)
                      <form method="POST" action="{{ route('coordenador.pontos.destroy', $ponto) }}" onsubmit="return confirm('Excluir este ponto?');">
                        @csrf
                        @method('DELETE')
                        <button class="ui-btn-danger">Excluir</button>
                      </form>
                    @endif
                  </div>
                @endif

                @if($canAnyStatusAction)
                  <div class="ui-coord-inline-actions mt-3">
                    @if($canRascunho && $statusPonto !== 'rascunho')
                      <form method="POST" action="{{ route('coordenador.pontos.rascunho', $ponto) }}">
                        @csrf @method('PATCH')
                        <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Rascunho</button>
                      </form>
                    @endif

                    @if($canPublicar && $statusPonto !== 'publicado')
                      <form method="POST" action="{{ route('coordenador.pontos.publicar', $ponto) }}">
                        @csrf @method('PATCH')
                        <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Publicar</button>
                      </form>
                    @endif

                    @if($canArquivar && $statusPonto !== 'arquivado')
                      <form method="POST" action="{{ route('coordenador.pontos.arquivar', $ponto) }}">
                        @csrf @method('PATCH')
                        <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Arquivar</button>
                      </form>
                    @endif
                  </div>
                @endif

                @if($canRecommend && Route::has('coordenador.pontos.recomendar') && Route::has('coordenador.pontos.recomendar.remover'))
                  <div class="ui-coord-inline-actions mt-3">
                    <form method="POST" action="{{ route('coordenador.pontos.recomendar', $ponto) }}">
                      @csrf
                      <input type="hidden" name="contexto" value="global">
                      <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Recomendar</button>
                    </form>

                    <form method="POST" action="{{ route('coordenador.pontos.recomendar.remover', $ponto) }}">
                      @csrf
                      @method('DELETE')
                      <input type="hidden" name="contexto" value="global">
                      <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Remover destaque</button>
                    </form>
                  </div>
                @endif
              </div>
            </article>
          @endforeach
        </div>

        <div class="mt-6">{{ $pontos->links() }}</div>
      @endif
    </x-dashboard.section-card>
  </div>
</div>
@endsection
