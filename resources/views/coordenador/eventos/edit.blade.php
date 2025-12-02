@extends('console.layout')
@section('title','Editar evento')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Editar: {{ $evento->nome }}</h1>
    <a class="px-3 py-2 rounded border border-neutral-700" href="{{ route('coordenador.eventos.edicoes.index',$evento) }}">Gerenciar edições</a>
  </div>

  @if(session('ok'))
    <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.eventos.update',$evento) }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-white/5 p-5">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Nome *</label>
        <input name="nome" value="{{ old('nome',$evento->nome) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Slug</label>
        <input name="slug" value="{{ old('slug',$evento->slug) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Cidade</label>
        <input name="cidade" value="{{ old('cidade',$evento->cidade) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Região</label>
        <input name="regiao" value="{{ old('regiao',$evento->regiao) }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Descrição</label>
      <textarea name="descricao" rows="5" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">{{ old('descricao',$evento->descricao) }}</textarea>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Capa</label>
        @if($evento->capa_path)
          <img src="{{ Storage::disk('public')->url($evento->capa_path) }}" class="w-full max-h-48 object-cover rounded mb-2">
          <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_capa" value="1"> Remover capa</label>
        @endif
        <input type="file" name="capa" accept="image/*" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2 mt-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Perfil</label>
        @if($evento->perfil_path)
          <img src="{{ Storage::disk('public')->url($evento->perfil_path) }}" class="w-24 h-24 object-cover rounded mb-2">
          <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_perfil" value="1"> Remover perfil</label>
        @endif
        <input type="file" name="perfil" accept="image/*" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2 mt-2">
      </div>
    </div>

    <div class="flex items-center gap-3">
      <select name="status" class="rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(old('status',$evento->status)===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Salvar</button>
      <a href="{{ route('coordenador.eventos.index') }}" class="px-4 py-2 rounded border border-neutral-700">Voltar</a>
    </div>
  </form>
</div>
@endsection
