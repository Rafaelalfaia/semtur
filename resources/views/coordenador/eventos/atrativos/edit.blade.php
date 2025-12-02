@extends('console.layout')
@section('title','Editar atrativo')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <h1 class="text-xl font-semibold">Editar atrativo — {{ $edicao->evento->nome }} ({{ $edicao->ano }})</h1>

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.atrativos.update',$atrativo) }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-white/5 p-5">
    @csrf @method('PUT')
    <div>
      <label class="block text-sm mb-1">Nome *</label>
      <input name="nome" value="{{ old('nome',$atrativo->nome) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" required>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Slug</label>
        <input name="slug" value="{{ old('slug',$atrativo->slug) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Ordem</label>
        <input type="number" name="ordem" value="{{ old('ordem',$atrativo->ordem) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
    </div>
    <div>
      <label class="block text-sm mb-1">Descrição</label>
      <textarea name="descricao" rows="4" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">{{ old('descricao',$atrativo->descricao) }}</textarea>
    </div>
    <div>
      <label class="block text-sm mb-1">Thumb</label>
      @if($atrativo->thumb_path)
        <img src="{{ Storage::disk('public')->url($atrativo->thumb_path) }}" class="w-24 h-24 object-cover rounded mb-2">
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_thumb" value="1"> Remover thumb</label>
      @endif
      <input type="file" name="thumb" accept="image/*" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2 mt-2">
    </div>
    <div class="flex items-center gap-3">
      <select name="status" class="rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(old('status',$atrativo->status)===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Salvar</button>
      <a href="{{ route('coordenador.edicoes.atrativos.index',$edicao) }}" class="px-4 py-2 rounded border border-neutral-700">Voltar</a>
    </div>
  </form>
</div>
@endsection
