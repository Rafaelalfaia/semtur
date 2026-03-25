@php
  /** @var \App\Models\Catalogo\Categoria $categoria */
  $isEdit = isset($categoria) && $categoria->exists;
  $statusAtual = old('status', $categoria->status ?? 'rascunho');
@endphp

<div class="grid gap-4 md:grid-cols-2">
  <div class="md:col-span-2">
    <label class="ui-form-label">Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $categoria->nome ?? '') }}" class="ui-form-control" required>
    @error('nome')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $categoria->slug ?? '') }}" class="ui-form-control">
    @error('slug')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Status *</label>
    <select name="status" class="ui-form-select" required>
      <option value="rascunho"  @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>
    @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    <label class="ui-form-label">Descricao</label>
    <textarea name="descricao" rows="3" class="ui-form-control ui-category-textarea">{{ old('descricao', $categoria->descricao ?? '') }}</textarea>
    @error('descricao')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Icone (imagem)</label>
    <input type="file" name="icone" accept="image/*" class="ui-banner-highlight-file">
    @error('icone')<p class="ui-form-error">{{ $message }}</p>@enderror

    @if($isEdit && ($categoria->icone_url ?? null))
      <div class="ui-category-icon-edit mt-3">
        <img src="{{ $categoria->icone_url }}" alt="Icone atual" class="ui-category-icon-preview">
        <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-danger)]">
          <input type="checkbox" name="remover_icone" value="1" class="ui-form-check rounded">
          Remover icone
        </label>
      </div>
    @endif
  </div>

  <div>
    <label class="ui-form-label">Ordem</label>
    <input type="number" name="ordem" min="0" value="{{ old('ordem', $categoria->ordem ?? 0) }}" class="ui-form-control">
    @error('ordem')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    @php
      $empresasSelecionadas = collect(old('empresas', $categoria->relationLoaded('empresas') ? $categoria->empresas->pluck('id')->all() : []))
        ->map(fn($id) => (int) $id)
        ->all();
    @endphp

    <label class="ui-form-label">Empresas relacionadas</label>
    <div class="ui-category-company-picker" role="group" aria-label="Empresas relacionadas">
      @forelse(($empresas ?? []) as $empresa)
        <label class="ui-category-company-option">
          <input
            type="checkbox"
            name="empresas[]"
            value="{{ $empresa->id }}"
            @checked(in_array((int) $empresa->id, $empresasSelecionadas, true))
          >
          <span>{{ $empresa->nome }}</span>
        </label>
      @empty
        <div class="ui-category-company-empty">Nenhuma empresa disponivel no momento.</div>
      @endforelse
    </div>
    @error('empresas')<p class="ui-form-error">{{ $message }}</p>@enderror
    @error('empresas.*')<p class="ui-form-error">{{ $message }}</p>@enderror
    <p class="ui-profile-help">Selecione uma ou mais empresas relacionadas a esta categoria.</p>
  </div>
</div>
