@extends('site.layouts.app')

@php
    $canonical = localized_route('site.jogos_indigenas.index');
    $baseTitle = $jogo?->titulo ?: ui_text('ui.indigenous_games.title');
    $title = $heroTranslation?->seo_title ?: ($heroTranslation?->titulo ?: $baseTitle);
    $description = $heroTranslation?->seo_description
        ?: ($heroTranslation?->lead ?: \Illuminate\Support\Str::limit(strip_tags($jogo?->descricao ?: ui_text('ui.indigenous_games.meta_description')), 160));
    $image = $heroMedia?->url ?: ($jogo?->foto_capa_url ?: $jogo?->foto_perfil_url ?: theme_asset('hero_image'));
@endphp

@section('title', $title)
@section('meta.description', $description)
@section('meta.image', $image)
@section('meta.canonical', $canonical)
@section('meta.type', 'website')

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $pageBlocks = $pageBlocks ?? collect();
    $jogosBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'editions_section' => $pageBlocks->get('editions_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $jogosTranslation = fn (string $key) => $jogosBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $jogosTranslation('about_section');
    $editionsTranslation = $jogosTranslation('editions_section');
    $emptyTranslation = $jogosTranslation('empty_state');

    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.indigenous_games.badge');
    $heroTitle = $heroTranslation?->titulo ?: $baseTitle;
    $heroSubtitle = $heroTranslation?->lead ?: ($jogo?->descricao ? Str::limit(strip_tags($jogo->descricao), 180) : ui_text('ui.indigenous_games.subtitle'));
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ($edicaoDestaque ? ui_text('ui.indigenous_games.view_editions') : (Route::has('site.home') ? ui_text('ui.common.back_to_home') : null));
    $heroPrimaryHref = $heroTranslation?->cta_href ?: ($edicaoDestaque ? '#edicoes-jogos' : (Route::has('site.home') ? localized_route('site.home') : null));
    $heroMeta = [];

    $aboutEyebrow = $aboutTranslation?->eyebrow ?: ui_text('ui.common.about');
    $aboutTitle = $aboutTranslation?->titulo ?: ui_text('ui.indigenous_games.about_title');
    $aboutSubtitle = $aboutTranslation?->lead ?: ui_text('ui.indigenous_games.about_subtitle');
    $editionsEyebrow = $editionsTranslation?->eyebrow ?: ui_text('ui.indigenous_games.badge');
    $editionsTitle = $editionsTranslation?->titulo ?: ui_text('ui.indigenous_games.editions_title');
    $editionsSubtitle = $editionsTranslation?->lead ?: ui_text('ui.indigenous_games.subtitle');
    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.indigenous_games.empty_title');
    $emptyCopy = $emptyTranslation?->lead ?: ui_text('ui.indigenous_games.empty_copy');

    $canManageEditionText = auth()->check() && auth()->user()->can('jogos_indigenas.edicoes.update');
    $canManageEditionPhotos = auth()->check() && auth()->user()->can('jogos_indigenas.edicoes.fotos.view');
    $canManageEditionVideos = auth()->check() && auth()->user()->can('jogos_indigenas.edicoes.videos.view');
    $canManageEditionSponsors = auth()->check() && auth()->user()->can('jogos_indigenas.edicoes.patrocinadores.view');
    $canManageBase = auth()->check() && auth()->user()->can('jogos_indigenas.update');
    $editJogoHref = $canManageBase && $jogo && Route::has('coordenador.jogos-indigenas.edit')
        ? route('coordenador.jogos-indigenas.edit', $jogo)
        : null;
@endphp

<div class="site-page site-page-shell site-jogos-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.indigenous_games.title')],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => $heroMeta,
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => Route::has('site.explorar') ? ui_text('ui.events.explore_city') : null,
        'secondaryActionHref' => Route::has('site.explorar') ? localized_route('site.explorar') : null,
        'image' => $image,
        'imageAlt' => $heroTitle,
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.jogos_indigenas.index',
            'key' => 'hero',
            'label' => 'Texto da capa dos Jogos Indígenas',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.jogos_indigenas.index',
            'key' => 'hero',
            'label' => 'Imagem da capa dos Jogos Indígenas',
            'locale' => route_locale(),
            'trigger_label' => 'Editar imagem',
            'translation' => $heroTranslation ?? null,
            'media' => $heroMedia ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
            'media_slot' => 'hero',
            'media_label' => 'Imagem da capa',
            'preview_label' => 'imagem atual da capa',
        ],
    ])

    @if(!$jogo)
        <section class="site-section">
            <div class="site-empty-state">
                @include('site.partials._content_editor', [
                    'editorTitle' => $emptyTitle,
                    'editorPage' => 'site.jogos_indigenas.index',
                    'editorKey' => 'empty_state',
                    'editorLabel' => 'Estado vazio dos Jogos Indígenas',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['titulo', 'lead'],
                    'editableTranslation' => $emptyTranslation,
                    'editableStatus' => $jogosBlocks['empty_state']?->status ?? 'publicado',
                    'editableFallback' => [
                        'titulo' => ui_text('ui.indigenous_games.empty_title'),
                        'lead' => ui_text('ui.indigenous_games.empty_copy'),
                    ],
                ])
                <h2 class="site-empty-state-title">{{ $emptyTitle }}</h2>
                <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
            </div>
        </section>
    @else
        <section class="site-section">
            <section class="site-surface site-content-block">
                @include('site.partials._content_editor', [
                    'editorTitle' => $aboutTitle,
                    'editorPage' => 'site.jogos_indigenas.index',
                    'editorKey' => 'about_section',
                    'editorLabel' => 'Seção sobre dos Jogos Indígenas',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $aboutTranslation,
                    'editableStatus' => $jogosBlocks['about_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.common.about'),
                        'titulo' => ui_text('ui.indigenous_games.about_title'),
                        'lead' => ui_text('ui.indigenous_games.about_subtitle'),
                    ],
                ])
                @if($editJogoHref)
                    <div class="site-inline-actions">
                        <a href="{{ $editJogoHref }}" class="site-button-secondary">Editar dados do evento</a>
                    </div>
                @endif
                <div class="site-detail-profile">
                    <img src="{{ site_image_url($jogo->foto_perfil_url ?: theme_asset('logo'), 'avatar') }}" alt="{{ $title }}" class="site-detail-avatar" loading="lazy" decoding="async">
                    <div>
                        <x-section-head :eyebrow="$aboutEyebrow" :title="$aboutTitle" :subtitle="$aboutSubtitle" />
                    </div>
                </div>

                <div class="site-prose">
                    {!! nl2br(e($jogo->descricao)) !!}
                </div>
            </section>
        </section>

        @if($edicoes->isNotEmpty())
            <section class="site-section" id="edicoes-jogos">
                @include('site.partials._content_editor', [
                    'editorTitle' => $editionsTitle,
                    'editorPage' => 'site.jogos_indigenas.index',
                    'editorKey' => 'editions_section',
                    'editorLabel' => 'Seção edições dos Jogos Indígenas',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $editionsTranslation,
                    'editableStatus' => $jogosBlocks['editions_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.indigenous_games.badge'),
                        'titulo' => ui_text('ui.indigenous_games.editions_title'),
                        'lead' => ui_text('ui.indigenous_games.subtitle'),
                    ],
                ])
                <x-section-head
                    :eyebrow="$editionsEyebrow"
                    :title="$editionsTitle"
                    :subtitle="$editionsSubtitle"
                />

                <div class="site-jogos-editions">
                    @foreach($edicoes as $edicao)
                        @php
                            $photos = $edicao->fotos->take(4);
                            $videos = $edicao->videos->take(3);
                            $sponsors = $edicao->patrocinadores->take(6);
                            $cover = $edicao->capa_url ?: $jogo->foto_capa_url ?: theme_asset('hero_image');
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
                                    @if($canManageEditionText || $canManageEditionPhotos || $canManageEditionVideos || $canManageEditionSponsors)
                                        <div class="site-inline-actions">
                                            @if($canManageEditionText && Route::has('coordenador.jogos-indigenas.edicoes.edit'))
                                                <a href="{{ route('coordenador.jogos-indigenas.edicoes.edit', [$jogo, $edicao]) }}" class="site-button-secondary">Texto e capa</a>
                                            @endif
                                            @if($canManageEditionPhotos && Route::has('coordenador.jogos-indigenas.edicoes.fotos.index'))
                                                <a href="{{ route('coordenador.jogos-indigenas.edicoes.fotos.index', [$jogo, $edicao]) }}" class="site-button-secondary">Fotos</a>
                                            @endif
                                            @if($canManageEditionVideos && Route::has('coordenador.jogos-indigenas.edicoes.videos.index'))
                                                <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.index', [$jogo, $edicao]) }}" class="site-button-secondary">Vídeos</a>
                                            @endif
                                            @if($canManageEditionSponsors && Route::has('coordenador.jogos-indigenas.edicoes.patrocinadores.index'))
                                                <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="site-button-secondary">Patrocinadores</a>
                                            @endif
                                        </div>
                                    @endif

                                    <span class="site-badge">{{ $edicao->ano }}</span>
                                    <h3 class="site-jogos-edition-title">{{ $edicao->titulo }}</h3>
                                    <p class="site-jogos-edition-summary">{{ Str::limit(strip_tags($edicao->descricao), 220) }}</p>
                                </div>

                                <div class="site-jogos-edition-stats">
                                    <span class="site-page-hero-meta-item">{{ $edicao->fotos_count }} fotos</span>
                                    <span class="site-page-hero-meta-item">{{ $edicao->videos_count }} {{ ui_text('ui.common.videos') }}</span>
                                    <span class="site-page-hero-meta-item">{{ $edicao->patrocinadores_count }} {{ ui_text('ui.indigenous_games.partners_label') }}</span>
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
                                                    <span class="site-jogos-video-text">{{ Str::limit($video->titulo ?: 'Vídeo', 42) }}</span>
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
                                            :title="current()?.title || 'Vídeo da edição'"
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
