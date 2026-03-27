@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $page['title'] ?? 'VisitAltamira';
    $pageDescription = $page['description'] ?? 'Portal turistico de Altamira.';
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
            'title' => $evento->nome ?? 'Evento',
            'subtitle' => $evento->cidade ?? 'Altamira',
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) ($edicao->resumo ?? $evento->descricao ?? '')), 110),
            'image' => $image,
            'href' => Route::has('eventos.show')
                ? route('eventos.show', [$evento->slug ?? $evento->id, $ano ?: now()->year])
                : ($evento->slug ?? '#'),
            'badge' => $periodo ?: ($ano ?: 'Evento'),
            'meta' => filled($edicao->local ?? null) ? $edicao->local : 'Programacao publicada',
            'cta' => 'Ver evento',
        ];
    })->values();

    $agendaHeroImage = optional($agendaEventCards->shuffle()->first())['image'] ?? theme_asset('hero_image');

    $agendaCards = $pageCards->values()->map(function ($card, $index) {
        $label = trim((string) ($card['label'] ?? 'Abrir'));
        $href = $card['href'] ?? '#';
        $isSoon = str_contains(\Illuminate\Support\Str::lower($label), 'breve') || $href === '#';

        return [
            'title' => $card['title'] ?? 'Agenda',
            'subtitle' => 'Altamira',
            'summary' => \Illuminate\Support\Str::limit((string) ($card['text'] ?? ''), 108),
            'image' => theme_asset('hero_image'),
            'href' => $href,
            'badge' => $isSoon ? 'Em breve' : ($index === 0 ? 'Agora' : 'Agenda'),
            'meta' => $isSoon ? 'Planejamento' : 'Disponivel',
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
                    ['label' => 'Inicio', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
                    ['label' => $page['title'] ?? 'Agenda'],
                ],
                'badge' => $page['eyebrow'] ?? 'Programacao da cidade',
                'title' => $page['title'] ?? 'Agenda',
                'subtitle' => 'Eventos, festivais e programacoes publicadas para descobrir Altamira com leitura rapida.',
                'meta' => [
                    $agendaEventCards->isNotEmpty() ? $agendaEventCards->count().' eventos publicados' : null,
                    'Altamira',
                ],
                'primaryActionLabel' => $page['cta_label'] ?? null,
                'primaryActionHref' => $page['cta_href'] ?? null,
                'secondaryActionLabel' => Route::has('site.explorar') ? 'Explorar a cidade' : null,
                'secondaryActionHref' => Route::has('site.explorar') ? route('site.explorar') : null,
                'image' => $agendaHeroImage,
                'imageAlt' => $page['title'] ?? 'Agenda',
                'compact' => true,
            ])

            <section class="site-section">
                <div class="site-surface-soft site-agenda-portal-shortcuts">
                    <div class="site-agenda-portal-shortcuts-row" role="navigation" aria-label="Atalhos da agenda">
                        @if($hasAgendaHighlight)
                            <a href="#agenda-destaques" class="site-year-chip is-active">Destaques</a>
                        @endif
                        @if($hasAgendaCarousel)
                            <a href="#agenda-atalhos" class="site-year-chip">Atalhos</a>
                        @endif
                        @if($hasAgendaCta)
                            <a href="#agenda-completa" class="site-year-chip">Agenda completa</a>
                        @endif
                    </div>
                </div>
            </section>

            @if($hasAgendaHighlight)
                <section class="site-section" id="agenda-destaques">
                    <div class="site-surface-soft site-agenda-portal-highlight">
                        <x-section-head
                            eyebrow="Agora"
                            title="Em destaque"
                            subtitle="Capas e acesso rapido aos eventos publicados agora."
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
                                            <img src="{{ $eventCard['image'] }}" alt="{{ $eventCard['title'] }}" loading="lazy" decoding="async" class="site-card-list-image">
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
                                        <span class="site-badge">Evento publicado</span>
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
                                            <a href="{{ $eventCard['href'] }}" class="site-button-secondary">Ver evento</a>
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
                        'eyebrow' => 'Proximos eventos',
                        'title' => 'Continue explorando',
                        'subtitle' => 'Deslize para ver mais eventos publicados.',
                        'items' => $agendaSecondaryCards,
                        'layout' => 'carousel',
                        'cardVariant' => 'compact',
                        'empty' => 'Nenhum outro evento apareceu agora.',
                    ])
                </div>
            @endif

            @if($hasAgendaCta)
                <section class="site-section" id="agenda-completa">
                    <div class="site-surface-soft site-agenda-portal-cta">
                        <div class="site-agenda-portal-cta-copy">
                            <span class="site-badge">Ver tudo</span>
                            <h2 class="site-section-head-title">Veja os eventos reais publicados</h2>
                            <p class="site-section-head-subtitle">Abra a agenda completa e siga pelos detalhes, edicoes e programacoes em andamento.</p>
                        </div>

                        <div class="site-agenda-portal-cta-actions">
                            <a href="{{ $page['cta_href'] }}" class="site-button-primary">{{ $page['cta_label'] }}</a>
                            @if(Route::has('site.mapa'))
                                <a href="{{ route('site.mapa') }}" class="site-button-secondary">Mapa</a>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if(!$hasAgendaContent)
                <section class="site-section">
                    <div class="site-empty-state">
                        <p class="site-empty-state-title">Sem eventos publicados agora</p>
                        <p class="site-empty-state-copy">Assim que uma nova programacao entrar no ar, ela aparece aqui.</p>
                        @if($hasAgendaCta)
                            <a href="{{ $page['cta_href'] }}" class="site-button-primary">Ver agenda completa</a>
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
                        ['label' => 'Inicio', 'href' => route('site.home')],
                        ['label' => $page['title'] ?? 'Secao'],
                    ],
                ])

                <div class="max-w-3xl mt-4">
                    @if(!empty($page['eyebrow']))
                        <div class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">
                            {{ $page['eyebrow'] }}
                        </div>
                    @endif

                    <h1 class="mt-4 text-3xl md:text-5xl font-bold tracking-tight text-slate-900">
                        {{ $page['title'] ?? 'Secao publica' }}
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

        <section class="py-10 md:py-14">
            <div class="mx-auto w-full max-w-[1200px] px-4 md:px-6">
                <div class="flex items-center justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-xl md:text-2xl font-semibold text-slate-900">Estrutura inicial da secao</h2>
                        <p class="text-sm md:text-base text-slate-500 mt-1">
                            Esta pagina ja funciona como ponto de entrada oficial da arquitetura publica.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach(($page['cards'] ?? []) as $card)
                        <x-site.portal-entry
                            :title="$card['title'] ?? ''"
                            :text="$card['text'] ?? null"
                            :href="$card['href'] ?? '#'"
                            :label="$card['label'] ?? 'Abrir'"
                        />
                    @endforeach
                </div>

                <div class="mt-10 rounded-3xl border border-slate-200 bg-slate-50 p-6 md:p-8">
                    <h3 class="text-lg font-semibold text-slate-900">Proximo passo desta secao</h3>
                    <p class="mt-2 text-slate-600 leading-7">
                        Nesta fase, a pagina entra como esqueleto funcional com SEO, rota, navegacao, breadcrumbs
                        e padrao visual reutilizavel. O conteudo final sera conectado aos modulos especificos nas proximas etapas.
                    </p>
                </div>
            </div>
        </section>
    @endif
@endsection
