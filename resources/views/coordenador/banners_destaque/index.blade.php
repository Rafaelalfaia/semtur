@extends('console.layout')
@section('title','Banners em Destaque')
@section('page.title','Banners em Destaque')
@section('topbar.description', 'Gerencie os banners principais da home com filtros, ordenacao e a mesma base visual do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Banners destaque</span>
  @can('banners_destaque.manage')
    <a href="{{ route('coordenador.banners-destaque.create') }}" class="ui-console-topbar-tab">Novo banner</a>
  @endcan
@endsection

@push('head')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php
  $u           = auth()->user();
  $canManage   = $u->can('banners_destaque.manage');
  $canToggle   = $u->can('banners_destaque.toggle') || $canManage;
  $canReorder  = $u->can('banners_destaque.reordenar') || $canManage;
  $showActions = $canManage || $canToggle;
  $status = $status ?? 'todos';
@endphp

<div class="ui-console-page">
  @if(session('ok'))
    <div class="ui-alert ui-alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <x-dashboard.page-header
    title="Banners em destaque"
    subtitle="Controle a vitrine principal da home com ordem, status e janelas de publicacao em um painel mais limpo e consistente."
  >
    @can('banners_destaque.manage')
      <a href="{{ route('coordenador.banners-destaque.create') }}" class="ui-btn-primary">Novo banner</a>
    @endcan
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Filtros" subtitle="Busque por titulo, subtitulo, link e status" class="ui-coord-dashboard-panel mt-5">
    <form method="get" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
      <input
        type="text"
        name="busca"
        value="{{ $busca ?? '' }}"
        placeholder="Buscar por titulo, subtitulo ou link..."
        class="ui-form-control"
      >
      <select name="status" class="ui-form-select">
        <option value="todos" @selected($status==='todos')>Todos</option>
        <option value="publicado" @selected($status==='publicado')>Publicado</option>
        <option value="rascunho" @selected($status==='rascunho')>Rascunho</option>
      </select>
      <button class="ui-btn-secondary">Filtrar</button>
    </form>
  </x-dashboard.section-card>

  <x-dashboard.section-card title="Lista de banners" subtitle="Ordene por arraste quando permitido e acompanhe o status de publicacao" class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left w-10">{{ $canReorder ? '#' : '' }}</th>
            <th class="px-3 py-3 text-left w-32">Midia</th>
            <th class="px-3 py-3 text-left">Titulo</th>
            <th class="px-3 py-3 text-left">Janela</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-left">Ordem</th>
            @if($showActions)
              <th class="px-3 py-3 text-left w-[320px]">Acoes</th>
            @endif
          </tr>
        </thead>
        <tbody id="sortable">
          @forelse($banners as $b)
            @php
              $thumb = $b->poster_desktop_url
                ?? $b->fallback_image_desktop_url
                ?? $b->imagem_desktop_url
                ?? $b->poster_mobile_url
                ?? $b->fallback_image_mobile_url
                ?? $b->imagem_mobile_url
                ?? null;
              $isVideo = $b->media_type === 'video';
            @endphp
            <tr
              data-id="{{ $b->id }}"
              @if($canReorder) draggable="true" @endif
              class="ui-table-row"
            >
              <td class="px-3 py-3 align-middle">
                <span class="ui-banner-highlight-handle {{ $canReorder ? 'is-draggable' : 'is-disabled' }}">↕</span>
              </td>

              <td class="px-3 py-3 align-middle">
                <div class="space-y-2">
                  @if($thumb)
                    <img
                      src="{{ $thumb }}"
                      alt=""
                      class="ui-banner-highlight-thumb"
                      onerror="this.closest('td').querySelector('[data-thumb-fallback]').classList.remove('hidden'); this.remove();"
                    >
                    <div data-thumb-fallback class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty hidden">sem midia</div>
                  @else
                    <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty">sem midia</div>
                  @endif

                  <div class="flex flex-wrap gap-2">
                    <span class="ui-badge {{ $isVideo ? 'ui-badge-neutral' : 'ui-badge-success' }}">
                      {{ $isVideo ? 'Video' : 'Imagem' }}
                    </span>
                    @if($isVideo && $b->video_valido)
                      <span class="ui-badge ui-badge-neutral">Hero em video</span>
                    @endif
                  </div>
                </div>
              </td>

              <td class="px-3 py-3 align-middle">
                <div class="font-semibold text-[var(--ui-text-title)]">{{ $b->titulo ?: '—' }}</div>
                <div class="mt-1 text-xs text-[var(--ui-text-soft)] line-clamp-1 max-w-[48ch]">{{ $b->subtitulo }}</div>
                @if($b->link_url)
                  <a href="{{ $b->link_url }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-medium text-[var(--ui-primary)] hover:opacity-80">
                    {{ \Illuminate\Support\Str::limit($b->link_url, 48) }}
                  </a>
                @endif
              </td>

              <td class="px-3 py-3 align-middle text-xs text-[var(--ui-text-soft)]">
                <div>Inicio: {{ optional($b->inicio_publicacao)->format('d/m/Y H:i') ?? '—' }}</div>
                <div>Fim: {{ optional($b->fim_publicacao)->format('d/m/Y H:i') ?? '—' }}</div>
              </td>

              <td class="px-3 py-3 align-middle">
                @if($b->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($b->status === 'arquivado')
                  <span class="ui-badge ui-badge-danger">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>

              <td class="px-3 py-3 align-middle font-semibold text-[var(--ui-text-title)]">{{ $b->ordem }}</td>

              @if($showActions)
                <td class="px-3 py-3 align-middle">
                  <div class="ui-banner-highlight-actions">
                    @canany(['banners_destaque.toggle','banners_destaque.manage'])
                      <form method="post" action="{{ route('coordenador.banners-destaque.toggle',$b) }}">
                        @method('PUT')
                        @csrf
                        <button class="ui-btn-secondary">
                          {{ $b->status==='publicado' ? 'Rascunhar' : 'Publicar' }}
                        </button>
                      </form>
                    @endcanany

                    @can('banners_destaque.manage')
                      <a href="{{ route('coordenador.banners-destaque.edit',$b) }}" class="ui-btn-secondary">Editar</a>
                    @endcan

                    @can('banners_destaque.manage')
                      <form method="post" action="{{ route('coordenador.banners-destaque.destroy',$b) }}" onsubmit="return confirm('Deseja remover este banner?');">
                        @method('DELETE')
                        @csrf
                        <button class="ui-btn-danger">Excluir</button>
                      </form>
                    @endcan
                  </div>
                </td>
              @endif
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="{{ 6 + (int)$showActions }}" class="px-4 py-10 text-center text-sm text-[var(--ui-text-soft)]">
                Nenhum banner encontrado.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $banners->links() }}
    </div>
  </x-dashboard.section-card>

  @if($canReorder)
    <script>
      (function(){
        const tbody = document.getElementById('sortable');
        if(!tbody) return;
        let dragEl;

        tbody.addEventListener('dragstart', e => {
          dragEl = e.target.closest('tr');
          e.dataTransfer.effectAllowed = 'move';
        });

        tbody.addEventListener('dragover', e => {
          e.preventDefault();
          const tr = e.target.closest('tr');
          if (!tr || tr === dragEl) return;
          const rect = tr.getBoundingClientRect();
          const next = (e.clientY - rect.top) / rect.height > 0.5;
          tbody.insertBefore(dragEl, next ? tr.nextSibling : tr);
        });

        tbody.addEventListener('drop', () => {
          const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => tr.dataset.id);
          fetch('{{ route('coordenador.banners-destaque.reordenar') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ ids })
          }).catch(()=> alert('Falha ao salvar ordem'));
        });
      })();
    </script>
  @endif
</div>
@endsection
