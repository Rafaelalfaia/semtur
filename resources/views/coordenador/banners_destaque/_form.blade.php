@csrf

@php
  $dx = (float) old('pos_desktop_x', data_get($banner, 'pos_desktop_x', 50));
  $dy = (float) old('pos_desktop_y', data_get($banner, 'pos_desktop_y', 50));
  $mx = (float) old('pos_mobile_x', data_get($banner, 'pos_mobile_x', 50));
  $my = (float) old('pos_mobile_y', data_get($banner, 'pos_mobile_y', 50));
  $dx = max(0, min(100, $dx));
  $dy = max(0, min(100, $dy));
  $mx = max(0, min(100, $mx));
  $my = max(0, min(100, $my));

  $desktopImageUrl = ($banner->exists && $banner->imagem_desktop_url && !old('imagem_desktop')) ? $banner->imagem_desktop_url : '';
  $mobileImageUrl = ($banner->exists && $banner->imagem_mobile_url && !old('imagem_mobile')) ? $banner->imagem_mobile_url : '';
  $desktopVideoUrl = $banner->video_desktop_url ?? null;
  $mobileVideoUrl = $banner->video_mobile_url ?? null;
  $desktopPosterUrl = $banner->poster_desktop_url ?? $banner->fallback_image_desktop_url ?? $desktopImageUrl;
  $mobilePosterUrl = $banner->poster_mobile_url ?? $banner->fallback_image_mobile_url ?? $mobileImageUrl ?? $desktopPosterUrl;
@endphp

<div
  x-data="{
    mediaType: @js(old('media_type', $banner->media_type ?? 'image')),
    desktopVideoPreview: @js($desktopVideoUrl),
    mobileVideoPreview: @js($mobileVideoUrl),
    desktopPosterPreview: @js($desktopPosterUrl),
    mobilePosterPreview: @js($mobilePosterUrl),
    desktopFallbackPreview: @js($banner->fallback_image_desktop_url ?? $desktopImageUrl),
    mobileFallbackPreview: @js($banner->fallback_image_mobile_url ?? $mobileImageUrl),
    objectUrls: [],
    updatePreview(event, key) {
      const file = event.target.files?.[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      this.objectUrls.push(url);
      this[key] = url;
    },
    dispose() {
      this.objectUrls.forEach((url) => URL.revokeObjectURL(url));
    }
  }"
  x-init="$watch('mediaType', () => {})"
  x-on:beforeunload.window="dispose()"
  class="ui-banner-highlight-form-grid"
