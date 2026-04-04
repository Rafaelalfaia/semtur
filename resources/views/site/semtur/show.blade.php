@extends('site.layouts.app')

@php
    $semturCanonical = localized_route('site.semtur');
    $semturTitle = ($sec->nome ?? 'SEMTUR').' de Altamira';
    $semturDescription = \Illuminate\Support\Str::limit(strip_tags($sec->descricao ?? ui_text('ui.semtur.default_short')), 160);
    $semturImage = $sec->foto_capa_url ?: $sec->foto_url ?: theme_asset('hero_image');
    $semturRedes = is_array($sec->redes ?? null) ? array_filter($sec->redes) : [];
    $semturSchema = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $semturCanonical.'#breadcrumbs',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => ui_text('ui.common.home'),
                    'item' => localized_route('site.home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => ui_text('ui.semtur.name'),
                    'item' => $semturCanonical,
                ],
            ],
        ],
        array_filter([
            '@type' => 'GovernmentOrganization',
            '@id' => $semturCanonical.'#organization',
            'name' => $sec->nome ?? 'SEMTUR',
            'url' => $semturCanonical,
            'description' => $semturDescription,
            'image' => [$semturImage],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Altamira',
                'addressRegion' => 'PA',
                'addressCountry' => 'BR',
            ],
            'sameAs' => array_values($semturRedes) ?: null,
        ], fn ($value) => $value !== null),
    ];
@endphp

@section('title', $semturTitle)
@section('meta.description', $semturDescription)
@section('meta.image', $semturImage)
@section('meta.canonical', $semturCanonical)
@section('meta.type', 'website')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $semturSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $heroUrl = $heroMedia?->url ?: ($sec->foto_capa_url ?: ($sec->foto_url ?: theme_asset('hero_image')));
    $logoUrl = $sec->foto_url ?: theme_asset('logo');
    $redes = is_array($sec->redes ?? null) ? $sec->redes : [];
    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.semtur.name');
    $heroTitle = $heroTranslation?->titulo ?: ($sec->nome ?? 'SEMTUR');
    $heroSubtitle = $heroTranslation?->lead ?: ui_text('ui.semtur.subtitle');
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: (Route::has('site.mapa') ? ui_text('ui.semtur.view_map') : null);
    $heroPrimaryHref = $heroTranslation?->cta_href ?: (Route::has('site.mapa') ? localized_route('site.mapa') : null);
    $redesLabels = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'site' => 'Site',
        'whatsapp' => 'WhatsApp',
    ];
    $redeItems = collect($redesLabels)
        ->map(fn ($label, $key) => ['label' => $label, 'href' => $redes[$key] ?? null])
        ->filter(fn ($item) => filled($item['href']))
        ->values();

    $membroCards = collect($membros ?? [])->map(fn ($membro) => [
        'title' => $membro->nome,
        'subtitle' => $membro->cargo ?: ui_text('ui.semtur.team_role'),
        'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $membro->resumo), 96),
        'image' => $membro->foto_url ?: asset('imagens/avatar.png'),
        'href' => null,
        'badge' => ui_text('ui.semtur.team_badge'),
    ]);
@endphp

<div class="site-page site-page-shell site-semtur-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.semtur.name')],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => Route::has('site.contato') ? ui_text('ui.portal_pages.contato.title') : null,
        'secondaryActionHref' => Route::has('site.contato') ? localized_route('site.contato') : null,
        'image' => $heroUrl,
        'imageAlt' => $heroTitle,
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <div class="site-detail-profile">
                        <img src="{{ site_image_url($logoUrl, "avatar") }}" alt="{{ $sec->nome ?? 'SEMTUR' }}" class="site-detail-avatar" loading="lazy" decoding="async">
                        <div>
                            <x-section-head :eyebrow="ui_text('ui.semtur.about_eyebrow')" :title="ui_text('ui.semtur.about_title')" :subtitle="ui_text('ui.semtur.about_subtitle')" />
                        </div>
                    </div>

                    <div class="site-prose">
                        {!! nl2br(e($sec->descricao ?: ui_text('ui.semtur.default_long'))) !!}
                    </div>
                </section>
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head :eyebrow="ui_text('ui.semtur.channels_eyebrow')" :title="ui_text('ui.semtur.channels_title')" />

                    @if($redeItems->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-copy">{{ ui_text('ui.semtur.channels_empty') }}</p>
                        </div>
                    @else
                        <div class="site-detail-links">
                            @foreach($redeItems as $item)
                                <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" class="site-link">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </section>

    <section class="site-section">
        <x-section-head
            :eyebrow="ui_text('ui.semtur.team_eyebrow')"
            :title="ui_text('ui.semtur.team_title')"
            :subtitle="ui_text('ui.semtur.team_subtitle')"
        />

        @if($membroCards->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">{{ ui_text('ui.semtur.team_empty') }}</p>
            </div>
        @else
            <div class="site-card-list-grid">
                @foreach($membroCards as $item)
                    <div class="site-card-list">
                        <div class="site-card-list-media">
                            <img src="{{ site_image_url($item['image'], "card") }}" alt="{{ $item['title'] }}" class="site-card-list-image" loading="lazy" decoding="async">
                        </div>
                        <div class="site-card-list-body">
                            <span class="site-badge">{{ $item['badge'] }}</span>
                            <h3 class="site-card-list-title">{{ $item['title'] }}</h3>
                            <div class="site-card-list-meta"><span>{{ $item['subtitle'] }}</span></div>
                            @if($item['summary'])
                                <p class="site-card-list-summary">{{ $item['summary'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

@include('site.partials._content_editor', [
    'editorTitle' => $heroTitle,
    'editorPage' => 'site.semtur',
    'editorKey' => 'hero',
    'editorLabel' => 'Hero SEMTUR',
    'editorLocale' => route_locale(),
    'editableTranslation' => $heroTranslation ?? null,
    'editableHeroMedia' => $heroMedia ?? null,
    'editableStatus' => $heroBlock?->status ?? 'publicado',
    'editableFallback' => [
        'eyebrow' => $heroBadge,
        'titulo' => $heroTitle,
        'subtitulo' => null,
        'lead' => $heroSubtitle,
        'conteudo' => null,
        'cta_label' => $heroPrimaryLabel,
        'cta_href' => $heroPrimaryHref,
        'seo_title' => $heroTitle,
        'seo_description' => $semturDescription,
    ],
])
@endsection

