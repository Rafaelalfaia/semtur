@php
  /** @var \App\Models\Catalogo\PontoTuristico|null $ponto */
  $statusAtual = old('status', $ponto->status ?? 'rascunho');

  // estamos no editar?
  $isEdit = ($isEdit ?? false) && isset($ponto) && $ponto?->exists;

  // montar URL da capa, independente do nome da coluna
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
    <label class="block text-sm text-slate-300 mb-1">Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $ponto->nome ?? '') }}"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
    @error('nome')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Status *</label>
    <select id="status-field" name="status"
            class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
      <option value="rascunho"  @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>
    @error('status')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
    <p class="text-xs text-slate-400 mt-1">Ao publicar, <strong>lat/lng</strong> tornam-se obrigatórios.</p>
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Ordem</label>
    <input type="number" min="0" name="ordem" value="{{ old('ordem', $ponto->ordem ?? 0) }}"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    @error('ordem')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-1">Localização (URL do mapa)</label>
    <input type="url" name="maps_url"
           value="{{ old('maps_url', $ponto->maps_url ?? '') }}"
           placeholder="https://www.google.com/maps/place/.../@-3.2059,-52.2137,16z"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    <p class="text-xs text-slate-400 mt-1">
      Cole um link do Google Maps (aceita também Bing/OSM). As coordenadas serão extraídas automaticamente.
    </p>
    @error('maps_url')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- dentro do <form ...> do CREATE --}}
  <input type="hidden" name="form_token" value="{{ old('form_token', (string) \Illuminate\Support\Str::uuid()) }}">

  {{-- opcional UX: impedir duplo clique --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (!form) return;
    form.addEventListener('submit', function () {
      const btn = form.querySelector('button[type="submit"]');
      if (btn && !btn.disabled) { btn.disabled = true; btn.dataset.t = btn.textContent; btn.textContent = 'Salvando...'; }
    }, { once: true });
  });

  // helper p/ enviar DELETE sem aninhar <form>
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
    <label class="block text-sm text-slate-300 mb-1">Descrição</label>
    <textarea name="descricao" rows="4"
              class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">{{ old('descricao', $ponto->descricao ?? '') }}</textarea>
    @error('descricao')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Capa</label>
    <input type="file" name="capa" accept="image/*"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    @error('capa')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror

    {{-- PREVIEW CAPA + REMOVER (apenas no editar) --}}
    @if($isEdit && $capaUrl)
      <div class="mt-3 rounded-xl border border-white/10 bg-white/5 p-2">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-slate-400">Capa atual</span>
          <button type="button"
                  class="text-xs rounded-lg bg-rose-600/20 px-2.5 py-1.5 text-rose-200 hover:bg-rose-600/30"
                  onclick="postDelete('{{ route('coordenador.pontos.capa.remover', $ponto) }}','Remover a capa?')">
            Remover capa
          </button>
        </div>
        <img src="{{ $capaUrl }}" alt="Capa" class="max-h-56 rounded-lg object-cover w-full">
      </div>
    @elseif(!empty($ponto?->capa_url))
      <p class="text-xs text-slate-400 mt-1">Atual: <a class="text-sky-300 underline" target="_blank" href="{{ $ponto->capa_url }}">ver</a></p>
    @endif
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Galeria (várias)</label>
    <input type="file" name="galeria[]" accept="image/*" multiple
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    @error('galeria.*')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- PREVIEW GALERIA (imagens) + REMOVER --}}
  @if($isEdit && ($ponto->midias?->count()))
    <div class="md:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium text-slate-200">Galeria</h4>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        @foreach($ponto->midias as $m)
          @if($m->tipo === 'image' && $m->path)
            @php
              $url = \Illuminate\Support\Facades\Storage::disk('public')->url($m->path);
            @endphp
            <div class="group relative rounded-xl border border-white/10 bg-white/5 overflow-hidden">
              <img src="{{ $url }}" class="h-36 w-full object-cover" alt="Imagem da galeria">
              <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                <button type="button"
                        class="rounded-md bg-rose-600/80 text-white text-xs px-2 py-1 hover:bg-rose-600"
                        onclick="postDelete('{{ route('coordenador.pontos.midias.destroy', $m) }}','Remover esta imagem?')">
                  Remover
                </button>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  @endif

  <div>
    <label class="block text-sm text-slate-300 mb-1">Categorias</label>
    <select name="categorias[]" multiple
            class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      @foreach(($categorias ?? []) as $cat)
        <option value="{{ $cat->id }}"
          @selected(collect(old('categorias', isset($ponto)? $ponto->categorias->pluck('id')->all() : []))->contains($cat->id))>
          {{ $cat->nome }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-300 mb-1">Empresas relacionadas</label>
    <select name="empresas[]" multiple
            class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      @foreach(($empresas ?? []) as $emp)
        <option value="{{ $emp->id }}"
          @selected(collect(old('empresas', isset($ponto)? $ponto->empresas->pluck('id')->all() : []))->contains($emp->id))>
          {{ $emp->nome }}
        </option>
      @endforeach
    </select>
  </div>
</div>
