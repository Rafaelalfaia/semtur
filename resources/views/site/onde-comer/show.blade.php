@extends('site.layouts.app')

@section('title', ($pagina->seo_title ?: $pagina->titulo ?: ui_text('ui.where_eat.title')) . ' - Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($pagina->seo_description ?: $pagina->resumo ?: ui_text('ui.where_eat.subtitle'))), 160))
@section('meta.image', $pagina->hero_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $pageBlocks = $pageBlocks ?? collect();
    $whereEatBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'intro_section' => $pageBlocks->get('intro_section'),
        'flavors_section' => $pageBlocks->get('flavors_section'),
        'listing_section' => $pageBlocks->get('listing_section'),
        'tips_section' => $pageBlocks->get('tips_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $whereEatTranslation = fn (string $key) => $whereEatBlocks[$key]?->getAttribute('traducao_resolvida');
    $introTranslation = $whereEatTranslation('intro_section');
    $flavorsTranslation = $whereEatTranslation('flavors_section');
    $listingTranslation = $whereEatTranslation('listing_section');
    $tipsTranslation = $whereEatTranslation('tips_section');
    $emptyTranslation = $whereEatTranslation('empty_state');

    $hero = $heroMedia?->url ?: ($pagina->hero_url ?: asset('imagens/altamira.jpg'));
    $empresas = collect($pagina->empresasSelecionadas ?? [])->filter(fn ($item) => $item && $item->empresa)->values();
    $explorarUrl = localized_route('site.explorar');
    $heroBadge = $heroTranslation?->eyebrow ?: ($pagina->subtitulo ?: ui_text('ui.where_eat.badge'));
    $heroTitle = $heroTranslation?->titulo ?: $pagina->titulo;
    $heroSubtitle = $heroTranslation?->lead ?: ($pagina->resumo ?: ui_text('ui.where_eat.subtitle'));
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.where_eat.view_businesses');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#empresas';
    $canCreateCompany = auth()->check() && auth()->user()->can('empresas.create');
    $canManageCompany = auth()->check() && auth()->user()->can('empresas.update');
    $createCompanyHref = $canCreateCompany && Route::has('coordenador.empresas.create')
        ? route('coordenador.empresas.create')
        : null;

    $introEyebrow = $introTranslation?->eyebrow ?: ui_text('ui.common.about');
    $introTitle = $introTranslation?->titulo ?: ui_text('ui.where_eat.intro_title');
    $introContent = $introTranslation?->conteudo ?: $pagina->texto_intro;

    $flavorsEyebrow = $flavorsTranslation?->eyebrow ?: ui_text('ui.where_eat.local_flavors_eyebrow');
    $flavorsTitle = $flavorsTranslation?->titulo ?: ui_text('ui.where_eat.local_flavors_title');
    $flavorsContent = $flavorsTranslation?->conteudo ?: $pagina->texto_gastronomia_local;

    $listingEyebrow = $listingTranslation?->eyebrow ?: ui_text('ui.where_eat.curation_eyebrow');
    $listingTitle = $listingTranslation?->titulo ?: ui_text('ui.where_eat.curation_title');
    $listingSubtitle = $listingTranslation?->lead ?: ui_text('ui.where_eat.subtitle');

    $tipsEyebrow = $tipsTranslation?->eyebrow ?: 'Dicas';
    $tipsTitle = $tipsTranslation?->titulo ?: ui_text('ui.where_eat.tips_title');
    $tipsContent = $tipsTranslation?->conteudo ?: $pagina->texto_dicas;

    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.where_eat.empty_title');
    $emptyCopy = $emptyTranslation?->lead ?: ui_text('ui.where_eat.empty_copy');
@endphp

<div class="site-page site-page-shell site-where-eat-page">
    @include('site.partials._page_hero', [
        'backHref' => $explorarUrl,
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.nav.explore'), 'href' => $explorarUrl],
            ['label' => $heroTitle],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => ui_text('ui.common.view_more'),
        'secondaryActionHref' => $explorarUrl,
        'image' => $hero,
        'imageAlt' => $heroTitle,
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.onde_comer',
            'key' => 'hero',
            'label' => 'Texto da capa de Onde comer',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.onde_comer',
            'key' => 'hero',
            'label' => 'Imagem da capa de Onde comer',
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

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                @if($introContent)
                    <section class="site-surface site-content-block">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $introTitle,
                            'editorPage' => 'site.onde_comer',
                            'editorKey' => 'intro_section',
                            'editorLabel' => 'Seção de introdução de Onde comer',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'conteudo'],
                            'editableTranslation' => $introTranslation,
                            'editableStatus' => $whereEatBlocks['intro_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.common.about'),
                                'titulo' => ui_text('ui.where_eat.intro_title'),
                                'conteudo' => $pagina->texto_intro,
                            ],
                        ])
                        <x-section-head :eyebrow="$introEyebrow" :title="$introTitle" />
                        <div class="site-prose">{!! nl2br(e($introContent)) !!}</div>
                    </section>
                @endif

                @if($flavorsContent)
                    <section class="site-surface-soft site-content-block">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $flavorsTitle,
                            'editorPage' => 'site.onde_comer',
                            'editorKey' => 'flavors_section',
                            'editorLabel' => 'Seção de gastronomia local de Onde comer',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'conteudo'],
                            'editableTranslation' => $flavorsTranslation,
                            'editableStatus' => $whereEatBlocks['flavors_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.where_eat.local_flavors_eyebrow'),
                                'titulo' => ui_text('ui.where_eat.local_flavors_title'),
                                'conteudo' => $pagina->texto_gastronomia_local,
                            ],
                        ])
                        <x-section-head :eyebrow="$flavorsEyebrow" :title="$flavorsTitle" />
                        <div class="site-prose">{!! nl2br(e($flavorsContent)) !!}</div>
                    </section>
                @endif
            </div>
        </div>
    </section>

    <section id="empresas" class="site-section">
        @if($createCompanyHref)
            <div class="site-inline-actions">
                @if($createCompanyHref)
                    <a href="{{ $createCompanyHref }}" class="site-button-primary">Cadastrar empresa</a>
                @endif
            </div>
        @endif

        @include('site.partials._content_editor', [
            'editorTitle' => $listingTitle,
            'editorPage' => 'site.onde_comer',
            'editorKey' => 'listing_section',
            'editorLabel' => 'Seção de empresas de Onde comer',
            'editorLocale' => route_locale(),
            'editorTriggerVariant' => 'inline-compact',
            'editorTriggerLabel' => 'Editar texto',
            'editorFields' => ['eyebrow', 'titulo', 'lead'],
            'editableTranslation' => $listingTranslation,
            'editableStatus' => $whereEatBlocks['listing_section']?->status ?? 'publicado',
            'editableFallback' => [
                'eyebrow' => ui_text('ui.where_eat.curation_eyebrow'),
                'titulo' => ui_text('ui.where_eat.curation_title'),
                'lead' => ui_text('ui.where_eat.subtitle'),
            ],
        ])
        <x-section-head
            :eyebrow="$listingEyebrow"
            :title="$listingTitle"
            :subtitle="$listingSubtitle"
        />

        @if($empresas->isEmpty())
            <div class="site-empty-state">
                @include('site.partials._content_editor', [
                    'editorTitle' => $emptyTitle,
                    'editorPage' => 'site.onde_comer',
                    'editorKey' => 'empty_state',
                    'editorLabel' => 'Estado vazio de Onde comer',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['titulo', 'lead'],
                    'editableTranslation' => $emptyTranslation,
                    'editableStatus' => $whereEatBlocks['empty_state']?->status ?? 'publicado',
                    'editableFallback' => [
                        'titulo' => ui_text('ui.where_eat.empty_title'),
                        'lead' => ui_text('ui.where_eat.empty_copy'),
                    ],
                ])
                <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
            </div>
        @else
            <div class="site-directory-grid">
                @foreach($empresas as $item)
                    @php
                        $empresa = $item->empresa;
                        $imagem = $empresa->foto_capa_url ?: $empresa->foto_perfil_url ?: asset('imagens/altamira.jpg');
                        $whats = data_get($empresa->social_links ?? [], 'whatsapp');
                    @endphp

                    <article class="site-directory-card">
                        @if($canManageCompany && Route::has('coordenador.empresas.edit'))
                            <div class="site-inline-actions">
                                <a href="{{ route('coordenador.empresas.edit', $empresa) }}" class="site-button-secondary">Editar empresa</a>
                            </div>
                        @endif

                        <div class="site-directory-card-media">
                            <img src="{{ $imagem }}" alt="{{ $empresa->nome }}" class="site-directory-card-image" loading="lazy" decoding="async">
                            <div class="site-directory-card-overlay">
                                @if($item->destaque)
                                    <span class="site-badge">{{ ui_text('ui.where_eat.featured_badge') }}</span>
                                @endif

                                @foreach(($empresa->categorias ?? collect())->take(2) as $categoria)
                                    <span class="site-badge">{{ $categoria->nome }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="site-directory-card-body">
                            <div>
                                <h3 class="site-directory-card-title">{{ $empresa->nome }}</h3>
                                <p class="site-directory-card-subtitle">{{ collect([$empresa->bairro, $empresa->cidade])->filter()->implode(' - ') ?: ui_text('ui.common.altamira') }}</p>
                                <p class="site-inline-meta">{{ ui_text('ui.where_eat.official_curation') }}</p>
                            </div>

                            <p class="site-directory-card-summary">
                                {{ \Illuminate\Support\Str::limit($item->observacao_curta ?: strip_tags((string) $empresa->descricao), 140) }}
                            </p>

                            <div class="site-directory-card-actions">
                                <a href="{{ localized_route('site.empresa', ['empresa' => $empresa->slug ?: $empresa->id]) }}" class="site-button-primary">{{ ui_text('ui.explore.view_company') }}</a>
                                @if($empresa->maps_url)
                                    <a href="{{ $empresa->maps_url }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">{{ ui_text('ui.common.open_map') }}</a>
                                @endif
                                @if($whats)
                                    <a href="{{ $whats }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">WhatsApp</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if($tipsContent)
        <section class="site-section">
            <div class="site-surface site-content-block">
                @include('site.partials._content_editor', [
                    'editorTitle' => $tipsTitle,
                    'editorPage' => 'site.onde_comer',
                    'editorKey' => 'tips_section',
                    'editorLabel' => 'Seção de dicas de Onde comer',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'conteudo'],
                    'editableTranslation' => $tipsTranslation,
                    'editableStatus' => $whereEatBlocks['tips_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => 'Dicas',
                        'titulo' => ui_text('ui.where_eat.tips_title'),
                        'conteudo' => $pagina->texto_dicas,
                    ],
                ])
                <x-section-head :eyebrow="$tipsEyebrow" :title="$tipsTitle" />
                <div class="site-prose">{!! nl2br(e($tipsContent)) !!}</div>
            </div>
        </section>
    @endif
</div>
@endsection
