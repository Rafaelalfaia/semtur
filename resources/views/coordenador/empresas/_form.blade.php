@csrf

<div class="grid gap-4 md:grid-cols-2">
  <div>
    <label class="ui-form-label">Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $empresa->nome ?? '') }}" class="ui-form-control" required>
    @error('nome')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Status *</label>
    @php
      $statusAtual = old('status', $empresa->status ?? 'rascunho');
    @endphp
    <select name="status" class="ui-form-select" required>
      <option value="rascunho" @selected($statusAtual==='rascunho')>Rascunho</option>
      <option value="publicado" @selected($statusAtual==='publicado')>Publicado</option>
      <option value="arquivado" @selected($statusAtual==='arquivado')>Arquivado</option>
    </select>
    @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    <label for="maps_url" class="ui-form-label">Localizacao (URL do Google Maps)</label>
    <input
      type="url"
      name="maps_url"
      id="maps_url"
      value="{{ old('maps_url', $empresa->maps_url ?? '') }}"
      placeholder="Cole aqui o link do Google Maps"
      class="ui-form-control"
    >
    @error('maps_url')<p class="ui-form-error">{{ $message }}</p>@enderror

    <div class="flex items-center gap-3 text-xs text-[var(--ui-text-soft)] mt-2">
      @php
        $latSaved = $empresa->lat ?? null;
        $lngSaved = $empresa->lng ?? null;
        $mapsUrl = old('maps_url', $empresa->maps_url ?? '');
      @endphp

      @if($mapsUrl)
        <a href="{{ $mapsUrl }}" target="_blank" class="underline hover:no-underline">Abrir no Maps</a>
      @endif

      @if(!is_null($latSaved) && !is_null($lngSaved))
        <span>Coordenadas salvas: {{ number_format($latSaved, 7, ',', '') }}, {{ number_format($lngSaved, 7, ',', '') }}</span>
      @endif
    </div>

    <p class="ui-profile-help">
      Basta colar a URL do Google Maps. Ao publicar, a URL precisa conter coordenadas validas.
    </p>
  </div>

  <div class="md:col-span-2">
    <label class="ui-form-label">Descricao</label>
    <textarea name="descricao" rows="4" class="ui-form-control">{{ old('descricao', $empresa->descricao ?? '') }}</textarea>
    @error('descricao')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  @php $c = old('contatos', $empresa->contatos ?? []); @endphp
  <div class="md:col-span-2">
    <div class="ui-coord-contact-card">
      <h3 class="text-sm font-semibold text-[var(--ui-text-title)] uppercase tracking-wide mb-4">
        Redes sociais e contatos
      </h3>

      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label for="contatos_whatsapp" class="ui-form-label">WhatsApp</label>
          <input type="text" id="contatos_whatsapp" name="contatos[whatsapp]" value="{{ $c['whatsapp'] ?? '' }}" placeholder="5593999999999" class="ui-form-control">
          @error('contatos.whatsapp')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_site" class="ui-form-label">Site</label>
          <input type="text" id="contatos_site" name="contatos[site]" value="{{ $c['site'] ?? '' }}" placeholder="https://www.seusite.com.br" class="ui-form-control">
          @error('contatos.site')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_instagram" class="ui-form-label">Instagram</label>
          <input type="text" id="contatos_instagram" name="contatos[instagram]" value="{{ $c['instagram'] ?? '' }}" placeholder="@usuario ou url" class="ui-form-control">
          @error('contatos.instagram')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_facebook" class="ui-form-label">Facebook</label>
          <input type="text" id="contatos_facebook" name="contatos[facebook]" value="{{ $c['facebook'] ?? '' }}" placeholder="pagina ou url" class="ui-form-control">
          @error('contatos.facebook')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_tiktok" class="ui-form-label">TikTok</label>
          <input type="text" id="contatos_tiktok" name="contatos[tiktok]" value="{{ $c['tiktok'] ?? '' }}" placeholder="@usuario ou url" class="ui-form-control">
          @error('contatos.tiktok')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_youtube" class="ui-form-label">YouTube</label>
          <input type="text" id="contatos_youtube" name="contatos[youtube]" value="{{ $c['youtube'] ?? '' }}" placeholder="url do canal ou @handle" class="ui-form-control">
          @error('contatos.youtube')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_maps" class="ui-form-label">Google Maps (URL)</label>
          <input type="url" id="contatos_maps" name="contatos[maps]" value="{{ $c['maps'] ?? '' }}" placeholder="https://maps.google.com/..." class="ui-form-control">
          @error('contatos.maps')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="contatos_email" class="ui-form-label">E-mail</label>
          <input type="email" id="contatos_email" name="contatos[email]" value="{{ $c['email'] ?? '' }}" placeholder="contato@empresa.com" class="ui-form-control">
          @error('contatos.email')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>
  </div>

  <div>
    <label class="ui-form-label">Foto de capa</label>
    <input type="file" name="capa" id="capa" @if(!$empresa->exists) required @endif accept="image/*" class="ui-form-control">
    @error('capa')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="ui-form-label">Foto de perfil</label>
    <input type="file" name="perfil" id="perfil" @if(!$empresa->exists) required @endif accept="image/*" class="ui-form-control">
    @error('perfil')<p class="ui-form-error">{{ $message }}</p>@enderror
  </div>

  <div class="md:col-span-2">
    <label class="ui-form-label">Galeria de fotos</label>
    <input
      type="file"
      name="galeria[]"
      id="empresa-galeria"
      accept="image/*"
      multiple
      class="ui-form-control"
    >
    @error('galeria')<p class="ui-form-error">{{ $message }}</p>@enderror
    @error('galeria.*')<p class="ui-form-error">{{ $message }}</p>@enderror
    <p class="ui-profile-help">Envie fotos complementares da empresa. A capa principal continua separada.</p>

    <div id="empresa-galeria-preview" class="mt-3 hidden grid grid-cols-2 gap-3 md:grid-cols-4"></div>
  </div>

  @if(($empresa->exists ?? false) && $empresa->relationLoaded('galeriaFotos') && $empresa->galeriaFotos->count())
    <div class="md:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium text-[var(--ui-text-title)]">Fotos cadastradas</h4>
        <span class="text-xs text-[var(--ui-text-soft)]">Marque apenas as imagens que deseja remover.</span>
      </div>

      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach($empresa->galeriaFotos as $foto)
          <label class="ui-coord-media-card block p-2 cursor-pointer">
            <img src="{{ $foto->url }}" class="h-36 w-full rounded-xl object-cover" alt="{{ $foto->alt ?: 'Foto da galeria de '.$empresa->nome }}">
            <div class="mt-2 flex items-center gap-2 text-xs text-[var(--ui-text-soft)]">
              <input type="checkbox" name="remover_fotos[]" value="{{ $foto->id }}">
              Remover foto
            </div>
          </label>
        @endforeach
      </div>
    </div>
  @endif

  <div class="md:col-span-2">
    @php
      $categoriasSelecionadas = collect(old('categorias', $selecionadas ?? (isset($empresa) ? $empresa->categorias->pluck('id')->all() : [])))
        ->map(fn($id) => (int) $id)
        ->all();
    @endphp

    <label class="ui-form-label">Categorias</label>
    <div class="ui-company-category-picker" role="group" aria-label="Categorias da empresa">
      @forelse(($categorias ?? []) as $cat)
        <label class="ui-company-category-option">
          <input
            type="checkbox"
            name="categorias[]"
            value="{{ $cat->id }}"
            @checked(in_array((int) $cat->id, $categoriasSelecionadas, true))
          >
          <span>{{ $cat->nome }}</span>
        </label>
      @empty
        <div class="ui-company-category-empty">Nenhuma categoria disponivel no momento.</div>
      @endforelse
    </div>
    @error('categorias')<p class="ui-form-error">{{ $message }}</p>@enderror
    @error('categorias.*')<p class="ui-form-error">{{ $message }}</p>@enderror
    <p class="ui-profile-help">Selecione uma ou mais categorias que representam esta empresa.</p>
  </div>
</div>

<p class="ui-profile-help mt-3">
  Latitude e longitude sao obrigatorias quando o status for <strong>Publicado</strong>.
</p>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('empresa-galeria');
    const preview = document.getElementById('empresa-galeria-preview');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
      preview.innerHTML = '';

      const files = Array.from(input.files || []).filter(file => file.type.startsWith('image/'));
      preview.classList.toggle('hidden', files.length === 0);

      files.forEach(function (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          const card = document.createElement('div');
          card.className = 'ui-coord-media-card p-2';
          card.innerHTML = `
            <img src="${event.target.result}" alt="${file.name}" class="h-36 w-full rounded-xl object-cover">
            <div class="mt-2 text-xs text-[var(--ui-text-soft)] truncate">${file.name}</div>
          `;
          preview.appendChild(card);
        };
        reader.readAsDataURL(file);
      });
    });
  });
</script>
