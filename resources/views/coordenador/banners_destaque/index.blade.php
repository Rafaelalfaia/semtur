@extends('console.layout')
@section('title','Banners em Destaque')
@section('page.title','Banners em Destaque')

@push('head')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php
  $u           = auth()->user();
  $canManage   = $u->can('banners_destaque.manage');
  $canToggle   = $u->can('banners_destaque.toggle') || $canManage;
  $canReorder  = $u->can('banners_destaque.reordenar') || $canManage;
  $showActions = $canManage || $canToggle; // decide se mostra a coluna Ações
@endphp

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-lg font-semibold">Banners em Destaque</h1>

    @can('banners_destaque.manage')
      <a href="{{ route('coordenador.banners-destaque.create') }}"
         class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-500">
        Novo
      </a>
    @endcan
  </div>

  <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-[1fr_200px_auto] gap-3">
    <input
      type="text"
      name="busca"
      value="{{ $busca ?? '' }}"
      placeholder="Buscar por título, subtítulo ou link…"
      class="rounded-lg border border-white/10 bg-white/5 text-slate-100 placeholder:text-slate-400 px-3 py-2"
    >
    @php $status = $status ?? 'todos'; @endphp
    <select name="status" class="rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
      <option value="todos"     @selected($status==='todos')>Todos</option>
      <option value="publicado" @selected($status==='publicado')>Publicado</option>
      <option value="rascunho"  @selected($status==='rascunho')>Rascunho</option>
    </select>
    <button class="px-4 py-2 rounded-lg bg-white/10 text-slate-100 hover:bg-white/15">Filtrar</button>
  </form>

  <div class="overflow-x-auto rounded-xl border border-white/10 bg-[#0F1412]">
    <table class="min-w-full text-sm">
      <thead class="text-slate-300">
        <tr class="border-b border-white/10">
          {{-- só aparece handle/coluna quando pode reordenar --}}
          @if($canReorder)
            <th class="px-3 py-2 w-10">#</th>
          @else
            <th class="px-3 py-2 w-10"></th>
          @endif
          <th class="px-3 py-2 w-28">Imagem</th>
          <th class="px-3 py-2">Título</th>
          <th class="px-3 py-2">Janela</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2">Ordem</th>
          @if($showActions)
            <th class="px-3 py-2 w-[300px]">Ações</th>
          @endif
        </tr>
      </thead>

      <tbody id="sortable" class="text-slate-100">
        @forelse($banners as $b)
          @php
            $thumb = $b->imagem_desktop_url ?? $b->imagem_mobile_url ?? null;
          @endphp

          <tr data-id="{{ $b->id }}"
              @if($canReorder) draggable="true" @endif
              class="border-t border-white/10 hover:bg-white/5">
            <td class="px-3 py-2 align-middle {{ $canReorder ? 'cursor-move' : 'opacity-30' }}">↕</td>

            <td class="px-3 py-2 align-middle">
              @if($thumb)
                <img
                  src="{{ $thumb }}" alt=""
                  class="h-12 w-20 object-cover rounded-md border border-white/10 bg-white/5"
                  onerror="this.closest('td').innerHTML='<div class=&quot;h-12 w-20 rounded-md border border-white/10 bg-white/5 grid place-items-center text-[10px] text-slate-400&quot;>sem imagem</div>'"
                >
              @else
                <div class="h-12 w-20 rounded-md border border-white/10 bg-white/5 grid place-items-center text-[10px] text-slate-400">
                  sem imagem
                </div>
              @endif
            </td>

            <td class="px-3 py-2 align-middle">
              <div class="font-medium">{{ $b->titulo ?: '—' }}</div>
              <div class="text-xs text-slate-400 line-clamp-1 max-w-[48ch]">{{ $b->subtitulo }}</div>
              @if($b->link_url)
                <a href="{{ $b->link_url }}" target="_blank" rel="noopener"
                   class="text-xs text-emerald-400 hover:text-emerald-300">
                   {{ \Illuminate\Support\Str::limit($b->link_url, 48) }}
                </a>
              @endif
            </td>

            <td class="px-3 py-2 align-middle text-xs text-slate-300">
              <div>Início: {{ optional($b->inicio_publicacao)->format('d/m/Y H:i') ?? '—' }}</div>
              <div>Fim: {{ optional($b->fim_publicacao)->format('d/m/Y H:i') ?? '—' }}</div>
            </td>

            <td class="px-3 py-2 align-middle">
              @if($b->status === 'publicado')
                <span class="inline-block px-2 py-1 text-xs rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">Publicado</span>
              @else
                <span class="inline-block px-2 py-1 text-xs rounded bg-white/10 text-slate-200 border border-white/20">Rascunho</span>
              @endif
            </td>

            <td class="px-3 py-2 align-middle">{{ $b->ordem }}</td>

            @if($showActions)
              <td class="px-3 py-2 align-middle">
                <div class="flex flex-wrap gap-2">
                  {{-- Publicar/Rascunhar --}}
                  @canany(['banners_destaque.toggle','banners_destaque.manage'])
                    <form method="post" action="{{ route('coordenador.banners-destaque.toggle',$b) }}">
                      @method('PUT') @csrf
                      <button class="px-3 py-1.5 text-xs rounded-lg bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 hover:bg-indigo-500/15">
                        {{ $b->status==='publicado' ? 'Rascunhar' : 'Publicar' }}
                      </button>
                    </form>
                  @endcanany

                  {{-- Editar --}}
                  @can('banners_destaque.manage')
                    <a href="{{ route('coordenador.banners-destaque.edit',$b) }}"
                       class="px-3 py-1.5 text-xs rounded-lg bg-amber-500/10 text-amber-300 border border-amber-500/20 hover:bg-amber-500/15">
                      Editar
                    </a>
                  @endcan

                  {{-- Excluir --}}
                  @can('banners_destaque.manage')
                    <form method="post" action="{{ route('coordenador.banners-destaque.destroy',$b) }}"
                          onsubmit="return confirm('Deseja remover este banner?');">
                      @method('DELETE') @csrf
                      <button class="px-3 py-1.5 text-xs rounded-lg bg-rose-500/10 text-rose-300 border border-rose-500/20 hover:bg-rose-500/15">
                        Excluir
                      </button>
                    </form>
                  @endcan
                </div>
              </td>
            @endif
          </tr>
        @empty
          <tr>
            <td colspan="{{ 6 + (int)$showActions }}" class="px-3 py-6 text-center text-slate-400">
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

  {{-- Drag & drop só se puder reordenar --}}
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
@endsection
