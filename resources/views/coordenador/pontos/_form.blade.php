@php
  /** @var \App\Models\Catalogo\PontoTuristico|null $ponto */
  $statusAtual = old('status', $ponto->status ?? 'rascunho');
  $isEdit = ($isEdit ?? false) && isset($ponto) && $ponto?->exists;

  $capaUrl = null;
  if ($isEdit) {
      $capaUrl = $ponto->capa_url
          ?? ($ponto->capa_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($ponto->capa_path) : null)
          ?? ($ponto->foto_capa_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($ponto->foto_capa_path) : null)
          ?? ($ponto->capa ? \Illuminate\Support\Facades\Storage::disk('public')->url($ponto->capa) : null);
  }
@endphp

<div class="grid gap-4 md:grid-cols-2">
  <div class="md:col-span-2">
    <label class="ui-form-label">Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $ponto->nome ?? '') }}" class="ui-form-control" required>
    @error('nome')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Status *</label>
    <select id="status-field" name="status" class="ui-form-select" required>
      <option value="rascunho"  @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>
    @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
    <p class="ui-profile-help">Ao publicar, <strong>lat/lng</strong> tornam-se obrigatorios.</p>
  </div>

  <div>
    <label class="ui-form-label">Ordem</label>
    <input type="number" min="0" name="ordem" value="{{ old('ordem', $ponto->ordem ?? 0) }}" class="ui-form-control">
    @error('ordem')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    <label class="ui-form-label">Localizacao (URL do mapa)</label>
    <input
      type="url"
      name="maps_url"
      value="{{ old('maps_url', $ponto->maps_url ?? '') }}"
      placeholder="https://www.google.com/maps/place/.../@-3.2059,-52.2137,16z"
      class="ui-form-control"
    >
    <p class="ui-profile-help">Cole um link do Google Maps. As coordenadas serao extraidas automaticamente.</p>
    @error('maps_url')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <input type="hidden" name="form_token" value="{{ old('form_token', (string) \Illuminate\Support\Str::uuid()) }}">

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (!form) return;
    form.addEventListener('submit', function () {
      const btn = form.querySelector('button[type="submit"]');
      if (btn && !btn.disabled) { btn.disabled = true; btn.dataset.t = btn.textContent; btn.textContent = 'Salvando...'; }
    }, { once: true });
  });

  function postDelete(url, msg) {
    if (!confirm(msg || 'Remover?')) return;
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = url;
    const m = document.createElement('input'); m.type='hidden'; m.name='_method'; m.value='DELETE';
    const t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value='{{ csrf_token() }}';
    f.appendChild(m); f.appendChild(t); document.body.appendChild(f); f.submit();
  }
  </script>

  <div class="md:col-span-2">
    <label class="ui-form-label">Descricao</label>
    <textarea name="descricao" rows="4" class="ui-form-control">{{ old('descricao', $ponto->descricao ?? '') }}</textarea>
    @error('descricao')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Capa</label>
    <input type="file" name="capa" accept="image/*" class="ui-form-control">
    @error('capa')<p class="ui-form-error">{{ $message }}</p>@enderror

    @if($isEdit && $capaUrl)
      <div class="ui-coord-media-preview mt-3">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-[var(--ui-text-soft)]">Capa atual</span>
          <button
            type="button"
            class="ui-btn-danger !min-h-0 px-2.5 py-1.5 text-xs"
            onclick="postDelete('{{ route('coordenador.pontos.capa.remover', $ponto) }}','Remover a capa?')"
          >
            Remover capa
          </button>
        </div>
        <img src="{{ $capaUrl }}" alt="Capa" class="max-h-56 rounded-lg object-cover w-full">
      </div>
    @elseif(!empty($ponto?->capa_url))
      <p class="ui-profile-help">Atual: <a class="text-[var(--ui-primary)] underline" target="_blank" href="{{ $ponto->capa_url }}">ver</a></p>
    @endif
  </div>

  <div>
    <label class="ui-form-label">Galeria (varias)</label>
    <input type="file" name="galeria[]" accept="image/*" multiple class="ui-form-control">
    @error('galeria.*')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  @if($isEdit && ($ponto->midias?->count()))
    <div class="md:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium text-[var(--ui-text-title)]">Galeria</h4>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        @foreach($ponto->midias as $m)
          @if($m->tipo === 'image' && $m->path)
            @php
              $url = \Illuminate\Support\Facades\Storage::disk('public')->url($m->path);
            @endphp
            <div class="ui-coord-media-card group relative">
              <img src="{{ $url }}" class="h-36 w-full object-cover" alt="Imagem da galeria">
              <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                <button
                  type="button"
                  class="ui-btn-danger !min-h-0 px-2 py-1 text-xs"
                  onclick="postDelete('{{ route('coordenador.pontos.midias.destroy', $m) }}','Remover esta imagem?')"
                >
                  Remover
                </button>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  @endif

  <div class="md:col-span-2">
    @php
      $categoriasSelecionadas = collect(old('categorias', isset($ponto) ? $ponto->categorias->pluck('id')->all() : []))
        ->map(fn($id) => (int) $id)
        ->all();
    @endphp

    <label class="ui-form-label">Categorias</label>
    <div class="ui-related-picker" role="group" aria-label="Categorias do ponto">
      @forelse(($categorias ?? []) as $cat)
        <label class="ui-related-option">
          <input
            type="checkbox"
            name="categorias[]"
            value="{{ $cat->id }}"
            @checked(in_array((int) $cat->id, $categoriasSelecionadas, true))
          >
          <span>{{ $cat->nome }}</span>
        </label>
      @empty
        <div class="ui-related-empty">Nenhuma categoria disponivel no momento.</div>
      @endforelse
    </div>
    @error('categorias')<p class="ui-form-error">{{ $message }}</p>@enderror
    @error('categorias.*')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    @php
      $empresasSelecionadas = collect(old('empresas', isset($ponto) ? $ponto->empresas->pluck('id')->all() : []))
        ->map(fn($id) => (int) $id)
        ->all();
      $empresasPayload = collect($empresas ?? [])->map(fn($emp) => [
        'id' => (int) $emp->id,
        'nome' => (string) $emp->nome,
      ])->values();
    @endphp

    <label class="ui-form-label">Empresas relacionadas</label>
    <div
      class="ui-search-picker"
      x-data="{
        query: '',
        items: @js($empresasPayload),
        selected: @js($empresasSelecionadas),
        get filtered() {
          const term = this.query.trim().toLowerCase();
          if (!term) return this.items.slice(0, 12);
          return this.items.filter(item => item.nome.toLowerCase().includes(term));
        },
        isChecked(id) {
          return this.selected.includes(id);
        },
        sync(id, checked) {
          if (checked) {
            if (!this.selected.includes(id)) this.selected.push(id);
            return;
          }
          this.selected = this.selected.filter(value => value !== id);
        },
        selectedLabels() {
          return this.items.filter(item => this.selected.includes(item.id));
        }
      }"
    >
      <input
        type="text"
        x-model="query"
        class="ui-form-control"
        placeholder="Pesquise a empresa pelo nome para selecionar"
        autocomplete="off"
      >

      <div class="ui-search-picker-selected" x-show="selected.length" x-cloak>
        <template x-for="item in selectedLabels()" :key="item.id">
          <span class="ui-search-picker-chip" x-text="item.nome"></span>
        </template>
      </div>

      <div class="ui-search-picker-results" role="group" aria-label="Empresas relacionadas ao ponto">
        <template x-if="!items.length">
          <div class="ui-related-empty">Nenhuma empresa disponivel no momento.</div>
        </template>

        <template x-if="items.length && !filtered.length">
          <div class="ui-related-empty">Nenhuma empresa encontrada para esta busca.</div>
        </template>

        <template x-for="item in filtered" :key="item.id">
          <label class="ui-related-option">
            <input
              type="checkbox"
              name="empresas[]"
              :value="item.id"
              :checked="isChecked(item.id)"
              @change="sync(item.id, $event.target.checked)"
            >
            <span x-text="item.nome"></span>
          </label>
        </template>
      </div>
    </div>
    @error('empresas')<p class="ui-form-error">{{ $message }}</p>@enderror
    @error('empresas.*')<p class="ui-form-error">{{ $message }}</p>@enderror
    <p class="ui-profile-help">Digite parte do nome para localizar e vincular uma ou mais empresas.</p>
  </div>
</div>