>
  <div class="space-y-5">
    <x-dashboard.section-card title="Conteudo do destaque" subtitle="Texto, CTA e configuracao principal do hero" class="ui-coord-dashboard-panel">
      <div class="grid gap-4">
        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px]">
          <div>
            <label for="titulo" class="ui-form-label">Titulo</label>
            <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $banner->titulo) }}" class="ui-form-control">
            @error('titulo')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="media_type" class="ui-form-label">Tipo de midia</label>
            <select id="media_type" name="media_type" x-model="mediaType" class="ui-form-select">
              <option value="image">Imagem</option>
              <option value="video">Video</option>
            </select>
            @error('media_type')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>
        </div>

        <div>
          <label for="subtitulo" class="ui-form-label">Subtitulo (opcional)</label>
          <input id="subtitulo" type="text" name="subtitulo" value="{{ old('subtitulo', $banner->subtitulo) }}" class="ui-form-control">
          @error('subtitulo')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_160px]">
          <div>
            <label for="link_url" class="ui-form-label">Link (opcional)</label>
            <input id="link_url" type="text" name="link_url" value="{{ old('link_url', $banner->link_url) }}" class="ui-form-control" placeholder="https://...">
            @error('link_url')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>
          <div class="flex items-end">
            <label class="ui-banner-highlight-check">
              <input type="checkbox" name="target_blank" value="1" @checked(old('target_blank', $banner->target_blank)) class="ui-form-check h-4 w-4">
              <span>Nova aba</span>
            </label>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
          <div>
            <label for="hero_variant" class="ui-form-label">Variante do hero</label>
            <select id="hero_variant" name="hero_variant" class="ui-form-select">
              <option value="">Padrao</option>
              <option value="hero" @selected(old('hero_variant', $banner->hero_variant) === 'hero')>Hero</option>
              <option value="hero_short" @selected(old('hero_variant', $banner->hero_variant) === 'hero_short')>Hero curto</option>
            </select>
            @error('hero_variant')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="preload_mode" class="ui-form-label">Preload do video</label>
            <select id="preload_mode" name="preload_mode" class="ui-form-select">
              <option value="">Padrao</option>
              <option value="none" @selected(old('preload_mode', $banner->preload_mode) === 'none')>none</option>
              <option value="metadata" @selected(old('preload_mode', $banner->preload_mode ?? 'metadata') === 'metadata')>metadata</option>
              <option value="auto" @selected(old('preload_mode', $banner->preload_mode) === 'auto')>auto</option>
            </select>
            @error('preload_mode')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="alt_text" class="ui-form-label">Alt do fallback</label>
            <input id="alt_text" type="text" name="alt_text" value="{{ old('alt_text', $banner->alt_text) }}" class="ui-form-control" placeholder="Descreva a imagem de apoio">
            @error('alt_text')<p class="ui-form-error">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Publicacao" subtitle="Status, vigencia e comportamento visual do destaque" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label for="cor_fundo" class="ui-form-label">Cor de fundo</label>
          <input id="cor_fundo" type="color" name="cor_fundo" value="{{ old('cor_fundo', $banner->cor_fundo ?? '#00837B') }}" class="ui-banner-highlight-color">
          @error('cor_fundo')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="overlay_opacity" class="ui-form-label">Opacidade da sobreposicao</label>
          <input id="overlay_opacity" type="number" name="overlay_opacity" min="0" max="100" value="{{ old('overlay_opacity', $banner->overlay_opacity ?? 0) }}" class="ui-form-control">
          @error('overlay_opacity')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="ordem" class="ui-form-label">Ordem</label>
          <input id="ordem" type="number" name="ordem" min="0" value="{{ old('ordem', $banner->ordem ?? 0) }}" class="ui-form-control">
          @error('ordem')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="status" class="ui-form-label">Status</label>
          <select id="status" name="status" class="ui-form-select">
            @foreach(['publicado' => 'Publicado', 'rascunho' => 'Rascunho', 'arquivado' => 'Arquivado'] as $v => $lbl)
              <option value="{{ $v }}" @selected(old('status', $banner->status) === $v)>{{ $lbl }}</option>
            @endforeach
          </select>
          @error('status')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="inicio_publicacao" class="ui-form-label">Inicio (opcional)</label>
          <input id="inicio_publicacao" type="datetime-local" name="inicio_publicacao" value="{{ old('inicio_publicacao', optional($banner->inicio_publicacao)->format('Y-m-d\TH:i')) }}" class="ui-form-control">
          @error('inicio_publicacao')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="fim_publicacao" class="ui-form-label">Fim (opcional)</label>
          <input id="fim_publicacao" type="datetime-local" name="fim_publicacao" value="{{ old('fim_publicacao', optional($banner->fim_publicacao)->format('Y-m-d\TH:i')) }}" class="ui-form-control">
          @error('fim_publicacao')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="mt-4 grid gap-3 md:grid-cols-3">
        <label class="ui-banner-highlight-check">
          <input type="hidden" name="autoplay" value="0">
          <input type="checkbox" name="autoplay" value="1" @checked(old('autoplay', $banner->autoplay ?? true)) class="ui-form-check h-4 w-4">
          <span>Autoplay</span>
        </label>
        <label class="ui-banner-highlight-check">
          <input type="hidden" name="loop" value="0">
          <input type="checkbox" name="loop" value="1" @checked(old('loop', $banner->loop ?? true)) class="ui-form-check h-4 w-4">
          <span>Loop</span>
        </label>
        <label class="ui-banner-highlight-check">
          <input type="hidden" name="muted" value="0">
          <input type="checkbox" name="muted" value="1" @checked(old('muted', $banner->muted ?? true)) class="ui-form-check h-4 w-4">
          <span>Mutado</span>
        </label>
      </div>
    </x-dashboard.section-card>
  </div>

  <div class="space-y-5">
    <div x-show="mediaType === 'image'" x-cloak class="space-y-5">
      <x-dashboard.section-card title="Imagem desktop" subtitle="Formato recomendado 1920x700" class="ui-coord-dashboard-panel">
        <input type="hidden" name="pos_desktop_x" id="pos_desktop_x" value="{{ $dx }}">
        <input type="hidden" name="pos_desktop_y" id="pos_desktop_y" value="{{ $dy }}">
        <input type="hidden" name="pos_desktop" id="pos_desktop" value="{{ $dx }},{{ $dy }}">

        <div class="ui-banner-highlight-media-meta">
          <span class="ui-badge ui-badge-neutral">JPG, PNG ou WebP</span>
          <span class="text-xs text-[var(--ui-text-soft)]">Ate 6MB</span>
        </div>

        <input id="imagem_desktop" type="file" name="imagem_desktop" accept="image/*" class="ui-banner-highlight-file mt-3">
        @error('imagem_desktop')<p class="ui-form-error">{{ $message }}</p>@enderror

        <div id="preview-desktop" class="ui-banner-highlight-preview mt-3" style="aspect-ratio: 1920 / 700;">
          <img
            id="preview-desktop-img"
            src="{{ $desktopImageUrl }}"
            data-src-processed="{{ $banner->imagem_desktop_url ?? '' }}"
            data-src-original="{{ $banner->imagem_desktop_original_url ?? ($banner->imagem_desktop_url ?? '') }}"
            alt="Previa desktop"
            style="object-position: {{ $dx }}% {{ $dy }}%;"
            class="w-full h-full object-cover {{ $desktopImageUrl ? '' : 'hidden' }}"
          >
        </div>

        <p class="ui-profile-help mt-3">Arraste para ajustar o foco. Duplo clique centraliza a imagem.</p>
      </x-dashboard.section-card>

      <x-dashboard.section-card title="Imagem mobile" subtitle="Formato recomendado 1080x1080" class="ui-coord-dashboard-panel">
        <input type="hidden" name="pos_mobile_x" id="pos_mobile_x" value="{{ $mx }}">
        <input type="hidden" name="pos_mobile_y" id="pos_mobile_y" value="{{ $my }}">
        <input type="hidden" name="pos_mobile" id="pos_mobile" value="{{ $mx }},{{ $my }}">

        <div class="ui-banner-highlight-media-meta">
          <span class="ui-badge ui-badge-neutral">JPG, PNG ou WebP</span>
          <span class="text-xs text-[var(--ui-text-soft)]">Ate 6MB</span>
        </div>

        <input id="imagem_mobile" type="file" name="imagem_mobile" accept="image/*" class="ui-banner-highlight-file mt-3">
        @error('imagem_mobile')<p class="ui-form-error">{{ $message }}</p>@enderror

        <div id="preview-mobile" class="ui-banner-highlight-preview mt-3" style="aspect-ratio: 1 / 1;">
          <img
            id="preview-mobile-img"
            src="{{ $mobileImageUrl }}"
            data-src-processed="{{ $banner->imagem_mobile_url ?? '' }}"
            data-src-original="{{ $banner->imagem_mobile_original_url ?? ($banner->imagem_mobile_url ?? '') }}"
            alt="Previa mobile"
            style="object-position: {{ $mx }}% {{ $my }}%;"
            class="w-full h-full object-cover {{ $mobileImageUrl ? '' : 'hidden' }}"
          >
        </div>

        <p class="ui-profile-help mt-3">Arraste para ajustar o foco. Duplo clique centraliza a imagem.</p>
      </x-dashboard.section-card>
    </div>

    <div x-show="mediaType === 'video'" x-cloak class="space-y-5">
      <x-dashboard.section-card title="Videos de abertura" subtitle="Use MP4 leve, mutado e com poster para carregamento seguro" class="ui-coord-dashboard-panel">
        <div class="grid gap-5 lg:grid-cols-2">
          <div class="space-y-4">
            <div>
              <label for="video_desktop" class="ui-form-label">Video desktop</label>
              <input id="video_desktop" type="file" name="video_desktop" accept="video/mp4,video/quicktime" class="ui-banner-highlight-file mt-2" @change="updatePreview($event, 'desktopVideoPreview')">
              @error('video_desktop')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div class="ui-banner-highlight-video-preview" style="aspect-ratio: 16 / 9;">
              <template x-if="desktopVideoPreview">
                <video :src="desktopVideoPreview" :poster="desktopPosterPreview || desktopFallbackPreview" muted playsinline controls class="h-full w-full object-cover"></video>
              </template>
              <template x-if="!desktopVideoPreview">
                <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty h-full">Sem video desktop</div>
              </template>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label for="video_mobile" class="ui-form-label">Video mobile</label>
              <input id="video_mobile" type="file" name="video_mobile" accept="video/mp4,video/quicktime" class="ui-banner-highlight-file mt-2" @change="updatePreview($event, 'mobileVideoPreview')">
              @error('video_mobile')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>

            <div class="ui-banner-highlight-video-preview" style="aspect-ratio: 9 / 16;">
              <template x-if="mobileVideoPreview">
                <video :src="mobileVideoPreview" :poster="mobilePosterPreview || mobileFallbackPreview || desktopPosterPreview" muted playsinline controls class="h-full w-full object-cover"></video>
              </template>
              <template x-if="!mobileVideoPreview">
                <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty h-full">Sem video mobile</div>
              </template>
            </div>
          </div>
        </div>
      </x-dashboard.section-card>

      <x-dashboard.section-card title="Poster e fallback" subtitle="Poster aparece antes do autoplay. Fallback cobre navegadores e conexoes mais restritas." class="ui-coord-dashboard-panel">
        <div class="grid gap-5 xl:grid-cols-2">
          <div class="grid gap-5">
            <div>
              <label for="poster_desktop" class="ui-form-label">Poster desktop</label>
              <input id="poster_desktop" type="file" name="poster_desktop" accept="image/*" class="ui-banner-highlight-file mt-2" @change="updatePreview($event, 'desktopPosterPreview')">
              @error('poster_desktop')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="ui-banner-highlight-preview" style="aspect-ratio: 16 / 9;">
              <template x-if="desktopPosterPreview">
                <img :src="desktopPosterPreview" alt="Poster desktop" class="h-full w-full object-cover">
              </template>
              <template x-if="!desktopPosterPreview">
                <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty h-full">Sem poster desktop</div>
              </template>
            </div>

            <div>
              <label for="fallback_image_desktop" class="ui-form-label">Imagem fallback desktop</label>
              <input id="fallback_image_desktop" type="file" name="fallback_image_desktop" accept="image/*" class="ui-banner-highlight-file mt-2" @change="updatePreview($event, 'desktopFallbackPreview')">
              @error('fallback_image_desktop')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="ui-banner-highlight-preview" style="aspect-ratio: 16 / 9;">
              <template x-if="desktopFallbackPreview">
                <img :src="desktopFallbackPreview" alt="Fallback desktop" class="h-full w-full object-cover">
              </template>
              <template x-if="!desktopFallbackPreview">
                <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty h-full">Sem fallback desktop</div>
              </template>
            </div>
          </div>

          <div class="grid gap-5">
            <div>
              <label for="poster_mobile" class="ui-form-label">Poster mobile</label>
              <input id="poster_mobile" type="file" name="poster_mobile" accept="image/*" class="ui-banner-highlight-file mt-2" @change="updatePreview($event, 'mobilePosterPreview')">
              @error('poster_mobile')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="ui-banner-highlight-preview" style="aspect-ratio: 9 / 16;">
              <template x-if="mobilePosterPreview">
                <img :src="mobilePosterPreview" alt="Poster mobile" class="h-full w-full object-cover">
              </template>
              <template x-if="!mobilePosterPreview">
                <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty h-full">Sem poster mobile</div>
              </template>
            </div>

            <div>
              <label for="fallback_image_mobile" class="ui-form-label">Imagem fallback mobile</label>
              <input id="fallback_image_mobile" type="file" name="fallback_image_mobile" accept="image/*" class="ui-banner-highlight-file mt-2" @change="updatePreview($event, 'mobileFallbackPreview')">
              @error('fallback_image_mobile')<p class="ui-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="ui-banner-highlight-preview" style="aspect-ratio: 9 / 16;">
              <template x-if="mobileFallbackPreview">
                <img :src="mobileFallbackPreview" alt="Fallback mobile" class="h-full w-full object-cover">
              </template>
              <template x-if="!mobileFallbackPreview">
                <div class="ui-banner-highlight-thumb ui-banner-highlight-thumb-empty h-full">Sem fallback mobile</div>
              </template>
            </div>
          </div>
        </div>
      </x-dashboard.section-card>
    </div>
  </div>
</div>

<div class="mt-6 flex flex-wrap items-center gap-3">
  <button type="submit" class="ui-btn-primary">Salvar</button>
  <a href="{{ route('coordenador.banners-destaque.index') }}" class="ui-btn-secondary">Cancelar</a>
  <a href="{{ url('/') }}" target="_blank" class="ui-btn-secondary md:ml-auto">Ver na Home</a>
</div>

@push('scripts')
  @vite('resources/js/simple-previews.js')
@endpush
