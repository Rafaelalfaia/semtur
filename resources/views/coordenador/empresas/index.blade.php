@extends('console.layout')

@section('title', 'Empresas')
@section('page.title', 'Empresas')
@section('topbar.description', 'Gerencie empresas com filtros, status editoriais e ações rápidas no mesmo padrão visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Listagem</span>
  <a href="#empresas-filtros" class="ui-console-topbar-tab">Filtros</a>
  <a href="#empresas-lista" class="ui-console-topbar-tab">Lista</a>
@endsection

@section('content')
@php
  $u = auth()->user();
  $canCreate = $u->can('empresas.create');
  $canEdit = $u->can('empresas.update');
  $canDelete = $u->can('empresas.delete');
  $canRascunho = $u->can('empresas.rascunho');
  $canPublicar = $u->can('empresas.publicar');
  $canArquivar = $u->can('empresas.arquivar');
  $canAnyStatusAction = $canRascunho || $canPublicar || $canArquivar;
  $canRecommend = $canEdit;
@endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Empresas"
    subtitle="Acompanhe o catálogo empresarial com filtros estáveis, cards compactos e ações administrativas."
  >
    <x-slot:actions>
      @if($canCreate)
        <a href="{{ route('coordenador.empresas.create') }}" class="ui-btn-primary">Nova empresa</a>
      @endif
    </x-slot:actions>
  </x-dashboard.page-header>

  <div class="mt-5 grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
    <x-dashboard.section-card id="empresas-filtros" title="Filtros" subtitle="Busca e status editorial" class="h-fit">
      <form method="GET" class="space-y-4">
        <div>
          <label class="ui-form-label">Buscar</label>
          <input type="text" name="busca" value="{{ $busca }}" class="ui-form-control" placeholder="Digite pelo menos 3 letras...">
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
          <a href="{{ route('coordenador.empresas.index') }}" class="ui-btn-secondary">Limpar</a>
        </div>
      </form>
    </x-dashboard.section-card>

    <x-dashboard.section-card id="empresas-lista" title="Lista de empresas" subtitle="Cards compactos com status, links e operacao">
      @if($empresas->count() === 0)
        <div class="ui-dashboard-empty">
          {{ mb_strlen(trim((string) $busca)) < 3
              ? 'Digite pelo menos 3 letras para pesquisar empresas.'
              : 'Nenhuma empresa encontrada.' }}
        </div>
      @else
        <div class="ui-coord-entity-grid">
          @foreach($empresas as $empresa)
            @php
              $statusEmp = $empresa->status ?? ($empresa->publicado ? 'publicado' : 'rascunho');
              $statusClass = match ($statusEmp) {
                'publicado' => 'ui-badge-success',
                'arquivado' => 'ui-badge-warning',
                default => 'ui-badge-neutral',
              };
            @endphp

            <article class="ui-coord-entity-card">
              <div class="ui-coord-entity-cover">
                @if($empresa->capa_url)
                  <img src="{{ $empresa->capa_url }}" alt="Capa de {{ $empresa->nome }}" class="h-full w-full object-cover">
                @endif
              </div>

              <div class="p-4">
                <div class="flex items-center gap-3">
                  <div class="ui-coord-entity-avatar">
                    @if($empresa->perfil_url)
                      <img src="{{ $empresa->perfil_url }}" alt="Logo {{ $empresa->nome }}" class="h-full w-full object-cover">
                    @endif
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="font-semibold text-[var(--ui-text-title)] truncate">{{ $empresa->nome }}</div>
                    <div class="text-xs text-[var(--ui-primary)] truncate">/{{ $empresa->slug }}</div>
                  </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                  <span class="ui-badge {{ $statusClass }}">{{ ucfirst($statusEmp) }}</span>
                  @if(($empresa->em_destaque ?? false) === true)
                    <span class="ui-badge ui-badge-warning">Em destaque</span>
                  @endif
                </div>

                @if($empresa->maps_url)
                  <div class="mt-3 text-sm">
                    <a href="{{ $empresa->maps_url }}" target="_blank" class="text-[var(--ui-primary)] hover:underline">Ver no Maps</a>
                  </div>
                @endif

                @if($canEdit || $canDelete)
                  <div class="ui-coord-entity-actions mt-3">
                    @if($canEdit)
                      <a href="{{ route('coordenador.empresas.edit', $empresa) }}" class="ui-btn-secondary">Editar</a>
                    @endif

                    @if($canDelete)
                      <form method="POST" action="{{ route('coordenador.empresas.destroy', $empresa) }}" onsubmit="return confirm('Excluir esta empresa?');">
                        @csrf
                        @method('DELETE')
                        <button class="ui-btn-danger">Excluir</button>
                      </form>
                    @endif
                  </div>
                @endif

                @if($canAnyStatusAction)
                  <div class="ui-coord-inline-actions mt-3">
                    @if($canRascunho && $statusEmp !== 'rascunho')
                      <form method="POST" action="{{ route('coordenador.empresas.rascunho', $empresa) }}">
                        @csrf @method('PATCH')
                        <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Rascunho</button>
                      </form>
                    @endif

                    @if($canPublicar && $statusEmp !== 'publicado')
                      <form method="POST" action="{{ route('coordenador.empresas.publicar', $empresa) }}">
                        @csrf @method('PATCH')
                        <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Publicar</button>
                      </form>
                    @endif

                    @if($canArquivar && $statusEmp !== 'arquivado')
                      <form method="POST" action="{{ route('coordenador.empresas.arquivar', $empresa) }}">
                        @csrf @method('PATCH')
                        <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Arquivar</button>
                      </form>
                    @endif
                  </div>
                @endif

                @if($canRecommend && Route::has('coordenador.empresas.recomendar') && Route::has('coordenador.empresas.recomendar.remover'))
                  <div class="ui-coord-inline-actions mt-3">
                    <form method="POST" action="{{ route('coordenador.empresas.recomendar', $empresa) }}">
                      @csrf
                      <input type="hidden" name="contexto" value="global">
                      <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Recomendar</button>
                    </form>

                    <form method="POST" action="{{ route('coordenador.empresas.recomendar.remover', $empresa) }}">
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

        <div class="mt-6">{{ $empresas->links() }}</div>
      @endif
    </x-dashboard.section-card>
  </div>
</div>
@endsection
