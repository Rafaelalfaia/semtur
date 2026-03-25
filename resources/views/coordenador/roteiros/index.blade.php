@extends('console.layout')

@section('title', 'Roteiros')
@section('page.title', 'Roteiros')
@section('topbar.description', 'Gerencie roteiros por duracao, perfil e status editorial no mesmo padrao visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Roteiros</span>
  @can('roteiros.create')
    <a href="{{ route('coordenador.roteiros.create') }}" class="ui-console-topbar-tab">Novo roteiro</a>
  @endcan
@endsection

@section('content')
@php
    $u = auth()->user();

    $canCreate   = $u->can('roteiros.create');
    $canEdit     = $u->can('roteiros.update');
    $canDelete   = $u->can('roteiros.delete');

    $canRascunho = $u->can('roteiros.rascunho');
    $canPublicar = $u->can('roteiros.publicar');
    $canArquivar = $u->can('roteiros.arquivar');

    $canAnyStatusAction = $canRascunho || $canPublicar || $canArquivar;
@endphp

<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Roteiros"
    subtitle="Organize experiencias por duracao e perfil, mantendo a curadoria editorial alinhada ao novo console."
  >
    @can('roteiros.create')
      <a href="{{ route('coordenador.roteiros.create') }}" class="ui-btn-primary">Novo roteiro</a>
    @endcan
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Refine por busca, status, duracao e perfil" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
      <div class="sm:col-span-2">
        <label class="ui-form-label">Busca</label>
        <input
          type="text"
          name="busca"
          value="{{ $busca ?? '' }}"
          placeholder="Titulo, resumo ou descricao..."
          class="ui-form-control"
        >
      </div>

      <div>
        <label class="ui-form-label">Status</label>
        <select name="status" class="ui-form-select">
          @php $statusAtual = $status ?? 'todos'; @endphp
          <option value="todos" @selected($statusAtual === 'todos')>Todos</option>
          <option value="rascunho" @selected($statusAtual === 'rascunho')>Rascunho</option>
          <option value="publicado" @selected($statusAtual === 'publicado')>Publicado</option>
          <option value="arquivado" @selected($statusAtual === 'arquivado')>Arquivado</option>
        </select>
      </div>

      <div>
        <label class="ui-form-label">Duracao</label>
        <select name="duracao" class="ui-form-select">
          <option value="todos" @selected(($duracao ?? 'todos') === 'todos')>Todas</option>
          @foreach(($duracoes ?? []) as $key => $label)
            <option value="{{ $key }}" @selected(($duracao ?? 'todos') === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="ui-form-label">Perfil</label>
        <select name="perfil" class="ui-form-select">
          <option value="todos" @selected(($perfil ?? 'todos') === 'todos')>Todos</option>
          @foreach(($perfis ?? []) as $key => $label)
            <option value="{{ $key }}" @selected(($perfil ?? 'todos') === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="sm:col-span-2 xl:col-span-5 flex flex-wrap gap-2 pt-1">
        <button class="ui-btn-primary">Filtrar</button>
        <a href="{{ route('coordenador.roteiros.index') }}" class="ui-btn-secondary">Limpar</a>
      </div>
    </form>
  </x-dashboard.section-card>

  @if(($roteiros ?? collect())->count() === 0)
    <x-dashboard.section-card title="Nenhum roteiro encontrado" subtitle="Crie roteiros por duracao e perfil para organizar a experiencia do visitante." class="ui-coord-dashboard-panel mt-5">
      @can('roteiros.create')
        <div class="mt-2">
          <a href="{{ route('coordenador.roteiros.create') }}" class="ui-btn-primary">Criar primeiro roteiro</a>
        </div>
      @endcan
    </x-dashboard.section-card>
  @else
    <div class="ui-roteiro-card-grid mt-5">
      @foreach($roteiros as $roteiro)
        @php
          $statusRoteiro = $roteiro->status ?? 'rascunho';
          $cover = $roteiro->capa_url;
        @endphp

        <article class="ui-roteiro-card">
          <div class="ui-roteiro-card-media">
            @if($cover)
              <img src="{{ $cover }}" alt="{{ $roteiro->titulo }}" class="h-full w-full object-cover">
            @else
              <div class="ui-roteiro-card-media-fallback"></div>
            @endif

            <div class="ui-roteiro-card-badges">
              <span class="ui-roteiro-card-chip">{{ $roteiro->duracao_label }}</span>
              <span class="ui-roteiro-card-chip">{{ $roteiro->perfil_label }}</span>
            </div>
          </div>

          <div class="p-4">
            <div class="flex items-start gap-3">
              <div class="min-w-0 flex-1">
                <h3 class="truncate text-lg font-semibold text-[var(--ui-text-title)]">{{ $roteiro->titulo }}</h3>
                @if($roteiro->slug)
                  <div class="mt-1 truncate text-xs text-[var(--ui-primary)]">/roteiros/{{ $roteiro->slug }}</div>
                @endif
              </div>

              <div class="shrink-0">
                @if($statusRoteiro === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($statusRoteiro === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </div>
            </div>

            @if($roteiro->resumo)
              <p class="mt-3 line-clamp-3 text-sm leading-6 text-[var(--ui-text-soft)]">
                {{ \Illuminate\Support\Str::limit($roteiro->resumo, 180) }}
              </p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">
              <div class="ui-roteiro-card-stat">
                <div class="ui-roteiro-card-stat-label">Etapas</div>
                <div class="ui-roteiro-card-stat-value">{{ (int) ($roteiro->etapas_count ?? 0) }}</div>
              </div>

              <div class="ui-roteiro-card-stat">
                <div class="ui-roteiro-card-stat-label">Empresas</div>
                <div class="ui-roteiro-card-stat-value">{{ (int) ($roteiro->empresas_sugestao_count ?? 0) }}</div>
              </div>
            </div>

            <div class="mt-4 ui-roteiro-card-actions">
              @can('roteiros.update')
                <a href="{{ route('coordenador.roteiros.edit', $roteiro) }}" class="ui-btn-secondary">Editar</a>
              @endcan

              @if($statusRoteiro === 'publicado' && Route::has('site.roteiros.show'))
                <a href="{{ route('site.roteiros.show', $roteiro->slug) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
              @endif

              @can('roteiros.delete')
                <form method="POST" action="{{ route('coordenador.roteiros.destroy', $roteiro) }}" onsubmit="return confirm('Excluir este roteiro?');">
                  @csrf
                  @method('DELETE')
                  <button class="ui-btn-danger">Excluir</button>
                </form>
              @endcan
            </div>

            @if($canAnyStatusAction)
              <div class="mt-3 ui-roteiro-card-status-bar">
                @can('roteiros.rascunho')
                  @if($statusRoteiro !== 'rascunho')
                    <form method="POST" action="{{ route('coordenador.roteiros.rascunho', $roteiro) }}">
                      @csrf
                      @method('PATCH')
                      <button class="ui-btn-secondary">Mover para rascunho</button>
                    </form>
                  @endif
                @endcan

                @can('roteiros.publicar')
                  @if($statusRoteiro !== 'publicado')
                    <form method="POST" action="{{ route('coordenador.roteiros.publicar', $roteiro) }}">
                      @csrf
                      @method('PATCH')
                      <button class="ui-btn-secondary">Publicar</button>
                    </form>
                  @endif
                @endcan

                @can('roteiros.arquivar')
                  @if($statusRoteiro !== 'arquivado')
                    <form method="POST" action="{{ route('coordenador.roteiros.arquivar', $roteiro) }}">
                      @csrf
                      @method('PATCH')
                      <button class="ui-btn-secondary">Arquivar</button>
                    </form>
                  @endif
                @endcan
              </div>
            @endif

            @if($roteiro->published_at)
              <div class="mt-3 text-xs text-[var(--ui-text-subtle)]">
                Publicado em {{ optional($roteiro->published_at)->format('d/m/Y \à\s H:i') }}
              </div>
            @endif
          </div>
        </article>
      @endforeach
    </div>

    <div class="mt-6">
      {{ $roteiros->links() }}
    </div>
  @endif
</div>
@endsection
