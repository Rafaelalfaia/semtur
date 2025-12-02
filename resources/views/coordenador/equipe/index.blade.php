@extends('console.layout')

@section('title','Equipe')
@section('page.title','Equipe SEMTUR')

@section('content')
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
      {{ session('ok') }}
    </div>
  @endif

  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex w-full max-w-xl items-center gap-2">
      <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nome/cargo..."
             class="w-full rounded-lg bg-white/5 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/50"/>
      <button class="rounded-lg bg-white/10 px-3 py-2 hover:bg-white/20">Buscar</button>
      @if($q) <a href="{{ route('coordenador.equipe.index') }}" class="rounded-lg bg-white/5 px-3 py-2">Limpar</a> @endif
    </form>
    <a href="{{ route('coordenador.equipe.create') }}"
       class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 font-medium hover:bg-emerald-500">
      + Novo membro
    </a>
  </div>

  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @forelse($itens as $m)
      <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4">
        <div class="flex items-start gap-3">
          <img src="{{ $m->foto_url ?? asset('imagens/avatar.png') }}" class="h-14 w-14 rounded-lg object-cover bg-white/5" alt="">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <div class="font-semibold truncate">{{ $m->nome }}</div>
              <span class="rounded-full px-2 py-0.5 text-xs
                {{ $m->status==='publicado'?'bg-emerald-500/10 text-emerald-300':'bg-white/10 text-slate-300' }}">
                {{ ucfirst($m->status) }}
              </span>
            </div>
            <div class="text-sm text-slate-400 truncate">{{ $m->cargo }}</div>
            @if($m->resumo)
              <div class="mt-1 line-clamp-2 text-sm text-slate-300">{{ $m->resumo }}</div>
            @endif
          </div>
        </div>

        <div class="mt-3 flex items-center justify-between text-sm text-slate-300">
          <div>Ordem: <span class="font-medium">{{ $m->ordem }}</span></div>
          <div class="flex gap-2">
            <a href="{{ route('coordenador.equipe.edit',$m) }}" class="rounded-md bg-white/10 px-3 py-1 hover:bg-white/20">Editar</a>
            <form method="POST" action="{{ route('coordenador.equipe.destroy',$m) }}"
                  onsubmit="return confirm('Remover este membro?')">
              @csrf @method('DELETE')
              <button class="rounded-md bg-red-500/10 px-3 py-1 text-red-200 hover:bg-red-500/20">Apagar</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-xl border border-white/10 bg-[#0F1412] p-6 text-slate-300">
        Nenhum membro cadastrado.
      </div>
    @endforelse
  </div>

  <div class="mt-6">{{ $itens->withQueryString()->links() }}</div>
@endsection
