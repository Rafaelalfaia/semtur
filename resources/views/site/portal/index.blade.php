@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $page['title'] ?? 'VisitAltamira';
    $pageDescription = $page['description'] ?? ui_text('ui.home.description');
    $isAgendaPage = request()->routeIs('site.agenda');
    $pageCards = collect($page['cards'] ?? []);
    $agendaEvents = collect($agendaEvents ?? []);
    $pageBlocks = $pageBlocks ?? collect();
    $portalPageKey = $editablePageKey ?? null;
    $canCreateEvent = auth()->check() && auth()->user()->can('eventos.create');
    $canManageEvent = auth()->check() && auth()->user()->can('eventos.update');
    $canViewEventsPanel = auth()->check() && auth()->user()->can('eventos.view');
    $createEventHref = $canCreateEvent && Route::has('coordenador.eventos.create')
        ? route('coordenador.eventos.create')
        : null;
    $eventsPanelHref = $canViewEventsPanel && Route::has('coordenador.eventos.index')
        ? route('coordenador.eventos.index')
        : null;
    $agendaBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'highlight_section' => $pageBlocks->get('highlight_section'),
        'carousel_section' => $pageBlocks->get('carousel_section'),
        'cta_section' => $pageBlocks->get('cta_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $agendaTranslation = fn (string $key) => $agendaBlocks[$key]?->getAttribute('traducao_resolvida');
    $agendaHeroTranslation = $editableHeroTranslation ?? $agendaTranslation('hero');
    $highlightTranslation = $agendaTranslation('highlight_section');
    $carouselTranslation = $agendaTranslation('carousel_section');
    $ctaTranslation = $agendaTranslation('cta_section');
    $emptyTranslation = $agendaTranslation('empty_state');

    $agendaEventCards = $agendaEvents->map(function ($evento) use ($canManageEvent) {
        $edicao = collect($evento->edicoes ?? [])->sortByDesc('ano')->first();
        $ano = $edicao->ano ?? null;
        $periodo = $edicao->periodo
            ?? (($edicao->data_inicio ? $edicao->data_inicio->format('d/m') : null)
            . ($edicao->data_fim ? ' - '.$edicao->data_fim->format('d/m') : ''));
        $image = $evento->capa_url
            ?? (!empty($evento->capa_path) ? Storage::disk('public')->url($evento->capa_path) : null)
            ?? $evento->perfil_url
            ?? (!empty($evento->perfil_path) ? Storage::disk('public')->url($evento->perfil_path) : null)
            ?? theme_asset('hero_image');

        return [
            'title' => $evento->nome ?? ui_text('ui.agenda.title'),
            'subtitle' => $evento->cidade ?? ui_text('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) ($edicao->resumo ?? $evento->descricao ?? '')), 110),
            'image' => $image,
            'href' => Route::has('eventos.show')
                ? localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $ano ?: now()->year])
                : ($evento->slug ?? '#'),
            'badge' => $periodo ?: ($ano ?: ui_text('ui.agenda.title')),
            'meta' => filled($edicao->local ?? null) ? $edicao->local : ui_text('ui.agenda.published_programming'),
            'cta' => ui_text('ui.agenda.view_event'),
            'admin_action' => $canManageEvent && Route::has('coordenador.eventos.edit')
                ? [
                    'label' => 'Editar evento',
                    'href' => route('coordenador.eventos.edit', $evento),
                ]
                : null,
        ];
    })->values();

    $agendaHeroImage = optional($agendaEventCards->shuffle()->first())['image'] ?? theme_asset('hero_image');

    $agendaCards = $pageCards->values()->map(function ($card, $index) {
        $label = trim((string) ($card['label'] ?? ui_text('ui.common.view_more')));
        $href = $card['href'] ?? '#';
        $isSoon = str_contains(\Illuminate\Support\Str::lower($label), 'breve') || $href === '#';

        return [
            'title' => $card['title'] ?? ui_text('ui.agenda.title'),
            'subtitle' => ui_text('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit((string) ($card['text'] ?? ''), 108),
            'image' => theme_asset('hero_image'),
            'href' => $href,
            'badge' => $isSoon ? ui_text('ui.agenda.soon') : ($index === 0 ? ui_text('ui.agenda.available_now') : ui_text('ui.agenda.title')),
            'meta' => $isSoon ? ui_text('ui.agenda.planning') : ui_text('ui.agenda.available'),
            'cta' => $label,
        ];
    });

    $agendaActionCards = $agendaCards
        ->filter(fn ($card) => filled($card['href']) && $card['href'] !== '#')
        ->values();

    $agendaPrimaryCard = $agendaEventCards->first() ?? $agendaActionCards->first();
    $agendaSecondaryCards = $agendaEventCards->slice(1)->values();

    if ($agendaSecondaryCards->isEmpty()) {
        $agendaSecondaryCards = $agendaActionCards->reject(fn ($card) => ($card['href'] ?? null) === ($agendaPrimaryCard['href'] ?? null))->values();
    }

    $hasAgendaHighlight = filled($agendaPrimaryCard);
    $hasAgendaCarousel = $agendaSecondaryCards->isNotEmpty();
    $hasAgendaCta = !empty($page['cta_href']) && !empty($page['cta_label']);
    $hasAgendaContent = $hasAgendaHighlight || $hasAgendaCarousel;
    $agendaHeroImage = $editableHeroMedia?->url ?: $agendaHeroImage;
    $agendaHeroBadge = $agendaHeroTranslation?->eyebrow ?: ($page['eyebrow'] ?? ui_text('ui.agenda.city_programming'));
    $agendaHeroTitle = $agendaHeroTranslation?->titulo ?: ($page['title'] ?? ui_text('ui.agenda.title'));
    $agendaHeroSubtitle = $agendaHeroTranslation?->lead ?: ui_text('ui.agenda.subtitle');
    $agendaHeroPrimaryLabel = $agendaHeroTranslation?->cta_label ?: ($page['cta_label'] ?? null);
    $agendaHeroPrimaryHref = $agendaHeroTranslation?->cta_href ?: ($page['cta_href'] ?? null);

    $highlightEyebrow = $highlightTranslation?->eyebrow ?: ui_text('ui.agenda.now');
    $highlightTitle = $highlightTranslation?->titulo ?: ui_text('ui.agenda.highlight_title');
    $highlightSubtitle = $highlightTranslation?->lead ?: ui_text('ui.agenda.highlight_subtitle');

    $carouselEyebrow = $carouselTranslation?->eyebrow ?: ui_text('ui.agenda.upcoming_eyebrow');
    $carouselTitle = $carouselTranslation?->titulo ?: ui_text('ui.agenda.continue_title');
    $carouselSubtitle = $carouselTranslation?->lead ?: ui_text('ui.agenda.continue_subtitle');

    $ctaBadge = $ctaTranslation?->eyebrow ?: ui_text('ui.agenda.cta_badge');
    $ctaTitle = $ctaTranslation?->titulo ?: ui_text('ui.agenda.cta_title');
    $ctaSubtitle = $ctaTranslation?->lead ?: ui_text('ui.agenda.cta_subtitle');
    $ctaLabel = $ctaTranslation?->cta_label ?: ($page['cta_label'] ?? null);
    $ctaHref = $ctaTranslation?->cta_href ?: ($page['cta_href'] ?? null);

    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.agenda.empty_title');
    $emptyCopy = $emptyTranslation?->lead ?: ui_text('ui.agenda.empty_copy');
@endphp

@section('title', $pageTitle)
@section('meta.description', $pageDescription)
@section('meta.image', theme_asset('hero_image'))

@section('site.content')
    @if($isAgendaPage)
        <div class="site-page site-page-shell site-portal-page site-portal-page--agenda">
            @include('site.partials._page_hero', [
                'backHref' => localized_route('site.home'),
                'breadcrumbs' => [
                    ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
                    ['label' => $agendaHeroTitle],
                ],
                'badge' => $agendaHeroBadge,
                'title' => $agendaHeroTitle,
                'subtitle' => $agendaHeroSubtitle,
                'meta' => [
                    $agendaEventCards->isNotEmpty() ? ui_text('ui.agenda.events_published', ['count' => $agendaEventCards->count()]) : null,
                    ui_text('ui.common.altamira'),
                ],
                'primaryActionLabel' => $agendaHeroPrimaryLabel,
                'primaryActionHref' => $agendaHeroPrimaryHref,
                'secondaryActionLabel' => Route::has('site.explorar') ? ui_text('ui.agenda.explore_city') : null,
                'secondaryActionHref' => Route::has('site.explorar') ? localized_route('site.explorar') : null,
                'image' => $agendaHeroImage,
                'imageAlt' => $agendaHeroTitle,
                'compact' => true,
                'textEditor' => [
                    'title' => $agendaHeroTitle,
                    'page' => $portalPageKey,
                    'key' => 'hero',
                    'label' => 'Texto da capa da agenda',
                    'locale' => route_locale(),
                    'trigger_label' => 'Editar texto',
                    'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
                    'translation' => $agendaHeroTranslation,
                    'status' => $editableHeroBlock?->status ?? 'publicado',
                ],
                'imageEditor' => [
                    'title' => $agendaHeroTitle,
                    'page' => $portalPageKey,
                    'key' => 'hero',
                    'label' => 'Imagem da capa da agenda',
                    'locale' => route_locale(),
                    'trigger_label' => 'Editar imagem',
                    'translation' => $agendaHeroTranslation,
                    'media' => $editableHeroMedia ?? null,
                    'status' => $editableHeroBlock?->status ?? 'publicado',
                    'media_slot' => 'hero',
                    'media_label' => 'Imagem da capa',
                    'preview_label' => 'imagem atual da capa',
                ],
            ])

            <section class="site-section">
                <div class="site-surface-soft site-agenda-portal-shortcuts">
                    <div class="site-agenda-portal-shortcuts-row" role="navigation" aria-label="{{ ui_text('ui.agenda.shortcuts_aria') }}">
                        @if($hasAgendaHighlight)
                            <a href="#agenda-destaques" class="site-year-chip is-active">{{ ui_text('ui.agenda.highlights') }}</a>
                        @endif
                        @if($hasAgendaCarousel)
                            <a href="#agenda-atalhos" class="site-year-chip">{{ ui_text('ui.agenda.shortcuts') }}</a>
                        @endif
                        @if($hasAgendaCta)
                            <a href="#agenda-completa" class="site-year-chip">{{ ui_text('ui.agenda.full_agenda') }}</a>
                        @endif
                    </div>

                    @if($createEventHref || $eventsPanelHref)
                        <div class="site-inline-actions">
                            @if($createEventHref)
                                <a href="{{ $createEventHref }}" class="site-button-primary">Novo evento</a>
                            @endif
                            @if($eventsPanelHref)
                                <a href="{{ $eventsPanelHref }}" class="site-button-secondary">Painel de eventos</a>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            @if($hasAgendaHighlight)
                <section class="site-section" id="agenda-destaques">
                    <div class="site-surface-soft site-agenda-portal-highlight">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $highlightTitle,
                            'editorPage' => $portalPageKey,
                            'editorKey' => 'highlight_section',
                            'editorLabel' => 'Seção de destaques da agenda',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'lead'],
                            'editableTranslation' => $highlightTranslation,
                            'editableStatus' => $agendaBlocks['highlight_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.agenda.now'),
                                'titulo' => ui_text('ui.agenda.highlight_title'),
                                'lead' => ui_text('ui.agenda.highlight_subtitle'),
                            ],
                        ])
                        <x-section-head
                            :eyebrow="$highlightEyebrow"
                            :title="$highlightTitle"
                            :subtitle="$highlightSubtitle"
                        />

                        <div
                            class="site-agenda-portal-highlight-grid"
                            x-data="{
                                active: 0,
                                total: {{ max($agendaEventCards->count(), 1) }},
                                interval: null,
                                start() {
                                    if (this.total <= 1) return;
                                    this.stop();
                                    this.interval = window.setInterval(() => {
                                        this.active = (this.active + 1) % this.total;
                                    }, 5000);
                                },
                                stop() {
                                    if (this.interval) {
                                        window.clearInterval(this.interval);
                                        this.interval = null;
                                    }
                                }
                            }"
                            x-init="start()"
                            x-on:mouseenter="stop()"
                            x-on:mouseleave="start()"
                        >
                            <div class="site-agenda-portal-featured-card">
                                <div class="site-agenda-portal-featured-media">
                                    @foreach(($agendaEventCards->isNotEmpty() ? $agendaEventCards : collect([$agendaPrimaryCard])) as $index => $eventCard)
                                        <a
                                            href="{{ $eventCard['href'] }}"
                                            class="site-agenda-portal-featured-slide"
                                            x-show="active === {{ $index }}"
                                            x-transition.opacity.duration.500ms
                                            @if($index > 0) x-cloak @endif
                                        >
                                            <img src="{{ site_image_url($eventCard['image'], "hero") }}" alt="{{ $eventCard['title'] }}" loading="lazy" decoding="async" class="site-card-list-image">
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="site-agenda-portal-highlight-copy">
                                @foreach(($agendaEventCards->isNotEmpty() ? $agendaEventCards : collect([$agendaPrimaryCard])) as $index => $eventCard)
                                    <div
                                        class="site-agenda-portal-highlight-panel"
                                        x-show="active === {{ $index }}"
                                        x-transition.opacity.duration.350ms
                                        @if($index > 0) x-cloak @endif
                                    >
                                        <span class="site-badge">{{ ui_text('ui.agenda.published_event') }}</span>
                                        <div class="site-card-list-meta">
                                            <span>{{ $eventCard['badge'] }}</span>
                                            <span>{{ $eventCard['subtitle'] }}</span>
                                            <span>{{ $eventCard['meta'] }}</span>
                                        </div>
                                        <h2 class="site-section-head-title">{{ $eventCard['title'] }}</h2>
                                        @if($eventCard['summary'])
                                            <p class="site-section-head-subtitle">{{ $eventCard['summary'] }}</p>
                                        @endif
                                        <div class="site-agenda-portal-highlight-actions">
                                            @if(!empty($eventCard['admin_action']['href']) && !empty($eventCard['admin_action']['label']))
                                                <a href="{{ $eventCard['admin_action']['href'] }}" class="site-button-secondary">{{ $eventCard['admin_action']['label'] }}</a>
                                            @endif
                                            <a href="{{ $eventCard['href'] }}" class="site-button-secondary">{{ ui_text('ui.agenda.view_event') }}</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if($hasAgendaCarousel)
                <div id="agenda-atalhos">
                    @include('site.partials._category_section', [
                        'eyebrow' => $carouselEyebrow,
                        'title' => $carouselTitle,
                        'subtitle' => $carouselSubtitle,
                        'items' => $agendaSecondaryCards,
                        'layout' => 'carousel',
                        'cardVariant' => 'compact',
                        'empty' => ui_text('ui.agenda.empty_copy'),
                        'editor' => [
                            'title' => $carouselTitle,
                            'page' => $portalPageKey,
                            'key' => 'carousel_section',
                            'label' => 'Seção de atalhos da agenda',
                            'locale' => route_locale(),
                            'trigger_label' => 'Editar texto',
                            'fields' => ['eyebrow', 'titulo', 'lead'],
                            'translation' => $carouselTranslation,
                            'status' => $agendaBlocks['carousel_section']?->status ?? 'publicado',
                            'fallback' => [
                                'eyebrow' => ui_text('ui.agenda.upcoming_eyebrow'),
                                'titulo' => ui_text('ui.agenda.continue_title'),
                                'lead' => ui_text('ui.agenda.continue_subtitle'),
                            ],
                        ],
                    ])
                </div>
            @endif

            @if($hasAgendaCta)
                <section class="site-section" id="agenda-completa">
                    <div class="site-surface-soft site-agenda-portal-cta">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $ctaTitle,
                            'editorPage' => $portalPageKey,
                            'editorKey' => 'cta_section',
                            'editorLabel' => 'Seção de chamada final da agenda',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
                            'editableTranslation' => $ctaTranslation,
                            'editableStatus' => $agendaBlocks['cta_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.agenda.cta_badge'),
                                'titulo' => ui_text('ui.agenda.cta_title'),
                                'lead' => ui_text('ui.agenda.cta_subtitle'),
                                'cta_label' => $page['cta_label'] ?? null,
                                'cta_href' => $page['cta_href'] ?? null,
                            ],
                        ])
                        <div class="site-agenda-portal-cta-copy">
                            <span class="site-badge">{{ $ctaBadge }}</span>
                            <h2 class="site-section-head-title">{{ $ctaTitle }}</h2>
                            <p class="site-section-head-subtitle">{{ $ctaSubtitle }}</p>
                        </div>

                        <div class="site-agenda-portal-cta-actions">
                            <a href="{{ $ctaHref }}" class="site-button-primary">{{ $ctaLabel }}</a>
                            @if(Route::has('site.mapa'))
                                <a href="{{ localized_route('site.mapa') }}" class="site-button-secondary">{{ ui_text('ui.common.map') }}</a>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if(!$hasAgendaContent)
                <section class="site-section">
                    <div class="site-empty-state">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $emptyTitle,
                            'editorPage' => $portalPageKey,
                            'editorKey' => 'empty_state',
                            'editorLabel' => 'Estado vazio da agenda',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['titulo', 'lead'],
                            'editableTranslation' => $emptyTranslation,
                            'editableStatus' => $agendaBlocks['empty_state']?->status ?? 'publicado',
                            'editableFallback' => [
                                'titulo' => ui_text('ui.agenda.empty_title'),
                                'lead' => ui_text('ui.agenda.empty_copy'),
                            ],
                        ])
                        <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                        <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
                        @if($hasAgendaCta)
                            <a href="{{ $ctaHref }}" class="site-button-primary">{{ ui_text('ui.agenda.view_full_agenda') }}</a>
                        @endif
                    </div>
                </section>
            @endif

            <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
        </div>
    @else
        <section class="bg-gradient-to-b from-emerald-50 via-white to-white border-b border-emerald-100">
            <div class="mx-auto grid w-full max-w-[1200px] gap-8 px-4 py-8 md:px-6 md:py-12 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)] lg:items-center">
                <div>
                @include('site.partials._breadcrumbs', [
                    'items' => [
                        ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
                        ['label' => $page['title'] ?? ui_text('ui.common.view_more')],
                    ],
                ])

                <div class="mt-4 max-w-3xl">
                    @if(!empty($page['eyebrow']))
                        <div class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">
                            {{ $page['eyebrow'] }}
                        </div>
                    @endif

                    <h1 class="mt-4 text-3xl md:text-5xl font-bold tracking-tight text-slate-900">
                        {{ $page['title'] ?? ui_text('ui.common.view_more') }}
                    </h1>

                    @if(!empty($page['lead']))
                        <p class="mt-4 text-base md:text-lg leading-8 text-slate-600">
                            {{ $page['lead'] }}
                        </p>
                    @endif

                    @if(!empty($page['cta_href']) && !empty($page['cta_label']))
                        <div class="mt-6">
                            <a href="{{ $page['cta_href'] }}"
                               class="inline-flex items-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 py-3 text-white font-medium transition">
                                {{ $page['cta_label'] }}
                            </a>
                        </div>
                    @endif
                </div>

                </div>

                @if($editableHeroMedia?->url)
                    <div class="overflow-hidden rounded-[2rem] border border-emerald-100 bg-white p-2 shadow-[0_30px_80px_-40px_rgba(15,23,42,0.35)]">
                        <img
                            src="{{ $editableHeroMedia->url }}"
                            alt="{{ $editableHeroMedia->alt_text ?: ($page['title'] ?? ui_text('ui.common.view_more')) }}"
                            class="h-[280px] w-full rounded-[1.5rem] object-cover lg:h-[360px]"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                @endif
            </div>
        </section>

        @if(($pageCards ?? collect())->isNotEmpty())
            <section class="bg-white">
                <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-8 md:py-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($pageCards as $card)
                        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="text-sm font-semibold text-emerald-700">{{ $card['title'] ?? ui_text('ui.common.view_more') }}</div>
                            @if(!empty($card['text']))
                                <p class="mt-3 text-slate-600 leading-7">{{ $card['text'] }}</p>
                            @endif
                            @if(!empty($card['href']) && !empty($card['label']))
                                <div class="mt-5">
                                    <a href="{{ $card['href'] }}" class="inline-flex items-center rounded-xl border border-emerald-200 px-4 py-2 text-emerald-700 hover:bg-emerald-50 transition">
                                        {{ $card['label'] }}
                                    </a>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endif

    @unless($isAgendaPage)
    @include('site.partials._content_editor', [
        'editorTitle' => $page['title'] ?? 'Conteúdo da página',
        'editorPage' => $editablePageKey ?? null,
        'editorKey' => 'hero',
        'editorLabel' => 'Hero editorial',
        'editorLocale' => route_locale(),
        'editableTranslation' => $editableHeroTranslation ?? null,
        'editableHeroMedia' => $editableHeroMedia ?? null,
        'editableStatus' => $editableHeroBlock?->status ?? 'publicado',
        'editableFallback' => [
            'eyebrow' => $page['eyebrow'] ?? null,
            'titulo' => $page['title'] ?? null,
            'subtitulo' => null,
            'lead' => $page['lead'] ?? null,
            'conteudo' => null,
            'cta_label' => $page['cta_label'] ?? null,
            'cta_href' => $page['cta_href'] ?? null,
            'seo_title' => $page['title'] ?? null,
            'seo_description' => $page['description'] ?? null,
        ],
    ])
    @endunless
@endsection
