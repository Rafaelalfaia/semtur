@extends('console.layout')

@section('title', 'Pontos Turísticos')
@section('page.title', 'Pontos Turísticos')

@section('content')
@php
  $u = auth()->user();

  // Permissões de Pontos
  $canCreate   = $u->can('pontos.create');
  $canEdit     = $u->can('pontos.update');
  $canDelete   = $u->can('pontos.delete');

  $canRascunho = $u->can('pontos.rascunho');
  $canPublicar = $u->can('pontos.publicar');
  $canArquivar = $u->can('pontos.arquivar');

  $canAnyStatusAction = $canRascunho || $canPublicar || $canArquivar;
  $canRecommend = $canEdit; // usar update como guarda
@endphp

<div class="mb-4 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
  <form method="GET" class="flex flex-col sm:flex-row gap-2">
    <input type="text" name="busca" value="{{ $busca ?? '' }}"
           class="rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100 w-72"
           placeholder="Buscar por nome ou descrição…">

    <select name="status" class="rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      @php $statusAtual = $status ?? 'todos'; @endphp
      <option value="todos"     @selected($statusAtual==='todos')>Todos</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="rascunho"  @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>

    <button class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-white">Filtrar</button>
  </form>

  @can('pontos.create')
    <a href="{{ route('coordenador.pontos.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-white/10 hover:bg-white/20 px-4 py-2 text-slate-100">
      + Novo ponto
    </a>
  @endcan
</div>

@if(($pontos ?? collect())->count() === 0)
  <div class="text-slate-300/80">Nenhum ponto encontrado.</div>
@else
  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach($pontos as $ponto)
      @php
        // status normalizado
        $statusPonto = $ponto->status ?? ($ponto->publicado ? 'publicado' : 'rascunho');
      @endphp

      <div class="rounded-2xl overflow-hidden bg-slate-900/60 border border-white/10">
        {{-- CAPA --}}
        <div class="h-28 w-full bg-slate-800">
          @if(!empty($ponto->capa_url))
            <img src="{{ $ponto->capa_url }}" alt="Capa de {{ $ponto->nome }}" class="w-full h-full object-cover">
          @endif
        </div>

        <div class="p-4">
          <div class="flex items-center gap-3">
            {{-- THUMB opcional --}}
            <div class="h-12 w-12 rounded-lg overflow-hidden bg-slate-700 shrink-0">
              @if(!empty($ponto->perfil_url))
                <img src="{{ $ponto->perfil_url }}" alt="Imagem {{ $ponto->nome }}" class="w-full h-full object-cover">
              @endif
            </div>

            <div class="min-w-0">
              <div class="font-semibold text-slate-100 truncate">{{ $ponto->nome }}</div>
              @if(!empty($ponto->slug))
                <div class="text-xs text-emerald-400/80 truncate">/{{ $ponto->slug }}</div>
              @endif
            </div>

            <div class="ml-auto flex items-center gap-2">
              @if($statusPonto === 'publicado')
                <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700/40">Publicado</span>
              @elseif($statusPonto === 'arquivado')
                <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/30 text-amber-200 border border-amber-700/40">Arquivado</span>
              @else
                <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700 text-slate-300 border border-slate-600">Rascunho</span>
              @endif

              @if(($ponto->em_destaque ?? false) === true)
                <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/40 text-amber-100 border border-amber-700/40">Em destaque</span>
              @endif
            </div>
          </div>

          {{-- Link rápido opcional (ex.: Maps) --}}
          @if(!empty($ponto->maps_url))
            <div class="mt-3 text-sm">
              <a href="{{ $ponto->maps_url }}" target="_blank" class="text-emerald-400 hover:underline">Ver no Maps</a>
            </div>
          @endif

          {{-- Editar / Excluir --}}
          @if($canEdit || $canDelete)
            <div class="mt-2 text-sm">
              @can('pontos.update')
                <a href="{{ route('coordenador.pontos.edit', $ponto) }}" class="text-emerald-300 hover:underline">Editar</a>
              @endcan

              @can('pontos.delete')
                <form method="POST" action="{{ route('coordenador.pontos.destroy', $ponto) }}" class="inline"
                      onsubmit="return confirm('Excluir este ponto?');">
                  @csrf @method('DELETE')
                  <button class="text-rose-300 hover:underline ml-2">Excluir</button>
                </form>
              @endcan
            </div>
          @endif

          {{-- Ações de status --}}
          @if($canAnyStatusAction)
            <div class="mt-3 flex flex-wrap gap-2">
              @can('pontos.rascunho')
                @if($statusPonto !== 'rascunho')
                  <form method="POST" action="{{ route('coordenador.pontos.rascunho', $ponto) }}">
                    @csrf @method('PATCH')
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Rascunho</button>
                  </form>
                @endif
              @endcan

              @can('pontos.publicar')
                @if($statusPonto !== 'publicado')
                  <form method="POST" action="{{ route('coordenador.pontos.publicar', $ponto) }}">
                    @csrf @method('PATCH')
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Publicar</button>
                  </form>
                @endif
              @endcan

              @can('pontos.arquivar')
                @if($statusPonto !== 'arquivado')
                  <form method="POST" action="{{ route('coordenador.pontos.arquivar', $ponto) }}">
                    @csrf @method('PATCH')
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Arquivar</button>
                  </form>
                @endif
              @endcan
            </div>
          @endif

          {{-- Destaque na Home (se as rotas existirem) --}}
          @if($canRecommend && Route::has('coordenador.pontos.recomendar') && Route::has('coordenador.pontos.recomendar.remover'))
            <div class="mt-3 flex flex-wrap gap-2">
              <form method="POST" action="{{ route('coordenador.pontos.recomendar', $ponto) }}">
                @csrf
                <input type="hidden" name="contexto" value="global">
                <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Destacar (Home)</button>
              </form>

              <form method="POST" action="{{ route('coordenador.pontos.recomendar.remover', $ponto) }}">
                @csrf @method('DELETE')
                <input type="hidden" name="contexto" value="global">
                <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Remover destaque</button>
              </form>
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">{{ $pontos->links() }}</div>
@endif
@endsection
