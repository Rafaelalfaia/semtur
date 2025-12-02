@extends('console.layout')
@section('title','Novo evento')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <h1 class="text-xl font-semibold">Novo evento</h1>

  @if($errors->any())
    <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.eventos.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-white/5 p-5">
    @csrf
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Nome *</label>
        <input name="nome" value="{{ old('nome') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Slug (opcional)</label>
        <input name="slug" value="{{ old('slug') }}" placeholder="gerado do nome" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Cidade</label>
        <input name="cidade" value="{{ old('cidade') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Região</label>
        <input name="regiao" value="{{ old('regiao') }}" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Descrição</label>
      <textarea name="descricao" rows="5" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">{{ old('descricao') }}</textarea>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Capa (1920×700 aprox.)</label>
        <input type="file" name="capa" accept="image/*" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Perfil (quadrada)</label>
        <input type="file" name="perfil" accept="image/*" class="w-full rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
      </div>
    </div>

    <div class="flex items-center gap-3">
      <select name="status" class="rounded bg-neutral-900 border border-neutral-700 px-3 py-2">
        @foreach(['publicado','rascunho','arquivado'] as $st)
          <option value="{{ $st }}" @selected(old('status')===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-white">Salvar</button>
      <a href="{{ route('coordenador.eventos.index') }}" class="px-4 py-2 rounded border border-neutral-700">Cancelar</a>
    </div>
  </form>
</div>
@endsection
