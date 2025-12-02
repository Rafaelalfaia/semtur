@extends('console.layout')
@section('title','Editar edição — '.$evento->nome)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <h1 class="text-xl font-semibold">Editar edição — {{ $evento->nome }}</h1>

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.edicoes.update',$edicao) }}" class="space-y-4 rounded-xl border border-white/5 p-5">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm mb-1">Ano *</label>
        <input type="number" name="ano" value="{{ old('ano',$edicao->ano) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Início</label>
        <input type="date" name="data_inicio" value="{{ old('data_inicio',$edicao->data_inicio?->format('Y-m-d')) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Fim</label>
        <input type="date" name="data_fim" value="{{ old('data_fim',$edicao->data_fim?->format('Y-m-d')) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Local</label>
      <input name="local" value="{{ old('local',$edicao->local) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    </div>

    @php
    $mapsFromCoords = ($edicao->lat && $edicao->lng)
        ? 'https://www.google.com/maps?q='.$edicao->lat.','.$edicao->lng
        : '';
    @endphp

    <div>
    <label class="block text-sm mb-1">Link do Google Maps</label>
    <input name="maps_url"
            value="{{ old('maps_url', $mapsFromCoords) }}"
            placeholder="Cole aqui o link do Google Maps (compartilhar)"
            class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    <p class="mt-1 text-xs text-neutral-400">
        Dica: ao salvar, extraímos as coordenadas do link automaticamente.
    </p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Latitude</label>
        <input name="lat" value="{{ old('lat', $edicao->lat) }}"
            class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">Longitude</label>
        <input name="lng" value="{{ old('lng', $edicao->lng) }}"
            class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
    </div>
    </div>


    <div>
      <label class="block text-sm mb-1">Resumo</label>
      <textarea name="resumo" rows="4" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">{{ old('resumo',$edicao->resumo) }}</textarea>
    </div>

    <div class="flex items-center gap-3">
      <select name="status" class="rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(old('status',$edicao->status)===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Salvar</button>
      <a href="{{ route('coordenador.eventos.edicoes.index',$evento) }}" class="px-4 py-2 rounded border border-neutral-700">Voltar</a>
    </div>
  </form>
</div>
@endsection
