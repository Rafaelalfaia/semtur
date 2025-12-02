@extends('console.layout')
@section('title','Novo atrativo')
@section('page.title','Novo atrativo — '.$edicao->evento->nome.' ('.$edicao->ano.')')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST"
        action="{{ route('coordenador.edicoes.atrativos.store',$edicao) }}"
        enctype="multipart/form-data"
        class="space-y-4 rounded-xl border border-white/5 p-5">
    @csrf

    <div>
      <label class="block text-sm mb-1">Nome *</label>
      <input name="nome" value="{{ old('nome') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" required>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Slug (opcional)</label>
        <input name="slug" value="{{ old('slug') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Ordem</label>
        <input type="number" name="ordem" value="{{ old('ordem',$atrativo->ordem ?? 1) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" min="1">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Descrição</label>
      <textarea name="descricao" rows="4" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">{{ old('descricao') }}</textarea>
    </div>

    <div>
      <label class="block text-sm mb-1">Thumb (imagem)</label>
      <input type="file" name="thumb" accept="image/*" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      <p class="mt-1 text-xs text-neutral-400">Recomendado: 800×600 (ou similar)</p>
    </div>

    <div class="flex items-center gap-3">
      <select name="status" class="rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(old('status')===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>

      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Salvar</button>
      <a href="{{ route('coordenador.edicoes.atrativos.index',$edicao) }}" class="px-4 py-2 rounded border border-neutral-700">
        Cancelar
      </a>
    </div>
  </form>
</div>
@endsection
