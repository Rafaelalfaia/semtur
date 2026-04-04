@extends('site.layouts.app')

@php
    $canonical = localized_route('site.rota_do_cacau.index');
    $baseTitle = $rota?->titulo ?: ui_text('ui.cocoa_route.title');
    $title = $heroTranslation?->seo_title ?: ($heroTranslation?->titulo ?: $baseTitle);
    $description = $heroTranslation?->seo_description
        ?: ($heroTranslation?->lead ?: \Illuminate\Support\Str::limit(strip_tags((string) ($rota?->descricao ?: ui_text('ui.cocoa_route.meta_description'))), 160));
    $image = $heroMedia?->url ?: ($rota?->foto_capa_url ?: ($rota?->foto_perfil_url ?: asset('imagens/altamira.jpg')));
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

    $pageBlocks = $pageBlocks ?? collect();
    $rotaBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'territory_section' => $pageBlocks->get('territory_section'),
        'editions_section' => $pageBlocks->get('editions_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $rotaBlockTranslation = fn (string $key) => $rotaBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $rotaBlockTranslation('about_section');
    $territoryTranslation = $rotaBlockTranslation('territory_section');
    $editionsTranslation = $rotaBlockTranslation('editions_section');
    $emptyTranslation = $rotaBlockTranslation('empty_state');
    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.cocoa_route.title');
    $heroTitle = $heroTranslation?->titulo ?: $baseTitle;
    $heroSubtitle = $heroTranslation?->lead ?: ($rota?->descricao ? Str::limit(strip_tags($rota->descricao), 180) : ui_text('ui.cocoa_route.subtitle'));
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ($edicaoDestaque ? ui_text('ui.cocoa_route.view_editions') : (Route::has('site.home') ? ui_text('ui.common.back_to_home') : null));
    $heroPrimaryHref = $heroTranslation?->cta_href ?: ($edicaoDestaque ? '#edicoes-rota' : (Route::has('site.home') ? localized_route('site.home') : null));
    $aboutEyebrow = $aboutTranslation?->eyebrow ?: ui_text('ui.common.about');
    $aboutTitle = $aboutTranslation?->titulo ?: ui_text('ui.cocoa_route.about_title');
    $aboutSubtitle = $aboutTranslation?->lead ?: ui_text('ui.cocoa_route.subtitle');
    $territoryEyebrow = $territoryTranslation?->eyebrow ?: ui_text('ui.cocoa_route.territory_eyebrow');
    $territoryTitle = $territoryTranslation?->titulo ?: ui_text('ui.cocoa_route.territory_title');
    $territorySubtitle = $territoryTranslation?->lead ?: ui_text('ui.cocoa_route.territory_subtitle');
    $editionsEyebrow = $editionsTranslation?->eyebrow ?: ui_text('ui.cocoa_route.badge');
    $editionsTitle = $editionsTranslation?->titulo ?: ui_text('ui.cocoa_route.editions_title');
    $editionsSubtitle = $editionsTranslation?->lead ?: ui_text('ui.cocoa_route.subtitle');
    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.cocoa_route.empty_title');
    $emptyCopy = $emptyTranslation?->lead ?: ui_text('ui.cocoa_route.empty_copy');
    $heroMeta = [];
    $canManageEditionText = auth()->check() && auth()->user()->can('rota_do_cacau.edicoes.update');
    $canManageEditionPhotos = auth()->check() && auth()->user()->can('rota_do_cacau.edicoes.fotos.view');
    $canManageEditionVideos = auth()->check() && auth()->user()->can('rota_do_cacau.edicoes.videos.view');
    $canManageEditionSponsors = auth()->check() && auth()->user()->can('rota_do_cacau.edicoes.patrocinadores.view');
@endphp

<div class="site-page site-page-shell site-rota-page site-jogos-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.cocoa_route.title')],
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
            'page' => 'site.rota_do_cacau.index',
            'key' => 'hero',
            'label' => 'Texto da capa da Rota do Cacau',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.rota_do_cacau.index',
            'key' => 'hero',
            'label' => 'Imagem da capa da Rota do Cacau',
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

    @if(!$rota)
        <section class="site-section">
            @include('site.partials._content_editor', [
                'editorTitle' => $emptyTitle,
                'editorPage' => 'site.rota_do_cacau.index',
                'editorKey' => 'empty_state',
                'editorLabel' => 'Estado vazio da Rota do Cacau',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline',
                'editableTranslation' => $emptyTranslation,
                'editableStatus' => $rotaBlocks['empty_state']?->status ?? 'publicado',
                'editableFallback' => [
                    'titulo' => ui_text('ui.cocoa_route.empty_title'),
                    'lead' => ui_text('ui.cocoa_route.empty_copy'),
                ],
            ])
            <div class="site-empty-state">
                <h2 class="site-empty-state-title">{{ $emptyTitle }}</h2>
                <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
            </div>
        </section>
    @else
        <section class="site-section">
            <section class="site-surface site-content-block">
                @include('site.partials._content_editor', [
                    'editorTitle' => $aboutTitle,
                    'editorPage' => 'site.rota_do_cacau.index',
                    'editorKey' => 'about_section',
                    'editorLabel' => 'Seção sobre da Rota do Cacau',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $aboutTranslation,
                    'editableStatus' => $rotaBlocks['about_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.common.about'),
                        'titulo' => ui_text('ui.cocoa_route.about_title'),
                        'lead' => ui_text('ui.cocoa_route.subtitle'),
                    ],
                ])
                <div class="site-detail-profile">
                    <img src="{{ site_image_url($rota->foto_perfil_url ?: theme_asset('logo'), 'avatar') }}" alt="{{ $title }}" class="site-detail-avatar" loading="lazy" decoding="async">
                    <div>
                        <x-section-head :eyebrow="$aboutEyebrow" :title="$aboutTitle" :subtitle="$aboutSubtitle" />
                    </div>
                </div>

                <div class="site-prose">
                    {!! nl2br(e($rota->descricao)) !!}
                </div>
            </section>
        </section>

        <section class="site-section">
            <section class="site-surface site-content-block">
                @include('site.partials._content_editor', [
                    'editorTitle' => $territoryTitle,
                    'editorPage' => 'site.rota_do_cacau.index',
                    'editorKey' => 'territory_section',
                    'editorLabel' => 'Seção território da Rota do Cacau',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $territoryTranslation,
                    'editableStatus' => $rotaBlocks['territory_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.cocoa_route.territory_eyebrow'),
                        'titulo' => ui_text('ui.cocoa_route.territory_title'),
                        'lead' => ui_text('ui.cocoa_route.territory_subtitle'),
                    ],
                ])
                <x-section-head :eyebrow="$territoryEyebrow" :title="$territoryTitle" :subtitle="$territorySubtitle" />
            </section>
        </section>

        @if($edicoes->isNotEmpty())
            <section class="site-section" id="edicoes-rota">
                @include('site.partials._content_editor', [
                    'editorTitle' => $editionsTitle,
                    'editorPage' => 'site.rota_do_cacau.index',
                    'editorKey' => 'editions_section',
                    'editorLabel' => 'Seção edições da Rota do Cacau',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $editionsTranslation,
                    'editableStatus' => $rotaBlocks['editions_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.cocoa_route.badge'),
                        'titulo' => ui_text('ui.cocoa_route.editions_title'),
                        'lead' => ui_text('ui.cocoa_route.subtitle'),
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
                                    @if($canManageEditionText || $canManageEditionPhotos || $canManageEditionVideos || $canManageEditionSponsors)
                                        <div class="site-inline-actions">
                                            @if($canManageEditionText && Route::has('coordenador.rota-do-cacau.edicoes.edit'))
                                                <a href="{{ route('coordenador.rota-do-cacau.edicoes.edit', [$rota, $edicao]) }}" class="site-button-secondary">Texto e capa</a>
                                            @endif
                                            @if($canManageEditionPhotos && Route::has('coordenador.rota-do-cacau.edicoes.fotos.index'))
                                                <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="site-button-secondary">Fotos</a>
                                            @endif
                                            @if($canManageEditionVideos && Route::has('coordenador.rota-do-cacau.edicoes.videos.index'))
                                                <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="site-button-secondary">Vídeos</a>
                                            @endif
                                            @if($canManageEditionSponsors && Route::has('coordenador.rota-do-cacau.edicoes.patrocinadores.index'))
                                                <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="site-button-secondary">Patrocinadores</a>
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

