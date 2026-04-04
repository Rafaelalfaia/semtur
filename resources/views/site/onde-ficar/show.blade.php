@extends('site.layouts.app')

@section('title', ($pagina->seo_title ?: $pagina->titulo ?: ui_text('ui.where_stay.title')) . ' - Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($pagina->seo_description ?: $pagina->resumo ?: ui_text('ui.where_stay.subtitle') ?: $pagina->titulo)), 160))
@section('meta.image', $pagina->hero_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $pageBlocks = $pageBlocks ?? collect();
    $whereStayBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'intro_section' => $pageBlocks->get('intro_section'),
        'lodging_section' => $pageBlocks->get('lodging_section'),
        'listing_section' => $pageBlocks->get('listing_section'),
        'tips_section' => $pageBlocks->get('tips_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $whereStayTranslation = fn (string $key) => $whereStayBlocks[$key]?->getAttribute('traducao_resolvida');
    $introTranslation = $whereStayTranslation('intro_section');
    $lodgingTranslation = $whereStayTranslation('lodging_section');
    $listingTranslation = $whereStayTranslation('listing_section');
    $tipsTranslation = $whereStayTranslation('tips_section');
    $emptyTranslation = $whereStayTranslation('empty_state');

    $hero = $heroMedia?->url ?: ($pagina->hero_url ?: asset('imagens/altamira.jpg'));
    $empresas = collect($pagina->empresasSelecionadas ?? [])->filter(fn ($item) => $item && $item->empresa)->values();
    $explorarUrl = localized_route('site.explorar');
    $heroBadge = $heroTranslation?->eyebrow ?: ($pagina->subtitulo ?: ui_text('ui.where_stay.badge'));
    $heroTitle = $heroTranslation?->titulo ?: ($pagina->titulo ?: ui_text('ui.where_stay.title'));
    $heroSubtitle = $heroTranslation?->lead ?: ($pagina->resumo ?: ui_text('ui.where_stay.subtitle'));
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.where_stay.view_accommodations');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#hospedagens';
    $canCreateCompany = auth()->check() && auth()->user()->can('empresas.create');
    $canManageCompany = auth()->check() && auth()->user()->can('empresas.update');
    $createCompanyHref = $canCreateCompany && Route::has('coordenador.empresas.create')
        ? route('coordenador.empresas.create')
        : null;

    $introEyebrow = $introTranslation?->eyebrow ?: ui_text('ui.common.about');
    $introTitle = $introTranslation?->titulo ?: ui_text('ui.where_stay.intro_title');
    $introContent = $introTranslation?->conteudo ?: $pagina->texto_intro;

    $lodgingEyebrow = $lodgingTranslation?->eyebrow ?: ui_text('ui.where_stay.local_stay_eyebrow');
    $lodgingTitle = $lodgingTranslation?->titulo ?: ui_text('ui.where_stay.local_stay_title');
    $lodgingContent = $lodgingTranslation?->conteudo ?: $pagina->texto_hospedagem_local;

    $listingEyebrow = $listingTranslation?->eyebrow ?: ui_text('ui.where_stay.selection_eyebrow');
    $listingTitle = $listingTranslation?->titulo ?: ui_text('ui.where_stay.selection_title');
    $listingSubtitle = $listingTranslation?->lead ?: ui_text('ui.where_stay.subtitle');

    $tipsEyebrow = $tipsTranslation?->eyebrow ?: 'Dicas';
    $tipsTitle = $tipsTranslation?->titulo ?: ui_text('ui.where_stay.tips_title');
    $tipsContent = $tipsTranslation?->conteudo ?: $pagina->texto_dicas;

    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.where_stay.empty_title');
    $emptyCopy = $emptyTranslation?->lead ?: ui_text('ui.where_stay.empty_copy');
@endphp

<div class="site-page site-page-shell site-where-stay-page">
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
            'page' => 'site.onde_ficar',
            'key' => 'hero',
            'label' => 'Texto da capa de Onde ficar',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.onde_ficar',
            'key' => 'hero',
            'label' => 'Imagem da capa de Onde ficar',
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
                            'editorPage' => 'site.onde_ficar',
                            'editorKey' => 'intro_section',
                            'editorLabel' => 'Seção de introdução de Onde ficar',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'conteudo'],
                            'editableTranslation' => $introTranslation,
                            'editableStatus' => $whereStayBlocks['intro_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.common.about'),
                                'titulo' => ui_text('ui.where_stay.intro_title'),
                                'conteudo' => $pagina->texto_intro,
                            ],
                        ])
                        <x-section-head :eyebrow="$introEyebrow" :title="$introTitle" />
                        <div class="site-prose">{!! nl2br(e($introContent)) !!}</div>
                    </section>
                @endif

                @if($lodgingContent)
                    <section class="site-surface-soft site-content-block">
                        @include('site.partials._content_editor', [
                            'editorTitle' => $lodgingTitle,
                            'editorPage' => 'site.onde_ficar',
                            'editorKey' => 'lodging_section',
                            'editorLabel' => 'Seção de hospedagem local de Onde ficar',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['eyebrow', 'titulo', 'conteudo'],
                            'editableTranslation' => $lodgingTranslation,
                            'editableStatus' => $whereStayBlocks['lodging_section']?->status ?? 'publicado',
                            'editableFallback' => [
                                'eyebrow' => ui_text('ui.where_stay.local_stay_eyebrow'),
                                'titulo' => ui_text('ui.where_stay.local_stay_title'),
                                'conteudo' => $pagina->texto_hospedagem_local,
                            ],
                        ])
                        <x-section-head :eyebrow="$lodgingEyebrow" :title="$lodgingTitle" />
                        <div class="site-prose">{!! nl2br(e($lodgingContent)) !!}</div>
                    </section>
                @endif
            </div>
        </div>
    </section>

    <section id="hospedagens" class="site-section">
        @if($createCompanyHref)
            <div class="site-inline-actions">
                <a href="{{ $createCompanyHref }}" class="site-button-primary">Cadastrar empresa</a>
            </div>
        @endif

        @include('site.partials._content_editor', [
            'editorTitle' => $listingTitle,
            'editorPage' => 'site.onde_ficar',
            'editorKey' => 'listing_section',
            'editorLabel' => 'Seção de empresas de Onde ficar',
            'editorLocale' => route_locale(),
            'editorTriggerVariant' => 'inline-compact',
            'editorTriggerLabel' => 'Editar texto',
            'editorFields' => ['eyebrow', 'titulo', 'lead'],
            'editableTranslation' => $listingTranslation,
            'editableStatus' => $whereStayBlocks['listing_section']?->status ?? 'publicado',
            'editableFallback' => [
                'eyebrow' => ui_text('ui.where_stay.selection_eyebrow'),
                'titulo' => ui_text('ui.where_stay.selection_title'),
                'lead' => ui_text('ui.where_stay.subtitle'),
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
                    'editorPage' => 'site.onde_ficar',
                    'editorKey' => 'empty_state',
                    'editorLabel' => 'Estado vazio de Onde ficar',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['titulo', 'lead'],
                    'editableTranslation' => $emptyTranslation,
                    'editableStatus' => $whereStayBlocks['empty_state']?->status ?? 'publicado',
                    'editableFallback' => [
                        'titulo' => ui_text('ui.where_stay.empty_title'),
                        'lead' => ui_text('ui.where_stay.empty_copy'),
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
                        $contatos = is_array($empresa->contatos ?? null) ? $empresa->contatos : [];
                        $whatsapp = $contatos['whatsapp'] ?? null;
                        $maps = $empresa->maps_url ?? ($contatos['maps'] ?? null);
                        $site = $empresa->site_url ?? ($contatos['site'] ?? null);
                        $email = $empresa->email ?? ($contatos['email'] ?? null);
                        $descricao = $item->observacao_curta ?: strip_tags((string) $empresa->descricao);
                        $urlEmpresa = Route::has('site.empresa') ? localized_route('site.empresa', ['empresa' => $empresa->slug ?: $empresa->id]) : '#';
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
                                    <span class="site-badge">{{ ui_text('ui.where_stay.featured_badge') }}</span>
                                @endif
                                @foreach(collect($empresa->categorias ?? [])->take(3) as $categoria)
                                    <span class="site-badge">{{ $categoria->nome }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="site-directory-card-body">
                            <div>
                                <h3 class="site-directory-card-title">{{ $empresa->nome }}</h3>
                                <p class="site-directory-card-subtitle">{{ collect([$empresa->bairro, $empresa->cidade])->filter()->implode(' - ') ?: ui_text('ui.common.altamira') }}</p>
                                <p class="site-inline-meta">{{ ui_text('ui.where_stay.official_curation') }}</p>
                            </div>

                            @if($descricao)
                                <p class="site-directory-card-summary">{{ \Illuminate\Support\Str::limit($descricao, 150) }}</p>
                            @endif

                            <div class="site-directory-card-actions">
                                <a href="{{ $urlEmpresa }}" class="site-button-primary">{{ ui_text('ui.explore.view_company') }}</a>
                                @if($maps)
                                    <a href="{{ $maps }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">{{ ui_text('ui.common.open_map') }}</a>
                                @endif
                                @if($whatsapp)
                                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">WhatsApp</a>
                                @endif
                            </div>

                            @if($site || $email)
                                <div class="site-directory-card-footer">
                                    @if($site)
                                        <a href="{{ $site }}" target="_blank" rel="noopener noreferrer" class="site-link">Site</a>
                                    @endif
                                    @if($email)
                                        <a href="mailto:{{ $email }}" class="site-link">{{ $email }}</a>
                                    @endif
                                </div>
                            @endif
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
                    'editorPage' => 'site.onde_ficar',
                    'editorKey' => 'tips_section',
                    'editorLabel' => 'Seção de dicas de Onde ficar',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'conteudo'],
                    'editableTranslation' => $tipsTranslation,
                    'editableStatus' => $whereStayBlocks['tips_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => 'Dicas',
                        'titulo' => ui_text('ui.where_stay.tips_title'),
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
