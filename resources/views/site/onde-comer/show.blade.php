@extends('site.layouts.app')

@section('title', ($pagina->seo_title ?: $pagina->titulo ?: ui_text('ui.where_eat.title')) . ' - Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($pagina->seo_description ?: $pagina->resumo ?: ui_text('ui.where_eat.subtitle'))), 160))
@section('meta.image', $pagina->hero_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $hero = $pagina->hero_url ?: asset('imagens/altamira.jpg');
    $empresas = collect($pagina->empresasSelecionadas ?? [])->filter(fn ($item) => $item && $item->empresa)->values();
    $explorarUrl = localized_route('site.explorar');
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => $explorarUrl,
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.nav.explore'), 'href' => $explorarUrl],
            ['label' => $pagina->titulo],
        ],
        'badge' => $pagina->subtitulo ?: ui_text('ui.where_eat.badge'),
        'title' => $pagina->titulo,
        'subtitle' => $pagina->resumo ?: ui_text('ui.where_eat.subtitle'),
        'meta' => [
            $empresas->count().' '.ui_text('ui.where_eat.businesses_label'),
            ui_text('ui.where_eat.official_curation'),
        ],
        'primaryActionLabel' => ui_text('ui.where_eat.view_businesses'),
        'primaryActionHref' => '#empresas',
        'secondaryActionLabel' => ui_text('ui.common.view_more'),
        'secondaryActionHref' => $explorarUrl,
        'image' => $hero,
        'imageAlt' => $pagina->titulo,
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                @if($pagina->texto_intro)
                    <section class="site-surface site-content-block">
                        <x-section-head :eyebrow="ui_text('ui.common.about')" :title="ui_text('ui.where_eat.intro_title')" />
                        <div class="site-prose">{!! nl2br(e($pagina->texto_intro)) !!}</div>
                    </section>
                @endif

                @if($pagina->texto_gastronomia_local)
                    <section class="site-surface-soft site-content-block">
                        <x-section-head :eyebrow="ui_text('ui.where_eat.local_flavors_eyebrow')" :title="ui_text('ui.where_eat.local_flavors_title')" />
                        <div class="site-prose">{!! nl2br(e($pagina->texto_gastronomia_local)) !!}</div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head :eyebrow="ui_text('ui.common.summary')" :title="ui_text('ui.common.general_overview')" />
                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">{{ ui_text('ui.where_eat.businesses_label') }}</span>
                            <span class="site-stat-value">{{ $empresas->count() }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">{{ ui_text('ui.where_eat.profile_label') }}</span>
                            <span class="site-stat-value">{{ ui_text('ui.where_eat.profile_value') }}</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <section id="empresas" class="site-section">
        <x-section-head
            :eyebrow="ui_text('ui.where_eat.curation_eyebrow')"
            :title="ui_text('ui.where_eat.curation_title')"
            :subtitle="ui_text('ui.where_eat.subtitle')"
        />

        @if($empresas->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-title">{{ ui_text('ui.where_eat.empty_title') }}</p>
                <p class="site-empty-state-copy">{{ ui_text('ui.where_eat.empty_copy') }}</p>
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

    @if($pagina->texto_dicas)
        <section class="site-section">
            <div class="site-surface site-content-block">
                <x-section-head eyebrow="Dicas" :title="ui_text('ui.where_eat.tips_title')" />
                <div class="site-prose">{!! nl2br(e($pagina->texto_dicas)) !!}</div>
            </div>
        </section>
    @endif
</div>
@endsection
