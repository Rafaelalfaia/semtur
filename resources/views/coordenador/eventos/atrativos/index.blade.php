@extends('console.layout')
@section('title','Atrativos — '.$edicao->evento->nome.' ('.$edicao->ano.')')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Atrativos — {{ $edicao->evento->nome }} ({{ $edicao->ano }})</h1>
      <a href="{{ route('coordenador.eventos.edicoes.index',$edicao->evento) }}" class="text-sm text-neutral-400 hover:underline">← Voltar às edições</a>
    </div>
    <a href="{{ route('coordenador.edicoes.atrativos.create',$edicao) }}" class="inline-flex items-center rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">
      + Novo atrativo
    </a>
  </div>

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">{{ session('ok') }}</div>
  @endif

  <div class="overflow-x-auto rounded-xl border border-white/5">
    <table class="w-full text-sm">
      <thead class="bg-neutral-900/60">
        <tr class="text-left">
          <th class="px-3 py-2">Ordem</th>
          <th class="px-3 py-2">Atrativo</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2 text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($atrativos as $a)
          <tr class="border-t border-white/5">
            <td class="px-3 py-2 w-24">{{ $a->ordem }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-3">
                @if($a->thumb_path)
                  <img src="{{ Storage::disk('public')->url($a->thumb_path) }}" class="w-12 h-12 rounded object-cover" alt="">
                @endif
                <div>
                  <div class="font-medium">{{ $a->nome }}</div>
                  <div class="text-xs text-neutral-400">/{{ $a->slug }}</div>
                </div>
              </div>
            </td>
            <td class="px-3 py-2"><span class="px-2 py-1 rounded text-xs bg-neutral-800 border border-neutral-700">{{ $a->status }}</span></td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2 justify-end">
                <a class="px-3 py-1 rounded border border-neutral-700 hover:bg-neutral-800" href="{{ route('coordenador.atrativos.edit',$a) }}">Editar</a>
                <form method="POST" action="{{ route('coordenador.atrativos.destroy',$a) }}" onsubmit="return confirm('Excluir atrativo?');">
                  @csrf @method('DELETE')
                  <button class="px-3 py-1 rounded border border-red-700 text-red-300 hover:bg-red-900/20">Excluir</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-3 py-10 text-center text-neutral-400">Nenhum atrativo.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <form method="POST" action="{{ route('coordenador.edicoes.atrativos.reordenar',$edicao) }}" class="flex items-center gap-3">
    @csrf
    {{-- exemplo simples: campo JSON {id:nova_ordem,...} para ordenar rapidamente --}}
    <input name="ordem" placeholder='{"12":1,"15":2}' class="flex-1 rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    <button class="rounded bg-neutral-800 border border-neutral-700 px-3 py-2">Aplicar ordem</button>
  </form>

  <div>{{ $atrativos->links() }}</div>
</div>
@endsection
