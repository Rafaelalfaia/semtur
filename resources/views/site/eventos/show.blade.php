@extends('site.layouts.app')

@php
    $seoTitle = trim(($evento->nome ?? ui_text('ui.events.event')).($edicao->ano ? ' '.$edicao->ano : '').' '.ui_text('ui.events.in_city_suffix'));
    $seoDescription = \Illuminate\Support\Str::limit(strip_tags($edicao->resumo ?: ($evento->descricao ?? ui_text('ui.events.details'))), 160);
    $seoImage = $evento->capa_url
        ?? ($evento->capa_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($evento->capa_path) : null)
        ?? $evento->perfil_url
        ?? ($evento->perfil_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($evento->perfil_path) : null)
        ?? theme_asset('hero_image');
    $seoCanonical = isset($edicao->ano) && $edicao->ano
        ? localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $edicao->ano])
        : localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id]);
@endphp

@section('title', $seoTitle)
@section('meta.description', $seoDescription)
@section('meta.image', $seoImage)
@section('meta.canonical', $seoCanonical)
@section('meta.type', 'article')

@php
    $eventSchema = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $seoCanonical.'#breadcrumbs',
            'itemListElement' => array_values(array_filter([
                ['@type' => 'ListItem', 'position' => 1, 'name' => ui_text('ui.nav.home'), 'item' => localized_route('site.home')],
                \Illuminate\Support\Facades\Route::has('eventos.index') ? ['@type' => 'ListItem', 'position' => 2, 'name' => ui_text('ui.events.title'), 'item' => localized_route('eventos.index')] : null,
                ['@type' => 'ListItem', 'position' => 3, 'name' => $seoTitle, 'item' => $seoCanonical],
            ])),
        ],
        array_filter([
            '@type' => 'Event',
            '@id' => $seoCanonical.'#event',
            'name' => $evento->nome ?? ui_text('ui.events.event'),
            'description' => $seoDescription,
            'url' => $seoCanonical,
            'image' => [$seoImage],
            'startDate' => !empty($edicao->data_inicio) ? \Illuminate\Support\Carbon::parse($edicao->data_inicio)->toAtomString() : null,
            'endDate' => !empty($edicao->data_fim) ? \Illuminate\Support\Carbon::parse($edicao->data_fim)->toAtomString() : null,
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => array_filter([
                '@type' => 'Place',
                'name' => trim((string) ($edicao->local ?? $evento->cidade ?? ui_text('ui.common.altamira'))),
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $evento->cidade ?? ui_text('ui.common.altamira'),
                    'addressRegion' => 'PA',
                    'addressCountry' => 'BR',
                ],
                'geo' => (is_numeric($edicao->lat ?? null) && is_numeric($edicao->lng ?? null))
                    ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $edicao->lat, 'longitude' => (float) $edicao->lng]
                    : null,
            ], fn ($value) => $value !== null),
            'organizer' => ['@type' => 'Organization', 'name' => 'VisitAltamira', 'url' => config('app.url') ?: url('/')],
        ], fn ($value) => $value !== null),
    ];
