@extends('site.layouts.app')

@php
    $canonical = localized_route('site.rota_do_cacau.index');
    $title = $rota?->titulo ?: ui_text('ui.cocoa_route.title');
    $description = \Illuminate\Support\Str::limit(strip_tags((string) ($rota?->descricao ?: ui_text('ui.cocoa_route.meta_description'))), 160);
    $image = $rota?->foto_capa_url ?: ($rota?->foto_perfil_url ?: asset('imagens/altamira.jpg'));
@endphp

@section('title', $title.' • Visit Altamira')
@section('meta.description', $description)
@section('meta.image', $image)
@section('meta.canonical', $canonical)
@section('meta.type', 'website')

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $heroMeta = $rota ? array_filter([
        ($edicoes->count() ?? 0) ? (($edicoes->count() ?? 0).' '.ui_text('ui.cocoa_route.editions')) : null,
        ($edicoes->sum('fotos_count') ?? 0) ? ($edicoes->sum('fotos_count').' fotos') : null,
        ($edicoes->sum('videos_count') ?? 0) ? ($edicoes->sum('videos_count').' '.ui_text('ui.common.videos')) : null,
    ]) : [];
@endphp

<div class="site-page site-page-shell site-rota-page site-jogos-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.cocoa_route.title')],
        ],
        'badge' => ui_text('ui.cocoa_route.title'),
        'title' => $title,
        'subtitle' => $rota?->descricao ? Str::limit(strip_tags($rota->descricao), 180) : ui_text('ui.cocoa_route.subtitle'),
        'meta' => $heroMeta,
        'primaryActionLabel' => $edicaoDestaque ? ui_text('ui.cocoa_route.view_editions') : (Route::has('site.home') ? ui_text('ui.common.back_to_home') : null),
        'primaryActionHref' => $edicaoDestaque ? '#edicoes-rota' : (Route::has('site.home') ? localized_route('site.home') : null),
        'secondaryActionLabel' => Route::has('site.explorar') ? ui_text('ui.events.explore_city') : null,
        'secondaryActionHref' => Route::has('site.explorar') ? localized_route('site.explorar') : null,
        'image' => $image,
        'imageAlt' => $title,
        'compact' => true,
    ])

    @if(!$rota)
        <section class="site-section">
            <div class="site-empty-state">
                <h2 class="site-empty-state-title">{{ ui_text('ui.cocoa_route.empty_title') }}</h2>
                <p class="site-empty-state-copy">{{ ui_text('ui.cocoa_route.empty_copy') }}</p>
            </div>
        </section>
    @else
        <section class="site-section">
            <section class="site-surface site-content-block">
                <div class="site-detail-profile">
                    <img src="{{ site_image_url($rota->foto_perfil_url ?: theme_asset('logo'), 'avatar') }}" alt="{{ $title }}" class="site-detail-avatar" loading="lazy" decoding="async">
                    <div>
                        <x-section-head :eyebrow="ui_text('ui.common.about')" :title="ui_text('ui.cocoa_route.about_title')" :subtitle="ui_text('ui.cocoa_route.subtitle')" />
                    </div>
                </div>

                <div class="site-prose">
                    {!! nl2br(e($rota->descricao)) !!}
                </div>
            </section>
        </section>

        <section class="site-section">
            <section class="site-surface site-content-block">
                <x-section-head :eyebrow="ui_text('ui.cocoa_route.territory_eyebrow')" :title="ui_text('ui.cocoa_route.territory_title')" :subtitle="ui_text('ui.cocoa_route.territory_subtitle')" />
            </section>
        </section>

        @if($edicoes->isNotEmpty())
            <section class="site-section" id="edicoes-rota">
                <x-section-head
                    :eyebrow="ui_text('ui.cocoa_route.badge')"
                    :title="ui_text('ui.cocoa_route.editions_title')"
                    :subtitle="ui_text('ui.cocoa_route.subtitle')"
                />

                <div class="site-jogos-editions">
                    @foreach($edicoes as $edicao)
                        @php
                            $photos = $edicao->fotos->take(4);
                            $videos = $edicao->videos->take(3);
                            $sponsors = $edicao->patrocinadores->take(6);
                            $cover = $edicao->capa_url ?: $rota->foto_capa_url ?: asset('imagens/altamira.jpg');
                            $media = collect();

                            foreach ($photos as $foto) {
                                $media->push([
                                    'type' => 'photo',
                                    'src' => $foto->imagem_url,
                                    'alt' => $foto->legenda ?: $edicao->titulo,
                                    'title' => $foto->legenda ?: $edicao->titulo,
                                ]);
                            }

                            foreach ($videos as $video) {
                                if ($video->embed_url_resolvida) {
                                    $media->push([
                                        'type' => 'video',
                                        'src' => $video->embed_url_resolvida,
                                        'alt' => $video->titulo ?: 'Vídeo da edição',
                                        'title' => $video->titulo ?: 'Vídeo da edição',
                                    ]);
                                }
                            }
                        @endphp

                        <article
                            class="site-surface site-jogos-edition-card"
                            x-data="{
                                open: false,
                                index: 0,
                                items: @js($media->values()),
                                show(i) {
                                    if (!this.items.length) return;
                                    this.index = i;
                                    this.open = true;
                                    document.body.style.overflow = 'hidden';
                                },
                                close() {
                                    this.open = false;
                                    document.body.style.overflow = '';
                                },
                                next() {
                                    if (!this.items.length) return;
                                    this.index = (this.index + 1) % this.items.length;
                                },
                                prev() {
                                    if (!this.items.length) return;
                                    this.index = (this.index - 1 + this.items.length) % this.items.length;
                                },
                                current() {
                                    return this.items[this.index] || null;
                                }
                            }"
                        >
                            <div class="site-jogos-edition-media">
                                <img
                                    src="{{ site_image_url($cover, 'card') }}"
                                    alt="{{ $edicao->titulo }}"
                                    class="site-jogos-edition-image"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>

                            <div class="site-jogos-edition-body">
                                <div class="site-jogos-edition-head">
                                    <span class="site-badge">{{ $edicao->ano }}</span>
                                    <h3 class="site-jogos-edition-title">{{ $edicao->titulo }}</h3>
                                    <p class="site-jogos-edition-summary">{{ Str::limit(strip_tags($edicao->descricao), 220) }}</p>
                                </div>

                                <div class="site-jogos-edition-stats">
                                    <span class="site-page-hero-meta-item">{{ $edicao->fotos_count }} fotos</span>
                                    <span class="site-page-hero-meta-item">{{ $edicao->videos_count }} {{ ui_text('ui.common.videos') }}</span>
                                    <span class="site-page-hero-meta-item">{{ $edicao->patrocinadores_count }} {{ ui_text('ui.cocoa_route.partners_label') }}</span>
                                </div>

                                @if($photos->isNotEmpty())
                                    <div class="site-jogos-inline-block">
                                        <div class="site-jogos-inline-label">{{ ui_text('ui.common.gallery') }}</div>
                                        <div class="site-jogos-photo-strip">
                                            @foreach($photos as $mediaIndex => $foto)
                                                <button type="button" class="site-jogos-photo-button" @click="show({{ $mediaIndex }})">
                                                    <img
                                                        src="{{ site_image_url($foto->imagem_url, 'mini') }}"
                                                        alt="{{ $foto->legenda ?: $edicao->titulo }}"
                                                        class="site-jogos-photo-thumb"
                                                        loading="lazy"
                                                        decoding="async"
                                                    >
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($videos->isNotEmpty())
                                    <div class="site-jogos-inline-block">
                                        <div class="site-jogos-inline-label">{{ ui_text('ui.common.videos') }}</div>
                                        <div class="site-jogos-video-links">
                                            @foreach($videos as $videoIndex => $video)
                                                @php
                                                    $videoMediaIndex = $photos->count() + $videoIndex;
                                                @endphp
                                                @if($video->embed_url_resolvida)
                                                    <button type="button" class="site-jogos-video-card" @click="show({{ $videoMediaIndex }})">
                                                        <span class="site-jogos-video-icon" aria-hidden="true">Play</span>
                                                        <span class="site-jogos-video-card-title">{{ Str::limit($video->titulo ?: ui_text('ui.common.watch_now'), 42) }}</span>
                                                    </button>
                                                @else
                                                    <span class="site-jogos-video-text">{{ Str::limit($video->titulo ?: ui_text('ui.common.videos'), 42) }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($sponsors->isNotEmpty())
                                    <div class="site-jogos-inline-block">
                                        <div class="site-jogos-inline-label">Patrocinadores</div>
                                        <div class="site-jogos-sponsor-strip">
                                            @foreach($sponsors as $patrocinador)
                                                @if(filled($patrocinador->url))
                                                    <a href="{{ $patrocinador->url }}" target="_blank" rel="noopener noreferrer" class="site-jogos-sponsor-item" aria-label="{{ $patrocinador->nome }}">
                                                        @if($patrocinador->logo_url)
                                                            <img src="{{ site_image_url($patrocinador->logo_url, 'mini') }}" alt="{{ $patrocinador->nome }}" class="site-jogos-sponsor-logo" loading="lazy" decoding="async">
                                                        @else
                                                            <span class="site-jogos-sponsor-name">{{ Str::limit($patrocinador->nome, 20) }}</span>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="site-jogos-sponsor-item" aria-label="{{ $patrocinador->nome }}">
                                                        @if($patrocinador->logo_url)
                                                            <img src="{{ site_image_url($patrocinador->logo_url, 'mini') }}" alt="{{ $patrocinador->nome }}" class="site-jogos-sponsor-logo" loading="lazy" decoding="async">
                                                        @else
                                                            <span class="site-jogos-sponsor-name">{{ Str::limit($patrocinador->nome, 20) }}</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                                <div class="site-lightbox-frame site-jogos-lightbox-frame">
                                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="Fechar mídia">&times;</button>
                                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="Mídia anterior">&#8249;</button>

                                    <template x-if="current()?.type === 'photo'">
                                        <img :src="current()?.src" :alt="current()?.alt || ''" class="site-lightbox-image">
                                    </template>

                                    <template x-if="current()?.type === 'video'">
                                        <iframe
                                            class="site-jogos-lightbox-embed"
                                            :src="current()?.src"
                                            :title="current()?.title || ui_text('ui.cocoa_route.video_title')"
                                            loading="lazy"
                                            allow="autoplay; encrypted-media; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    </template>

                                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="Próxima mídia">&#8250;</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
@endsection

