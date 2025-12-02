@extends('console.layout')
@section('title','Galeria — '.$edicao->evento->nome.' ('.$edicao->ano.')')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Galeria — {{ $edicao->evento->nome }} ({{ $edicao->ano }})</h1>
      <a href="{{ route('coordenador.eventos.edicoes.index',$edicao->evento) }}" class="text-sm text-neutral-400 hover:underline">← Voltar às edições</a>
    </div>
  </div>

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.edicoes.midias.store',$edicao) }}" enctype="multipart/form-data" class="rounded-xl border border-white/5 p-5 space-y-3">
    @csrf
    <label class="block text-sm">Adicionar fotos (múltiplas):</label>
    <input type="file" name="fotos[]" accept="image/*" multiple class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    <button class="mt-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Enviar</button>
  </form>

  <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
    @forelse($midias as $m)
      <div class="rounded-lg border border-white/5 overflow-hidden">
        <img src="{{ Storage::disk('public')->url($m->path) }}" class="w-full h-36 object-cover" alt="{{ $m->alt }}">
        <div class="p-2 flex items-center justify-between">
          <span class="text-xs text-neutral-400">#{{ $m->ordem }}</span>
          <form method="POST" action="{{ route('coordenador.midias.destroy',$m) }}" onsubmit="return confirm('Excluir imagem?');">
            @csrf @method('DELETE')
            <button class="text-xs px-2 py-1 rounded border border-red-700 text-red-300 hover:bg-red-900/20">Excluir</button>
          </form>
        </div>
      </div>
    @empty
      <div class="col-span-full text-center text-neutral-400 py-10">Nenhuma foto enviada.</div>
    @endforelse
  </div>

  <div>{{ $midias->links() }}</div>

  <form method="POST" action="{{ route('coordenador.edicoes.midias.reordenar',$edicao) }}" class="flex items-center gap-3">
    @csrf
    <input name="ordem" placeholder='{"33":1,"41":2}' class="flex-1 rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    <button class="rounded bg-neutral-800 border border-neutral-700 px-3 py-2">Aplicar ordem</button>
  </form>
</div>
@endsection
