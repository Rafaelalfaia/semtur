{{-- resources/views/coordenador/empresas/_form.blade.php --}}
@csrf

<div class="grid gap-4 md:grid-cols-2">
  {{-- Nome --}}
  <div>
    <label class="block text-sm text-slate-300 mb-1">Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $empresa->nome ?? '') }}"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
    @error('nome')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- Status --}}
  <div>
    <label class="block text-sm text-slate-300 mb-1">Status *</label>
    @php
      $statusAtual = old('status', $empresa->status ?? 'rascunho');
    @endphp
    <select name="status" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
      <option value="rascunho"  @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>
    @error('status')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- Localização via URL do Google Maps --}}
  <div class="md:col-span-2">
    <label for="maps_url" class="block text-sm text-slate-300 mb-1">
      Localização (URL do Google Maps)
    </label>
    <input type="url" name="maps_url" id="maps_url"
           value="{{ old('maps_url', $empresa->maps_url ?? '') }}"
           placeholder="Cole aqui o link do Google Maps (ex.: https://maps.google.com/...@-3.1,-60.0,17z)"
           class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
    @error('maps_url')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror

    <div class="flex items-center gap-3 text-xs text-slate-400 mt-2">
      @php
        $latSaved = $empresa->lat ?? null;
        $lngSaved = $empresa->lng ?? null;
        $mapsUrl  = old('maps_url', $empresa->maps_url ?? '');
      @endphp

      @if($mapsUrl)
        <a href="{{ $mapsUrl }}" target="_blank" class="underline hover:no-underline">Abrir no Maps ↗</a>
      @endif

      @if(!is_null($latSaved) && !is_null($lngSaved))
        <span>Coordenadas salvas: {{ number_format($latSaved, 7, ',', '') }}, {{ number_format($lngSaved, 7, ',', '') }}</span>
      @endif
    </div>

    <p class="text-xs text-slate-400 mt-2">
      Basta colar a URL do Google Maps — as coordenadas serão extraídas automaticamente.
      <strong>Ao publicar</strong>, é obrigatório que a URL contenha coordenadas válidas.
    </p>
  </div>

  {{-- Descrição --}}
  <div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-1">Descrição</label>
    <textarea name="descricao" rows="4"
              class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">{{ old('descricao', $empresa->descricao ?? '') }}</textarea>
    @error('descricao')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
  </div>

  {{-- ====== REDES SOCIAIS & CONTATOS ====== --}}
  @php $c = old('contatos', $empresa->contatos ?? []); @endphp
  <div class="md:col-span-2">
    <div class="rounded-xl border border-white/10 p-5 bg-[#0F1412]">
      <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wide mb-4">
        Redes sociais & Contatos
      </h3>

      <div class="grid gap-4 md:grid-cols-2">
        {{-- WhatsApp --}}
        <div>
          <label for="contatos_whatsapp" class="block text-sm text-slate-300 mb-1">WhatsApp (DDI+DDD+Número)</label>
          <input type="text" id="contatos_whatsapp" name="contatos[whatsapp]"
                 value="{{ $c['whatsapp'] ?? '' }}" placeholder="5593999999999"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.whatsapp')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Site --}}
        <div>
          <label for="contatos_site" class="block text-sm text-slate-300 mb-1">Site</label>
          <input type="text" id="contatos_site" name="contatos[site]"
                 value="{{ $c['site'] ?? '' }}" placeholder="https://www.seusite.com.br"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.site')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Instagram --}}
        <div>
          <label for="contatos_instagram" class="block text-sm text-slate-300 mb-1">Instagram</label>
          <input type="text" id="contatos_instagram" name="contatos[instagram]"
                 value="{{ $c['instagram'] ?? '' }}" placeholder="@usuario ou url"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.instagram')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Facebook --}}
        <div>
          <label for="contatos_facebook" class="block text-sm text-slate-300 mb-1">Facebook</label>
          <input type="text" id="contatos_facebook" name="contatos[facebook]"
                 value="{{ $c['facebook'] ?? '' }}" placeholder="página ou url"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.facebook')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- TikTok --}}
        <div>
          <label for="contatos_tiktok" class="block text-sm text-slate-300 mb-1">TikTok</label>
          <input type="text" id="contatos_tiktok" name="contatos[tiktok]"
                 value="{{ $c['tiktok'] ?? '' }}" placeholder="@usuario ou url"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.tiktok')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- YouTube --}}
        <div>
          <label for="contatos_youtube" class="block text-sm text-slate-300 mb-1">YouTube</label>
          <input type="text" id="contatos_youtube" name="contatos[youtube]"
                 value="{{ $c['youtube'] ?? '' }}" placeholder="url do canal ou @handle"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.youtube')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Google Maps extra (link direto do lugar) --}}
        <div>
          <label for="contatos_maps" class="block text-sm text-slate-300 mb-1">Google Maps (URL)</label>
          <input type="url" id="contatos_maps" name="contatos[maps]"
                 value="{{ $c['maps'] ?? '' }}" placeholder="https://maps.google.com/..."
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.maps')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- E-mail --}}
        <div>
          <label for="contatos_email" class="block text-sm text-slate-300 mb-1">E-mail</label>
          <input type="email" id="contatos_email" name="contatos[email]"
                 value="{{ $c['email'] ?? '' }}" placeholder="contato@empresa.com"
                 class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
          @error('contatos.email')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- CAPA --}}
  <label class="block text-sm text-slate-300 mb-1">Foto de capa</label>
  <input type="file" name="capa" id="capa"
         @if(!$empresa->exists) required @endif
         accept="image/*"
         class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
  @error('capa')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror

  {{-- PERFIL --}}
  <label class="block text-sm text-slate-300 mb-1 mt-4">Foto de perfil</label>
  <input type="file" name="perfil" id="perfil"
         @if(!$empresa->exists) required @endif
         accept="image/*"
         class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
  @error('perfil')<p class="text-xs text-rose-300 mt-1">{{ $message }}</p>@enderror

  {{-- Categorias --}}
  <div class="md:col-span-2">
    <label class="block text-sm text-slate-300 mb-1">Categorias</label>
    <select name="categorias[]" multiple
            class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      @foreach(($categorias ?? []) as $cat)
        <option value="{{ $cat->id }}"
          @selected(collect(old('categorias', isset($empresa)? $empresa->categorias->pluck('id')->all() : []))->contains($cat->id))>
          {{ $cat->nome }}
        </option>
      @endforeach
    </select>
  </div>
</div>

<p class="text-xs text-slate-400 mt-3">
  * <strong>Latitude</strong> e <strong>Longitude</strong> são obrigatórias quando o status for <strong>Publicado</strong> (para aparecer no mapa).
</p>
