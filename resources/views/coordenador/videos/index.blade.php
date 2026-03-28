@extends('console.layout')

@section('title', 'Vídeos')
@section('page.title', 'Vídeos')
@section('topbar.description', 'Gerencie vídeos institucionais com o mesmo padrão visual, modo global e base semântica do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Vídeos</span>
  @if(auth()->user()->can('videos.create'))
    <a href="{{ route('coordenador.videos.create') }}" class="ui-console-topbar-tab">Novo vídeo</a>
  @endif
@endsection

@section('content')
@php
    use Illuminate\Support\Str;

    $u = auth()->user();

    $canCreate = $u->can('videos.create');
    $canEdit = $u->can('videos.update');
    $canDelete = $u->can('videos.delete');

    $canRascunho = $u->can('videos.rascunho');
    $canPublicar = $u->can('videos.publicar');
    $canArquivar = $u->can('videos.arquivar');
@endphp

<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Vídeos"
    subtitle="Cadastre e mantenha materiais audiovisuais com leitura mais limpa, visual premium e total compatibilidade com o shell do console."
  >
    @if($canCreate)
      <a href="{{ route('coordenador.videos.create') }}" class="ui-btn-primary">Novo vídeo</a>
    @endif
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por título e status do vídeo" class="ui-coord-dashboard-panel mt-5">
    <form method="GET" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      <div class="sm:col-span-2">
        <label class="ui-form-label">Busca</label>
        <input
          type="text"
          name="busca"
          value="{{ $busca ?? '' }}"
          placeholder="Título ou descrição..."
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

      <div class="sm:col-span-2 xl:col-span-3 flex flex-wrap gap-2 pt-1">
        <button class="ui-btn-primary">Filtrar</button>
        <a href="{{ route('coordenador.videos.index') }}" class="ui-btn-secondary">Limpar</a>
      </div>
    </form>
  </x-dashboard.section-card>

  @if(($videos ?? collect())->count() === 0)
    <x-dashboard.section-card title="Nenhum vídeo encontrado" subtitle="Cadastre vídeos oficiais para publicar materiais audiovisuais no site." class="ui-coord-dashboard-panel mt-5">
      @if($canCreate)
        <div class="mt-2">
          <a href="{{ route('coordenador.videos.create') }}" class="ui-btn-primary">Criar primeiro vídeo</a>
        </div>
      @endif
    </x-dashboard.section-card>
  @else
    <div class="ui-video-card-grid mt-5">
      @foreach($videos as $video)
        @php
            $statusVideo = $video->status ?? 'rascunho';
            $cover = $video->capa_url;
            $hostLink = parse_url((string) $video->link_acesso, PHP_URL_HOST) ?: 'Google Drive';
        @endphp

        <article class="ui-video-card">
          <div class="ui-video-card-media">
            @if($cover)
              <img src="{{ $cover }}" alt="{{ $video->titulo }}" class="h-full w-full object-cover">
            @else
              <div class="ui-video-card-media-fallback"></div>
            @endif

            <div class="ui-video-card-chips">
              <span class="ui-video-card-chip">Vídeo</span>
              <span class="ui-video-card-chip">Ordem {{ (int) ($video->ordem ?? 0) }}</span>
            </div>
          </div>

          <div class="p-4">
            <div class="flex items-start gap-3">
              <div class="min-w-0 flex-1">
                <h3 class="truncate text-lg font-semibold text-[var(--ui-text-title)]">{{ $video->titulo }}</h3>
                @if($video->slug)
                  <div class="mt-1 truncate text-xs text-[var(--ui-primary)]">/videos/{{ $video->slug }}</div>
                @endif
              </div>

              <div class="shrink-0">
                @if($statusVideo === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($statusVideo === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </div>
            </div>

            @if($video->descricao)
              <p class="mt-3 line-clamp-3 text-sm leading-6 text-[var(--ui-text-soft)]">
                {{ Str::limit(strip_tags((string) $video->descricao), 180) }}
              </p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">
              <div class="ui-video-card-stat">
                <div class="ui-video-card-stat-label">Link</div>
                <div class="mt-1 truncate text-sm font-semibold text-[var(--ui-text-title)]">{{ $hostLink }}</div>
              </div>

              <div class="ui-video-card-stat">
                <div class="ui-video-card-stat-label">Publicação</div>
                <div class="mt-1 text-sm font-semibold text-[var(--ui-text-title)]">{{ optional($video->published_at)->format('d/m/Y') ?: '—' }}</div>
              </div>
            </div>

            <div class="mt-4 ui-video-card-actions">
              @if($canEdit)
                <a href="{{ route('coordenador.videos.edit', $video) }}" class="ui-btn-secondary">Editar</a>
              @endif

              @if($statusVideo === 'publicado' && Route::has('site.videos.show'))
                <a href="{{ route('site.videos.show', $video->slug) }}" target="_blank" class="ui-btn-secondary">Ver no site</a>
              @endif

              <a href="{{ $video->link_acesso }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">Abrir link</a>
            </div>

            <div class="mt-4 ui-video-card-status-bar">
              @if($canPublicar && $statusVideo !== 'publicado')
                <form method="POST" action="{{ route('coordenador.videos.publicar', $video) }}">
                  @csrf
                  @method('PATCH')
                  <button class="ui-btn-primary">Publicar</button>
                </form>
              @endif

              @if($canRascunho && $statusVideo !== 'rascunho')
                <form method="POST" action="{{ route('coordenador.videos.rascunho', $video) }}">
                  @csrf
                  @method('PATCH')
                  <button class="ui-btn-secondary">Rascunho</button>
                </form>
              @endif

              @if($canArquivar && $statusVideo !== 'arquivado')
                <form method="POST" action="{{ route('coordenador.videos.arquivar', $video) }}">
                  @csrf
                  @method('PATCH')
                  <button class="ui-btn-secondary">Arquivar</button>
                </form>
              @endif

              @if($canDelete)
                <form method="POST" action="{{ route('coordenador.videos.destroy', $video) }}" onsubmit="return confirm('Excluir este vídeo?');">
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

    @if(method_exists($videos, 'links'))
      <div class="mt-6">
        {{ $videos->links() }}
      </div>
    @endif
  @endif
</div>
@endsection
