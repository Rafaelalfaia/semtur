@extends('console.layout')
@section('title','Nova edição — '.$evento->nome)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <h1 class="text-xl font-semibold">Nova edição — {{ $evento->nome }}</h1>

  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.eventos.edicoes.store',$evento) }}" class="space-y-4 rounded-xl border border-white/5 p-5">
    @csrf
    <div class="grid md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm mb-1">Ano *</label>
        <input type="number" name="ano" value="{{ old('ano',$edicao->ano) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Início</label>
        <input type="date" name="data_inicio" value="{{ old('data_inicio') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Fim</label>
        <input type="date" name="data_fim" value="{{ old('data_fim') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Local</label>
      <input name="local" value="{{ old('local') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    </div>

    <div>
    <label class="block text-sm mb-1">Link do Google Maps</label>
    <input name="maps_url" value="{{ old('maps_url') }}"
            placeholder="Cole aqui o link do Google Maps (compartilhar)"
            class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    <p class="mt-1 text-xs text-neutral-400">
        Ex.: https://maps.app.goo.gl/... ou https://www.google.com/maps/... @lat,lng
    </p>
    </div>


    <div>
      <label class="block text-sm mb-1">Resumo</label>
      <textarea name="resumo" rows="4" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">{{ old('resumo') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
      <select name="status" class="rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(old('status')===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Salvar</button>
      <a href="{{ route('coordenador.eventos.edicoes.index',$evento) }}" class="px-4 py-2 rounded border border-neutral-700">Cancelar</a>
    </div>
  </form>
</div>
@endsection
