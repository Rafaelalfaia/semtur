@php
  /** @var \App\Models\Catalogo\Categoria $categoria */
  $isEdit = isset($categoria) && $categoria->exists;
  $statusAtual = old('status', $categoria->status ?? 'rascunho');
@endphp

<div class="grid gap-4 md:grid-cols-2">
  <div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-1">Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $categoria->nome ?? '') }}"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
    @error('nome')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $categoria->slug ?? '') }}"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    @error('slug')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Status *</label>
    <select name="status" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
      <option value="rascunho"  @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>
    @error('status')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-1">Descrição</label>
    <textarea name="descricao" rows="3"
              class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">{{ old('descricao', $categoria->descricao ?? '') }}</textarea>
    @error('descricao')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div>
  <label class="block text-sm text-slate-300 mb-1">Ícone (imagem)</label>
  <input type="file" name="icone" accept="image/*"
         class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
  @error('icone')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror

  @if($isEdit && ($categoria->icone_url ?? null))
    <div class="mt-2 flex items-center gap-3">
      <img src="{{ $categoria->icone_url }}" alt="Ícone atual"
           class="h-10 w-10 rounded-md object-cover border border-white/10">
      <label class="inline-flex items-center gap-2 text-sm text-rose-200">
        <input type="checkbox" name="remover_icone" value="1"
               class="rounded border-white/20 bg-white/5">
        Remover ícone
      </label>
    </div>
  @endif
</div>


  <div>
    <label class="block text-sm text-slate-300 mb-1">Ordem</label>
    <input type="number" name="ordem" min="0" value="{{ old('ordem', $categoria->ordem ?? 0) }}"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    @error('ordem')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>
</div>
