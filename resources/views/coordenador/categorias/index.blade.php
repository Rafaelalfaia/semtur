@extends('console.layout')
@section('title','Categorias — Coordenador')
@section('page.title','Categorias')

@section('content')
<div class="space-y-6">

  {{-- Filtros + Novo --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <form method="GET" class="flex items-center gap-2">
      <input
        type="text" name="busca" value="{{ $busca }}" placeholder="Buscar..."
        class="w-[220px] rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100 placeholder:text-slate-400"
      >
      @php $opts = ['todos'=>'Todos','rascunho'=>'Rascunho','publicado'=>'Publicado','arquivado'=>'Arquivado']; @endphp
      <select name="status"
              class="rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
        @foreach($opts as $k=>$v)
          <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
        @endforeach
      </select>
      <button class="px-3 py-2 rounded-lg bg-emerald-600 text-black font-semibold">Filtrar</button>
    </form>

    {{-- Mostrar o botão "Nova Categoria" somente para quem pode criar --}}
    @can('categorias.create')
      <a href="{{ route('coordenador.categorias.create') }}"
        class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-black font-semibold">
        + Nova Categoria
      </a>
    @endcan
  </div>

  {{-- Feedback --}}
  @if(session('ok'))
    <div class="rounded-lg border border-emerald-600/30 bg-emerald-500/10 text-emerald-300 px-3 py-2">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Lista --}}
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse($categorias as $c)
      @php
        $isDraft = $c->status === 'rascunho';
        $isPub   = $c->status === 'publicado';
        $isArch  = $c->status === 'arquivado';
      @endphp

      <div class="rounded-xl border border-white/5 bg-[#0F1412] p-4">
        <div class="flex items-start gap-3">
          <div class="h-12 w-12 rounded-lg overflow-hidden bg-white/5 flex items-center justify-center">
            @if($c->icone_url)
              <img src="{{ $c->icone_url }}" alt="Ícone {{ $c->nome }}" class="h-10 w-10 object-contain">
            @else
              <span class="text-xs text-slate-400">sem ícone</span>
            @endif
          </div>

          <div class="flex-1">
            <div class="font-semibold">{{ $c->nome }}</div>
            <div class="text-xs text-slate-400">/{{ $c->slug }}</div>
            <div class="mt-1 text-xs">
              <span class="rounded-full px-2 py-0.5
                {{ $isPub ? 'bg-emerald-500/15 text-emerald-300' :
                   ($isArch ? 'bg-rose-500/15 text-rose-300' : 'bg-white/10 text-slate-300') }}">
                {{ ucfirst($c->status) }}
              </span>
            </div>
          </div>
        </div>

        {{-- Ações de edição/remoção: respeitam permissões --}}
        <div class="mt-3 flex items-center gap-2">
          @can('categorias.update')
            <a href="{{ route('coordenador.categorias.edit',$c) }}"
               class="text-sm text-emerald-300 hover:text-emerald-200 hover:underline">Editar</a>
          @endcan

          @can('categorias.delete')
            <form method="POST" action="{{ route('coordenador.categorias.destroy',$c) }}"
                  onsubmit="return confirm('Remover categoria?');" class="inline">
              @csrf @method('DELETE')
              <button class="text-sm text-rose-300 hover:text-rose-200 hover:underline">Excluir</button>
            </form>
          @endcan
        </div>

        {{-- Ações de status: só mostra se puder, e desabilita quando já está nesse status --}}
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
          @can('categorias.rascunho')
            <form method="POST" action="{{ route('coordenador.categorias.rascunho',$c) }}">
              @csrf @method('PATCH')
              <button @disabled($isDraft)
                class="rounded bg-white/5 px-2 py-1 hover:bg-white/10 {{ $isDraft ? 'opacity-40 cursor-not-allowed' : '' }}">
                Rascunho
              </button>
            </form>
          @endcan

          @can('categorias.publicar')
            <form method="POST" action="{{ route('coordenador.categorias.publicar',$c) }}">
              @csrf @method('PATCH')
              <button @disabled($isPub)
                class="rounded bg-white/5 px-2 py-1 hover:bg-white/10 {{ $isPub ? 'opacity-40 cursor-not-allowed' : '' }}">
                Publicar
              </button>
            </form>
          @endcan

          @can('categorias.arquivar')
            <form method="POST" action="{{ route('coordenador.categorias.arquivar',$c) }}">
              @csrf @method('PATCH')
              <button @disabled($isArch)
                class="rounded bg-white/5 px-2 py-1 hover:bg-white/10 {{ $isArch ? 'opacity-40 cursor-not-allowed' : '' }}">
                Arquivar
              </button>
            </form>
          @endcan
        </div>
      </div>
    @empty
      <div class="text-slate-400">Nenhuma categoria encontrada.</div>
    @endforelse
  </div>

  <div>{{ $categorias->links() }}</div>
</div>
@endsection
