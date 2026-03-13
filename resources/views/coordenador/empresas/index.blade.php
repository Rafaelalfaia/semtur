@extends('console.layout')

@section('title', 'Empresas')
@section('page.title', 'Empresas')

@section('content')
@php
  $u = auth()->user();
  $canCreate = $u->can('empresas.create');
  $canEdit   = $u->can('empresas.update');
  $canDelete = $u->can('empresas.delete');

  $canRascunho = $u->can('empresas.rascunho');
  $canPublicar = $u->can('empresas.publicar');
  $canArquivar = $u->can('empresas.arquivar');

  // sem permissão de nenhuma ação, mostra só a listagem
  $canAnyStatusAction = $canRascunho || $canPublicar || $canArquivar;
  $canRecommend = $canEdit; // usar update como guarda para "destacar"
@endphp

<div class="mb-4 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
  <form method="GET" class="flex flex-col sm:flex-row gap-2">
    <input type="text" name="busca" value="{{ $busca }}"
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

  @can('empresas.create')
    <a href="{{ route('coordenador.empresas.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-white/10 hover:bg-white/20 px-4 py-2 text-slate-100">
      + Nova empresa
    </a>
  @endcan
</div>

@if($empresas->count() === 0)
  <div class="text-slate-300/80">Nenhuma empresa encontrada.</div>
@else
  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach($empresas as $empresa)
      @php
        // Normaliza status para decidir quais botões mostrar
        $statusEmp = $empresa->status ?? ($empresa->publicado ? 'publicado' : 'rascunho');
      @endphp

      <div class="rounded-2xl overflow-hidden bg-slate-900/60 border border-white/10">
        {{-- CAPA --}}
        <div class="h-28 w-full bg-slate-800">
          @if($empresa->capa_url)
            <img src="{{ $empresa->capa_url }}" alt="Capa de {{ $empresa->nome }}" class="w-full h-full object-cover">
          @endif
        </div>

        <div class="p-4">
          <div class="flex items-center gap-3">
            {{-- PERFIL --}}
            <div class="h-12 w-12 rounded-full overflow-hidden bg-slate-700 shrink-0">
              @if($empresa->perfil_url)
                <img src="{{ $empresa->perfil_url }}" alt="Logo {{ $empresa->nome }}" class="w-full h-full object-cover">
              @endif
            </div>

            <div class="min-w-0">
              <div class="font-semibold text-slate-100 truncate">{{ $empresa->nome }}</div>
              <div class="text-xs text-emerald-400/80 truncate">/{{ $empresa->slug }}</div>
            </div>

            <div class="ml-auto flex items-center gap-2">
              {{-- STATUS --}}
              @if($statusEmp === 'publicado')
                <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700/40">Publicado</span>
              @elseif($statusEmp === 'arquivado')
                <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/30 text-amber-200 border border-amber-700/40">Arquivado</span>
              @else
                <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700 text-slate-300 border border-slate-600">Rascunho</span>
              @endif

              {{-- DESTAQUE HOME --}}
              @if(($empresa->em_destaque ?? false) === true)
                <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/40 text-amber-100 border border-amber-700/40">Em destaque</span>
              @endif
            </div>
          </div>

          {{-- LINKS RÁPIDOS --}}
          <div class="mt-3 text-sm">
            @if($empresa->maps_url)
              <a href="{{ $empresa->maps_url }}" target="_blank" class="text-emerald-400 hover:underline">Ver no Maps</a>
            @endif
          </div>

          {{-- Editar/Excluir --}}
          @if($canEdit || $canDelete)
            <div class="mt-2 text-sm">
              @can('empresas.update')
                <a href="{{ route('coordenador.empresas.edit', $empresa) }}" class="text-emerald-300 hover:underline">Editar</a>
              @endcan

              @can('empresas.delete')
                <form method="POST" action="{{ route('coordenador.empresas.destroy', $empresa) }}" class="inline"
                      onsubmit="return confirm('Excluir esta empresa?');">
                  @csrf @method('DELETE')
                  <button class="text-rose-300 hover:underline ml-2">Excluir</button>
                </form>
              @endcan
            </div>
          @endif

          {{-- AÇÕES DE STATUS --}}
          @if($canAnyStatusAction)
            <div class="mt-3 flex flex-wrap gap-2">
              @can('empresas.rascunho')
                @if($statusEmp !== 'rascunho')
                  <form method="POST" action="{{ route('coordenador.empresas.rascunho', $empresa) }}">
                    @csrf @method('PATCH')
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Rascunho</button>
                  </form>
                @endif
              @endcan

              @can('empresas.publicar')
                @if($statusEmp !== 'publicado')
                  <form method="POST" action="{{ route('coordenador.empresas.publicar', $empresa) }}">
                    @csrf @method('PATCH')
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Publicar</button>
                  </form>
                @endif
              @endcan

              @can('empresas.arquivar')
                @if($statusEmp !== 'arquivado')
                  <form method="POST" action="{{ route('coordenador.empresas.arquivar', $empresa) }}">
                    @csrf @method('PATCH')
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Arquivar</button>
                  </form>
                @endif
              @endcan
            </div>
          @endif

          {{-- Destaque Home (global) --}}
          @if($canRecommend && (Route::has('coordenador.empresas.recomendar') && Route::has('coordenador.empresas.recomendar.remover')))
            <div class="mt-3 flex flex-wrap gap-2">
              <form method="POST" action="{{ route('coordenador.empresas.recomendar', $empresa) }}">
                @csrf
                <input type="hidden" name="contexto" value="global">
                    <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Recomendar (Home)</button>
                </form>

              <form method="POST" action="{{ route('coordenador.empresas.recomendar.remover', $empresa) }}">
                @csrf @method('DELETE')
                <input type="hidden" name="contexto" value="global">
                <button class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-sm">Remover recomendação</button>
            </form>
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">{{ $empresas->links() }}</div>
@endif
@endsection
