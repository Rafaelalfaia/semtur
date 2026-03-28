@extends('site.layouts.app')

@section('title', $pagina->titulo . ' - Visit Altamira')
@section('meta.description', \Illuminate\Support\Str::limit(strip_tags((string) ($pagina->seo_description ?: $pagina->resumo)), 160))
@section('meta.image', $pagina->hero_url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;

    $hero = $pagina->hero_url ?: asset('imagens/altamira.jpg');
    $empresas = collect($pagina->empresasSelecionadas ?? [])->filter(fn ($item) => $item && $item->empresa)->values();
    $explorarUrl = Route::has('site.explorar') ? route('site.explorar') : '#';
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => $explorarUrl,
        'breadcrumbs' => [
            ['label' => 'Início', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Explorar', 'href' => $explorarUrl],
            ['label' => $pagina->titulo],
        ],
        'badge' => $pagina->subtitulo ?: 'Sabores locais',
        'title' => $pagina->titulo,
        'subtitle' => $pagina->resumo ?: 'Curadoria editorial para descobrir Altamira pela mesa, pelos sabores e pelos lugares que acolhem bem.',
        'meta' => [
            $empresas->count().' empresas',
            'Curadoria oficial',
        ],
        'primaryActionLabel' => 'Ver empresas',
        'primaryActionHref' => '#empresas',
        'secondaryActionLabel' => 'Ver mais lugares',
        'secondaryActionHref' => $explorarUrl,
        'image' => $hero,
        'imageAlt' => $pagina->titulo,
    ])

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                @if($pagina->texto_intro)
                    <section class="site-surface site-content-block">
                        <x-section-head eyebrow="Introdução" title="Comer bem também é conhecer a cidade" />
                        <div class="site-prose">{!! nl2br(e($pagina->texto_intro)) !!}</div>
                    </section>
                @endif

                @if($pagina->texto_gastronomia_local)
                    <section class="site-surface-soft site-content-block">
                        <x-section-head eyebrow="Gastronomia local" title="Sabores que representam Altamira" />
                        <div class="site-prose">{!! nl2br(e($pagina->texto_gastronomia_local)) !!}</div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Resumo" title="Visão geral" />
                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">Empresas</span>
                            <span class="site-stat-value">{{ $empresas->count() }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">Perfil</span>
                            <span class="site-stat-value">Gastronomia</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <section id="empresas" class="site-section">
        <x-section-head
            eyebrow="Curadoria"
            title="Onde comer em Altamira"
            subtitle="Empresas publicadas e selecionadas para quem quer combinar boa experiência, contexto local e praticidade."
        />

        @if($empresas->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-title">Curadoria em atualização</p>
                <p class="site-empty-state-copy">A curadoria gastronômica de Altamira ainda está sendo preparada.</p>
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
                                    <span class="site-badge">Destaque</span>
                                @endif

                                @foreach(($empresa->categorias ?? collect())->take(2) as $categoria)
                                    <span class="site-badge">{{ $categoria->nome }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="site-directory-card-body">
                            <div>
                                <h3 class="site-directory-card-title">{{ $empresa->nome }}</h3>
                                <p class="site-directory-card-subtitle">{{ collect([$empresa->bairro, $empresa->cidade])->filter()->implode(' • ') ?: 'Altamira' }}</p>
                                <p class="site-inline-meta">Seleção editorial para quem quer comer bem sem perder o contexto da viagem.</p>
                            </div>

                            <p class="site-directory-card-summary">
                                {{ \Illuminate\Support\Str::limit($item->observacao_curta ?: strip_tags((string) $empresa->descricao), 140) }}
                            </p>

                            <div class="site-directory-card-actions">
                                <a href="{{ route('site.empresa', ['empresa' => $empresa->slug ?: $empresa->id]) }}" class="site-button-primary">Ver empresa</a>
                                @if($empresa->maps_url)
                                    <a href="{{ $empresa->maps_url }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">Ver rota</a>
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
                <x-section-head eyebrow="Dicas" title="Como aproveitar melhor a experiência" />
                <div class="site-prose">{!! nl2br(e($pagina->texto_dicas)) !!}</div>
            </div>
        </section>
    @endif
</div>
@endsection
