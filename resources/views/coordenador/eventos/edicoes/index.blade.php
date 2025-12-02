@extends('console.layout')
@section('title','Edições — '.$evento->nome)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Edições — {{ $evento->nome }}</h1>
      <div class="text-sm text-neutral-400">/eventos/{{ $evento->slug }}</div>
    </div>
    <a href="{{ route('coordenador.eventos.edicoes.create',$evento) }}" class="inline-flex items-center rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">
      + Nova edição
    </a>
  </div>

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">{{ session('ok') }}</div>
  @endif

  <div class="overflow-x-auto rounded-xl border border-white/5">
    <table class="w-full text-sm">
      <thead class="bg-neutral-900/60">
        <tr class="text-left">
          <th class="px-3 py-2">Ano</th>
          <th class="px-3 py-2">Período</th>
          <th class="px-3 py-2">Local</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2 text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($edicoes as $ed)
          <tr class="border-t border-white/5">
            <td class="px-3 py-2 font-medium">{{ $ed->ano }}</td>
            <td class="px-3 py-2">{{ $ed->periodo }}</td>
            <td class="px-3 py-2">{{ $ed->local ?? '—' }}</td>
            <td class="px-3 py-2"><span class="px-2 py-1 rounded text-xs bg-neutral-800 border border-neutral-700">{{ $ed->status }}</span></td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2 justify-end">
                <a class="px-3 py-1 rounded border border-neutral-700 hover:bg-neutral-800" href="{{ route('coordenador.edicoes.atrativos.index',$ed) }}">Atrativos</a>
                <a class="px-3 py-1 rounded border border-neutral-700 hover:bg-neutral-800" href="{{ route('coordenador.edicoes.midias.index',$ed) }}">Galeria</a>
                <a class="px-3 py-1 rounded border border-neutral-700 hover:bg-neutral-800" href="{{ route('coordenador.edicoes.edit',$ed) }}">Editar</a>
                <form method="POST" action="{{ route('coordenador.edicoes.destroy',$ed) }}" onsubmit="return confirm('Remover edição e sua galeria/atrativos?');">
                  @csrf @method('DELETE')
                  <button class="px-3 py-1 rounded border border-red-700 text-red-300 hover:bg-red-900/20">Excluir</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-10 text-center text-neutral-400">Nenhuma edição.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $edicoes->links() }}</div>

  <div>
    <a href="{{ route('coordenador.eventos.index') }}" class="px-4 py-2 rounded border border-neutral-700">Voltar aos eventos</a>
  </div>
</div>
@endsection
