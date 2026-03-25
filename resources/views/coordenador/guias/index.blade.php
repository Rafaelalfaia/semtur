@extends('console.layout')

@section('title', 'Guias e Revistas')
@section('page.title', 'Guias e Revistas')
@section('topbar.description', 'Gerencie materiais institucionais com filtros, status editorial e o mesmo padrao visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Guias e Revistas</span>
  @if(auth()->user()->can('guias.create'))
    <a href="{{ route('coordenador.guias.create') }}" class="ui-console-topbar-tab">Novo material</a>
  @endif
@endsection

@section('content')
@php
    use Illuminate\Support\Str;

    $u = auth()->user();

    $canCreate   = $u->can('guias.create');
    $canEdit     = $u->can('guias.update');
    $canDelete   = $u->can('guias.delete');

    $canRascunho = $u->can('guias.rascunho');
    $canPublicar = $u->can('guias.publicar');
    $canArquivar = $u->can('guias.arquivar');
@endphp

<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Guias e revistas"
    subtitle="Cadastre e mantenha materiais oficiais com leitura mais limpa, visual premium e total compatibilidade com o shell do console."
  >
    @if($canCreate)
      <a href="{{ route('coordenador.guias.create') }}" class="ui-btn-primary">Novo material</a>
    @endif
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por nome, status e tipo de material" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <div class="sm:col-span-2">
        <label class="ui-form-label">Busca</label>
        <input
          type="text"
          name="busca"
          value="{{ $busca ?? '' }}"
          placeholder="Nome ou descricao..."
          class="ui-form-control"
        >
      </div>

      <div>
        <label class="ui-form-label">Status</label>
        <select name="status" class="ui-form-select">
          @php $statusAtual = $status ?? 'todos'; @endphp
          <option value="todos" @selected($statusAtual === 'todos')>Todos</option>
          @foreach(($statuses ?? []) as $itemStatus)
            <option value="{{ $itemStatus }}" @selected($statusAtual === $itemStatus)>{{ ucfirst($itemStatus) }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="ui-form-label">Tipo</label>
        <select name="tipo" class="ui-form-select">
          @php $tipoAtual = $tipo ?? 'todos'; @endphp
          <option value="todos" @selected($tipoAtual === 'todos')>Todos</option>
          @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
            <option value="{{ $tipoKey }}" @selected($tipoAtual === $tipoKey)>{{ $tipoLabel }}</option>
          @endforeach
        </select>
      </div>

      <div class="sm:col-span-2 xl:col-span-4 flex flex-wrap gap-2 pt-1">
        <button class="ui-btn-primary">Filtrar</button>
        <a href="{{ route('coordenador.guias.index') }}" class="ui-btn-secondary">Limpar</a>
      </div>
    </form>
  </x-dashboard.section-card>

  @if(($materiais ?? collect())->count() === 0)
    <x-dashboard.section-card title="Nenhum material encontrado" subtitle="Cadastre guias e revistas para publicar materiais oficiais no site." class="ui-coord-dashboard-panel mt-5">
      @if($canCreate)
        <div class="mt-2">
          <a href="{{ route('coordenador.guias.create') }}" class="ui-btn-primary">Criar primeiro material</a>
        </div>
      @endif
    </x-dashboard.section-card>
  @else
    <div class="ui-guide-card-grid mt-5">
      @foreach($materiais as $material)
        @php
          $statusMaterial = $material->status ?? 'rascunho';
          $cover = $material->capa_url;
          $hostLink = parse_url((string) $material->link_acesso, PHP_URL_HOST) ?: 'Google Drive';
        @endphp

        <article class="ui-guide-card">
          <div class="ui-guide-card-media">
            @if($cover)
              <img src="{{ $cover }}" alt="{{ $material->nome }}" class="h-full w-full object-cover">
            @else
              <div class="ui-guide-card-media-fallback"></div>
            @endif

            <div class="ui-guide-card-chips">
              <span class="ui-guide-card-chip">{{ $material->tipo_label }}</span>
              <span class="ui-guide-card-chip">Ordem {{ (int) ($material->ordem ?? 0) }}</span>
            </div>
          </div>

          <div class="p-4">
            <div class="flex items-start gap-3">
              <div class="min-w-0 flex-1">
                <h3 class="truncate text-lg font-semibold text-[var(--ui-text-title)]">{{ $material->nome }}</h3>
                @if($material->slug)
                  <div class="mt-1 truncate text-xs text-[var(--ui-primary)]">/guias/{{ $material->slug }}</div>
                @endif
              </div>

              <div class="shrink-0">
                @if($statusMaterial === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($statusMaterial === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </div>
            </div>

            @if($material->descricao)
              <p class="mt-3 line-clamp-3 text-sm leading-6 text-[var(--ui-text-soft)]">
                {{ Str::limit(strip_tags((string) $material->descricao), 180) }}
              </p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">
              <div class="ui-guide-card-stat">
                <div class="ui-guide-card-stat-label">Link</div>
                <div class="mt-1 truncate text-sm font-semibold text-[var(--ui-text-title)]">{{ $hostLink }}</div>
              </div>

              <div class="ui-guide-card-stat">
                <div class="ui-guide-card-stat-label">Publicacao</div>
                <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ optional($material->published_at)->format('d/m/Y') ?: '—' }}</div>
              </div>
            </div>

            <div class="mt-4 ui-guide-card-actions">
              @if($canEdit)
                <a href="{{ route('coordenador.guias.edit', $material) }}" class="ui-btn-secondary">Editar</a>
              @endif

              @if($statusMaterial === 'publicado' && Route::has('site.guias.show'))
                <a href="{{ route('site.guias.show', $material->slug) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
              @endif

              <a href="{{ $material->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Abrir link</a>
            </div>

            <div class="mt-4 ui-guide-card-status-bar">
              @if($canPublicar && $statusMaterial !== 'publicado')
                <form method="POST" action="{{ route('coordenador.guias.publicar', $material) }}">
                  @csrf
                  @method('PATCH')
                  <button class="ui-btn-primary">Publicar</button>
                </form>
              @endif

              @if($canRascunho && $statusMaterial !== 'rascunho')
                <form method="POST" action="{{ route('coordenador.guias.rascunho', $material) }}">
                  @csrf
                  @method('PATCH')
                  <button class="ui-btn-secondary">Rascunho</button>
                </form>
              @endif

              @if($canArquivar && $statusMaterial !== 'arquivado')
                <form method="POST" action="{{ route('coordenador.guias.arquivar', $material) }}">
                  @csrf
                  @method('PATCH')
                  <button class="ui-btn-secondary">Arquivar</button>
                </form>
              @endif

              @if($canDelete)
                <form method="POST" action="{{ route('coordenador.guias.destroy', $material) }}" onsubmit="return confirm('Excluir este material?');">
                  @csrf
                  @method('DELETE')
                  <button class="ui-btn-danger">Excluir</button>
                </form>
              @endif
            </div>
          </div>
        </article>
      @endforeach
    </div>

    @if(method_exists($materiais, 'links'))
      <div class="mt-6">
        {{ $materiais->links() }}
      </div>
    @endif
  @endif
</div>
@endsection
