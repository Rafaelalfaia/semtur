@csrf

<div class="grid gap-8 lg:grid-cols-[1fr_540px]">
  {{-- ===================== COLUNA ESQUERDA — DADOS ===================== --}}
  <div class="space-y-6">
    {{-- Título --}}
    <div>
      <label for="titulo" class="block text-sm font-medium text-slate-300 mb-1">Título</label>
      <input id="titulo" type="text" name="titulo" value="{{ old('titulo',$banner->titulo) }}"
             class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500/20">
      @error('titulo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>

    {{-- Subtítulo --}}
    <div>
      <label for="subtitulo" class="block text-sm font-medium text-slate-300 mb-1">Subtítulo (opcional)</label>
      <input id="subtitulo" type="text" name="subtitulo" value="{{ old('subtitulo',$banner->subtitulo) }}"
             class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
      @error('subtitulo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
    </div>

    {{-- Link + Nova aba --}}
    <div class="grid grid-cols-4 gap-3 items-end">
      <div class="col-span-3">
        <label for="link_url" class="block text-sm font-medium text-slate-300 mb-1">Link (opcional)</label>
        <input id="link_url" type="text" name="link_url" value="{{ old('link_url',$banner->link_url) }}"
               class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2" placeholder="https://...">
        @error('link_url')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="target_blank" value="1"
               @checked(old('target_blank',$banner->target_blank))
               class="rounded border-white/10 bg-white/5 text-emerald-500 focus:ring-emerald-500/40">
        <span class="text-sm text-slate-300">Nova aba</span>
      </label>
    </div>

    {{-- Cor + Opacidade --}}
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label for="cor_fundo" class="block text-sm font-medium text-slate-300 mb-1">Cor de fundo</label>
        <input id="cor_fundo" type="color" name="cor_fundo" value="{{ old('cor_fundo',$banner->cor_fundo ?? '#00837B') }}"
               class="h-10 w-full rounded-lg border border-white/10 bg-white/5">
        @error('cor_fundo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="overlay_opacity" class="block text-sm font-medium text-slate-300 mb-1">Opacidade da sobreposição</label>
        <input id="overlay_opacity" type="number" name="overlay_opacity" min="0" max="100"
               value="{{ old('overlay_opacity',$banner->overlay_opacity ?? 0) }}"
               class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
        @error('overlay_opacity')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
    </div>

    {{-- Ordem + Status --}}
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label for="ordem" class="block text-sm font-medium text-slate-300 mb-1">Ordem</label>
        <input id="ordem" type="number" name="ordem" min="0" value="{{ old('ordem',$banner->ordem ?? 0) }}"
               class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
        @error('ordem')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="status" class="block text-sm font-medium text-slate-300 mb-1">Status</label>
        <select id="status" name="status"
                class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
          @foreach(['publicado'=>'Publicado','rascunho'=>'Rascunho','arquivado'=>'Arquivado'] as $v=>$lbl)
            <option value="{{ $v }}" @selected(old('status',$banner->status)===$v)>{{ $lbl }}</option>
          @endforeach
        </select>
        @error('status')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
    </div>

    {{-- Período --}}
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label for="inicio_publicacao" class="block text-sm font-medium text-slate-300 mb-1">Início (opcional)</label>
        <input id="inicio_publicacao" type="datetime-local" name="inicio_publicacao"
               value="{{ old('inicio_publicacao', optional($banner->inicio_publicacao)->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
        @error('inicio_publicacao')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
      <div>
        <label for="fim_publicacao" class="block text-sm font-medium text-slate-300 mb-1">Fim (opcional)</label>
        <input id="fim_publicacao" type="datetime-local" name="fim_publicacao"
               value="{{ old('fim_publicacao', optional($banner->fim_publicacao)->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-white/10 bg-white/5 text-slate-100 px-3 py-2">
        @error('fim_publicacao')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>

  {{-- ===================== COLUNA DIREITA — IMAGENS (sticky) ===================== --}}
  <aside class="space-y-6 lg:sticky lg:top-6">
    @php
      // valores default seguros (evita notice se o atributo não existir no model)
      $dx = (float) old('pos_desktop_x', data_get($banner, 'pos_desktop_x', 50));
      $dy = (float) old('pos_desktop_y', data_get($banner, 'pos_desktop_y', 50));
      $mx = (float) old('pos_mobile_x',  data_get($banner, 'pos_mobile_x',  50));
      $my = (float) old('pos_mobile_y',  data_get($banner, 'pos_mobile_y',  50));
      $dx = max(0,min(100,$dx)); $dy = max(0,min(100,$dy));
      $mx = max(0,min(100,$mx)); $my = max(0,min(100,$my));
    @endphp

    {{-- ======================== DESKTOP ======================== --}}
    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm text-slate-200">Imagem (Desktop) — 1920×700</h3>
        <span class="text-[11px] text-slate-400">JPG/PNG/WebP • até 6MB</span>
    </div>

    {{-- Posição (atualizada via JS durante o pan) --}}
    <input type="hidden" name="pos_desktop_x" id="pos_desktop_x" value="{{ $dx }}">
    <input type="hidden" name="pos_desktop_y" id="pos_desktop_y" value="{{ $dy }}">
    {{-- Campo combinado no formato aceito pelo backend: "x,y" --}}
    <input type="hidden" name="pos_desktop" id="pos_desktop" value="{{ $dx }},{{ $dy }}">

    <input id="imagem_desktop" type="file" name="imagem_desktop" accept="image/*"
            class="w-full rounded-lg bg-transparent border border-white/10 px-3 py-2 text-slate-100
                    file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-slate-100
                    hover:file:bg-white/20">
    @error('imagem_desktop')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror

    <div id="preview-desktop"
        class="mt-3 w-full rounded-lg bg-white/10 ring-1 ring-white/10 overflow-hidden"
        style="aspect-ratio: 1920 / 700;">
        <img id="preview-desktop-img"
            src="{{ ($banner->exists && $banner->imagem_desktop_url && !old('imagem_desktop')) ? $banner->imagem_desktop_url : '' }}"
            data-src-processed="{{ $banner->imagem_desktop_url ?? '' }}"
            data-src-original="{{ $banner->imagem_desktop_original_url ?? ($banner->imagem_desktop_url ?? '') }}"
                    alt="Prévia (Desktop)"
            style="object-position: {{ $dx }}% {{ $dy }}%;"
            class="w-full h-full object-cover {{ ($banner->exists && $banner->imagem_desktop_url && !old('imagem_desktop')) ? '' : 'hidden' }}">
    </div>

    <p class="mt-2 text-[11px] text-slate-400">Arraste para ajustar o foco • Duplo clique para centralizar</p>
    </section>

    {{-- ========================= MOBILE ========================= --}}
    <section class="rounded-xl border border-white/10 bg-white/5 p-3">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm text-slate-200">Imagem (Mobile) — 1080×1080</h3>
        <span class="text-[11px] text-slate-400">JPG/PNG/WebP • até 6MB</span>
    </div>

    {{-- Posição (atualizada via JS durante o pan) --}}
    <input type="hidden" name="pos_mobile_x" id="pos_mobile_x" value="{{ $mx }}">
    <input type="hidden" name="pos_mobile_y" id="pos_mobile_y" value="{{ $my }}">
    {{-- Campo combinado no formato aceito pelo backend: "x,y" --}}
    <input type="hidden" name="pos_mobile"  id="pos_mobile"  value="{{ $mx }},{{ $my }}">

    <input id="imagem_mobile" type="file" name="imagem_mobile" accept="image/*"
            class="w-full rounded-lg bg-transparent border border-white/10 px-3 py-2 text-slate-100
                    file:mr-3 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-slate-100
                    hover:file:bg-white/20">
    @error('imagem_mobile')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror

    <div id="preview-mobile"
        class="mt-3 w-full rounded-lg bg-white/10 ring-1 ring-white/10 overflow-hidden"
        style="aspect-ratio: 1 / 1;">
        <img id="preview-mobile-img"
            src="{{ ($banner->exists && $banner->imagem_mobile_url && !old('imagem_mobile')) ? $banner->imagem_mobile_url : '' }}"
            data-src-processed="{{ $banner->imagem_mobile_url ?? '' }}"
            data-src-original="{{ $banner->imagem_mobile_original_url ?? ($banner->imagem_mobile_url ?? '') }}"

            alt="Prévia (Mobile)"
            style="object-position: {{ $mx }}% {{ $my }}%;"
            class="w-full h-full object-cover {{ ($banner->exists && $banner->imagem_mobile_url && !old('imagem_mobile')) ? '' : 'hidden' }}">
    </div>

    <p class="mt-2 text-[11px] text-slate-400">Arraste para ajustar o foco • Duplo clique para centralizar</p>
    </section>

  </aside>
</div>

{{-- ===================== AÇÕES ===================== --}}
<div class="mt-8 flex flex-wrap gap-3">
  <button type="submit"
          class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
    Salvar
  </button>

  <a href="{{ route('coordenador.banners-destaque.index') }}"
     class="px-5 py-2.5 rounded-lg bg-white/10 text-slate-100 hover:bg-white/15">
    Cancelar
  </a>

  <a href="{{ url('/') }}" target="_blank"
     class="ml-auto px-5 py-2.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">
    Ver na Home
  </a>
</div>

@push('scripts')
  {{-- apenas visualização local do arquivo selecionado --}}
  @vite('resources/js/simple-previews.js')
@endpush
