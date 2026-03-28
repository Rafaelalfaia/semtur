@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $page['title'] ?? 'VisitAltamira';
    $pageDescription = $page['description'] ?? __('ui.home.description');
    $isAgendaPage = request()->routeIs('site.agenda');
    $pageCards = collect($page['cards'] ?? []);
    $agendaEvents = collect($agendaEvents ?? []);

    $agendaEventCards = $agendaEvents->map(function ($evento) {
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
            'title' => $evento->nome ?? __('ui.agenda.title'),
            'subtitle' => $evento->cidade ?? __('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) ($edicao->resumo ?? $evento->descricao ?? '')), 110),
            'image' => $image,
            'href' => Route::has('eventos.show')
                ? route('eventos.show', [$evento->slug ?? $evento->id, $ano ?: now()->year])
                : ($evento->slug ?? '#'),
            'badge' => $periodo ?: ($ano ?: __('ui.agenda.title')),
            'meta' => filled($edicao->local ?? null) ? $edicao->local : __('ui.agenda.published_programming'),
            'cta' => __('ui.agenda.view_event'),
        ];
    })->values();

    $agendaHeroImage = optional($agendaEventCards->shuffle()->first())['image'] ?? theme_asset('hero_image');

    $agendaCards = $pageCards->values()->map(function ($card, $index) {
        $label = trim((string) ($card['label'] ?? __('ui.common.view_more')));
        $href = $card['href'] ?? '#';
        $isSoon = str_contains(\Illuminate\Support\Str::lower($label), 'breve') || $href === '#';

        return [
            'title' => $card['title'] ?? __('ui.agenda.title'),
            'subtitle' => __('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit((string) ($card['text'] ?? ''), 108),
            'image' => theme_asset('hero_image'),
            'href' => $href,
            'badge' => $isSoon ? __('ui.agenda.soon') : ($index === 0 ? __('ui.agenda.available_now') : __('ui.agenda.title')),
            'meta' => $isSoon ? __('ui.agenda.planning') : __('ui.agenda.available'),
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
@endphp

@section('title', $pageTitle)
@section('meta.description', $pageDescription)
@section('meta.image', theme_asset('hero_image'))

@section('site.content')
    @if($isAgendaPage)
        <div class="site-page site-page-shell site-portal-page site-portal-page--agenda">
            @include('site.partials._page_hero', [
                'backHref' => Route::has('site.home') ? route('site.home') : url('/'),
                'breadcrumbs' => [
                    ['label' => __('ui.common.home'), 'href' => Route::has('site.home') ? route('site.home') : url('/')],
                    ['label' => $page['title'] ?? __('ui.agenda.title')],
                ],
                'badge' => $page['eyebrow'] ?? __('ui.agenda.city_programming'),
                'title' => $page['title'] ?? __('ui.agenda.title'),
                'subtitle' => __('ui.agenda.subtitle'),
                'meta' => [
                    $agendaEventCards->isNotEmpty() ? __('ui.agenda.events_published', ['count' => $agendaEventCards->count()]) : null,
                    __('ui.common.altamira'),
                ],
                'primaryActionLabel' => $page['cta_label'] ?? null,
                'primaryActionHref' => $page['cta_href'] ?? null,
                'secondaryActionLabel' => Route::has('site.explorar') ? __('ui.agenda.explore_city') : null,
                'secondaryActionHref' => Route::has('site.explorar') ? route('site.explorar') : null,
                'image' => $agendaHeroImage,
                'imageAlt' => $page['title'] ?? __('ui.agenda.title'),
                'compact' => true,
            ])

            <section class="site-section">
                <div class="site-surface-soft site-agenda-portal-shortcuts">
                    <div class="site-agenda-portal-shortcuts-row" role="navigation" aria-label="{{ __('ui.agenda.shortcuts_aria') }}">
                        @if($hasAgendaHighlight)
                            <a href="#agenda-destaques" class="site-year-chip is-active">{{ __('ui.agenda.highlights') }}</a>
                        @endif
                        @if($hasAgendaCarousel)
                            <a href="#agenda-atalhos" class="site-year-chip">{{ __('ui.agenda.shortcuts') }}</a>
                        @endif
                        @if($hasAgendaCta)
                            <a href="#agenda-completa" class="site-year-chip">{{ __('ui.agenda.full_agenda') }}</a>
                        @endif
                    </div>
                </div>
            </section>

            @if($hasAgendaHighlight)
                <section class="site-section" id="agenda-destaques">
                    <div class="site-surface-soft site-agenda-portal-highlight">
                        <x-section-head
                            :eyebrow="__('ui.agenda.now')"
                            :title="__('ui.agenda.highlight_title')"
                            :subtitle="__('ui.agenda.highlight_subtitle')"
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
                                        <span class="site-badge">{{ __('ui.agenda.published_event') }}</span>
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
                                            <a href="{{ $eventCard['href'] }}" class="site-button-secondary">{{ __('ui.agenda.view_event') }}</a>
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
                        'eyebrow' => __('ui.agenda.upcoming_eyebrow'),
                        'title' => __('ui.agenda.continue_title'),
                        'subtitle' => __('ui.agenda.continue_subtitle'),
                        'items' => $agendaSecondaryCards,
                        'layout' => 'carousel',
                        'cardVariant' => 'compact',
                        'empty' => __('ui.agenda.empty_copy'),
                    ])
                </div>
            @endif

            @if($hasAgendaCta)
                <section class="site-section" id="agenda-completa">
                    <div class="site-surface-soft site-agenda-portal-cta">
                        <div class="site-agenda-portal-cta-copy">
                            <span class="site-badge">{{ __('ui.agenda.cta_badge') }}</span>
                            <h2 class="site-section-head-title">{{ __('ui.agenda.cta_title') }}</h2>
                            <p class="site-section-head-subtitle">{{ __('ui.agenda.cta_subtitle') }}</p>
                        </div>

                        <div class="site-agenda-portal-cta-actions">
                            <a href="{{ $page['cta_href'] }}" class="site-button-primary">{{ $page['cta_label'] }}</a>
                            @if(Route::has('site.mapa'))
                                <a href="{{ route('site.mapa') }}" class="site-button-secondary">{{ __('ui.common.map') }}</a>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if(!$hasAgendaContent)
                <section class="site-section">
                    <div class="site-empty-state">
                        <p class="site-empty-state-title">{{ __('ui.agenda.empty_title') }}</p>
                        <p class="site-empty-state-copy">{{ __('ui.agenda.empty_copy') }}</p>
                        @if($hasAgendaCta)
                            <a href="{{ $page['cta_href'] }}" class="site-button-primary">{{ __('ui.agenda.view_full_agenda') }}</a>
                        @endif
                    </div>
                </section>
            @endif

            <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
        </div>
    @else
        <section class="bg-gradient-to-b from-emerald-50 via-white to-white border-b border-emerald-100">
            <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-8 md:py-12">
                @include('site.partials._breadcrumbs', [
                    'items' => [
                        ['label' => __('ui.common.home'), 'href' => route('site.home')],
                        ['label' => $page['title'] ?? __('ui.common.view_more')],
                    ],
                ])

                <div class="max-w-3xl mt-4">
                    @if(!empty($page['eyebrow']))
                        <div class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">
                            {{ $page['eyebrow'] }}
                        </div>
                    @endif

                    <h1 class="mt-4 text-3xl md:text-5xl font-bold tracking-tight text-slate-900">
                        {{ $page['title'] ?? __('ui.common.view_more') }}
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
        </section>

        @if(($pageCards ?? collect())->isNotEmpty())
            <section class="bg-white">
                <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6 py-8 md:py-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($pageCards as $card)
                        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="text-sm font-semibold text-emerald-700">{{ $card['title'] ?? __('ui.common.view_more') }}</div>
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
@endsection