@endphp

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $eventSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageBlocks = $pageBlocks ?? collect();
    $eventShowBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'editions_section' => $pageBlocks->get('editions_section'),
        'highlights_section' => $pageBlocks->get('highlights_section'),
        'summary_sidebar' => $pageBlocks->get('summary_sidebar'),
        'gallery_section' => $pageBlocks->get('gallery_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $eventShowTranslation = fn (string $key) => $eventShowBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $eventShowTranslation('about_section');
    $editionsTranslation = $eventShowTranslation('editions_section');
    $highlightsTranslation = $eventShowTranslation('highlights_section');
    $summaryTranslation = $eventShowTranslation('summary_sidebar');
    $galleryTranslation = $eventShowTranslation('gallery_section');
    $emptyTranslation = $eventShowTranslation('empty_state');

    $pub = fn ($path) => $path ? Storage::disk('public')->url($path) : null;
    $nome = $evento->nome ?? ui_text('ui.events.event');
    $cidade = $evento->cidade ?? ui_text('ui.common.altamira');
    $descricao = $edicao->resumo ?: ($evento->descricao ?? null);
    $capaUrl = $heroMedia?->url
        ?: $evento->capa_url
        ?: $pub($evento->capa_path ?? null)
        ?: $evento->perfil_url
        ?: $pub($evento->perfil_path ?? null)
        ?: theme_asset('hero_image');
    $nota = property_exists($evento, 'rating') ? (float) $evento->rating : null;
    $quando = $edicao->periodo
        ?: (($edicao->data_inicio ? \Carbon\Carbon::parse($edicao->data_inicio)->format('d/m/Y') : null)
        . ($edicao->data_fim ? ' - '.\Carbon\Carbon::parse($edicao->data_fim)->format('d/m/Y') : ''))
        ?: ($edicao->ano ?? null);
    $onde = trim($edicao->local ?? '') ?: $cidade;
    $lat = is_numeric($edicao->lat ?? null) ? (float) $edicao->lat : null;
    $lng = is_numeric($edicao->lng ?? null) ? (float) $edicao->lng : null;
    $mapBase = localized_route('site.mapa');
    $slugOrId = $evento->slug ?? $evento->id;
    $mapQuery = array_filter(['focus' => 'evento:'.$slugOrId, 'lat' => $lat, 'lng' => $lng, 'open' => 1], fn ($v) => $v !== null && $v !== '');
    $mapHref = $mapBase.(count($mapQuery) ? ('?'.http_build_query($mapQuery)) : '');
    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.events.event');
    $heroTitle = $heroTranslation?->titulo ?: $nome;
    $heroSubtitle = $heroTranslation?->lead ?: ui_text('ui.events.description_subtitle');
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.common.open_map');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: $mapHref;

    $galeria = collect($edicao->midias ?? [])->map(function ($midia) {
        $src = Str::startsWith($midia->path, ['http://', 'https://', '/'])
            ? $midia->path
            : Storage::disk('public')->url($midia->path);

        return [
            'src' => $src,
            'alt' => $midia->alt ?? '',
        ];
    })->values();

    $atrativos = collect($edicao->atrativos ?? [])->map(function ($atrativo) use ($pub) {
        return [
            'title' => $atrativo->nome,
            'subtitle' => ui_text('ui.events.highlights'),
            'summary' => Str::limit(strip_tags((string) $atrativo->descricao), 120),
            'image' => $atrativo->thumb_url ?? $pub($atrativo->thumb_path ?? null) ?? theme_asset('hero_image'),
            'badge' => ui_text('ui.events.highlight_badge'),
        ];
    })->values();

    $anos = collect($anos ?? []);
    $hasComplementaryContent = $galeria->isNotEmpty() || $atrativos->isNotEmpty();

    $canManageEvent = auth()->check() && auth()->user()->can('eventos.manage');
    $canManageEdition = auth()->check() && auth()->user()->can('eventos.edicoes.manage');
    $canManageHighlights = auth()->check() && auth()->user()->can('eventos.atrativos.manage');
    $canManageMedia = auth()->check() && auth()->user()->can('eventos.midias.manage');

    $aboutEyebrow = $aboutTranslation?->eyebrow ?: ui_text('ui.common.about');
    $aboutTitle = $aboutTranslation?->titulo ?: ui_text('ui.events.description_title');
    $aboutSubtitle = $aboutTranslation?->lead ?: ui_text('ui.events.description_subtitle');

    $editionsEyebrow = $editionsTranslation?->eyebrow ?: ui_text('ui.events.editions');
    $editionsTitle = $editionsTranslation?->titulo ?: ui_text('ui.events.choose_year');
    $editionsSubtitle = $editionsTranslation?->lead ?: ui_text('ui.events.choose_year_subtitle');

    $highlightsEyebrow = $highlightsTranslation?->eyebrow ?: ui_text('ui.events.highlights');
    $highlightsTitle = $highlightsTranslation?->titulo ?: ui_text('ui.events.highlights_title');
    $highlightsSubtitle = $highlightsTranslation?->lead ?: ui_text('ui.events.highlights_subtitle');

    $summaryEyebrow = $summaryTranslation?->eyebrow ?: ui_text('ui.common.service');
    $summaryTitle = $summaryTranslation?->titulo ?: ui_text('ui.events.service_title');

    $galleryEyebrow = $galleryTranslation?->eyebrow ?: ui_text('ui.common.gallery');
    $galleryTitle = $galleryTranslation?->titulo ?: ui_text('ui.events.edition_gallery_title');
    $gallerySubtitle = $galleryTranslation?->lead ?: ui_text('ui.events.edition_gallery_subtitle');

    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.events.edition_empty_description');
    $emptyCopy = $emptyTranslation?->lead ?: 'Esta edição ainda não possui galeria ou atrativos publicados no portal.';
@endphp

<div class="site-page site-page-shell site-event-detail-page">
    @include('site.partials._page_hero', [
        'backHref' => Route::has('eventos.index') ? localized_route('eventos.index') : localized_route('site.home'),
        'breadcrumbs' => array_values(array_filter([
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            Route::has('eventos.index') ? ['label' => ui_text('ui.events.title'), 'href' => localized_route('eventos.index')] : null,
            ['label' => $heroTitle],
        ])),
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [$cidade, $quando, $nota ? number_format($nota, 1, ',', '.').' '.ui_text('ui.events.rating_suffix') : null],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => Route::has('eventos.index') ? ui_text('ui.agenda.view_full_agenda') : null,
        'secondaryActionHref' => Route::has('eventos.index') ? localized_route('eventos.index') : null,
        'image' => $capaUrl,
        'imageAlt' => $heroTitle,
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.eventos.show',
            'key' => 'hero',
            'label' => 'Texto da capa do evento',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.eventos.show',
            'key' => 'hero',
            'label' => 'Imagem da capa do evento',
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

    @if($canManageEvent || $canManageEdition || $canManageHighlights || $canManageMedia)
        <section class="site-section">
            <div class="site-inline-actions">
                @if($canManageEvent && Route::has('coordenador.eventos.edit'))
                    <a href="{{ route('coordenador.eventos.edit', $evento) }}" class="site-button-secondary">Editar evento</a>
                @endif
                @if($canManageEdition && Route::has('coordenador.edicoes.edit'))
                    <a href="{{ route('coordenador.edicoes.edit', $edicao) }}" class="site-button-secondary">Texto e capa</a>
                @endif
                @if($canManageMedia && Route::has('coordenador.edicoes.midias.index'))
                    <a href="{{ route('coordenador.edicoes.midias.index', $edicao) }}" class="site-button-secondary">Fotos</a>
                @endif
                @if($canManageHighlights && Route::has('coordenador.edicoes.atrativos.index'))
                    <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="site-button-secondary">Atrativos</a>
                @endif
            </div>
        </section>
    @endif

    @if($anos->count() > 1)
        <section class="site-section">
            <div class="site-surface-soft">
                @include('site.partials._content_editor', [
                    'editorTitle' => $editionsTitle,
                    'editorPage' => 'site.eventos.show',
                    'editorKey' => 'editions_section',
                    'editorLabel' => 'Seção de edições do evento',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $editionsTranslation,
                    'editableStatus' => $eventShowBlocks['editions_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.events.editions'),
                        'titulo' => ui_text('ui.events.choose_year'),
                        'lead' => ui_text('ui.events.choose_year_subtitle'),
                    ],
                ])
                <x-section-head :eyebrow="$editionsEyebrow" :title="$editionsTitle" :subtitle="$editionsSubtitle" />
                <div class="site-filter-row">
                    @foreach($anos as $ano)
                        <a href="{{ localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $ano]) }}" class="{{ $ano == $edicao->ano ? 'site-year-chip is-active' : 'site-year-chip' }}">{{ $ano }}</a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $aboutTitle,
                        'editorPage' => 'site.eventos.show',
                        'editorKey' => 'about_section',
                        'editorLabel' => 'Seção de apresentação do evento',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'lead'],
                        'editableTranslation' => $aboutTranslation,
                        'editableStatus' => $eventShowBlocks['about_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => ui_text('ui.common.about'),
                            'titulo' => ui_text('ui.events.description_title'),
                            'lead' => ui_text('ui.events.description_subtitle'),
                        ],
                    ])
                    <x-section-head :eyebrow="$aboutEyebrow" :title="$aboutTitle" :subtitle="$aboutSubtitle" />
                    <div class="site-prose">{!! $descricao ? nl2br(e($descricao)) : '<p>'.e(ui_text('ui.events.edition_empty_description')).'</p>' !!}</div>
                </section>

                @if($atrativos->isNotEmpty())
                    <section class="site-section">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $highlightsTitle,
                            'editorPage' => 'site.eventos.show',
                            'editorKey' => 'highlights_section',
                            'editorLabel' => 'Seção de atrativos do evento',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'lead'],
                            'editableTranslation' => $highlightsTranslation,
                            'editableStatus' => $eventShowBlocks['highlights_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.events.highlights'),
                                'titulo' => ui_text('ui.events.highlights_title'),
                                'lead' => ui_text('ui.events.highlights_subtitle'),
                            ],
                        ])
                        <x-section-head :eyebrow="$highlightsEyebrow" :title="$highlightsTitle" :subtitle="$highlightsSubtitle" />
                        <div class="site-card-list-grid">
                            @foreach($atrativos as $item)
                                <div class="site-card-list">
                                    <div class="site-card-list-media">
                                        <img src="{{ site_image_url($item['image'], 'card') }}" alt="{{ $item['title'] }}" class="site-card-list-image" loading="lazy" decoding="async">
                                    </div>
                                    <div class="site-card-list-body">
                                        <span class="site-badge">{{ $item['badge'] }}</span>
                                        <h3 class="site-card-list-title">{{ $item['title'] }}</h3>
                                        <p class="site-card-list-summary">{{ $item['summary'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $summaryTitle,
                        'editorPage' => 'site.eventos.show',
                        'editorKey' => 'summary_sidebar',
                        'editorLabel' => 'Resumo lateral do evento',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo'],
                        'editableTranslation' => $summaryTranslation,
                        'editableStatus' => $eventShowBlocks['summary_sidebar']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => ui_text('ui.common.service'),
                            'titulo' => ui_text('ui.events.service_title'),
                        ],
                    ])
                    <x-section-head :eyebrow="$summaryEyebrow" :title="$summaryTitle" />
                    <div class="site-stats-grid">
                        <div class="site-stat-card"><span class="site-stat-label">{{ ui_text('ui.common.date') }}</span><span class="site-stat-value">{{ $quando ?: ui_text('ui.events.to_define') }}</span></div>
                        <div class="site-stat-card"><span class="site-stat-label">{{ ui_text('ui.common.place') }}</span><span class="site-stat-value">{{ $onde }}</span></div>
                        <div class="site-stat-card"><span class="site-stat-label">{{ ui_text('ui.common.photos') }}</span><span class="site-stat-value">{{ $galeria->count() }}</span></div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    @if($galeria->isNotEmpty())
        <section class="site-section site-event-detail-gallery-section" x-data="{ open:false,index:0,images:@js($galeria), show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; }, close(){ this.open=false; document.body.style.overflow=''; }, next(){ this.index=(this.index+1)%this.images.length; }, prev(){ this.index=(this.index-1+this.images.length)%this.images.length; } }">
            @include('site.partials._content_editor', [
                'editorTitle' => $galleryTitle,
                'editorPage' => 'site.eventos.show',
                'editorKey' => 'gallery_section',
                'editorLabel' => 'Seção de galeria do evento',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar texto',
                'editorFields' => ['eyebrow', 'titulo', 'lead'],
                'editableTranslation' => $galleryTranslation,
                'editableStatus' => $eventShowBlocks['gallery_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'eyebrow' => ui_text('ui.common.gallery'),
                    'titulo' => ui_text('ui.events.edition_gallery_title'),
                    'lead' => ui_text('ui.events.edition_gallery_subtitle'),
                ],
            ])
            <x-section-head :eyebrow="$galleryEyebrow" :title="$galleryTitle" :subtitle="$gallerySubtitle" />
            <div class="site-gallery-grid">
                @foreach($galeria as $index => $img)
                    <button type="button" class="site-gallery-button" @click="show({{ $index }})">
                        <img src="{{ site_image_url($img['src'], 'gallery') }}" alt="{{ $img['alt'] }}" class="site-gallery-image" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>

            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                <div class="site-lightbox-frame">
                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="{{ ui_text('ui.common.gallery') }}">&times;</button>
                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="{{ ui_text('ui.common.photos') }}">&#8249;</button>
                    <img :src="images[index]?.src" :alt="images[index]?.alt || ''" class="site-lightbox-image">
                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="{{ ui_text('ui.common.photos') }}">&#8250;</button>
                </div>
            </div>
        </section>
    @elseif(!$hasComplementaryContent)
        <section class="site-section">
            <div class="site-empty-state">
                @include('site.partials._content_editor', [
                    'editorTitle' => $emptyTitle,
                    'editorPage' => 'site.eventos.show',
                    'editorKey' => 'empty_state',
                    'editorLabel' => 'Estado vazio do evento',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['titulo', 'lead'],
                    'editableTranslation' => $emptyTranslation,
                    'editableStatus' => $eventShowBlocks['empty_state']?->status ?? 'publicado',
                    'editableFallback' => [
                        'titulo' => ui_text('ui.events.edition_empty_description'),
                        'lead' => 'Esta edição ainda não possui galeria ou atrativos publicados no portal.',
                    ],
                ])
                <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
            </div>
        </section>
    @endif

    <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
</div>
@endsection
